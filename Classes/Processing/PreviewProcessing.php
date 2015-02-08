<?php
namespace MiniFranske\FalOnlineMediaConnector\Processing;

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

use TYPO3\CMS\Core\Utility;
use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * Preview of Online Media item Processing
 *
 * Inspired by code from Helmut Hummel (https://gist.github.com/helhum/e4a6cbdfd18c4f2f59f7)
 */
class PreviewProcessing {

	/**
	 * @var \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
	 */
	protected $processor;

	/**
	 * @param \TYPO3\CMS\Core\Resource\ProcessedFile $processedFile
	 * @return bool
	 */
	protected function needsReprocessing($processedFile) {
		return ($processedFile->isNew() || (!$processedFile->usesOriginalFile() && !$processedFile->exists()) ||
			$processedFile->isOutdated());
	}

	/**
	 * Process file
	 *
	 * Create static image preview for Online Media item when possible
	 *
	 * @param \TYPO3\CMS\Core\Resource\Service\FileProcessingService $fileProcessingService
	 * @param \TYPO3\CMS\Core\Resource\Driver\AbstractDriver $driver
	 * @param \TYPO3\CMS\Core\Resource\ProcessedFile $processedFile
	 * @param \TYPO3\CMS\Core\Resource\File $file
	 * @param string $taskType
	 * @param array $configuration
	 */
	public function processFile(\TYPO3\CMS\Core\Resource\Service\FileProcessingService $fileProcessingService, \TYPO3\CMS\Core\Resource\Driver\AbstractDriver $driver, \TYPO3\CMS\Core\Resource\ProcessedFile $processedFile, \TYPO3\CMS\Core\Resource\File $file, $taskType, array $configuration) {

		if (!in_array($taskType, array('Image.Preview', 'Image.CropScaleMask'))) {
			return;
		}
		// Check if there is a OnlineMediaHelper registerd for this file type
		if (($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) === FALSE) {
			return;
		}
		// check if processing is needed
		if (!$this->needsReprocessing($processedFile)) {
			return;
		}

		$temporaryFileName = $helper->getPreviewImage($file);
		$temporaryFileNameForResizedThumb = uniqid(PATH_site . 'typo3temp/online_media_' . $file->getHashedIdentifier()) . '.jpg';

		switch ($taskType) {
			case 'Image.Preview':
				// Merge custom configuration with default configuration
				$configuration = array_merge(array('width' => 64, 'height' => 64), $configuration);
				$configuration['width'] = Utility\MathUtility::forceIntegerInRange($configuration['width'], 1, 1000);
				$configuration['height'] = Utility\MathUtility::forceIntegerInRange($configuration['height'], 1, 1000);
				$this->resizeImage($temporaryFileName, $temporaryFileNameForResizedThumb, $configuration);
				break;

			case 'Image.CropScaleMask':
				$this->cropScaleImage($temporaryFileName, $temporaryFileNameForResizedThumb, $configuration);
				break;
		}
		if (is_file($temporaryFileNameForResizedThumb)) {
			$processedFile->setName($this->getTargetFileName($processedFile));
			list($width, $height) = getImageSize($temporaryFileNameForResizedThumb);
			$processedFile->updateProperties(
				array(
					'width' => $width,
					'height' => $height,
					'size' => filesize($temporaryFileNameForResizedThumb),
					'checksum' => $processedFile->getTask()->getConfigurationChecksum())
			);
			$processedFile->updateWithLocalFile($temporaryFileNameForResizedThumb);

			/** @var $processedFileRepository \TYPO3\CMS\Core\Resource\ProcessedFileRepository */
			$processedFileRepository = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ProcessedFileRepository');
			$processedFileRepository->add($processedFile);
		}
	}

	/**
	 * @param \TYPO3\CMS\Core\Resource\ProcessedFile $processedFile
	 * @param string $prefix
	 * @return string
	 */
	protected function getTargetFileName(\TYPO3\CMS\Core\Resource\ProcessedFile $processedFile, $prefix = 'preview_') {
		return $prefix . $processedFile->getTask()->getConfigurationChecksum() . '_' . $processedFile->getOriginalFile()->getNameWithoutExtension() . '.jpg';
	}

	/**
	 * @param string $originalFileName
	 * @param string $temporaryFileName
	 * @param array $configuration
	 */
	protected function resizeImage($originalFileName, $temporaryFileName, $configuration) {
		// Create the temporary file
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im']) {

			if (file_exists($originalFileName)) {
				$parameters = '-sample ' . $configuration['width'] . 'x' . $configuration['height'] . ' '
					. $this->wrapFileName($originalFileName) . '[0] ' . $this->wrapFileName($temporaryFileName);

				$cmd = Utility\GeneralUtility::imageMagickCommand('convert', $parameters) . ' 2>&1';
				Utility\CommandUtility::exec($cmd);
			}

			if (!file_exists($temporaryFileName)) {
				// Create a error gif
				$this->getProcessor()->getTemporaryImageWithText($temporaryFileName, 'No thumb', 'generated!', basename($originalFileName));
			}
		}
	}

	/**
	 * cropScaleImage
	 *
	 * todo: cleanup function
	 *
	 * @param $originalFileName
	 * @param $temporaryFileName
	 * @param $configuration
	 */
	protected function cropScaleImage($originalFileName, $temporaryFileName, $configuration) {
		if (file_exists($originalFileName)) {
			/** @var $gifBuilder \TYPO3\CMS\Frontend\Imaging\GifBuilder */
			$gifBuilder = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder');
			$gifBuilder->init();

			$options = $this->getConfigurationForImageCropScaleMask($configuration, $gifBuilder);
			$info = $gifBuilder->getImageDimensions($originalFileName);
			$data = $gifBuilder->getImageScale($info, $configuration['width'], $configuration['height'], $options);

			$info[0] = $data[0];
			$info[1] = $data[1];
			$frame = '';
	//		if (!$params) {
			$params = $gifBuilder->cmds['jpg'];
	//		}
			// Cropscaling:
			if ($data['crs']) {
				if (!$data['origW']) {
					$data['origW'] = $data[0];
				}
				if (!$data['origH']) {
					$data['origH'] = $data[1];
				}
				$offsetX = (int)(($data[0] - $data['origW']) * ($data['cropH'] + 100) / 200);
				$offsetY = (int)(($data[1] - $data['origH']) * ($data['cropV'] + 100) / 200);
				$params .= ' -crop ' . $data['origW'] . 'x' . $data['origH'] . '+' . $offsetX . '+' . $offsetY . '! ';
			}
			$command = $gifBuilder->scalecmd . ' ' . $info[0] . 'x' . $info[1] . '! ' . $params . ' ';
			$gifBuilder->imageMagickExec($originalFileName, $temporaryFileName, $command, $frame);
		}
		if (!file_exists($temporaryFileName)) {
			// Create a error gif
			$this->getProcessor()->getTemporaryImageWithText($temporaryFileName, 'No thumb', 'generated!', basename($originalFileName));
		}
	}


	/**
	 * Escapes a file name so it can safely be used on the command line.
	 *
	 * @param string $inputName filename to safeguard, must not be empty
	 * @return string $inputName escaped as needed	 *
	 */
	protected function wrapFileName($inputName) {
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem'])) {
			$currentLocale = setlocale(LC_CTYPE, 0);
			setlocale(LC_CTYPE, $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']);
			$escapedInputName = escapeshellarg($inputName);
			setlocale(LC_CTYPE, $currentLocale);
		} else {
			$escapedInputName = escapeshellarg($inputName);
		}
		return $escapedInputName;
	}

	/**
	 * @return \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
	 */
	protected function getProcessor() {
		if (!$this->processor) {
			$this->processor = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor');
		}
		return $this->processor;
	}

	/**
	 * @param array $configuration
	 * @param \TYPO3\CMS\Frontend\Imaging\GifBuilder $gifBuilder
	 * @return array
	 */
	protected function getConfigurationForImageCropScaleMask($configuration, \TYPO3\CMS\Frontend\Imaging\GifBuilder $gifBuilder) {
		if ($configuration['useSample']) {
			$gifBuilder->scalecmd = '-sample';
		}
		$options = array();
		if ($configuration['maxWidth']) {
			$options['maxW'] = $configuration['maxWidth'];
		}
		if ($configuration['maxHeight']) {
			$options['maxH'] = $configuration['maxHeight'];
		}
		if ($configuration['minWidth']) {
			$options['minW'] = $configuration['minWidth'];
		}
		if ($configuration['minHeight']) {
			$options['minH'] = $configuration['minHeight'];
		}

		$options['noScale'] = $configuration['noScale'];

		return $options;
	}
}