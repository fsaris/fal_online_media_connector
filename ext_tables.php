<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'OnlineMedia');


\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(array(
	'youtube' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/youtube.png',
	'vimeo' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/vimeo.png',
), 'online-media');


// Register online_media
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath(
	'online_media',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Modules/OnlineMedia/'
);