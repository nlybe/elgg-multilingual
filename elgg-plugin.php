<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

use Multilingual\Elgg\Bootstrap;

require_once(dirname(__FILE__) . '/lib/events.php');
require_once(dirname(__FILE__) . '/lib/functions.php'); 

return [
    'plugin' => [
        'name' => 'Multilingual Content',
		'version' => '5.3',
		'dependencies' => [],
	],	
    'bootstrap' => Bootstrap::class,
	'actions' => [
		'ml_expages/edit' => ['access' => 'admin'],
		'multilingual/translate' => [],
	],
	'routes' => [
		'translation:subtype:edit' => [
            'path' => '/translation/edit/{guid}',
            'resource' => 'multilingual/translation_edit',
        ],
	],
	'widgets' => [],
	'views' => [
		'default' => [
			'multilingual/graphics/' => __DIR__ . '/graphics',
			'multilingual/jquery-ui/' => __DIR__ . '/vendors/jquery/ui/',
		],
	],
	'settings' => [
		'ml_pages' => 'yes',
		'ml_externalpages' => 'yes',
	],
	'upgrades' => [],
];
