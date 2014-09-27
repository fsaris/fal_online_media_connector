<?php
namespace MiniFranske\FalOnlineMediaConnector\Rendering;

/**
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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RendererRegistry
 */
class RendererRegistry implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Registered ClassNames
	 * @var array
	 */
	protected $classNames = array();

	/**
	 * Instance Cache for Renderer classes
	 *
	 * @var FileRendererInterface[]
	 */
	protected $instances = NULL;

	/**
	 * Returns an instance of this class
	 *
	 * @return RendererRegistry
	 */
	public static function getInstance() {
		return GeneralUtility::makeInstance('MiniFranske\\FalOnlineMediaConnector\\Rendering\\RendererRegistry');
	}

	/**
	 * Allows to register a Renderer class
	 *
	 * @param string $className
	 * @throws \InvalidArgumentException
	 */
	public function registerRendererClass($className) {
		if (!class_exists($className)) {
			throw new \InvalidArgumentException('The Class you are registering is not available', 1411840174);
		} elseif (!in_array('MiniFranske\\FalOnlineMediaConnector\\Rendering\\FileRendererInterface', class_implements($className))) {
			throw new \InvalidArgumentException('The extractor needs to implement the FileRendererInterface', 1411840175);
		} else {
			$this->classNames[] = $className;
		}
	}
	/**
	 * Get all registered renderer instances
	 *
	 * @return array FileRendererInterface[]
	 */
	public function getRendererInstances() {
		if ($this->instances === NULL) {
			$this->instances = array();

			// as the result is in reverse order we need to reverse
			// the array before processing to keep the items with same
			// priority in the same order as they were added to the registry.
			$classNames = array_reverse($this->classNames);
			foreach ($classNames as $className) {
				/** @var FileRendererInterface $object */
				$object = $this->createRendererInstance($className);
				$this->instances[] = $object;
			}

			if (count($this->instances) > 1) {
				usort($this->instances, array($this, 'compareRendererPriority'));
			}
		}
		return $this->instances;
	}

	/**
	 * Create an instance of a certain renderer class
	 *
	 * @param $className
	 * @return FileRendererInterface
	 */
	protected function createRendererInstance($className) {
		return GeneralUtility::makeInstance($className);
	}

	/**
	 * Compare the priority of 2 Renderer classes
	 * Is used for sorting array of Renderer instances by priority
	 * We want the result to be ordered from high to low so a higher
	 * priority comes before a lower.
	 *
	 * @param FileRendererInterface $rendererA
	 * @param FileRendererInterface $rendererB
	 * @return int -1 a > b, 0 a == b, 1 a < b
	 */
	protected function compareRendererPriority(FileRendererInterface $rendererA, FileRendererInterface $rendererB) {
		return $rendererB->getPriority() - $rendererA->getPriority();
	}

	/**
	 * Get matching renderer with highest priority
	 *
	 * @param FileInterface $file
	 * @return NULL|FileRendererInterface
	 */
	public function getRenderer(FileInterface $file) {
		$matchingFileRenderer = NULL;

		/** @var FileRendererInterface $fileRenderer */
		foreach ($this->getRendererInstances() as $fileRenderer) {
			if ($fileRenderer->canRender($file)) {
				$matchingFileRenderer = $fileRenderer;
				break;
			}
		}
		return $matchingFileRenderer;
	}
}