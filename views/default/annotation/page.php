<?php
/**
 * Revision view for history page
 */

use Multilingual\MultilingualOptions;

$annotation = elgg_extract('annotation', $vars);
if (!$annotation instanceof \ElggAnnotation) {
	return;
}

$page = $annotation->getEntity();
if (!$page instanceof ElggPage) {
	return;
}

$owner = $annotation->getOwnerEntity();
if (!$owner instanceof ElggEntity) {
	return;
}

// $title_link = elgg_view_url($annotation->getURL(), $page->getDisplayName());
$title_link = elgg_view('output/url', [
	'href' => $annotation->getURL(),
	'text' => MultilingualOptions::getFieldValue('title', $page),
	'is_trusted' => true,
]); 

$params = [
	'title' => elgg_format_element('h3', [], $title_link),
	'byline' => true,
	'content' => false,
];
$params = $params + $vars;

echo elgg_view('annotation/elements/summary', $params);
