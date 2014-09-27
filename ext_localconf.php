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
		// Register JavaScript
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['RequireJS']['postInitializationModules']['TYPO3/CMS/Backend/DragUploader'][]
			= 'TYPO3/CMS/FalOnlineMediaConnector/DragUploader';
		// Ajax controller
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
			'FalOnlineMediaConnector::onlineMedia',
			'MiniFranske\\FalOnlineMediaConnector\\Ajax\\OnlineMediaController->add'
		);
	}
	$onlineMediaHelperRegistry = \MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry::getInstance();
	$onlineMediaHelperRegistry->registerOnlineMediaFileExtension(
		'ytb',
		'MiniFranske\\FalOnlineMediaConnector\\Helpers\\YouTubeHelper',
		'extensions-online-media-youtube'
	);
	$onlineMediaHelperRegistry->registerOnlineMediaFileExtension(
		'vimeo',
		'MiniFranske\\FalOnlineMediaConnector\\Helpers\\VimeoHelper',
		'extensions-online-media-vimeo'
	);

	$rendererRegistry = \MiniFranske\FalOnlineMediaConnector\Rendering\RendererRegistry::getInstance();
	$rendererRegistry->registerRendererClass(
		'MiniFranske\\FalOnlineMediaConnector\\Rendering\\AudioTagRenderer'
	);
	$rendererRegistry->registerRendererClass(
		'MiniFranske\\FalOnlineMediaConnector\\Rendering\\VideoTagRenderer'
	);
	$rendererRegistry->registerRendererClass(
		'MiniFranske\\FalOnlineMediaConnector\\Rendering\\VimeoRenderer'
	);
	$rendererRegistry->registerRendererClass(
		'MiniFranske\\FalOnlineMediaConnector\\Rendering\\YouTubeRenderer'
	);


	$pageTsConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($packageKey) . 'Configuration/TsConfig/ContentElementWizard.ts');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTsConfig);

}, $_EXTKEY);