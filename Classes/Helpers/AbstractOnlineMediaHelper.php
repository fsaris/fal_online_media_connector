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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractOnlineMediaHelper
 */
abstract class AbstractOnlineMediaHelper implements OnlineMediaHelperInterface{

	/**
	 * @var array
	 */
	protected $infoCache = array();

	/**
	 * Cached OnlineMediaIds [fileUid => id]
	 *
	 * @var array
	 */
	protected $onlineMediaIdCache = array();

	/**
	 * Get Online Media item id
	 *
	 * @param File $file
	 * @return string
	 */
	protected function getOnlineMediaId(File $file) {
		if (!array_key_exists($file->getUid(), $this->onlineMediaIdCache)) {
			// By definition these files contain the ID
			$this->onlineMediaIdCache[$file->getUid()] = trim($file->getContents());
		}
		return $this->onlineMediaIdCache[$file->getUid()];
	}

	/**
	 * Search for files with same onlineMediaId by content hash in indexed storage
	 *
	 * @param $onlineMediaId
	 * @return File|null
	 */
	protected function findExisingFileByOnlineMediaId($onlineMediaId) {
		$file = NULL;
		$fileHash = sha1($onlineMediaId);
		$files = $this->getFileIndexRepository()->findByContentHash($fileHash);
		if (!empty($files)) {
			$fileIndexEntry = array_shift($files);
			$file = $this->getResourceFactory()->getFileObject($fileIndexEntry['uid'], $fileIndexEntry);
		}
		return $file;
	}

	/**
	 * Create new OnlineMedia item container file
	 *
	 * @param Folder $targetFolder
	 * @param string $fileName
	 * @param string $onlineMediaId
	 * @return File
	 */
	protected function createNewFile(Folder $targetFolder, $fileName, $onlineMediaId) {
		$tempFilePath = GeneralUtility::tempnam('youtube');
		file_put_contents($tempFilePath, $onlineMediaId);
		return $targetFolder->addFile($tempFilePath, $fileName, 'changeName');
	}

	/**
	 * Returns an instance of the FileIndexRepository
	 *
	 * @return \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
	 */
	protected function getFileIndexRepository() {
		return \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::getInstance();
	}

	/**
	 * Returns the ResourceFactory
	 *
	 * @return \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected function getResourceFactory() {
		return \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
	}
}