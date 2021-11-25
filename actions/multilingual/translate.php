<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\MultilingualOptions;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof ElggEntity) {
    return elgg_error_response(elgg_echo('multilingual:translate:invalid_entity'));
}

$translate_status = MultilingualOptions::getSubtypeForTranslation($entity->getSubtype());
if (!$translate_status) {
    return elgg_error_response(elgg_echo('multilingual:translate:unregistered_subtype', [$entity->getSubtype()]));
}

$to_annotate = [];
$active_langs = MultilingualOptions::ml_language_selector_get_allowed_translations();
foreach ($active_langs as $key_l => $value_l) {
    foreach ($translate_status as $k => $v) {
        $default_lng = false;
        $field_name = "${k}_${key_l}";
        if (elgg_get_config('language') == $key_l) {
            // don't add the language prefix for default language
            $field_name = "${k}";
            $default_lng = true;
        }
	
		$entity->$field_name = get_input($field_name);

        // check if the value should be annotated
        if ($v['annotate']) {
            $annotate_key = $default_lng?$v['annotate']:$v['annotate']."_${key_l}";
            $to_annotate[$annotate_key] = $entity->$field_name; 
        }
    }
}

if (!$entity->save()) {
	return elgg_error_response(elgg_echo('multilingual:translate:save:fail', [$entity->title]));
}

// Now save "annotate" fields as an annotation
if (count($to_annotate) > 0) {
    foreach ($to_annotate as $k => $v) {
        $id = $entity->annotate($k, $v, $entity->access_id);
    }
}


return elgg_ok_response('', elgg_echo('multilingual:translate:save:success', [$entity->title]), $entity->getURL());