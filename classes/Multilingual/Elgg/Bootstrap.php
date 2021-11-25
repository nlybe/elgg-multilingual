<?php
/**
 * Elgg Multilingual Content
 * @package multilingual
 */

namespace Multilingual\Elgg;

use Elgg\DefaultPluginBootstrap;
use Multilingual\MultilingualOptions;

class Bootstrap extends DefaultPluginBootstrap {
	
	const HANDLERS = [];
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		$this->initViews();
	}

	/**
	 * Init views
	 *
	 * @return void
	 */
	protected function initViews() {
		// Extend CSS
		elgg_extend_view('css/elgg', 'multilingual/multilingual.css');
		
		// add option to edit translations, if enabled for subtype
		elgg_register_plugin_hook_handler('register', 'menu:entity', 'ml_entity_menu_setup', 400);

		if (MultilingualOptions::isSitePagesActive())	{
			// replace the admin/configure_utilities/expages view
			elgg_register_plugin_hook_handler('view', 'admin/configure_utilities/expages', "ml_admin_expages_view");

			// replace routes for site pages, even for expages_extended plugin if enabled
			if (elgg_is_active_plugin('externalpages_extended')) {
				elgg_unregister_route('expages_extended:section');
				$external_pages = expages_extended_pages();
			}
			else { 
				$external_pages = ['about', 'terms', 'privacy'];
			}		
			foreach($external_pages as $page) {
				elgg_register_route("view:object:$page", [
					'path' => "/{$page}",
					'resource' => "multilingual/externalpages/expages",
					'defaults' => [ 'expage' =>  $page, ],
					'walled' => false,
				]);

				if (!is_registered_entity_type('object', $page)) {
					elgg_register_entity_type('object', $page);
				}
			}
		}

		// add multilingual functionality to pages
		if (MultilingualOptions::isPagesActive())	{
			$options = [
				'title' => ['type' => 'text', 'label' => 'pages:title', 'annotate' => false],
				'description' => ['type' => 'longtext', 'label' => 'pages:description', 'annotate' => 'page'],
			];
			MultilingualOptions::setSubtypeForTranslation('page', $options);
			
			// replace views
			elgg_register_plugin_hook_handler('view', 'object/page', "ml_object_view");
			elgg_register_plugin_hook_handler('view', 'annotation/page', "ml_annotation_page_view");

			// replace hooks
			elgg_unregister_plugin_hook_handler('register', 'menu:pages_nav', '\Elgg\Pages\Menus::registerPageMenuItems');
			elgg_register_plugin_hook_handler('register', 'menu:pages_nav', 'ml_registerPageMenuItems');
			elgg_unregister_plugin_hook_handler('prepare', 'notification:create:object:page', 'pages_prepare_notification');
			elgg_register_plugin_hook_handler('prepare', 'notification:create:object:page', 'ml_pages_prepare_notification');
			elgg_unregister_plugin_hook_handler('extender:url', 'annotation', 'pages_set_revision_url');
			elgg_register_plugin_hook_handler('extender:url', 'annotation', 'ml_pages_set_revision_url');
			
			// replace routes
			elgg_unregister_route('edit:object:page');
			elgg_register_route("edit:object:page", [
				'path' => "/pages/edit/{guid}",
				'resource' => "multilingual/pages/edit",
			]);		
			elgg_unregister_route('history:object:page');
			elgg_register_route("history:object:page", [
				'path' => "/pages/history/{guid}",
				'resource' => "multilingual/pages/history",
			]);
			elgg_unregister_route('add:object:page');
			elgg_register_route("add:object:page", [
				'path' => "/pages/add/{guid}",
				'resource' => "multilingual/pages/new",
				'middleware' => [
					\Elgg\Router\Middleware\Gatekeeper::class,
				],
			]);
			elgg_unregister_route('revision:object:page');
			elgg_register_route("revision:object:page", [
				'path' => "/pages/revision/{id}",
				'resource' => "multilingual/pages/revision",
			]);
			elgg_unregister_route('view:object:page');
			elgg_register_route("view:object:page", [
				'path' => "/pages/view/{guid}/{title?}",
				'resource' => "multilingual/pages/view",
			]);

			// Check current language when create new page and duplicate annotation if other than default to the current language (since initial is create always to default language)
			elgg_register_event_handler('create', 'object', 'multilingual_page_create_object');
		}
		
	}
}
