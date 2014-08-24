<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// Add video palette to sys_file_reference
$GLOBALS['TCA']['sys_file_reference']['palettes']['videooverlayPalette'] = array(
	'canNotCollapse' => 1,
	'showitem' => 'title,description',
);