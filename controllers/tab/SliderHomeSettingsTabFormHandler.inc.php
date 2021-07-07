<?php

/**
 * @file plugins/generic/sliderHome/controllers/tab/SliderHomeSettingsTabFormHandler.inc.php
 *
 * Copyright (c) 2021 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief File implemeting the slider settings tab handler.
 */

import('pages/management/SettingsHandler');
import('lib.pkp.classes.validation.ValidatorFactory');

/**
 * @class SliderHomeSettingsTabFormHandler
 * @brief Class implemeting the slider settings tab handler.
 */
class SliderHomeSettingsTabFormHandler extends SettingsHandler {

	function saveFormData(... $functionArgs) {
		$errors = [];

		$plugin = PluginRegistry::getPlugin('generic', 'sliderhomeplugin');
		$request = Application::getRequest();
		$contextId = $request->getContext()->getId();
		$args = $request->_requestVars;
		$response =& $functionArgs[1];

		$props = ['maxHeight' => $args['maxHeight'],
					'speed' => $args['speed'],
					'delay' => $args['delay']
				];
		$rules = ['maxHeight' => ['integer','nullable','min:0','max:100'],
					'speed' => ['integer','min:0'],
					'delay' => ['integer','min:0']
				];

		$validator = ValidatorFactory::make($props, $rules);
		if ($validator->fails()) {
			$errors = $validator->errors();
		}
		  
		if (!empty($errors)) {
			return $response->withStatus(400)->withJson($errors);
		}

		$plugin->updateSetting($contextId, 'maxHeight', $args['maxHeight'], $type = null, $isLocalized = false);
		$plugin->updateSetting($contextId, 'speed', $args['speed'], $type = null, $isLocalized = false);
		$plugin->updateSetting($contextId, 'delay', $args['delay'], $type = null, $isLocalized = false);
		$plugin->updateSetting($contextId, 'stopOnLastSlide', ($args['stopOnLastSlide'] === "true")?true:false, $type = null, $isLocalized = false);

		return false;
	}
}

?>