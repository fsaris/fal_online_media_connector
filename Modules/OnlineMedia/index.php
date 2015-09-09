<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


$formProtection = \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get();
if ($formProtection->validateToken(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('formToken'), 'tceAction')) {
	$onlineMediaController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MiniFranske\\FalOnlineMediaConnector\\Controller\\OnlineMediaController');
	$onlineMediaController->main();
}