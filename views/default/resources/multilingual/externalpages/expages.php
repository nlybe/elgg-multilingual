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
		'text' => elgg_echo('edit'),
		'href' => "admin/configure_utilities/{$admin_page}?type={$type}",
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

// build page components
$title = elgg_echo("expages:$type");

$object = elgg_get_entities([
	'type' => 'object',
	'subtype' => $type,
	'limit' => 1,
]);

if (MultilingualOptions::isSitePagesActive()) {		// multilingual
	if (!$object) {
		$description = elgg_echo('expages:notset');
	}
	else {
		$field_name = 'description_'.get_current_language();
		$description = $object[0]->$field_name;
		if (!$description) {
			$description = $object[0]->description?$object[0]->description:elgg_echo('expages:notset');
		}
	} 
}
else {
	$description = $object ? $object[0]->description : elgg_echo('expages:notset');
}

$description = elgg_view('output/longtext', ['value' => $description]);

$content = elgg_view('expages/wrapper', [
	'content' => $description,
]);

// build page
$shell = 'default';
if (elgg_get_config('walled_garden') && !elgg_is_logged_in()) {
	$shell = 'walled_garden';
}

$body = elgg_view_layout('default', [
	'content' => $content,
	'title' => $title,
	'sidebar' => false,
]);

// draw page
echo elgg_view_page($title, $body, $shell);
