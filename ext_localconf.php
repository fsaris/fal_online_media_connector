<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');


call_user_func(function($packageKey) {

	// Extend/Xclass ImageViewHelper to support custom output
	if (TRUE) { // todo: add switch in ext_configuration
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\\Fluid\\ViewHelpers\\ImageViewHelper'] = array(
			'className' => 'MiniFranske\\FalOnlineMediaConnector\\Xclass\\ImageViewHelper',
		);
	}
	if (TRUE) { // todo: add switch in ext_configuration
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Controller\\ShowImageController'] = array(
			'className' => 'MiniFranske\\FalOnlineMediaConnector\\Xclass\\ShowImageController',
		);
	}
	if (TRUE) { // todo: add switch in ext_configuration
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Controller\\ContentElement\\ElementInformationController'] = array(
			'className' => 'MiniFranske\\FalOnlineMediaConnector\\Xclass\\ElementInformationController',
		);
	}

	// Resource Icon hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideResourceIcon']['FalOnlineMediaConnector'] =
		'MiniFranske\\FalOnlineMediaConnector\\Hooks\\IconUtilityHook';

	// Media content element rendering hook
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['css_styled_content']['pi1_hooks']['render_singleMediaElement']['FalOnlineMediaConnector'] =
		'MiniFranske\\FalOnlineMediaConnector\\Hooks\\CssStylesContentController->renderSingleMediaElement';

	\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()->registerExtractionService(
		\TYPO3\CMS\Core\Resource\OnlineMedia\Metadata\Extractor::class
	);
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\Service\FileProcessingService::SIGNAL_PreFileProcess,
		\TYPO3\CMS\Core\Resource\OnlineMedia\Processing\PreviewProcessing::class,
		'processFile'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileAdd,
		\MiniFranske\FalOnlineMediaConnector\Hooks\ResourceStorageSlots::class,
		'postFileAdd'
	);
	$signalSlotDispatcher->connect(
		'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
		\TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
		\MiniFranske\FalOnlineMediaConnector\Hooks\ResourceStorageSlots::class,
		'generatePublicUrl'
	);
	unset($signalSlotDispatcher);

	if (TYPO3_MODE === 'BE') {
		// Register JavaScript
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['RequireJS']['postInitializationModules']['TYPO3/CMS/Backend/DragUploader'][]
			= 'TYPO3/CMS/FalOnlineMediaConnector/DragUploader';
		// Ajax controller
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
			'FalOnlineMediaConnector::onlineMedia',
			'MiniFranske\\FalOnlineMediaConnector\\Controller\\OnlineMediaController->addAjaxAction'
		);
	}

	// Online media helpers
	if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'])) {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'] = array(
			'youtube' => \TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\YouTubeHelper::class,
			'vimeo' => \TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\VimeoHelper::class,
		);
	}
	$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] .= ',youtube,vimeo';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_online_media']['icons']['youtube'] = 'extensions-online-media-youtube';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_online_media']['icons']['vimeo'] = 'extensions-online-media-vimeo';

	// Static mapping for file extensions to mime types.
	if (!isset($GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType'])) {
		$GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType'] = array();
	}
	$GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType']['youtube'] = 'video/youtube';
	$GLOBALS['TYPO3_CONF_VARS']['FileInfo']['fileExtensionToMimeType']['vimeo'] = 'video/vimeo';

	// Renderers
	$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();
	$rendererRegistry->registerRendererClass(
		\TYPO3\CMS\Core\Resource\Rendering\AudioTagRenderer::class
	);
	$rendererRegistry->registerRendererClass(
		\TYPO3\CMS\Core\Resource\Rendering\VideoTagRenderer::class
	);
	$rendererRegistry->registerRendererClass(
		\TYPO3\CMS\Core\Resource\Rendering\VimeoRenderer::class
	);
	$rendererRegistry->registerRendererClass(
		\TYPO3\CMS\Core\Resource\Rendering\YouTubeRenderer::class
	);
	unset($rendererRegistry);

	$pageTsConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($packageKey) . 'Configuration/TsConfig/ContentElementWizard.ts'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTsConfig);

}, $_EXTKEY);