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
use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Interface OnlineMediaInterface
 */
interface OnlineMediaHelperInterface extends \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Try to transform given URL to a File
	 *
	 * @param $url
	 * @param Folder $targetFolder
	 * @return File|NULL
	 */
	public function transformUrlToFile($url, Folder $targetFolder);

	/**
	 * Get public url
	 *
	 * Return NULL if you want to use core default behaviour
	 *
	 * @param FileInterface $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(FileInterface $file, $relativeToCurrentScript = FALSE);

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