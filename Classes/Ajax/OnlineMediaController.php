<?php
namespace MiniFranske\FalOnlineMediaConnector\Ajax;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * Class OnlineMediaController
 */
class OnlineMediaController {

	/**
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj
	 */
	public function add($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
		$ajaxObj->setContentFormat('json');

		$url = GeneralUtility::_POST('url');
		$targetFolderIdentifier = GeneralUtility::_POST('targetFolder');

		if (!empty($url)) {
			$targetFolder = NULL;
			if ($targetFolderIdentifier) {
				try {
					$targetFolder = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier($targetFolderIdentifier);
				} catch (\Exception $e) {
					$targetFolder = NULL;
				}
			}
			if ($targetFolder === NULL) {
				$targetFolder = $this->getBeUser()->getDefaultUploadFolder();
			}
			$file = OnlineMediaHelperRegistry::getInstance()->transformUrlToFile($url, $targetFolder);
			if ($file !== NULL) {
				$ajaxObj->addContent('file', $file->getUid());
			} else {
				$ajaxObj->addContent('error', 'Unknown link');
			}
		}
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBeUser() {
		return $GLOBALS['BE_USER'];
	}
}