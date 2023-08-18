<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\MultilingualOptions;

$type = elgg_extract('expage', $vars);
$type = strtolower($type);

$admin_page = MultilingualOptions::isSitePagesActive()?"ml_expages":"expages";		// multilingual
// admin edit menu item
if (elgg_is_admin_logged_in()) {
	elgg_register_menu_item('title', [
		'name' => 'edit',
		'icon' => 'edit',
		'text' => elgg_echo('edit'),
		'href' => "admin/configure_utilities/{$admin_page}?type={$type}",
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

$objects = elgg_get_entities([
	'type' => 'object',
	'subtype' => $type,
	'limit' => 1,
]);

$object = $objects ? $objects[0] : null;

if (MultilingualOptions::isSitePagesActive()) {		// multilingual
	if (!$object) {
		$description = elgg_echo('expages:notset');
	}
	else {
		$field_name = 'description_'.get_current_language();
		$description = $object->$field_name;
		if (!$description) {
			$description = $object->description?$object->description:elgg_echo('expages:notset');
		}
	} 
}
else {
	$description = $object ? $object->description : elgg_echo('expages:notset');
}

$description = elgg_view('output/longtext', ['value' => $description]);

// build page
$shell = 'default';
if (elgg_get_config('walled_garden') && !elgg_is_logged_in()) {
	$shell = 'walled_garden';
}

// draw page
echo elgg_view_page(elgg_echo("expages:{$type}"), [
	'content' => elgg_view('expages/wrapper', [
		'content' => $description,
	]),
	'sidebar' => false,
	'entity' => $object,
], $shell);
