<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * Edit a page
 */

use Multilingual\MultilingualOptions;

$page_guid = (int) elgg_extract('guid', $vars);

elgg_entity_gatekeeper($page_guid, 'object', 'page', true);

/* @var $page \ElggPage */
$page = get_entity($page_guid);

$container = $page->getContainerEntity();
if ($container) {
	elgg_push_collection_breadcrumbs('object', 'page', $container);
}

ml_pages_prepare_parent_breadcrumbs($page);
// elgg_push_breadcrumb($page->getDisplayName(), $page->getURL());
elgg_push_breadcrumb(MultilingualOptions::getFieldValue('title', $page), $page->getURL());

echo elgg_view_page(elgg_echo('edit:object:page'), [
	'content' => elgg_view_form('pages/edit', ['sticky_enabled' => true], [
		'entity' => $page,
		'parent_guid' => $page->getParentGUID(),
	]),
	'filter_id' => 'pages/edit',
]);
