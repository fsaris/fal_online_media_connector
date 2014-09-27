<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// Add video palette to sys_file_reference
$GLOBALS['TCA']['sys_file_reference']['palettes']['videooverlayPalette'] = array(
	'canNotCollapse' => 1,
	'showitem' => 'title, description, --linebreak--, autoplay',
);

/**
 * Add extra field autoplay to sys_file_reference record
 */
$newSysFileReferenceColumns = array(
	'autoplay' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:sys_file_reference.autoplay',
		'config' => array(
			'type' => 'check',
			'default' => 0
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $newSysFileReferenceColumns);
