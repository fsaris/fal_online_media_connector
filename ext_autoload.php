<?php

$extensionPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('fal_online_media_connector');
$extensionClassesPath = $extensionPath . 'Classes/Backport/';

return array(
	'TYPO3\CMS\Core\Resource\Rendering\AudioTagRenderer' => $extensionClassesPath . 'Rendering/AudioTagRenderer.php',
	'TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface' => $extensionClassesPath . 'Rendering/FileRendererInterface.php',
	'TYPO3\CMS\Core\Resource\Rendering\RendererRegistry' => $extensionClassesPath . 'Rendering/RendererRegistry.php',
	'TYPO3\CMS\Core\Resource\Rendering\VideoTagRenderer' => $extensionClassesPath . 'Rendering/VideoTagRenderer.php',
	'TYPO3\CMS\Core\Resource\Rendering\VimeoRenderer' => $extensionClassesPath . 'Rendering/VimeoRenderer.php',
	'TYPO3\CMS\Core\Resource\Rendering\YouTubeRenderer' => $extensionClassesPath . 'Rendering/YouTubeRenderer.php',

	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper' => $extensionClassesPath . 'OnlineMedia/Helpers/AbstractOEmbedHelper.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOnlineMediaHelper' => $extensionClassesPath . 'OnlineMedia/Helpers/AbstractOnlineMediaHelper.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface' => $extensionClassesPath . 'OnlineMedia/Helpers/OnlineMediaHelperInterface.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry' => $extensionClassesPath . 'OnlineMedia/Helpers/OnlineMediaHelperRegistry.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\VimeoHelper' => $extensionClassesPath . 'OnlineMedia/Helpers/VimeoHelper.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\YouTubeHelper' => $extensionClassesPath . 'OnlineMedia/Helpers/YouTubeHelper.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Metadata\Extractor' => $extensionClassesPath . 'OnlineMedia/Metadata/Extractor.php',
	'TYPO3\CMS\Core\Resource\OnlineMedia\Processing\PreviewProcessing' => $extensionClassesPath . 'OnlineMedia/Processing/PreviewProcessing.php',
);