<?php
namespace MiniFranske\FalOnlineMediaConnector\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <frans@beech.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * IconUtility Hook to add "file" icons for Online Media
 */
class IconUtilityHook implements \TYPO3\CMS\Backend\Utility\IconUtilityOverrideResourceIconHookInterface {

	/**
	 * @var array
	 */
	static protected $mediaFolders;

	/**
	 * @param ResourceInterface $file
	 * @param $iconName
	 * @param array $options
	 * @param array $overlays
	 */
	public function overrideResourceIcon(ResourceInterface $file, &$iconName, array &$options, array &$overlays) {

		if ($file && $file instanceof File && !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_online_media']['icons'])) {
			if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_online_media']['icons'][$file->getExtension()])) {
				$iconName = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_online_media']['icons'][$file->getExtension()];
			}
		}
	}
}