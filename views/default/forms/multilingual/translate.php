<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\MultilingualOptions;

elgg_require_css('multilingual/jquery-ui/1.12.1/themes/base/jquery-ui.css');
elgg_require_js('multilingual/translate_form');

$entity = elgg_extract('entity', $vars, '');
if (!$entity instanceof ElggObject) {
    return;
}

$translate_status = MultilingualOptions::getSubtypeForTranslation($entity->getSubtype());
if (!$translate_status) {
    return;
}


$active_langs = MultilingualOptions::ml_language_selector_get_allowed_translations();
foreach ($active_langs as $key_l => $value_l) {
    $output .= elgg_format_element('h3', [], $value_l);
    
    $lang_output = '';
    foreach ($translate_status as $k => $v) {
        $field_name = "${k}_${key_l}";
        if (elgg_get_config('language') == $key_l) {
            $field_name = "${k}";
        }
    
        $lang_output .= elgg_view_field([
            '#type' => $v['type'],
            'name' => $field_name,
            'value' => $entity->$field_name,
            '#label' => elgg_echo($v['label']),
        ]);
    }
    $output .= elgg_format_element('div', [], $lang_output);
}

echo elgg_format_element('div', ['id' => 'translate_acc'], $output);

$hf_output .= elgg_view_field([
    '#type' => 'hidden',
    'name' => 'guid',
    'value' => $entity->guid,
]);

$hf_output .= elgg_view('input/submit', array(
	'value' => elgg_echo('save'),
	'name' => 'save',
));

echo elgg_format_element('div', ['class' => 'elgg-foot'], $hf_output);
