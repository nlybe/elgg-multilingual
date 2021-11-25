<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * View a revision of page
 */

use Multilingual\MultilingualOptions;

$id = elgg_extract('id', $vars);
$annotation = elgg_get_annotation_from_id($id);
if (!$annotation instanceof ElggAnnotation) {
	throw new \Elgg\EntityNotFoundException();
}

$page = get_entity($annotation->entity_guid);
if (!$page instanceof ElggPage) {
	throw new \Elgg\EntityNotFoundException();
}

elgg_entity_gatekeeper($page->container_guid);

elgg_set_page_owner_guid($page->getContainerGUID());

// $title = "{$page->getDisplayName()}: " . elgg_echo('pages:revision');
$title_txt = MultilingualOptions::getFieldValue('title', $page);
$title = "{$title_txt}: " . elgg_echo('pages:revision');

elgg_push_collection_breadcrumbs('object', 'page', $page->getContainerEntity());

ml_pages_prepare_parent_breadcrumbs($page);
// elgg_push_breadcrumb($page->getDisplayName(), $page->getURL());
elgg_push_breadcrumb(MultilingualOptions::getFieldValue('title', $page), $page->getURL());
elgg_push_breadcrumb(elgg_echo('pages:history'), elgg_generate_url('history:object:page', ['guid' => $page->guid]));

echo elgg_view_page($title, [
	'content' => elgg_view_entity($page, [
		'revision' => $annotation,
	]),
]);
