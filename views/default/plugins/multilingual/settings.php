<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */ 

use Multilingual\MultilingualOptions;
 
$plugin_id = elgg_get_plugin_from_id(MultilingualOptions::PLUGIN_ID);

// Pages
echo elgg_view_field([
    '#type' => 'checkbox',
    'name' => 'params[ml_pages]',
    'default' => 'no',
    'switch' => true,
    'value' => 'yes',
    'checked' => ($plugin_id->ml_pages === 'yes'),  
    '#label' => elgg_echo('multilingual:settings:ml_pages'),
    '#help' => elgg_echo('multilingual:settings:ml_pages:help'),
]);

// External pages
echo elgg_view_field([
    '#type' => 'checkbox',
    'name' => 'params[ml_externalpages]',
    'default' => 'no',
    'switch' => true,
    'value' => 'yes',
    'checked' => ($plugin_id->ml_externalpages === 'yes'),  
    '#label' => elgg_echo('multilingual:settings:ml_externalpages'),
    '#help' => elgg_echo('multilingual:settings:ml_externalpages:help'),
]);
