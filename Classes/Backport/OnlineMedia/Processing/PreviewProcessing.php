<?php
namespace TYPO3\CMS\Core\Resource\OnlineMedia\Processing;

/*
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
use TYPO3\CMS\Core\Resource\Driver\AbstractDriver;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor;
use TYPO3\CMS\Core\Resource\Service\FileProcessingService;
use TYPO3\CMS\Core\Utility;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;

/**
 * Preview of Online Media item Processing
 */
class PreviewProcessing {

	/**
	 * @var LocalImageProcessor
	 */
	protected $processor;

	/**
	 * @param ProcessedFile $processedFile
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
	 * @param FileProcessingService $fileProcessingService
	 * @param AbstractDriver $driver
	 * @param ProcessedFile $processedFile
	 * @param File $file
	 * @param string $taskType
	 * @param array $configuration
	 */
	public function processFile(FileProcessingService $fileProcessingService, AbstractDriver $driver, ProcessedFile $processedFile, File $file, $taskType, array $configuration) {

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
	 * @param ProcessedFile $processedFile
	 * @param string $prefix
	 * @return string
	 */
	protected function getTargetFileName(ProcessedFile $processedFile, $prefix = 'preview_') {
		return $prefix . $processedFile->getTask()->getConfigurationChecksum() . '_' . $processedFile->getOriginalFile()->getNameWithoutExtension() . '.jpg';
	}

	/**
	 * @param string $originalFileName
	 * @param string $temporaryFileName
	 * @param array $configuration
	 */
	protected function resizeImage($originalFileName, $temporaryFileName, $configuration) {
		// Create the temporary file
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'])) {

			if (file_exists($originalFileName)) {
				$arguments = self::escapeShellArguments([
					'width' => $configuration['width'],
					'height' => $configuration['height'],
					'originalFileName' => $originalFileName,
					'temporaryFileName' => $temporaryFileName,
				]);
				$parameters = '-sample ' . $arguments['width'] . 'x' . $arguments['height'] . ' '
					. $arguments['originalFileName'] . '[0] ' . $arguments['temporaryFileName'];

				$cmd = Utility\GeneralUtility::imageMagickCommand('convert', $parameters) . ' 2>&1';
				Utility\CommandUtility::exec($cmd);
			}

			if (!file_exists($temporaryFileName)) {
				// Create a error image
				$graphicalFunctions = $this->getGraphicalFunctionsObject();
				$graphicalFunctions->getTemporaryImageWithText($temporaryFileName, 'No thumb', 'generated!', basename($originalFileName));
			}
		}
	}

	/**
	 * cropScaleImage
	 *
	 * @param string $originalFileName
	 * @param string $temporaryFileName
	 * @param array $configuration
	 */
	protected function cropScaleImage($originalFileName, $temporaryFileName, $configuration) {
		if (file_exists($originalFileName)) {
			/** @var $gifBuilder GifBuilder */
			$gifBuilder = Utility\GeneralUtility::makeInstance(GifBuilder::class);
			$gifBuilder->init();

			$options = $this->getConfigurationForImageCropScaleMask($configuration, $gifBuilder);
			$info = $gifBuilder->getImageDimensions($originalFileName);
			$data = $gifBuilder->getImageScale($info, $configuration['width'], $configuration['height'], $options);

			$info[0] = $data[0];
			$info[1] = $data[1];
			$frame = '';
			$params = $gifBuilder->cmds['jpg'];

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
			// Create a error image
			$graphicalFunctions = $this->getGraphicalFunctionsObject();
			$graphicalFunctions->getTemporaryImageWithText($temporaryFileName, 'No thumb', 'generated!', basename($originalFileName));
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
	 * Get configuration for ImageCropScaleMask processing
	 *
	 * @param array $configuration
	 * @param GifBuilder $gifBuilder
	 * @return array
	 */
	protected function getConfigurationForImageCropScaleMask($configuration, GifBuilder $gifBuilder) {
		if (!empty($configuration['useSample'])) {
			$gifBuilder->scalecmd = '-sample';
		}
		$options = array();
		if (!empty($configuration['maxWidth'])) {
			$options['maxW'] = $configuration['maxWidth'];
		}
		if (!empty($configuration['maxHeight'])) {
			$options['maxH'] = $configuration['maxHeight'];
		}
		if (!empty($configuration['minWidth'])) {
			$options['minW'] = $configuration['minWidth'];
		}
		if (!empty($configuration['minHeight'])) {
			$options['minH'] = $configuration['minHeight'];
		}

		$options['noScale'] = $configuration['noScale'];

		return $options;
	}

	/**
	 * Escape shell arguments (for example filenames) to be used on the local system.
	 *
	 * The setting UTF8filesystem will be taken into account.
	 *
	 * @param string[] $input Input arguments to be escaped
	 * @return string[] Escaped shell arguments
	 */
	static public function escapeShellArguments(array $input) {
		$isUTF8Filesystem = !empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem']);
		if ($isUTF8Filesystem) {
			$currentLocale = setlocale(LC_CTYPE, 0);
			setlocale(LC_CTYPE, $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']);
		}

		$output = array_map('escapeshellarg', $input);

		if ($isUTF8Filesystem) {
			setlocale(LC_CTYPE, $currentLocale);
		}

		return $output;
	}

	/**
	 * @return \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
	 */
	protected function getProcessor() {
		if (!$this->processor) {
			$this->processor = Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor::class);
		}
		return $this->processor;
	}

	/**
	 * @return \TYPO3\CMS\Core\Imaging\GraphicalFunctions
	 */
	protected function getGraphicalFunctionsObject() {
		static $graphicalFunctionsObject = NULL;
		if ($graphicalFunctionsObject === NULL) {
			$graphicalFunctionsObject = Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\GraphicalFunctions::class);
		}
		return $graphicalFunctionsObject;
	}

}
