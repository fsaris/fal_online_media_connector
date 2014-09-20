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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class VimeoHelper
 */
class VimeoHelper extends AbstractOnlineMediaHelper {

	/**
	 * @var array
	 */
	protected $infoCache = array();

	/**
	 * Get video info from Vimeo
	 *
	 * @param $videoId
	 * @return array|null
	 */
	protected function getVideoInfo($videoId) {

		if (array_key_exists($videoId, $this->infoCache)) {
			return $this->infoCache[$videoId];
		}

		if ($videoId) {
			$info = NULL;
			$rawInfo = GeneralUtility::getUrl(
				sprintf('http://vimeo.com/api/v2/video/%s.php', $videoId)
			);
			if ($rawInfo) {
				$info = unserialize($rawInfo);
			}
			if (is_array($info)) {
				$this->infoCache[$videoId] = $info[0];
				return $info[0];
			}
		}
		return NULL;
	}

	/**
	 * Get public url
	 * Return NULL if you want to use core default behaviour
	 *
	 * @param FileInterface $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(FileInterface $file, $relativeToCurrentScript = FALSE) {
		$videoId = $this->getOnlineMediaId($file);
		return sprintf('http://vimeo.com/%s', $videoId);
	}

	/**
	 * Render the tag
	 *
	 * @param FileInterface $file
	 * @param integer|string $width TYPO3 known format; examples: 220, 200m or 200c
	 * @param integer|string $height TYPO3 known format; examples: 220, 200m or 200c
	 * @param array $additionalConfig
	 * @return string|NULL
	 */
	public function render(FileInterface $file, $width = '', $height = '', $additionalConfig = array()) {
		if ($file instanceof \TYPO3\CMS\Core\Resource\FileReference) {
			$orgFile = $file->getOriginalFile();
		} else {
			$orgFile = $file;
		}
		$output = '';
		$videoId = $this->getOnlineMediaId($orgFile);
		$attributes = array(
			'src' => sprintf('//player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0', $videoId),
		);
		$width = (int)$width;
		if (!empty($width)) {
			$attributes['width'] = $width;
		}
		$height = (int)$height;
		if (!empty($height)) {
			$attributes['height'] = $height;
		}
		if (is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->config['config']['doctype'] !== 'html5') {
			$attributes['frameborder'] = '0';
		}

		foreach ($attributes as $key => $value) {
			$output .= $key . '="' . $value . '"';
		}

		// wrap in div so you can make is responsive
		return '<div class="video-container"><iframe ' . $output . ' allowfullscreen></iframe></div>';
	}

	/**
	 * Get local absolute file path to preview image
	 *
	 * @param File $file
	 * @return string
	 */
	public function getPreviewImage(File $file) {
		$videoId = $this->getOnlineMediaId($file);
		$videoInfo = $this->getVideoInfo($videoId);
		if (!empty($videoInfo)) {
			$temporaryFileName = PATH_site . 'typo3temp/vimeo_' . md5($videoId) . '.jpg';

			if (!file_exists($temporaryFileName)) {
				$previewImage = GeneralUtility::getUrl($videoInfo["thumbnail_large"]);
				if ($previewImage !== FALSE) {
					file_put_contents($temporaryFileName, $previewImage);
				}
			}
		}
		return $temporaryFileName;
	}

	/**
	 * Try to transform given URL to a File
	 *
	 * @param $url
	 * @param Folder $targetFolder
	 * @return File|NULL
	 */
	public function transformUrlToFile($url, Folder $targetFolder) {
		$videoId = NULL;
		if (preg_match('/vimeo\.com\/([0-9]+)/i', $url, $matches)) {
			$videoId = $matches[1];
		}
		if (empty($videoId)) {
			return NULL;
		}

		$file = $this->findExisingFileByOnlineMediaId($videoId);

		// no existing file create new
		if ($file === NULL) {
			$info = $this->getVideoInfo($videoId);
			$fileName = $videoId . '.vimeo';
			if (!empty($info)) {
				$fileName = $info['title']. '.vimeo';
			}
			$file = $this->createNewFile($targetFolder, $fileName, $videoId);
		}

		return $file;
	}

	/**
	 * Get meta data for OnlineMedia item
	 * See $TCA[sys_file_metadata][columns] for possible fields to fill/use
	 *
	 * @param File $file
	 * @return array with metadata
	 */
	public function getMetaData(File $file) {
		$metadata = array();
		$info = $this->getVideoInfo($this->getOnlineMediaId($file));

		if ($info) {
			// todo: add more fields to index
			if (!$file->getProperty('title')) {
				$metadata['title'] = $info['title'];
			}
			if (!$file->getProperty('description')) {
				$metadata['description'] = $info['description'];
			}
			$metadata['duration'] = $info['duration'];
			$metadata['source'] = 'Vimeo.com';
		}
		$metadata['type'] = File::FILETYPE_VIDEO;
		$metadata['mime_type'] = 'video/vimeo';

		return $metadata;
	}

}