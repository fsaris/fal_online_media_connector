<?php
namespace MiniFranske\FalOnlineMediaConnector\Hooks;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 09-09-2014 22:59
 * All code (c) Beech Applications B.V. all rights reserved
 */

use MiniFranske\FalOnlineMediaConnector\Rendering\RendererRegistry;
use TYPO3\CMS\CssStyledContent\Controller\CssStyledContentController;

/**
 * Class CssStylesContentController
 */
class CssStylesContentController {

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
	 */
	protected $cObj;

	/**
	 * @param array $parameters
	 * @param CssStyledContentController $cssStyledContentController
	 * @return string|NULL
	 */
	public function renderSingleMediaElement($parameters, $cssStyledContentController) {
		$this->cObj = $cssStyledContentController->cObj;
		$file = $cssStyledContentController->cObj->getCurrentFile();
		if ($file) {
			$content = NULL;
		}
		if (!empty($file) && ($fileRenderer = RendererRegistry::getInstance()->getRenderer($file)) !== NULL) {
			list($width, $height) = $this->fetchDimensionsFromConfig($parameters['imageConfiguration']['file.']);
			$content = $fileRenderer->render($file, $width, $height, $parameters['imageConfiguration']);

			if (isset($parameters['imageConfiguration']['imageLinkWrap.'])) {
				// @todo: wrap in link?
			}

			if ($content !== NULL && isset($parameters['imageConfiguration']['stdWrap.'])) {
				$content = $this->cObj->stdWrap($content, $parameters['imageConfiguration']['stdWrap.']);
			}
		}
		return $content;
	}


	/**
	 * Parse given $conf and return width and height values
	 *
	 * @param $fileArray
	 * @return array(width,height)
	 */
	protected function fetchDimensionsFromConfig($fileArray) {
		$width = isset($fileArray['width.']) ? $this->cObj->stdWrap($fileArray['width'], $fileArray['width.']) : $fileArray['width'];
		$height = isset($fileArray['height.']) ? $this->cObj->stdWrap($fileArray['height'], $fileArray['height.']) : $fileArray['height'];

		if (empty($width)) {
			$width = isset($fileArray['maxW.']) ? (int)$this->cObj->stdWrap($fileArray['maxW'], $fileArray['maxW.']) : (int)$fileArray['maxW'];
			if ($width) {
				$width .= 'm';
			}
		}
		if (empty($height)) {
			$height = isset($fileArray['maxH.']) ? (int)$this->cObj->stdWrap($fileArray['maxH'], $fileArray['maxH.']) : (int)$fileArray['maxH'];
			if ($height) {
				$height .= 'm';
			}
		}

		return array(
			$width,
			$height
		);
	}
}