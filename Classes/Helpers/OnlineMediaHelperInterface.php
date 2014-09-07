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

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Interface OnlineMediaInterface
 */
interface OnlineMediaHelperInterface extends \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Get public url
	 *
	 * Return NULL if you want to use core default behaviour
	 *
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(\TYPO3\CMS\Core\Resource\AbstractFile $file, $relativeToCurrentScript = FALSE);

	/**
	 * Adjust template and source for TypoScript rendering
	 *
	 * @param string $template
	 * @param string $source
	 * @param File $file
	 * @param array $config
	 * @param ContentObjectRenderer $contentObjectRenderer
	 * @return void
	 */
	public function getContentObjectRendererTemplateAndSource(&$template, &$source, File $file, array &$config, ContentObjectRenderer $contentObjectRenderer);

	/**
	 * Render the tag
	 *
	 * @param TagBuilder $tag
	 * @param FileInterface $file
	 * @param array $additionalConfig
	 * @param integer|string $width TYPO3 known format; examples: 220, 200m or 200c
	 * @param integer|string $height TYPO3 known format; examples: 220, 200m or 200c
	 * @return string
	 */
	public function renderTag(TagBuilder $tag, FileInterface $file, $additionalConfig = array(), $width = '', $height = '');

	/**
	 * Get local absolute file path to preview image
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $file
	 * @return string
	 */
	public function getPreviewImage(\TYPO3\CMS\Core\Resource\File $file);

	/**
	 * Try to transform given URL to a File
	 *
	 * @param $url
	 * @param Folder $targetFolder
	 * @return File|NULL
	 */
	public function transformUrlToFile($url, Folder $targetFolder);

	/**
	 * Get meta data for OnlineMedia item
	 *
	 * See $TCA[sys_file_metadata][columns] for possible fields to fill/use
	 *
	 * @param \TYPO3\CMS\Core\Resource\File $file
	 * @return array with metadata
	 */
	public function getMetaData(\TYPO3\CMS\Core\Resource\File $file);

}