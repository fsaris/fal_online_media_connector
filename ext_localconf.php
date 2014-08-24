<?php
/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 16-08-2014 15:12
 * All code (c) Beech Applications B.V. all rights reserved
 */

// Extend ContentObjectRenderer to support custom output
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'] = array(
	'className' => 'MiniFranske\\FalOnlineMediaConnector\\Xclass\\ContentObjectRenderer',
);

// Extend/Xclass ImageViewHelper to support custom output
if (TRUE) { // todo: add switch in ext_configuration
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\\Fluid\\ViewHelpers\\ImageViewHelper'] = array(
		'className' => 'MiniFranske\\FalOnlineMediaConnector\\Xclass\\ImageViewHelper',
	);
}

// Resource Icon hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideResourceIcon']['FalOnlineMediaConnector'] =
	'MiniFranske\\FalOnlineMediaConnector\\Hooks\\IconUtilityHook';

\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()->registerExtractionService(
	'MiniFranske\\FalOnlineMediaConnector\\Metadata\\Extractor'
);

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
	'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
	\TYPO3\CMS\Core\Resource\Service\FileProcessingService::SIGNAL_PreFileProcess,
	'MiniFranske\\FalOnlineMediaConnector\\Processing\\PreviewProcessing',
	'processFile'
);

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
	'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
	\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
	'MiniFranske\\FalOnlineMediaConnector\\Aspects\\PublicUrlAspect',
	'generatePublicUrl'
);


if (TYPO3_MODE === 'BE') {
	// Register JS
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['RequireJS']['postInitializationModules']['TYPO3/CMS/Backend/DragUploader'][]
		= '/typo3conf/ext/fal_online_media_connector/Resources/Public/Js/DragUploader';
	// Ajax controller
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
		'FalOnlineMediaConnector::onlineMedia',
		'MiniFranske\\FalOnlineMediaConnector\\Ajax\\OnlineMediaController->add'
	);
}

\MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry::getInstance()->registerOnlineMediaFileExtension(
	'ytb',
	'MiniFranske\\FalOnlineMediaConnector\\Helpers\\YouTubeHelper',
	'extensions-online-media-youtube'
);

\MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry::getInstance()->registerOnlineMediaFileExtension(
	'vimeo',
	'MiniFranske\\FalOnlineMediaConnector\\Helpers\\VimeoHelper',
	'extensions-online-media-vimeo'
);