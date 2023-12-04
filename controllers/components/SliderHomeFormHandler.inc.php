<?php

/**
 * @file comtrollers/components/SliderHomeFormHandler.php
 *
 *  TODO @RS
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeFormHandlerHandler
 *
 * @ingroup api_v1_announcement
 *
 * @brief Handle API requests for announcement operations.
 *
 */

use APP\core\Application;
use APP\facades\Repo;
use Illuminate\Support\Facades\Bus;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\handler\APIHandler;
use PKP\jobs\notifications\NewAnnouncementNotifyUsers;
use PKP\mail\Mailer;
use PKP\notification\NotificationSubscriptionSettingsDAO;
use PKP\notification\PKPNotification;
use PKP\plugins\Hook;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;
use PKP\security\Role;
use PKP\services\PKPSchemaService;

import('plugins.generic.sliderHome.classes.SliderHomeDAO');

class SliderHomeFormHandler extends APIHandler
{
    /** @var int The default number of enties to return in one request */
    public const DEFAULT_COUNT = 30;

    /** @var int The maximum number of entries to return in one request */
    public const MAX_COUNT = 100;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_handlerPath = 'sliderHome';
        parent::__construct();
    }

    // Example APIHandler from the core setup endpoints in the constructor
    // doing this as a plugin results in an infinite loop since parant::__construct
    // also calls 'APIHandler::endpoints'. Also we need to merge our endpoints with
    // the ones provided by 'APIHandler::endpoints' in SliderHomePlugin::callbackSetupEndpoints()
    public function setupEndpoints() {
        $this->_endpoints = [
            'GET' => [
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'getMany'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '{itemId:\d+}',
                    'handler' => [$this, 'get'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
            'POST' => [
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'add'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
            'PUT' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/{announcementId:\d+}',
                    'handler' => [$this, 'edit'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '/toggleShow/{itemId:\d+}',
                    'handler' => [$this, 'toggleShow'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
            'DELETE' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/{itemId:\d+}',
                    'handler' => [$this, 'delete'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
        ];
        return $this->_endpoints;
    }

    // As a plugin we need to overwrite this because the API router otherwise searches our handler file in the api folder
    function getEndpointPattern() {
        return '/{contextPath}/api/{version}/contexts/{contextId}/' . $this->_handlerPath;
    }

    /**
     * @copydoc PKPHandler::authorize
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new UserRolesRequiredPolicy($request), true);

        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    function toggleShow($slimRequest, $response, $args) {
        $sliderContentId = $args['itemId'];
        $contextId = $args['contextId'];

        $sliderHomeDao = new SliderHomeDAO();
		$sliderContent = $sliderHomeDao->getById($sliderContentId, $contextId);
		$sliderContent->setShowContent(!$sliderContent->getShowContent());
        $sliderHomeDao->updateObject($sliderContent);
    }

    /**
     * Get a single submission
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function get($slimRequest, $response, $args)
    {
        $announcement = Repo::announcement()->get((int) $args['announcementId']);

        if (!$announcement) {
            return $response->withStatus(404)->withJsonError('api.announcements.404.announcementNotFound');
        }

        // The assocId in announcements should always point to the contextId
        if ($announcement->getData('assocId') !== $this->getRequest()->getContext()->getId()) {
            return $response->withStatus(404)->withJsonError('api.announcements.400.contextsNotMatched');
        }

        return $response->withJson(Repo::announcement()->getSchemaMap()->map($announcement), 200);
    }

    /**
     * Get a collection of announcements
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function getMany($slimRequest, $response, $args)
    {
        $collector = Repo::announcement()->getCollector()
            ->limit(self::DEFAULT_COUNT)
            ->offset(0);

        foreach ($slimRequest->getQueryParams() as $param => $val) {
            switch ($param) {
                case 'typeIds':
                    $collector->filterByTypeIds(
                        array_map('intval', $this->paramToArray($val))
                    );
                    break;
                case 'count':
                    $collector->limit(min((int) $val, self::MAX_COUNT));
                    break;
                case 'offset':
                    $collector->offset((int) $val);
                    break;
                case 'searchPhrase':
                    $collector->searchPhrase($val);
                    break;
            }
        }

        $collector->filterByContextIds([$this->getRequest()->getContext()->getId()]);

        Hook::call('API::submissions::params', [$collector, $slimRequest]);

        $announcements = $collector->getMany();

        return $response->withJson([
            'itemsMax' => $collector->limit(null)->offset(null)->getCount(),
            'items' => Repo::announcement()->getSchemaMap()->summarizeMany($announcements)->values(),
        ], 200);
    }

    /**
     * Add an announcement
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function add($slimRequest, $response, $args)
    {
        $request = $this->getRequest();
        $context = $request->getContext();
        $data = array_merge(
            [
                'name' => "",
                'content' => [],
                'showContent' => false,
                'copyright' => [],
                'sliderImage' => ""
            ],
             $slimRequest->getParsedBody()
        );

        if (!$context) {
            throw new Exception('You can not add a slide without sending a request to the API endpoint of a particular context.');
        }

		$sliderHomeDao = new SliderHomeDAO();
		if (isset($args['sliderContentId'])) {
			// Load and update an existing content
			$sliderContent = $sliderHomeDao->getById($args['sliderContentId'], $context->getId());
		} else {
			// Create a new item
			$sliderContent = $sliderHomeDao->newDataObject();
			$sliderContent->setContextId($context->getId());
		}		
		$sliderContent->setName($data['name']);
		$sliderContent->setContent($data['content']);
		$sliderContent->setShowContent(!empty($data['showContent']));	
		$sliderContent->setCopyright($data['copyright']);
		$sliderContent->setSliderImage($data['sliderImage']?:"");

		$locale = \PKP\facades\Locale::getLocale();
		// Copy an uploaded slider file
		if (isset($data['temporaryFileId']) && $temporaryFileId = $data['temporaryFileId']?:"") {
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

		$sliderContent->setSliderImageLink(isset($data['sliderImageLink'])?:"");
		$sliderContent->setSliderImageAltText(isset($data['sliderImageAltText'])?:"");

		if (isset($args['sliderContentId'])) {
			$sliderContent->setSequence($sliderContent->getData('sequence'));
			$sliderHomeDao->updateObject($sliderContent);
		} else {
			$sliderContent->setSequence($sliderHomeDao->getMaxSequence($context->getId())+1);
			$sliderHomeDao->insertObject($sliderContent);
		}

        return $response->withJson(['dummy' => ""], 200); // TODO @RS
    }

    /**
     * Edit an announcement
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function edit($slimRequest, $response, $args)
    {
        $request = $this->getRequest();

        $announcement = Repo::announcement()->get((int) $args['announcementId']);

        if (!$announcement) {
            return $response->withStatus(404)->withJsonError('api.announcements.404.announcementNotFound');
        }

        if ($announcement->getData('assocType') !== Application::get()->getContextAssocType()) {
            throw new Exception('Announcement has an assocType that did not match the context.');
        }

        // Don't allow to edit an announcement from one context from a different context's endpoint
        if ($request->getContext()->getId() !== $announcement->getData('assocId')) {
            return $response->withStatus(403)->withJsonError('api.announcements.400.contextsNotMatched');
        }

        $params = $this->convertStringsToSchema(PKPSchemaService::SCHEMA_ANNOUNCEMENT, $slimRequest->getParsedBody());
        $params['id'] = $announcement->getId();
        $params['typeId'] ??= null;

        $context = $request->getContext();
        $primaryLocale = $context->getPrimaryLocale();
        $allowedLocales = $context->getSupportedFormLocales();

        $errors = Repo::announcement()->validate($announcement, $params, $allowedLocales, $primaryLocale);
        if (!empty($errors)) {
            return $response->withStatus(400)->withJson($errors);
        }

        Repo::announcement()->edit($announcement, $params);

        $announcement = Repo::announcement()->get($announcement->getId());

        return $response->withJson(Repo::announcement()->getSchemaMap()->map($announcement), 200);
    }

    /**
     * Delete a slider entry
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function delete($slimRequest, $response, $args)
    {
        $sliderContentId = $args['itemId'];
        $contextId = $args['contextId'];

        $sliderHomeDao = new SliderHomeDAO();
        $sliderHomeDao->deleteById($sliderContentId);

        return $response->withJson($sliderContentId, 200);
    }
}
