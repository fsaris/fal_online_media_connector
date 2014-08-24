<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// Change showitem palette for video items
$GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']['4']['showitem'] =
	str_replace(
		'sys_file_reference.imageoverlayPalette;imageoverlayPalette',
		'sys_file_reference.imageoverlayPalette;videooverlayPalette',
		$GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']['4']['showitem']
	);