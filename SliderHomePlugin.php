<?php
/**
 * @file plugins/generic/home/HomePlugin.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 */

namespace APP\plugins\generic\sliderHome;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\core\PKPApplication;
use PKP\core\Registry;
use PKP\config\Config;
use APP\file\PublicFileManager;
use APP\template\TemplateManager;
use PKP\facades\Locale;
use DOMDocument;
use APP\plugins\generic\sliderHome\classes\SliderHomeDAO;
use APP\plugins\generic\sliderHome\classes\components\form\context\SliderHomeSettingsForm;
use APP\plugins\generic\sliderHome\classes\components\form\SliderContentForm;
use APP\plugins\generic\sliderHome\classes\components\SliderHomeContentList;
use APP\plugins\generic\sliderHome\controllers\components\SliderHomeFormHandler;
use APP\plugins\generic\sliderHome\SliderHomeSchemaMigration;
use APP\core\Application;
use PKP\security\Role;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\JsonResponse;

/**
 * @class SliderHomePlugin
 * 
 * @brief Enables display of image slider on the journal/press home page.
 */
class SliderHomePlugin extends GenericPlugin {

	protected $_endpointsSetup = false;
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				Hook::add('TemplateManager::display',array($this, 'callbackDisplay')); //to enable slider display in OMP frontend
				Hook::add('Template::Settings::website::appearance', array($this, 'callbackAppearanceTab')); //to enable display of plugin settings tab
				Hook::add('Templates::Index::journal', array($this, 'callbackIndexJournal')); //to enable slider display in OJS frontend
				Hook::add('Schema::get::context', array($this, 'addToContextSchema'));
				$router = Application::get()->getRequest()->getRouter();
				if ($router instanceof \PKP\core\APIRouter) {
					Hook::add("APIHandler::endpoints::{$router->getEntity()}", [$this, 'callbackSetupEndpoints']);
				}
				Hook::add('Schema::get::sliderHome', [$this, 'addSliderHomeDBSchema']);
			}
			return true;
		}
		return false;
	}

	public function addSliderHomeDBSchema($hookName, $params) {
		$schema = &$params[0];
		$schema = json_decode(file_get_contents($this->getPluginPath().'/schema.json'));
		return false;
	}

	public function addToContextSchema($hookName, $params) {
		$schema =& $params[0];

		$schema->properties->{"maxHeight"} = (object) [
			'type' => 'integer',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];
		$schema->properties->{"speed"} = (object) [
			'type' => 'integer',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];
		$schema->properties->{"delay"} = (object) [
			'type' => 'integer',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];
		$schema->properties->{"fallbackLocale"} = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];
		$schema->properties->{"slideEffect"} = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];
		$schema->properties->{"stopOnLastSlide"} = (object) [
			'type' => 'boolean',
			'apiSummary' => true,
			'validation' => ['nullable'],
		];

		return false;
	}

	function callbackSetupEndpoints($hook, $controller, $apiHandler) {
		if ($apiHandler instanceof SliderHomeFormHandler === false) {
			if ($hook === 'APIHandler::endpoints::contexts') {
				$request = Application::get()->getRequest();
				$apiHandler->addRoute(
					'POST',
					$request->getContext()->getId().'/sliderHome/edit',   // The route uri on top of the given hook
					function (IlluminateRequest $request) use ($controller): JsonResponse {
						$sliderContentFormHandler = new SliderHomeFormHandler($controller);
						return $sliderContentFormHandler->edit($request);
					}, // The handler function
					'sliderHomeSettings.add', // Name of the route
					[Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
				);
				$apiHandler->addRoute(
					'DELETE',
					$request->getContext()->getId().'/sliderHome/{sliderContentId}',   // The route uri on top of the given hook
					function (IlluminateRequest $request) use ($controller): JsonResponse {
						$sliderContentFormHandler = new SliderHomeFormHandler($controller);
						return $sliderContentFormHandler->delete($request);
					}, // The handler function
					'sliderHomeSettings.delete', // Name of the route
					[Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
				);
				$apiHandler->addRoute(
					'POST',
					$request->getContext()->getId().'/sliderHome/edit/{sliderContentId}',   // The route uri on top of the given hook
					function (IlluminateRequest $request, $sliderContentId) use ($controller): JsonResponse {
						$sliderContentFormHandler = new SliderHomeFormHandler($controller);
						return $sliderContentFormHandler->edit($request, $sliderContentId);
					}, // The handler function
					'sliderHomeSettings.edit', // Name of the route
					[Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
				);
				$apiHandler->addRoute(
					'POST',
					$request->getContext()->getId().'/sliderHome/saveOrder',   // The route uri on top of the given hook
					function (IlluminateRequest $request) use ($controller): JsonResponse {
						$sliderContentFormHandler = new SliderHomeFormHandler($controller);
						return $sliderContentFormHandler->saveOrder($request);
					}, // The handler function
					'sliderHomeSettings.saveOrder', // Name of the route
					[Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
				);
				$apiHandler->addRoute(
					'POST',
					$request->getContext()->getId().'/sliderHome/toggleVisibility/{sliderContentId}',  
					function (IlluminateRequest $request) use ($controller): JsonResponse {
						$sliderContentFormHandler = new SliderHomeFormHandler($controller);
						return $sliderContentFormHandler->toggleVisibility($request);
					}, // The handler function
					'sliderHomeSettings.toggleVisibility', // Name of the route
					[Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
				);
			}
		}
		return Hook::CONTINUE;
	}
	
	function callbackAppearanceTab($hookName, $args) {		

		# prepare data
		$templateMgr =& $args[1];
		$output =& $args[2];
		$request =& Registry::get('request');
		$context = $request->getContext();
		$contextId = $context->getId();
		$dispatcher = $request->getDispatcher();

		$supportedFormLocales = $context->getSupportedFormLocales();
		$localeNames = \PKP\facades\Locale::getLocales();
		$locales = array_map(function($localeKey) use ($localeNames) {
			return ['key' => $localeKey, 'label' => $localeNames[$localeKey]->locale];
		}, $supportedFormLocales);
		$formLocaleNames = $context->getSupportedFormLocaleNames(); // TODO @RS

		$publicFileManager = new PublicFileManager();
		$baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());
		$temporaryFileApiUrl = $dispatcher->url($request, Application::ROUTE_API, $context->getPath(), 'temporaryFiles');
		$publicFileApiUrl = $dispatcher->url($request, Application::ROUTE_API, $context->getPath(), '_uploadPublicFile');
		$contextApiUrl = $dispatcher->url(
			$request,
			Application::ROUTE_API,
			$context->getPath(),
			'contexts/' . $context->getId()
		);

		// instantinate settings form
		$sliderSettingsForm = new SliderHomeSettingsForm($contextApiUrl, $locales, $context);

		// get slider data
		$sliderHomeDao = new SliderHomeDao();
		$sliderImages = array_map(
			function ($item) use ($baseUrl) {
				$image = [];
				$imageData = array_merge_recursive($item->getData('sliderImageAltText')?:[], $item->getData('sliderImage')?:[]);
				foreach ($imageData as $locale => $localeData) {
					if (is_array($localeData)) {
						$image[$locale] = array_combine(['altText', 'uploadName'], $localeData);
					}
				}
				$thumbFileName = is_array($image[Locale::getLocale()])?$image[Locale::getLocale()]['uploadName']:"";
				return [
					'id' => $item->getData('id'),
					'name' => $item->getData('name'),
					'content' => $item->getData('content'),
					'copyright' => $item->getData('copyright'),
					'show_content' => $item->getData('show_content'),
					'sliderImage' => $image,
					'sliderImageLink' => $item->getData('sliderImageLink'),
					'thumbnail' => $thumbFileName?true:false,
					'thumbnailUrl' => $baseUrl.'/'.($thumbFileName?$thumbFileName:"")
				];
			},
			$sliderHomeDao->getByContextId($contextId)->toArray()
		);

		// get slider content form
		$sliderContentFormApiUrl = $dispatcher->url(
			$request,
			Application::ROUTE_API,
			$context->getPath(),
			"contexts/" . $contextId . "/sliderHome/edit"
		);
		$searchApiUrl = $dispatcher->url(
			$request,
			Application::ROUTE_API,
			$context->getPath(),
			"/issues"
		);
		$sliderContentForm = new SliderContentForm($sliderContentFormApiUrl, $context, $baseUrl, $temporaryFileApiUrl, $searchApiUrl);
		
		$apiUrl = $dispatcher->url(
			$request,
			Application::ROUTE_API,
			$context->getPath(),
			"contexts/" . $contextId . "/sliderHome"
		);
		$sliderHomeContentList = new SliderHomeContentList($apiUrl, $sliderImages);

		# setup template, this allows us to use the constants in the tpl-file
		$templateMgr->setConstants([
			'FORM_SLIDER_SETTINGS' => FORM_SLIDER_SETTINGS,
			'FORM_SLIDER_CONTENT' => FORM_SLIDER_CONTENT,
			'SLIDER_CONTENT_LIST' => SLIDER_CONTENT_LIST
		]);

		// set state
		$state = $templateMgr->getTemplateVars('state');
		$state['components'][FORM_SLIDER_SETTINGS] = $sliderSettingsForm->getConfig();
		$state['components'][FORM_SLIDER_CONTENT] = $sliderContentForm->getConfig();
		$state['components'][SLIDER_CONTENT_LIST] = $sliderHomeContentList->getConfig();
		$templateMgr->assign('state', $state);

		// render template
		$output .= $templateMgr->fetch($this->getTemplateResource('appearanceTab.tpl'));

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	// OJS: there's a template hook on the frontend journal index page
	function callbackIndexJournal($hookName, $args) {	
		$request = $this->getRequest();
		$templateMgr =& $args[1];

		// prepare and assign variables for the Smarty slider template
		$this->assignSliderTemplateVariables($request, $templateMgr);

		// ensure Swiper assets and the frontend initializer are available
		$this->addHeader($templateMgr, $request->getBaseUrl());
		// register a small frontend mount script that doesn't depend on backend Vue
		$templateMgr->addJavaScript(
			'sliderHomeFrontend',
			"{$request->getBaseUrl()}/{$this->getPluginPath()}/resources/js/slider-mount.js",
			[
				'inline' => false,
				'contexts' => ['frontend']
			]
		);

		$output =& $args[2];
		// fetch the mount element for the Vue component
		$output .= $templateMgr->fetch($this->getTemplateResource('slider.tpl'));

		return false;
	}	
		
	// OMP: no template hook on the index template -> use display hook to replace template
	function callbackDisplay($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr =& $args[0];
		$template =& $args[1];
		$applicationName = PKPApplication::get()->getName();		
		switch ($template) {
			case 'frontend/pages/index.tpl':
					if ($applicationName=="omp") {
						// Prepare variables and assets for the Smarty slider template
						$this->assignSliderTemplateVariables($request, $templateMgr);
						$this->addHeader($templateMgr, $request->getBaseUrl());
						$templateMgr->addJavaScript(
							'sliderHomeFrontend',
							"{$request->getBaseUrl()}/{$this->getPluginPath()}/resources/js/slider-mount.js",
							[
								'inline' => false,
								'contexts' => ['frontend']
							]
						);
						// Render the server-side Smarty slider and assign into home template
						$sliderHtml = $templateMgr->fetch($this->getTemplateResource('slider.tpl'));
						$templateMgr->assign('sliderContent', $sliderHtml);
						$templateMgr->display($this->getTemplateResource('homeOMP.tpl'));
						return true;
					}
			case 'frontend/pages/indexJournal.tpl':
				$this->addHeader($templateMgr,$request->getBaseUrl());
			case 'management/website.tpl':
				$templateMgr->addJavaScript(
					'sliderHomeJS',
					"{$request->getBaseUrl()}/{$this->getPluginPath()}/build/sliderHome.iife.js",
					[
						'inline' => false,
						'contexts' => ['backend'],
						'priority' => TemplateManager::STYLE_SEQUENCE_LAST
					]
				);
				$templateMgr->addStyleSheet('sliderHomeContentListStyle',"{$request->getBaseUrl()}/{$this->getPluginPath()}/resources/css/sliderHome.css", [
					'contexts' => ['backend']
				] );
		}
		return false;
	}
	
	private function addHeader($templateMgr,$baseUrl) {
		$templateMgr->addHeader(
			'slider',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/resources/css/sliderHome.css'>"
		);
		$templateMgr->addHeader(
			'swiper-min',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/build/swiper/swiper-bundle.min.css'>"
		);		
		$templateMgr->addHeader(
			'swiper-min-js',
			"<script src='".$baseUrl."/plugins/generic/sliderHome/build/swiper/swiper-bundle.min.js'></script>"
		);
	}
    
	/**
	 * Prepare and assign variables required by the Smarty slider template.
	 */
	private function assignSliderTemplateVariables($request, $templateMgr) {
		$context = $request->getContext();
		$primaryLocale = $context->getPrimaryLocale();
		$contextPath = get_class($context) === 'Press'?'/presses/':'/journals/';
		$contextId = $context->getId();

		$maxHeight = $context->getData('maxHeight');
		$speed = $context->getData('speed');
		$delay = $context->getData('delay');
		$stopOnLastSlide = $context->getData('stopOnLastSlide')?true:false;
		$fallbackLocale = $context->getData('fallbackLocale')?:"usePrimary";
		$slideEffect = $context->getData('slideEffect')?:"";

		$locale = $templateMgr->getTemplateVars('currentLocale');

		$sliderHomeDao = new SliderHomeDao();

		if ($fallbackLocale == 'usePrimary') {
			$contentArrayCurrentLocale = $sliderHomeDao->getAllContent($contextId, $locale);
			$contentArrayPrimaryLocale = $sliderHomeDao->getAllContent($contextId, $primaryLocale);
			$localizedFields = $sliderHomeDao->getLocaleFieldNames();
			$contentArray = array_map(
				function ($current, $primary) use ($localizedFields) {
					foreach ($localizedFields as $field) {
						$current[$field] = $current[$field]==""?$primary[$field]:$current[$field];
					}
					return $current;
				},
				$contentArrayCurrentLocale,
				$contentArrayPrimaryLocale
			);
		} else {
			$contentArray = $sliderHomeDao->getAllContent($contextId, $locale);
		}

		$baseUrl = Config::getVar('general', 'base_url');
		$publicFilesDir = Config::getVar('files', 'public_files_dir');

		$sliderItems = [];
		foreach ($contentArray as $value) {
			$noclick = false;
			if (!empty($value['content'])) {
				if (str_contains($value['content'], 'href')) {
					$noclick = false;
				} else {
					$noclick = true;
				}
			}

			$sliderItems[] = [
				'id' => $value['id'] ?? null,
				'name' => $value['name'] ?? '',
				'content' => $value['content'] ?? '',
				'copyright' => $value['copyright'] ?? '',
				'show_content' => $value['show_content'] ?? false,
				'sliderImage' => $value['sliderImage'] ?? '',
				'sliderImageAltText' => $value['sliderImageAltText'] ?? '',
				'sliderImageLink' => $value['sliderImageLink'] ?? '',
				'noclick' => $noclick,
			];
		}

		$templateMgr->assign([
			'sliderItems' => $sliderItems,
			'baseUrl' => $baseUrl,
			'publicFilesDir' => $publicFilesDir,
			'contextPath' => $contextPath,
			'contextId' => $contextId,
			'maxHeight' => $maxHeight,
			'speed' => $speed,
			'delay' => $delay,
			'stopOnLastSlide' => $stopOnLastSlide,
			'slideEffect' => $slideEffect,
		]);

		// also provide a JSON-encoded props payload for the frontend Vue component
		$sliderProps = json_encode([
			'slides' => $sliderItems,
			'baseUrl' => $baseUrl,
			'publicFilesDir' => $publicFilesDir,
			'contextPath' => $contextPath,
			'contextId' => $contextId,
			'maxHeight' => $maxHeight,
			'speed' => $speed,
			'delay' => $delay,
			'stopOnLastSlide' => $stopOnLastSlide,
			'slideEffect' => $slideEffect,
		]);

		$templateMgr->assign('sliderProps', $sliderProps);
	}
	
	

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.sliderHome.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.sliderHome.description');
	}

	/**
	 * @copydoc Plugin::getInstallMigration()
	 */
	function getInstallMigration() {
		return new SliderHomeSchemaMigration();
	}
}

?>
