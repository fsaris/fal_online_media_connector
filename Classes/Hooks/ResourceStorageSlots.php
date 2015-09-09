<?php
namespace MiniFranske\FalOnlineMediaConnector\Hooks;

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
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;

/**
 * Class ResourceStorageSlots
 */
class ResourceStorageSlots {

	/**
	 * @var \TYPO3\CMS\Core\Resource\Index\ExtractorInterface[]
	 */
	static protected $extractionServices = NULL;

	/**
	 * Generate public url for file
	 *
	 * @param ResourceStorage $storage
	 * @param DriverInterface $driver
	 * @param ResourceInterface $file
	 * @param $relativeToCurrentScript
	 * @param array $urlData
	 * @return void
	 */
	public function generatePublicUrl(ResourceStorage $storage, DriverInterface $driver, ResourceInterface $file, $relativeToCurrentScript, array $urlData) {
		if ($file instanceof File && ($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE) {
			// $urlData['publicUrl'] is passed by reference, so we can change that here and the value will be taken into account
			$urlData['publicUrl'] = $helper->getPublicUrl($file, $relativeToCurrentScript);
		}
	}

	/**
	 * Post file add slot
	 *
	 * @param FileInterface $file
	 * @param Folder $targetFolder
	 */
	public function postFileAdd(FileInterface $file, Folder $targetFolder) {
		if (!$file instanceof File) {
			return;
		}

		$mimeType = '';
		if (isset($GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType'][$file->getExtension()])) {
			$mimeType = $GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType'][$file->getExtension()];
		}
		if ($mimeType && $file->getMimeType() !== $mimeType) {
			$this->forceMimeType($file->getUid(), $mimeType);
		}

		// Extract metadata
		$this->runMetaDataExtraction($file);
	}

	/**
	 * Force file type and mime_type
	 *
	 * Hacky way, is fixed in TYPO3 7
	 *
	 * @param int $fileUid
	 * @param string $mimeType
	 */
	protected function forceMimeType($fileUid, $mimeType) {
		$updateFileFields = array();
		$updateFileFields['mime_type'] = $mimeType;
		$updateFileFields['type'] = strpos($mimeType, 'video') !== FALSE ? 4 : 3;
		$this->getDatabaseConnection()->exec_UPDATEquery('sys_file', 'uid = ' . (int)$fileUid, $updateFileFields);
	}

	/**
	 * Runs the metadata extraction for a given file.
	 *
	 * Copied from ext:extractor FileUploadHook
	 * Todo: move to own service class
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $fileObject
	 * @return void
	 * @see \TYPO3\CMS\Core\Resource\Index\Indexer::runMetaDataExtraction
	 */
	protected function runMetaDataExtraction(\TYPO3\CMS\Core\Resource\File $fileObject) {
		if (static::$extractionServices === NULL) {
			$extractorRegistry = \TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();
			static::$extractionServices = $extractorRegistry->getExtractorsWithDriverSupport('Local');
		}
		$newMetaData = array(
			0 => $fileObject->_getMetaData()
		);
		foreach (static::$extractionServices as $service) {
			if ($service->canProcess($fileObject)) {
				$newMetaData[$service->getPriority()] = $service->extractMetaData($fileObject, $newMetaData);
			}
		}
		ksort($newMetaData);
		$metaData = array();
		foreach ($newMetaData as $data) {
			$metaData = array_merge($metaData, $data);
		}
		$fileObject->_updateMetaDataProperties($metaData);
		$metaDataRepository = \TYPO3\CMS\Core\Resource\Index\MetaDataRepository::getInstance();
		$metaDataRepository->update($fileObject->getUid(), $metaData);
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

	/**
	 * Wrapper method for getting DatabaseConnection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
