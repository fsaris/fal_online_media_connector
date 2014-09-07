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
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class YouTubeHelper
 */
class YouTubeHelper extends AbstractOnlineMediaHelper {

	/**
	 * Get public url
	 *
	 * @param AbstractFile $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(AbstractFile $file, $relativeToCurrentScript = FALSE) {
		$videoId = $this->getOnlineMediaId($file);
		return sprintf('https://www.youtube.com/watch?v=%s', $videoId);
	}

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
	public function getContentObjectRendererTemplateAndSource(&$template, &$source, File $file, array &$config, ContentObjectRenderer $contentObjectRenderer) {

		// if linked then don't show iframe
		if (empty($config['imageLinkWrap'])) {
			$videoId = $this->getOnlineMediaId($file);
			$template = '<iframe width="###WIDTH###" height="###HEIGHT###"' .
						' src="###SRC###" ###PARAMS######ALTPARAMS######BORDER### ' .
						' frameborder="0" allowfullscreen></iframe>';
			$source = sprintf('//www.youtube.com/embed/%s?controls=2&showinfo=0', $videoId);
		}

		// use public url (direct link to video) when linking the 'preview' image
		if (!empty($config['imageLinkWrap.']['directImageLink'])) {
			$config['imageLinkWrap.']['typolink.']['parameter.']['data'] = 'file:current:publicUrl';
		}
	}

	/**
	 * Render the tag
	 *
	 * @param TagBuilder $tag
	 * @param FileInterface $file
	 * @param array $additionalConfig
	 * @param integer|string $width TYPO3 known format; examples: 220, 200m or 200c
	 * @param integer|string $height TYPO3 known format; examples: 220, 200m or 200c
	 * @return string|bool
	 */
	public function renderTag(TagBuilder $tag, FileInterface $file, $additionalConfig = array(), $width = '', $height = '') {
		$videoId = $this->getOnlineMediaId($file);

		$tag->setTagName('iframe');
		$tag->forceClosingTag(TRUE);

		$width = (int)$width;
		if (!empty($width)) {
			$tag->addAttribute('width', $width);
		}
		$height = (int)$height;
		if (!empty($height)) {
			$tag->addAttribute('height', $height);
		}

		$tag->addAttribute('src', sprintf('//www.youtube.com/embed/%s?controls=2&showinfo=0', $videoId));
		$tag->addAttribute('allowfullscreen', '');

		if (is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->config['config']['doctype'] !== 'html5') {
			$tag->addAttribute('frameborder', '0');
		}

		// wrap in div so you can make is responsive
		return '<div class="video-container">' . $tag->render() . '</div>';
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
			$info = $this->getYouTubeInfo($videoId);
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
	 * @param File $file
	 * @return array with metadata
	 */
	public function getMetaData(File $file) {
		$metadata = array();
		$info = $this->getYouTubeInfo($this->getOnlineMediaId($file));

		if ($info) {
			// todo: add more fields to index
			if (!$file->getProperty('title')) {
				$metadata['title'] = $info->entry->title->{'$t'};
			}
			if (!$file->getProperty('description')) {
				$metadata['description'] = $info->entry->{'media$group'}->{'media$description'}->{'$t'};
			}
			$metadata['duration'] = $info->entry->{'media$group'}->{'media$content'}[0]->{'duration'};
			$metadata['source'] = 'YouTube.com';
		}
		$metadata['type'] = File::FILETYPE_VIDEO;
		$metadata['mime_type'] = 'video/youtube';

		return $metadata;
	}


	/**
	 * Get YouTube video info
	 *
	 * @param string $videoId
	 * @return array|null
	 */
	protected function getYouTubeInfo($videoId) {
		$info = GeneralUtility::getUrl(
			sprintf('https://gdata.youtube.com/feeds/api/videos/%s?v=2&alt=json', $videoId)
		);
		if ($info) {
			$info = json_decode($info);
		}
		return $info;
	}
}