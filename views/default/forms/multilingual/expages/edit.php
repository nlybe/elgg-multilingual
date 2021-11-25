<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 * 
 * Edit form body for external pages
 * 
 * @uses $vars['type']
 */

use Multilingual\MultilingualOptions;

$active_langs = MultilingualOptions::ml_language_selector_get_allowed_translations();

$type = $vars['type'];

//grab the required entity
$page_contents = elgg_get_entities(array(
    'type' => 'object',
    'subtype' => $type,
    'limit' => 1,
));

if ($page_contents) {
    $description = $page_contents[0]->description;
    $guid = $page_contents[0]->guid;
} else {
    $description = "";
    $guid = 0;
}

// set the required form variables
$input_area_lang_others = '';
foreach ($active_langs as $key => $value) {
    if (elgg_get_config('language') == $key) {
        // set the required form variables
        $input_area_lang_default = elgg_view_field([
            '#type' => 'longtext',
            'name' => 'expagescontent',
            'value' => $description,
            '#label' => $value,
        ]);
    }
    else {
        $lang_description = "description_".$key;
        $description_lng = $page_contents[0]->$lang_description;
        $input_area_lang_others .= elgg_view_field([
            '#type' => 'longtext',
            'name' => 'expagescontent_'.$key,
            'value' => $description_lng,
            '#label' => $value,
        ]);            
        }
}

$submit_input = elgg_view('input/submit', [
	'name' => 'submit',
	'value' => elgg_echo('save'),
]);
$view_page = elgg_view('output/url', [
	'text' => elgg_echo('expages:edit:viewpage'),
	'href' => $type,
	'target' => '_blank',
	'class' => 'elgg-button elgg-button-action float-alt',
]);
$hidden_type = elgg_view('input/hidden', [
	'name' => 'content_type',
	'value' => $type,
]);
$hidden_guid = elgg_view('input/hidden', [
	'name' => 'guid',
	'value' => $guid,
]);

$external_page_title = elgg_echo("expages:$type");

//construct the form
echo <<<EOT
<div class="mtm">
    <label>$external_page_title</label>
    $input_area_lang_default
    $input_area_lang_others
</div>
<div class="elgg-foot">
$hidden_guid
$hidden_type
$view_page
$submit_input
</div>
EOT;

