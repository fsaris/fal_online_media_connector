<?php
namespace TYPO3\CMS\Core\Resource\OnlineMedia\Helpers;

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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;

/**
 * Interface OnlineMediaInterface
 */
interface OnlineMediaHelperInterface {

	/**
	 * Constructor
	 *
	 * @param string $extension file extension bind to the OnlineMedia helper
	 */
	public function __construct($extension);

	/**
	 * Try to transform given URL to a File
	 *
	 * @param string $url
	 * @param Folder $targetFolder
	 * @return File|NULL
	 */
	public function transformUrlToFile($url, Folder $targetFolder);

	/**
	 * Get Online Media item id
	 *
	 * @param File $file
	 * @return string
	 */
	public function getOnlineMediaId(File $file);

	/**
	 * Get public url
	 *
	 * Return NULL if you want to use core default behaviour
	 *
	 * @param File $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(File $file, $relativeToCurrentScript = FALSE);

	/**
	 * Get local absolute file path to preview image
	 *
	 * @param File $file
	 * @return string
	 */
	public function getPreviewImage(File $file);

	/**
	 * Get meta data for OnlineMedia item
	 *
	 * See $TCA[sys_file_metadata][columns] for possible fields to fill/use
	 *
	 * @param File $file
	 * @return array with metadata
	 */
	public function getMetaData(File $file);

}
