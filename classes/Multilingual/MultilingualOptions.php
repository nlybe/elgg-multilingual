<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

namespace Multilingual; 

class MultilingualOptions {

    const PLUGIN_ID = 'multilingual';    // current plugin ID
    const ML_YES = 'yes';  // general purpose yes
    const ML_NO = 'no';    // general purpose no

    /**
     * Get param value from settings
     * 
     * @return type
     */
    Public Static function getParams($setting_param = ''){
        if (!$setting_param) {
            return false;
        }
        
        return trim(elgg_get_plugin_setting($setting_param, self::PLUGIN_ID)); 
    } 

    /**
     * Check multilingual site pages (externalpages plugin) is active in settings
     * 
     * @return boolean
     */
    Public Static function isSitePagesActive() {
        if (!elgg_is_active_plugin('externalpages')) {
            return false;
        }

        $get_param = trim(elgg_get_plugin_setting('ml_externalpages', self::PLUGIN_ID));
        if ($get_param === self::ML_YES) {
            return true;
        }
        
        return false;
    }

    /**
     * Check multilingual pages (pages plugin) is active in settings
     * 
     * @return boolean
     */
    Public Static function isPagesActive() {
        if (!elgg_is_active_plugin('pages')) {
            return false;
        }

        $get_param = trim(elgg_get_plugin_setting('ml_pages', self::PLUGIN_ID));
        if ($get_param === self::ML_YES) {
            return true;
        }
            
        return false;
    }
        
    /**
     * Get the allowed languages/translation
     * 
     * Returns the results in array such as langs_arr['lang_key'] = 'lang_label'
     * 
     * @return array
     */
    Public Static function ml_language_selector_get_allowed_translations() {
        $allowed = [];
        // $allowed = elgg_get_available_languages();
        $allowed = elgg()->translator->getAllowedLanguages();
        
        $langs_arr = [];
        if (count($allowed) > 1 ) {
            foreach ($allowed as $k => $v) {
                $langs_arr[$v] = elgg_echo($v);
            }
        }
            
        return $langs_arr;
    }  
    
    /**
     * Function to register a subtype for translation with field to translate
     *
     * @param string $register_name: Name of the register where the fields are configured
     * @param array  $term_fields_to_translate: Array of options
     *
     * @return void
     */
    Public Static function setSubtypeForTranslation($register_name, $term_fields_to_translate) {
        global $SUBTYPE_FIELDS_TO_TRANSLATE;

        if (!is_array($term_fields_to_translate)) {
            $term_fields_to_translate = [];
        }
        
        if (!isset($SUBTYPE_FIELDS_TO_TRANSLATE)) {
            $SUBTYPE_FIELDS_TO_TRANSLATE = [];
        }
        if (!isset($SUBTYPE_FIELDS_TO_TRANSLATE[$register_name])) {
            $SUBTYPE_FIELDS_TO_TRANSLATE[$register_name] = $term_fields_to_translate;
        }
    }    

    /**
     * Returns the registered subtypes for translation
     *
     * @param string $register_name Name of the register to retrieve
     *
     * @return false|array
     */
    Public Static function getSubtypeForTranslation($register_name) {
        global $SUBTYPE_FIELDS_TO_TRANSLATE;
        
        if (isset($SUBTYPE_FIELDS_TO_TRANSLATE) && isset($SUBTYPE_FIELDS_TO_TRANSLATE[$register_name])) {
            return $SUBTYPE_FIELDS_TO_TRANSLATE[$register_name];
        }
        
        return false;
    }
    
    /**
     * Prepare the add/edit form variables
     *
     * @param ElggObject     $page
     * @param int            $parent_guid
     * @param ElggAnnotation $revision
     * @return array
     */
    Public Static function translatePrepareFormVars($entity = null, $container_guid = null) {
        // input names => defaults
        $values = [
            'title' => '',
            'description' => '',
            'tags' => '',
            'access_id' => ACCESS_DEFAULT,
            'container_guid' => $container_guid?$container_guid:elgg_get_page_owner_guid(),
            'entity' => $entity,
            'guid' => null,
            'comments_on' => NULL,
        ]; 
    
        if ($entity) {
            foreach (array_keys($values) as $field) {
                if (isset($entity->$field)) {
                    $values[$field] = $entity->$field;
                }
            }
        }
    
        if (elgg_is_sticky_form('publicity')) {
            $sticky_values = elgg_get_sticky_values('publicity');
            foreach ($sticky_values as $key => $value) {
                $values[$key] = $value;
            }
        }
    
        elgg_clear_sticky_form('publicity');
        
        return $values;
    }  
    
    /**
     * Returns the registered subtypes for translation
     *
     * @param string $field_name Name of the field
     * @param object $entity The entity need to be translated
     *
     * @return false|array
     */
    Public Static function getFieldTranslated($field_name = '', $entity = null) {
        if (!$field_name || !($entity instanceof \ElggEntity)) {
            return false;
        }
        
        $translate_status = MultilingualOptions::getSubtypeForTranslation($entity->getSubtype());
        if (!$translate_status) {
            return false;
        }
        
        $current_lang = elgg_get_current_language();
        $field_name_lang = "${field_name}_${current_lang}";        
        $active_langs = self::ml_language_selector_get_allowed_translations();
        foreach ($active_langs as $key_l => $value_l) {
            if ($current_lang == $key_l && isset($entity->$field_name_lang)) {
                return $entity->$field_name_lang;
            }
        }

        return $entity->$field_name;
    }
    
    /**
     * Return the original field value or the translated
     *
     * @param string $field_name the name of the field
     * @param string $default_value an optional but specific value should be returned if no translation found, otherwise it return the value of the object's field
     * @param object $entity The entity need to be translated
     *
     * @return string
     */
    Public Static function getFieldValue($field_name = '', $entity = null, $annotation_value = '') {
        if (!$entity) {
            return '';
        }

        if ($annotation_value) {
            return $annotation_value;
        }
        
        if ($val = self::getFieldTranslated($field_name, $entity)) {
            return $val;
        }
        
        return $entity->$field_name;
    }
    
    /**
     * Check if given lang is the default site langauge as has been set in settings
     *
     * @param string $lang the lang to check for
     *
     * @return string
     */
    Public Static function checkIfDefaultLang($lang = '') {
        if (elgg_get_config('language') == $lang) {
            return true;;
        }

        return false;
    }    
}
