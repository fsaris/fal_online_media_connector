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
	 * @param AbstractFile $file
	 * @param bool $relativeToCurrentScript
	 * @return string|NULL
	 */
	public function getPublicUrl(AbstractFile $file, $relativeToCurrentScript = FALSE) {
		$videoId = $this->getOnlineMediaId($file);
		return sprintf('http://vimeo.com/%s', $videoId);
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
			$source = sprintf('//player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0', $videoId);
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
	 * @return string
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

		$tag->addAttribute('src', sprintf('//player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0', $videoId));
		$tag->addAttribute('allowfullscreen', '');

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