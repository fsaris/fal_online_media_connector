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
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;


/**
 * Class VimeoHelper
 */
class VimeoHelper implements OnlineMediaHelperInterface {

	/**
	 * @var array
	 */
	protected $infoCache = array();

	/**
	 * Cached videoIds [fileUid => videoId]
	 *
	 * @var array
	 */
	protected $videoIdCache = array();

	/**
	 * Get Vimeo id
	 *
	 * @param File $file
	 * @return string
	 */
	protected function getVideoId(File $file) {
		if (!array_key_exists($file->getUid(), $this->videoIdCache)) {
			// By definition these files contain the video ID
			$this->videoIdCache[$file->getUid()] = trim($file->getContents());
		}
		return $this->videoIdCache[$file->getUid()];
	}

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
			$info = unserialize(file_get_contents("http://vimeo.com/api/v2/video/" . $videoId . ".php"));
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
		$videoId = $this->getVideoId($file);
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
	public function getContentObjectRendererTemplateAndSource(&$template, &$source, File $file, array $config, ContentObjectRenderer $contentObjectRenderer) {
		// todo: add min width check, maybe read from TypoScript?
		if (TRUE) {
			$videoId = $this->getVideoId($file);
			$template = '<iframe width="###WIDTH###" height="###HEIGHT###"' .
				' src="###SRC###" ###PARAMS######ALTPARAMS######BORDER### ' .
				' frameborder="0" allowfullscreen></iframe>';
			$source = sprintf('//player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0', $videoId);
		}
	}

	/**
	 * Render the tag
	 *
	 * @param TagBuilder $tag
	 * @param FileInterface $file
	 * @param array $additionalConfig
	 * @param null $width
	 * @param null $height
	 * @param null $minWidth
	 * @param null $minHeight
	 * @param null $maxWidth
	 * @param null $maxHeight
	 * @return string
	 */
	public function renderTag(TagBuilder $tag, FileInterface $file, $additionalConfig = array(), $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL) {
		$videoId = $this->getVideoId($file);
		$tag->setTagName('iframe');
		$tag->forceClosingTag(TRUE);

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
		$videoId = $this->getVideoId($file);
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
	 * Get meta data for OnlineMedia item
	 * See $TCA[sys_file_metadata][columns] for possible fields to fill/use
	 *
	 * @param File $file
	 * @return array with metadata
	 */
	public function getMetaData(File $file) {
		$metadata = array();
		$info = $this->getVideoInfo($this->getVideoId($file));

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

		return $metadata;
	}

}