<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 *
 * All events are here
 */
 
use Multilingual\MultilingualOptions;

/**
 * Check current language when create new page and duplicate annotation if other than default to the current language.
 * (since initial is create always to default language)
 *
 * @param \Elgg\Event $event 'delete', 'object'
 *
 * @return void
 */
function multilingual_page_create_object(\Elgg\Event $event) {
    $object = $event->getObject();
    if ($object->getSubtype() == 'page' && MultilingualOptions::isPagesActive()) {
        $current_lang = get_current_language();
        if (MultilingualOptions::checkIfDefaultLang($current_lang)) {
            // do nothing is current langauge is the default site language
            return true;
        }

        $translate_status = MultilingualOptions::getSubtypeForTranslation($object->getSubtype());
        $to_annotate = [];
        foreach ($translate_status as $k => $v) {
            $field_name = "${k}_${current_lang}";
            $object->$field_name = $object->$k;

            // check if the value should be annotated
            if ($v['annotate']) {
                $annotate_key = $v['annotate']."_${current_lang}";
                $to_annotate[$annotate_key] = $object->$field_name; 
            }
        }

        // Now save "annotate" fields as an annotation
        if (count($to_annotate) > 0) {
            foreach ($to_annotate as $k => $v) {
                $id = $object->annotate($k, $v, $object->access_id);
            }
        }

    }

    return true;
}