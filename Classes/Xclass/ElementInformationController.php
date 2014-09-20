<?php
namespace MiniFranske\FalOnlineMediaConnector\Xclass;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Resource\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ElementInformationController
 */
class ElementInformationController extends \TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController {

	/**
	 * Render preview for current record
	 *
	 * @return string
	 */
	protected function renderPreview() {
		$preview = '';
		$downloadLink = '';

		// check if file is marked as missing
		if ($this->fileObject->isMissing()) {
			$flashMessage = BackendUtility::getFlashMessageForMissingFile($this->fileObject);
			$preview .= $flashMessage->render();

		} else {

			// Check if there is a FileRenderer
			/** @var \MiniFranske\FalOnlineMediaConnector\Rendering\RendererRegistry $rendererRegistry */
			$rendererRegistry = GeneralUtility::makeInstance('MiniFranske\\FalOnlineMediaConnector\\Rendering\\RendererRegistry');
			$fileRenderer = $rendererRegistry->getRenderer($this->fileObject);
			if ($fileRenderer !== NULL) {
				$preview = $fileRenderer->render(
					$this->fileObject,
					'590m',
					'400m',
					array(
						'title' => $this->fileObject->getName(),
						'showinfo' => TRUE,
					),
					TRUE
				);
			} else {
				$fileExtension = $this->fileObject->getExtension();
				// Image
				if (GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileExtension)) {
					$thumbUrl = $this->fileObject->process(
						ProcessedFile::CONTEXT_IMAGEPREVIEW,
						array(
							'width' => '590m',
							'height' => '400m'
						)
					)->getPublicUrl(TRUE);

					// Create thumbnail image?
					if ($thumbUrl) {
						$preview .= '<img src="' . $thumbUrl . '" ' .
							'alt="' . htmlspecialchars(trim($this->fileObject->getName())) . '" ' .
							'title="' . htmlspecialchars(trim($this->fileObject->getName())) . '" />';
					}
				}
			}

			// Display download link?
			$url = $this->fileObject->getPublicUrl(TRUE);

			if ($url) {
				$downloadLink .= '<a href="' . htmlspecialchars($url) . '" target="_blank" class="t3-button">' .
					IconUtility::getSpriteIcon('actions-edit-download') . ' ' .
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xlf:download', TRUE) .
					'</a>';
			}
		}

		return ($preview ? '<p>' . $preview . '</p>' : '') .
		($downloadLink ? '<p>' . $downloadLink . '</p>' : '');
	}
}