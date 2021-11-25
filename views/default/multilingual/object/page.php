<?php
/**
 * View for page object
 *
 * @uses $vars['entity']   The page object
 * @uses $vars['revision'] This parameter not supported by elgg_view_entity()
 */

use Multilingual\MultilingualOptions;

$entity = elgg_extract('entity', $vars);
$revision = elgg_extract('revision', $vars, false);
if (!$entity instanceof ElggPage) {
	return;
}

// pages used to use Public for write access
if ($entity->write_access_id == ACCESS_PUBLIC) {
	// this works because this metadata is public
	$entity->write_access_id = ACCESS_LOGGED_IN;
}

if ($revision) {
	$annotation = $revision;
} else {
	$current_lang = get_current_language();
	$annotation_name = 'page_'.$current_lang;
	if (MultilingualOptions::checkIfDefaultLang($current_lang)) {
		$annotation_name = 'page';
	}
	$annotation = $entity->getAnnotations([
		'annotation_name' => $annotation_name,
		'limit' => 1,
		'order_by' => [
			new \Elgg\Database\Clauses\OrderByClause('n_table.time_created', 'desc'),
			new \Elgg\Database\Clauses\OrderByClause('n_table.id', 'desc'),
		],
	]);

	if (!$annotation) {
		$annotation = $entity->getAnnotations([
			'annotation_name' => 'page',
			'limit' => 1,
			'order_by' => [
				new \Elgg\Database\Clauses\OrderByClause('n_table.time_created', 'desc'),
				new \Elgg\Database\Clauses\OrderByClause('n_table.id', 'desc'),
			],
		]);
	}

	if ($annotation) {
		$annotation = $annotation[0];
	} else {
		elgg_log("Failed to access annotation for page with GUID {$entity->guid}", 'WARNING');
		return;
	}
}

if (!$annotation instanceof ElggAnnotation) {
	return;
}

$icon_entity = null;
$owner = $annotation->getOwnerEntity();
if ($owner) {
	$icon_entity = $owner;
}

$metadata = null;
// If we're looking at a revision, display annotation menu
if ($revision) {
	$metadata = elgg_view_menu('annotation', [
		'annotation' => $annotation,
		'class' => 'elgg-menu-hz float-alt',
	]);
}

if (elgg_extract('full_view', $vars)) {
	$body = elgg_view('output/longtext', ['value' => MultilingualOptions::getFieldValue('description', $entity, $annotation->value)]);
	
	$params = [
		'metadata' => $metadata,
		'show_summary' => true,
		'icon_entity' => $icon_entity,
		'body' => $body,
		'show_responses' => elgg_extract('show_responses', $vars, false),
	];

	$params = $params + $vars;
	echo elgg_view('object/elements/full', $params);
} else {
	// brief view
	$title = elgg_view('output/url', [
        'href' => $entity->getURL(), 
        'text' => MultilingualOptions::getFieldValue('title', $entity),
	]);
	
	$params = [
		'metadata' => $metadata,
		// 'content' => elgg_get_excerpt($entity->description),
		'content' => elgg_get_excerpt(MultilingualOptions::getFieldValue('description', $entity)),
		'title' => $title,
		'icon_entity' => $icon_entity,
	];
	$params = $params + $vars;
	echo elgg_view('object/elements/summary', $params);
}
