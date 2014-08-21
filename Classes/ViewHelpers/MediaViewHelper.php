<?php
namespace MiniFranske\FalOnlineMediaConnector\ViewHelpers;

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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * Class MediaViewHelper
 */
class MediaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper {

	/**
	 * Render a given media file (resized if required)
	 *
	 * @param FileInterface|AbstractFileFolder $file
	 * @param array $additionalConfig
	 * @param string $width
	 * @param string $height
	 * @param integer $minWidth
	 * @param integer $minHeight
	 * @param integer $maxWidth
	 * @param integer $maxHeight
	 * @return string Rendered tag
	 */
	public function render($file, $additionalConfig = array(), $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL) {

		// get Resource Object (non ExtBase version)
		$file = $this->imageService->getImage(NULL, $file, FALSE);
		if ($file instanceof \TYPO3\CMS\Core\Resource\FileReference) {
			$OriginalFile = $file->getOriginalFile();
		} else {
			$OriginalFile = $file;
		}

		// Fallback to imageViewHelper
		if (
			!empty($additionalConfig['forceStaticImage'])
			||
			($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($OriginalFile)) === FALSE
		) {
			return parent::render(NULL, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight, FALSE, $file);
		}

		return $helper->renderTag($this->tag, $file, $additionalConfig, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight);
	}
}