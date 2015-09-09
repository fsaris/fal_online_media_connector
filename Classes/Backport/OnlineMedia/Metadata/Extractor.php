<?php
namespace TYPO3\CMS\Core\Resource\OnlineMedia\Metadata;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\File;

/**
 * Class Extractor
 */
class Extractor implements \TYPO3\CMS\Core\Resource\Index\ExtractorInterface {

	/**
	 * Returns an array of supported file types
	 *
	 * @return array
	 */
	public function getFileTypeRestrictions() {
		return array();
	}

	/**
	 * Get all supported DriverClasses
	 * empty array indicates no restrictions
	 *
	 * @return array
	 */
	public function getDriverRestrictions() {
		return array();
	}

	/**
	 * Returns the data priority of the extraction Service
	 *
	 * @return int
	 */
	public function getPriority() {
		return 10;
	}

	/**
	 * Returns the execution priority of the extraction Service
	 *
	 * @return int
	 */
	public function getExecutionPriority() {
		return 10;
	}

	/**
	 * Checks if the given file can be processed by this Extractor
	 *
	 * @param File $file
	 * @return bool
	 */
	public function canProcess(File $file) {
		return (OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE;
	}

	/**
	 * The actual processing TASK
	 * Should return an array with database properties for sys_file_metadata to write
	 *
	 * @param File $file
	 * @param array $previousExtractedData optional, contains the array of already extracted data
	 * @return array
	 */
	public function extractMetaData(File $file, array $previousExtractedData = array()) {
		$metadata = array();

		/** @var OnlineMediaHelperInterface $helper */
		if (($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE) {
			$metadata = $helper->getMetaData($file);
		}
		return $metadata;
	}
}
