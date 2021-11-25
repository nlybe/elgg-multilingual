<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\MultilingualOptions;

elgg_gatekeeper();

$guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', null, true);

$entity = get_entity($guid);
$container = $entity->getContainerEntity();
elgg_set_page_owner_guid($container->getGUID());

// elgg_push_breadcrumb($container->title, $entity->getURL());
elgg_push_breadcrumb($entity->title, $entity->getURL());


$title = elgg_echo("multilingual:translations:edit:title", [$entity->title]);

// $vars = pages_prepare_form_vars($page, $page->parent_guid);
// $subtype = $entity->getSubtype();
$vars = MultilingualOptions::translatePrepareFormVars($entity, $entity->getContainerGUID());
$content = elgg_view_form('multilingual/translate', [], $vars);	

$body = elgg_view_layout('default', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);
