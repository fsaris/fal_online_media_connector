<?php
namespace MiniFranske\FalOnlineMediaConnector\Xclass;

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

use MiniFranske\FalOnlineMediaConnector\Rendering\RendererRegistry;

/**
 * Class ShowImageController
 */
class ShowImageController extends \TYPO3\CMS\Frontend\Controller\ShowImageController {

	/**
	 * Check if there is a OnlineMediaHelper for requested file
	 * else use normal ShowImage handling
	 */
	public function main() {

		/** @var $helper \MiniFranske\FalOnlineMediaConnector\Rendering\FileRendererInterface */
		if (($fileRenderer = RendererRegistry::getInstance()->getRenderer($this->file)) !== NULL) {
			$output = $fileRenderer->render($this->file, $this->width, $this->height);
			$markerArray = array(
				'###TITLE###' => ($this->file->getProperty('title') ?: $this->title),
				'###IMAGE###' => $output,
				'###BODY###' => $this->bodyTag
			);
			$this->content = str_replace(array_keys($markerArray), array_values($markerArray), $this->content);
		} else {
			parent::main();
		}
	}
}