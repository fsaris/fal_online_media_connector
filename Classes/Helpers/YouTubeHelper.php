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
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class YouTubeHelper
 */
class YouTubeHelper extends AbstractOnlineMediaHelper {

	/**
	 * Get public url
	 *
	 * @param FileInterface $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(FileInterface $file, $relativeToCurrentScript = FALSE) {
		$videoId = $this->getOnlineMediaId($file);
		return sprintf('https://www.youtube.com/watch?v=%s', $videoId);
	}

	/**
	 * Get local absolute file path to preview image
	 *
	 * @param File $file
	 * @return string
	 */
	public function getPreviewImage(File $file) {
		$videoId = $this->getOnlineMediaId($file);
		$temporaryFileName = PATH_site .'typo3temp/youtube_' . md5($videoId) . '.jpg';

		if (!file_exists($temporaryFileName)) {
			$previewImage = GeneralUtility::getUrl(
				sprintf('http://img.youtube.com/vi/%s/0.jpg', $videoId)
			);
			if ($previewImage !== FALSE) {
				file_put_contents($temporaryFileName, $previewImage);
			}
		}

		return $temporaryFileName;
	}

	/**
	 * Try to transform given URL to a File
	 *
	 * @param string $url
	 * @param Folder $targetFolder
	 * @return File|NULL
	 */
	public function transformUrlToFile($url, Folder $targetFolder) {
		$videoId = NULL;
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
			$videoId = $match[1];
		}
		if (empty($videoId)) {
			return NULL;
		}

		$file = $this->findExisingFileByOnlineMediaId($videoId);

		// no existing file create new
		if ($file === NULL) {
			$info = $this->getYouTubeGdata($videoId);
			$fileName = $videoId . '.ytb';
			if (!empty($info)) {
				$fileName = $info->entry->title->{'$t'} . '.ytb';
			}
			$file = $this->createNewFile($targetFolder, $fileName, $videoId);
		}

		return $file;
	}

	/**
	 * Get meta data for OnlineMedia item
	 *
	 * Using the meta data from Gdata and Oembed
	 * Gdata contains more information, but no dimensions
	 *
	 * @param File $file
	 * @return array with metadata
	 */
	public function getMetaData(File $file) {
		$metadata = array();
		$gdata = $this->getYouTubeGdata($this->getOnlineMediaId($file));

		if ($gdata) {
			// todo: add more fields to index
			if (!$file->getProperty('title')) {
				$metadata['title'] = strip_tags($gdata->entry->title->{'$t'});
			}
			if (!$file->getProperty('description')) {
				$metadata['description'] = strip_tags($gdata->entry->{'media$group'}->{'media$description'}->{'$t'});
			}
			$metadata['duration'] = $gdata->entry->{'media$group'}->{'media$content'}[0]->{'duration'};
			$metadata['source'] = 'YouTube.com';
		}

		$oembed = $this->getYouTubeOembed($this->getOnlineMediaId($file));

		if ($oembed) {
			if (!$file->getProperty('width')) {
				$metadata['width'] = (int) $oembed->width;
			}
			if (!$file->getProperty('height')) {
				$metadata['height'] = (int) $oembed->height;
			}
		}

		$metadata['type'] = File::FILETYPE_VIDEO;
		$metadata['mime_type'] = 'video/youtube';

		return $metadata;
	}


	/**
	 * Get YouTube GData video info
	 *
	 * @param string $videoId
	 * @return array|null
	 */
	protected function getYouTubeGdata($videoId) {
		$gdata = GeneralUtility::getUrl(
			sprintf('https://gdata.youtube.com/feeds/api/videos/%s?v=2&alt=json', $videoId)
		);
		if ($gdata) {
			$gdata = json_decode($gdata);
		}
		return $gdata;
	}

	/**
	 * Get YouTube OEmbed video info
	 *
	 * @param string $videoId
	 * @return array|null
	 */
	protected function getYouTubeOembed($videoId) {
		$oembed = GeneralUtility::getUrl(
			'http://www.youtube.com/oembed?url=' . urlencode(sprintf('http://www.youtube.com/watch?v=%s', $videoId)) . '&format=json'
		);
		if ($oembed) {
			$oembed = json_decode($oembed);
		}
		return $oembed;
	}
}