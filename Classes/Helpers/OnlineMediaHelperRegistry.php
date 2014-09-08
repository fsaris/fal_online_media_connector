<?php
namespace MiniFranske\FalOnlineMediaConnector\Helpers;

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


/**
 * Online Media Source Registry
 */
class OnlineMediaHelperRegistry implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Registered helper classes
	 *
	 * @var array
	 */
	protected $onlineMediaHelpers = array();

	/**
	 * @var array
	 */
	protected $iconNames = array();

	/**
	 * Returns an instance of this class
	 * @return OnlineMediaHelperRegistry
	 */
	public static function getInstance() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'MiniFranske\\FalOnlineMediaConnector\\Helpers\\OnlineMediaHelperRegistry'
		);
	}

	/**
	 * Register online media file extension and helper class
	 *
	 * @param string $fileExtension
	 * @param string $className
	 * @param string|NULL $iconName icon name
	 * @param bool $addToAllowedExtensions
	 * @throws \InvalidArgumentException
	 */
	public function registerOnlineMediaFileExtension($fileExtension, $className, $iconName = NULL, $addToAllowedExtensions = TRUE) {
		if (!class_exists($className)) {
			throw new \InvalidArgumentException('The class you are registering is not available');
		} elseif (!in_array('MiniFranske\\FalOnlineMediaConnector\\Helpers\\OnlineMediaHelperInterface', class_implements($className))) {
			throw new \InvalidArgumentException('The helper class needs to implement the OnlineMediaHelperInterface');
		} elseif (array_key_exists($fileExtension, $this->onlineMediaHelpers)) {
			if ($this->onlineMediaHelpers[$fileExtension] !== $className) {
				throw new \InvalidArgumentException('FileExtension ' . $fileExtension . ' is already registered');
			}
		} else {
			$this->onlineMediaHelpers[$fileExtension] = $className;

			if (!empty($iconName)) {
				$this->iconNames[$fileExtension] = $iconName;
			}

			// add file extension to allowed "image" extensions
			if ($addToAllowedExtensions) {
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] .= ',' . $fileExtension;
			}
		}
	}

	/**
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $file
	 * @return bool|OnlineMediaHelperInterface
	 */
	public function getOnlineMediaHelper(\TYPO3\CMS\Core\Resource\AbstractFile $file) {
		if (array_key_exists($file->getExtension(), $this->onlineMediaHelpers)) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->onlineMediaHelpers[$file->getExtension()]);
		}
		return FALSE;
	}

	/**
	 * Get icon name for OnlineMedia item
	 *
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $file
	 */
	/**
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $file
	 * @return string|bool
	 */
	public function getIconName(\TYPO3\CMS\Core\Resource\AbstractFile $file) {
		if (array_key_exists($file->getExtension(), $this->iconNames)) {
			return $this->iconNames[$file->getExtension()];
		}
		return FALSE;
	}

	/**
	 * Try to transform given URL to a File
	 *
	 * @param string $url
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @return \TYPO3\CMS\Core\Resource\File|NULL
	 */
	public function transformUrlToFile($url, \TYPO3\CMS\Core\Resource\Folder $targetFolder) {
		foreach ($this->onlineMediaHelpers as $className) {
			$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($className);
			$file = $helper->transformUrlToFile($url, $targetFolder);
			if ($file !== NULL) {
				return $file;
			}
		}
		return NULL;
	}
}