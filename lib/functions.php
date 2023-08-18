<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\MultilingualOptions;
 
/**
 * Recurses the page tree and adds the breadcrumbs for all ancestors
 * Replaces the pages_prepare_parent_breadcrumbs() from pages plugin
 *
 * @param ElggPage $page Page entity
 *
 * @return void
 */
function ml_pages_prepare_parent_breadcrumbs($page) {
	$crumbs = [];

	while ($page instanceof ElggPage) {
		$crumbs[] = [
			// 'text' => $page->getDisplayName(),
			'text' => MultilingualOptions::getFieldValue('title', $page),
			'href' => $page->getURL(),
		];
		$page = $page->getParentEntity();
	}

	array_shift($crumbs);
	$crumbs = array_reverse($crumbs);

	foreach ($crumbs as $crumb) {
		elgg_push_breadcrumb($crumb['text'], $crumb['href']);
	}
}