<?php
namespace MiniFranske\FalOnlineMediaConnector\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <franssaris@gmail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;

/**
 * Class OnlineMediaController
 */
class OnlineMediaController {

	/**
	 * @param array $params
	 * @param AjaxRequestHandler $ajaxObj
	 */
	public function addAjaxAction($params = array(), AjaxRequestHandler $ajaxObj = NULL) {
		$ajaxObj->setContentFormat('json');

		$url = GeneralUtility::_POST('url');
		$targetFolderIdentifier = GeneralUtility::_POST('targetFolder');

		if (!empty($url)) {
			$file = $this->addMediaFromUrl($url, $targetFolderIdentifier);
			if ($file !== NULL) {
				$ajaxObj->addContent('file', $file->getUid());
			} else {
				$ajaxObj->addContent('error', 'Unknown link');
			}
		}
	}

	/**
	 * @param $url
	 * @param string $targetFolderIdentifier
	 * @return NULL|File
	 */
	protected function addMediaFromUrl($url, $targetFolderIdentifier) {
		$targetFolder = NULL;
		if ($targetFolderIdentifier) {
			try {
				$targetFolder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier($targetFolderIdentifier);
			} catch (\Exception $e) {
				$targetFolder = NULL;
			}
		}
		if ($targetFolder === NULL) {
			$targetFolder = $this->getBeUser()->getDefaultUploadFolder();
		}
		return OnlineMediaHelperRegistry::getInstance()->transformUrlToFile($url, $targetFolder);
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBeUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Add flash message to message queue
	 *
	 * @param FlashMessage $flashMessage
	 * @return void
	 */
	protected function addFlashMessage(FlashMessage $flashMessage) {
		/** @var $flashMessageService FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');

		/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
	}

}
