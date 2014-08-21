<?php
namespace MiniFranske\FalOnlineMediaConnector\Aspects;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <franssaris@gmail.com>
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

use MiniFranske\FalOnlineMediaConnector\Helpers\OnlineMediaHelperRegistry;

/**
 * Class PublicUrlAspect
 */
class PublicUrlAspect {

	/**
	 * Generate public url for file
	 *
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $storage
	 * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param $relativeToCurrentScript
	 * @param array $urlData
	 * @return void
	 */
	public function generatePublicUrl(\TYPO3\CMS\Core\Resource\ResourceStorage $storage, \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver, \TYPO3\CMS\Core\Resource\FileInterface $file, $relativeToCurrentScript, array $urlData) {

		if (($helper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($file)) !== FALSE) {
			// $urlData['publicUrl'] is passed by reference, so we can change that here and the value will be taken into account
			$urlData['publicUrl'] = $helper->getPublicUrl($file, $relativeToCurrentScript);
		}
	}
}