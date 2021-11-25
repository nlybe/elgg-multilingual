<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 *
 * All hooks are here
 */

use Multilingual\MultilingualOptions;
 
/**
 * Add option to edit translations, if enabled for subtype
 * 
 * @param \Elgg\Hook $hook
 * @return array
 */
function ml_entity_menu_setup(\Elgg\Hook $hook) {
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        return;
    }

    $return = $hook->getValue();
    $entity = $hook->getEntityParam();
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
 * @param \Elgg\Hook $hook
 * @return string
 */

function ml_admin_expages_view(\Elgg\Hook $hook) {    
    $return = $hook->getValue();    
    $view = $hook->getParam('view');
    $check_hook = ($view == 'admin/configure_utilities/expages');
    if (!$check_hook) {
        return $return;
    }

    $params = $hook->getParams();
    return elgg_view('admin/configure_utilities/ml_expages', $params['vars']);
}

 /**
 * 
 * Replace the page view
 *
 * @param \Elgg\Hook $hook
 * @return string
 */

function ml_object_view(\Elgg\Hook $hook) {    
    $return = $hook->getValue();    
    $view = $hook->getParam('view');
    $check_hook = ($view == 'object/page');
    if (!$check_hook) {
        return $return;
    }

    $params = $hook->getParams();
    $entity = $params['vars']['entity'];
    if (!$entity instanceof ElggPage) {
        return $return;
    }

    return elgg_view('multilingual/object/page', $params['vars']);
}

 /**
 * Replace annotation_page_view from pages plugin
 * 
 * @param \Elgg\Hook $hook
 * @return string
 */
function ml_annotation_page_view(\Elgg\Hook $hook) {
    $return = $hook->getValue();    
    $view = $hook->getParam('view');
    $check_hook = ($view == 'annotation/page');
    if (!$check_hook) {
        return $return;
    }
    
    $params = $hook->getParams();
    return elgg_view('multilingual/annotation/page', $params['vars']);
}

/**
 * Replace registerPageMenuItems from pages plugin
 * 
 * Register menu items for pages_nav menu
 *
 * @param \Elgg\Hook $hook 'register', 'menu:pages_nav'
 *
 * @return void|MenuItems
 */
function ml_registerPageMenuItems(\Elgg\Hook $hook) {
    $entity = $hook->getEntityParam();
    if (!$entity instanceof \ElggPage) {
        return;
    }
    
    $return = $hook->getValue();
    
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
 * @param \Elgg\Hook $hook 'prepare', 'notification:create:object:page' | 'notification:create:object:page_top'
 *
 * @return void|Elgg\Notifications\Notification
 */
function ml_pages_prepare_notification(\Elgg\Hook $hook) {
	
	$event = $hook->getParam('event');
	
	$entity = $event->getObject();
	if (!$entity instanceof ElggPage) {
		return;
	}
	
	$owner = $event->getActor();
	$language = $hook->getParam('language');

	$descr = $entity->description;
	$title = MultilingualOptions::getFieldValue('title', $entity);

	$notification = $hook->getValue();
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
 * @param \Elgg\Hook $hook 'extender:url', 'annotation'
 *
 * @return void|string
 */
function ml_pages_set_revision_url(\Elgg\Hook $hook) {
	
	$annotation = $hook->getParam('extender');
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