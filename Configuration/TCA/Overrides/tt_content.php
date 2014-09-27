<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// Change showitem palette for video items
$GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']['4']['showitem'] =
	str_replace(
		'sys_file_reference.imageoverlayPalette;imageoverlayPalette',
		'sys_file_reference.imageoverlayPalette;videooverlayPalette',
		$GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']['4']['showitem']
	);

$GLOBALS['TCA']['tt_content']['columns']['image']['label'] =
	'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:tt_content.media';

$GLOBALS['TCA']['tt_content']['columns']['image']['config']['appearance']['createNewRelationLinkTitle'] =
	'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:media.addFileReference';

foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $key => $item) {
	if ($item[1] === 'textpic') {
		$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][$key][0] =
			'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textMedia_title';
	}
	if ($item[1] === 'image') {
		$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][$key][0] =
			'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db_new_content_el.xlf:common_mediaOnly_title';
	}
}

foreach ($GLOBALS['TCA']['tt_content']['types'] as $key => $typeConf) {
	if (!empty($typeConf['showitem'])) {

		$replace = array(
			'LLL:EXT:cms/locallang_ttc.xlf:tabs.images' =>
				'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:tt_content.media',
			'LLL:EXT:cms/locallang_ttc.xlf:palette.image_settings' =>
				'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:palette.media_settings',
			'LLL:EXT:cms/locallang_ttc.xlf:palette.imageblock' =>
				'LLL:EXT:fal_online_media_connector/Resources/Private/Language/locallang_db.xlf:palette.mediablock',
		);

		$GLOBALS['TCA']['tt_content']['types'][$key]['showitem'] =
			str_replace(
				array_keys($replace),
				$replace,
				$typeConf['showitem']
			);
	}
}