<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * History of revisions of a page
 */

use Multilingual\MultilingualOptions;

$page_guid = elgg_extract('guid', $vars);

elgg_entity_gatekeeper($page_guid, 'object', 'page');

$page = get_entity($page_guid);

$container = $page->getContainerEntity();
if (!$container) {
	throw new EntityNotFoundException();
}

elgg_set_page_owner_guid($container->getGUID());

elgg_push_collection_breadcrumbs('object', 'page', $container);

ml_pages_prepare_parent_breadcrumbs($page);

// elgg_push_breadcrumb($page->getDisplayName(), $page->getURL());
elgg_push_breadcrumb(MultilingualOptions::getFieldValue('title', $page), $page->getURL());

// $title = "{$page->getDisplayName()}: " . elgg_echo('pages:history');
$title_txt = MultilingualOptions::getFieldValue('title', $page);
$title = "{$title_txt}: " . elgg_echo('pages:history');

$current_lang = get_current_language();
$annotation_name = 'page_'.$current_lang;
if (MultilingualOptions::checkIfDefaultLang($current_lang)) {
	$annotation_name = 'page';
}

$content = elgg_list_annotations([
	'guid' => $page_guid,
	'annotation_name' => $annotation_name,
	'limit' => max(20, elgg_get_config('default_limit')),
	'order_by' => [
		new \Elgg\Database\Clauses\OrderByClause('n_table.time_created', 'desc'),
		new \Elgg\Database\Clauses\OrderByClause('n_table.id', 'desc'),
	],
	'no_results' => elgg_echo('multilingual:pages:history:none'),
]);

echo elgg_view_page($title, [
	'content' => $content,
]);
