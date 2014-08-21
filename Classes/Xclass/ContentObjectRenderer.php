<?php
namespace MiniFranske\FalOnlineMediaConnector\Xclass;

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


use TYPO3\CMS\Core\Utility\GeneralUtility;
use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * Class ContentObjectRenderer
 */
class ContentObjectRenderer extends \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer {

	/**
	 * Returns a <img> tag with the image file defined by $file and processed according to the properties in the TypoScript array.
	 * Mostly this function is a sub-function to the IMAGE function which renders the IMAGE cObject in TypoScript.
	 * This function is called by "$this->cImage($conf['file'], $conf);" from IMAGE().
	 *
	 * @param string $file File TypoScript resource
	 * @param array $conf TypoScript configuration properties
	 * @return string <img> tag, (possibly wrapped in links and other HTML) if any image found.
	 * @access private
	 * @see IMAGE()
	 */
	public function cImage($file, $conf) {
		$info = $this->getImgResource($file, $conf['file.']);
		$GLOBALS['TSFE']->lastImageInfo = $info;
		if (!is_array($info)) {
			return '';
		}
		if (is_file(PATH_site . $info['3'])) {
			$source = GeneralUtility::rawUrlEncodeFP(GeneralUtility::png_to_gif_by_imagemagick($info[3]));
			$source = $GLOBALS['TSFE']->absRefPrefix . $source;
		} else {
			$source = $info[3];
		}

		$layoutKey = $this->stdWrap($conf['layoutKey'], $conf['layoutKey.']);
		$imageTagTemplate = $this->getImageTagTemplate($layoutKey, $conf);
		$sourceCollection = $this->getImageSourceCollection($layoutKey, $conf, $file);

		//-----------------

		/**
		 * todo: check if we can enhance
		 * - getImageResource to return source url (HOOK: ContentObjectGetImageResourceHookInterface)
		 * - layoutKey stdWrap to check lastImageInfo and return correct template
		 */
		if (($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($info['originalFile'])) !== FALSE) {
			$helper->getContentObjectRendererTemplateAndSource($imageTagTemplate, $source, $info['originalFile'], $conf, $this);
		}

		//-----------------

		// This array is used to collect the image-refs on the page...
		$GLOBALS['TSFE']->imagesOnPage[] = $source;
		$altParam = $this->getAltParam($conf);
		$params = $this->stdWrapValue('params', $conf);
		if ($params !== '' && $params{0} !== ' ') {
			$params = ' ' . $params;
		}

		$imageTagValues = array(
			'width' =>  $info[0],
			'height' => $info[1],
			'src' => htmlspecialchars($source),
			'params' => $params,
			'altParams' => $altParam,
			'border' =>  $this->getBorderAttr(' border="' . (int)$conf['border'] . '"'),
			'sourceCollection' => $sourceCollection,
			'selfClosingTagSlash' => (!empty($GLOBALS['TSFE']->xhtmlDoctype) ? ' /' : ''),
		);

		$theValue = $this->substituteMarkerArray($imageTagTemplate, $imageTagValues, '###|###', TRUE, TRUE);

		$linkWrap = isset($conf['linkWrap.']) ? $this->stdWrap($conf['linkWrap'], $conf['linkWrap.']) : $conf['linkWrap'];
		if ($linkWrap) {
			$theValue = $this->linkWrap($theValue, $linkWrap);
		} elseif ($conf['imageLinkWrap']) {
			$theValue = $this->imageLinkWrap($theValue, $info['originalFile'], $conf['imageLinkWrap.']);
		}
		$wrap = isset($conf['wrap.']) ? $this->stdWrap($conf['wrap'], $conf['wrap.']) : $conf['wrap'];
		if ($wrap) {
			$theValue = $this->wrap($theValue, $conf['wrap']);
		}
		return $theValue;
	}
}