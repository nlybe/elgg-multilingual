<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * Elgg external pages: create or update
 */

use Multilingual\MultilingualOptions;

// Get input data and don't filter the content
$contents = get_input('expagescontent', '', false);
$subtype = get_input('content_type');
$guid = (int) get_input('guid');

if (empty($contents) || empty($subtype)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if ($guid) {
	// update
	$expages = get_entity($guid);
	if (!$expages) {
		return elgg_error_response(elgg_echo('expages:error'));
	}
} else {
	// create
	$expages = new \ElggObject();
	$expages->setSubtype($subtype);
}

$expages->owner_guid = elgg_get_logged_in_user_guid();
$expages->access_id = ACCESS_PUBLIC;
$expages->title = $subtype;
$expages->description = $contents;	// default language

$active_langs = MultilingualOptions::ml_language_selector_get_allowed_translations();
foreach ($active_langs as $key => $value) {
	if (elgg_get_config('language') != $key) {
		$lang_description = "description_".$key;
		$expages->$lang_description = get_input('expagescontent_'.$key, '', false);
	}
}

if (!$expages->save()) {
	return elgg_error_response(elgg_echo('expages:error'));
}

if (get_input('header_remove')) {
	$expages->deleteIcon('header');
} else {
	$expages->saveIconFromUploadedFile('header', 'header');
}

return elgg_ok_response('', elgg_echo('expages:posted'));
