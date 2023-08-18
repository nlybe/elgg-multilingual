<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * View a single page
 */

use Multilingual\MultilingualOptions;

$guid = elgg_extract('guid', $vars);

elgg_entity_gatekeeper($guid, 'object', 'page');

/* @var $page ElggPage */
$page = get_entity($guid);

$container = $page->getContainerEntity();
if (!$container) {
	throw new EntityNotFoundException();
}

elgg_push_collection_breadcrumbs('object', 'page', $container);
ml_pages_prepare_parent_breadcrumbs($page);

// can add subpage if can edit this page and write to container (such as a group)
if ($page->canEdit() && $container->canWriteToContainer(0, 'object', 'page')) {
	elgg_register_menu_item('title', [
		'name' => 'subpage',
		'href' => elgg_generate_url('add:object:page', [
			'guid' => $page->guid,
		]),
		'text' => elgg_echo('pages:newchild'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

// echo elgg_view_page($page->getDisplayName(), [
echo elgg_view_page(MultilingualOptions::getFieldValue('title', $page), [	
	'content' => elgg_view_entity($page, [
		'show_responses' => true,
	]),
	'sidebar' => elgg_view('pages/sidebar/navigation', [
		'page' => $page,
	]),
	'entity' => $page,
], 'default', [
	'entity' => $page,
]);
