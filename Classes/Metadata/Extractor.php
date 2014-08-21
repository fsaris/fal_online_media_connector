<?php
namespace MiniFranske\FalOnlineMediaConnector\Metadata;

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


use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

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
	 * @return integer
	 */
	public function getPriority() {
		return 10;
	}

	/**
	 * Returns the execution priority of the extraction Service
	 *
	 * @return integer
	 */
	public function getExecutionPriority() {
		return 10;
	}

	/**
	 * Checks if the given file can be processed by this Extractor
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $file
	 * @return boolean
	 */
	public function canProcess(\TYPO3\CMS\Core\Resource\File $file) {
		return (OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE;
	}

	/**
	 * The actual processing TASK
	 * Should return an array with database properties for sys_file_metadata to write
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $file
	 * @param array $previousExtractedData optional, contains the array of already extracted data
	 * @return array
	 */
	public function extractMetaData(\TYPO3\CMS\Core\Resource\File $file, array $previousExtractedData = array()) {
		$metadata = array();

		/** @var \MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperInterface $helper */
		if (($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE) {
			$metadata = $helper->getMetaData($file);

			// hacky way to set file type
			// todo: find better way
			if (!empty($metadata['type']) && $file->getType() !== $metadata['type']) {
				$this->getDatabaseConnection()->exec_UPDATEquery('sys_file', 'uid = ' . (int)$file->getUid(), array(
					'type' => $metadata['type']
				));
			}
		}
		return $metadata;
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