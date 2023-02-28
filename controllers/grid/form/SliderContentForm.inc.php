<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContentForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
* @brief File implemeting the slider content form.
 */

import('lib.pkp.classes.form.Form');

 /**
 * @class SliderContentForm
 * @brief Form to input slider content.
 */
class SliderContentForm extends Form {

	var $request;
	
	var $contextId;

	var $sliderContentId;
	
	var $plugin;	

	/**
	 * Constructor
	 */
	function __construct($request, $sliderHomePlugin, $contextId, $sliderContentId = null) {
		$this->_request = $request;
		$this->contextId = $contextId;
		$this->sliderContentId = $sliderContentId;
		$this->plugin = $sliderHomePlugin;
		
		parent::__construct($sliderHomePlugin->getTemplateResource('sliderContentForm.tpl'));		

		// Add form checks
		$this->addCheck(new FormValidator($this,'name','required', 'plugins.generic.sliderHome.nameRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'sliderImageLink', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data 
	 */
	function initData() {

		if ($this->sliderContentId) {
			$sliderHomeDao = new SliderHomeDAO();
			$sliderContent = $sliderHomeDao->getById($this->sliderContentId, $this->contextId);
			$this->setData('name', $sliderContent->getName());
			$this->setData('content', $sliderContent->getContent());
			$this->setData('showContent', $sliderContent->getShowContent());
			$this->setData('copyright', $sliderContent->getCopyright());
			
			$locale = AppLocale::getLocale();

			$this->setData('sliderImage', $sliderContent->getSliderImage()?:"");
			$this->setData('sliderImageLink', $sliderContent->getSliderImageLink()?:"");
			$this->setData('sliderImageAltText', $sliderContent->getSliderImageAltText($locale)?:"");	
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {	
		$this->readUserVars(array('name','content','showContent','copyright','temporaryFileId','sliderImage',
		'sliderImageAltText','sliderImageLink'));
	}

	/**
	 * @see Form::fetch
	 */
	function fetch($request, $template = null, $display = false) {

		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('sliderContentId', $this->sliderContentId);
		$templateMgr->registerPlugin('function', 'plugin_url', array($this->plugin, 'smartyPluginUrl'));
		$locale = $templateMgr->getTemplateVars('primaryLocale');
		
		if (!$this->sliderContentId) {
			$this->setData('content', '');
// "<div id='slider-text' class='slider-text'>
// <h3>Title</h3>
// <p>Text
// <a href='#'>Read more ...</a>
// </p>
// </div>");	
		} else {

			$sliderHomeDao = new SliderHomeDAO();
			$sliderContent = $sliderHomeDao->getById($this->sliderContentId, $this->contextId);

			// Slider image delete link action
			if ($sliderImage = $sliderContent->getSliderImage()) $templateMgr->assign(
				'deleteSliderImageLinkAction',
				new LinkAction(
					'deleteSliderImage',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'), null,
						$request->getRouter()->url(
							$request, null, null, 'deleteSliderImage', null, array(
								'sliderImage' => $sliderImage,
							)
						),
						'modal_delete'
					),
					__('common.delete'),
					null
				)
			);
		}

		return parent::fetch($request,$template,$display);
	}

	/**
	 * Save form values into the database
	 */
	function execute(...$functionArgs) {
		parent::execute(...$functionArgs);

		$request = Application::get()->getRequest();

		$sliderHomeDao = new SliderHomeDAO();
		if ($this->sliderContentId) {
			// Load and update an existing content
			$sliderContent = $sliderHomeDao->getById($this->sliderContentId, $this->contextId);
		} else {
			// Create a new item
			$sliderContent = $sliderHomeDao->newDataObject();
			$sliderContent->setContextId($this->contextId);
		}		
		$sliderContent->setName($this->getData('name'));
		$sliderContent->setContent(array_map(
			function($value){
				return "<div id='slider-text' class='slider-text'>".$value."</div>";
			},
			$this->getData('content'))
		);
		$sliderContent->setShowContent(!empty($this->getData('showContent')));	
		$sliderContent->setCopyright($this->getData('copyright'));	

		$locale = AppLocale::getLocale();
		// Copy an uploaded slider file
		if ($temporaryFileId = $this->getData('temporaryFileId')?:"") {
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /* @var $temporaryFileDao TemporaryFileDAO */
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$newFileName = 'slider_image_' . $temporaryFile->getData('fileName') . $publicFileManager->getImageExtension($temporaryFile->getFileType());
			$context = $request->getContext();
			$publicFileManager->copyContextFile($context->getId(), $temporaryFile->getFilePath(), $newFileName);
			$sliderContent->setSliderImage($newFileName);
		}

		$sliderContent->setSliderImageLink($this->getData('sliderImageLink')?:"");
		$sliderContent->setSliderImageAltText($this->getData('sliderImageAltText')?:"");

		if ($this->sliderContentId) {
			$sliderContent->setSequence($sliderContent->getData('sequence'));
			$sliderHomeDao->updateObject($sliderContent);
		} else {
			$sliderContent->setSequence($sliderHomeDao->getMaxSequence($this->contextId)+1);
			$sliderHomeDao->insertObject($sliderContent);
		}
	}
	
	/**
	 * Perform additional validation checks
	 * @copydoc Form::validate
	 */
	function validate($callHooks = true) {
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			$request = Application::get()->getRequest();
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /* @var $temporaryFileDao TemporaryFileDAO */
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			if (!$publicFileManager->getImageExtension($temporaryFile->getFileType())) {
				$this->addError('sliderImage', __('invalidSliderImageFormat'));
			}
		}
		return parent::validate($callHooks);
	}
}

?>
