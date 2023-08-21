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
        $current_lang = elgg_get_current_language();
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

/**
 * Add option to edit translations, if enabled for subtype
 * 
 * @param \Elgg\Event $event
 * @return array
 */
function ml_entity_menu_setup(\Elgg\Event $event) {
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        return;
    }

    $return = $event->getValue();
    $entity = $event->getEntityParam();
    if (!$entity || !$entity->canEdit()) {
        return;
	}

	$translate_status = MultilingualOptions::getSubtypeForTranslation($entity->getSubtype());
    if ($translate_status)    {        
        $return[] = \ElggMenuItem::factory([
            'name' => 'edit_translation',
            'icon' => 'globe',
			'text' => elgg_echo("multilingual:translations:edit"),
			'href' => elgg_generate_url('translation:subtype:edit', [
                'guid' => $entity->guid,
            ]), 
	    ]);
    }       

    return $return;
}

/**
 * 
 * Replace the admin/configure_utilities/expages view
 *
 * @param \Elgg\Event $event
 * @return string
 */

function ml_admin_expages_view(\Elgg\Event $event) {    
    $return = $event->getValue();    
    $view = $event->getParam('view');
    $check_event = ($view == 'admin/configure_utilities/expages');
    if (!$check_event) {
        return $return;
    }

    $params = $event->getParams();
    return elgg_view('admin/configure_utilities/ml_expages', $params['vars']);
}

 /**
 * 
 * Replace the page view
 *
 * @param \Elgg\Event $event
 * @return string
 */

function ml_object_view(\Elgg\Event $event) {    
    $return = $event->getValue();    
    $view = $event->getParam('view');
    $check_event = ($view == 'object/page');
    if (!$check_event) {
        return $return;
    }

    $params = $event->getParams();
    $entity = $params['vars']['entity'];
    if (!$entity instanceof ElggPage) {
        return $return;
    }

    return elgg_view('multilingual/object/page', $params['vars']);
}

 /**
 * Replace annotation_page_view from pages plugin
 * 
 * @param \Elgg\Event $event
 * @return string
 */
function ml_annotation_page_view(\Elgg\Event $event) {
    $return = $event->getValue();    
    $view = $event->getParam('view');
    $check_event = ($view == 'annotation/page');
    if (!$check_event) {
        return $return;
    }
    
    $params = $event->getParams();
    return elgg_view('multilingual/annotation/page', $params['vars']);
}

/**
 * Replace registerPageMenuItems from pages plugin
 * 
 * Register menu items for pages_nav menu
 *
 * @param \Elgg\Event $event 'register', 'menu:pages_nav'
 *
 * @return void|MenuItems
 */
function ml_registerPageMenuItems(\Elgg\Event $event) {
    $entity = $event->getEntityParam();
    if (!$entity instanceof \ElggPage) {
        return;
    }
    
    $return = $event->getValue();
    
    $top = $entity->getTopParentEntity();
            
    $next_level_guids = [$top->guid];
    while (!empty($next_level_guids)) {
        $children = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'page',
            'metadata_name_value_pairs' => [
                'name' => 'parent_guid',
                'value' => $next_level_guids,
                'operand' => 'IN',
            ],
            'batch' => true,
        ]);
        
        $next_level_guids = [];
        foreach ($children as $child) {
            $return[] = \ElggMenuItem::factory([
                'name' => $child->guid,
                'text' => MultilingualOptions::getFieldValue('title', $child),
                'href' => $child->getURL(),
                'parent_name' => $child->getParentGUID(),
            ]);
            
            $next_level_guids[] = $child->guid;
        }
    }
    
    if (count($return) < 1) {
        return;
    }
    
    $return[] = \ElggMenuItem::factory([
        'name' => $top->guid,
        'text' => MultilingualOptions::getFieldValue('title', $top),
        'href' => $top->getURL(),
        'parent_name' => $top->getParentGUID(),
    ]);
    
    return $return;
}

/**
 * Replace pages_prepare_notification from pages plugin
 * 
 * Prepare a notification message about a new page
 *
 * @param \Elgg\Event $event 'prepare', 'notification:create:object:page' | 'notification:create:object:page_top'
 *
 * @return void|Elgg\Notifications\Notification
 */
function ml_pages_prepare_notification(\Elgg\Event $event) {
	
	$event = $event->getParam('event');
	
	$entity = $event->getObject();
	if (!$entity instanceof ElggPage) {
		return;
	}
	
	$owner = $event->getActor();
	$language = $event->getParam('language');

	$descr = $entity->description;
	$title = MultilingualOptions::getFieldValue('title', $entity);

	$notification = $event->getValue();
	$notification->subject = elgg_echo('pages:notify:subject', [$title], $language);
	$notification->body = elgg_echo('pages:notify:body', [
		$owner->getDisplayName(),
		$title,
		$descr,
		$entity->getURL(),
	], $language);
	$notification->summary = elgg_echo('pages:notify:summary', [MultilingualOptions::getFieldValue('title', $entity)], $language);
	$notification->url = $entity->getURL();
	
	return $notification;
}

/**
 * Override the page annotation url by adding annotation for active languages check
 *
 * @param \Elgg\Event $event 'extender:url', 'annotation'
 *
 * @return void|string
 */
function ml_pages_set_revision_url(\Elgg\Event $event) {
	
	$annotation = $event->getParam('extender');
	if ($annotation->getSubtype() == 'page') {
		return elgg_generate_url('revision:object:page', [
			'id' => $annotation->id,
		]);
    }
    else {
        $active_langs = MultilingualOptions::ml_language_selector_get_allowed_translations();
        foreach ($active_langs as $key => $value) {
            if ($annotation->getSubtype() == 'page_'.$key) {
                return elgg_generate_url('revision:object:page', [
                    'id' => $annotation->id,
                ]);
            }
        }
    }
}