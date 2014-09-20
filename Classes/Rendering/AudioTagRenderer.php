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

/**
 * Class AudioTagRenderer
 */
class AudioTagRenderer implements FileRendererInterface {

	/**
	 * Mime types that can be used in the HTML Video tag
	 *
	 * @var array
	 */
	protected $possibleMimeTypes = array('audio/mpeg', 'audio/wav', 'audio/ogg');

	/**
	 * Returns the priority of the renderer
	 * This way it is possible to define/overrule a renderer
	 * for a specific file type/context.
	 * For example create a video renderer for a certain storage/driver type.
	 * Should be between 1 and 100, 100 is more important than 1
	 *
	 * @return integer
	 */
	public function getPriority() {
		return 1;
	}

	/**
	 * Check if given File(Reference) can be rendered
	 *
	 * @param FileInterface $file File of FileReference to render
	 * @return bool
	 */
	public function canRender(FileInterface $file) {
		return in_array($file->getMimeType(), $this->possibleMimeTypes);
	}

	/**
	 * Render for given File(Reference) html output
	 *
	 * @param FileInterface $file
	 * @param integer|string $width TYPO3 known format; examples: 220, 200m or 200c
	 * @param integer|string $height TYPO3 known format; examples: 220, 200m or 200c
	 * @param array $options
	 * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
	 * @return string
	 */
	public function render(FileInterface $file, $width, $height, array $options = NULL, $usedPathsRelativeToCurrentScript = FALSE) {
		$additionalAttributes = array();
		if (!isset($options['controls']) || !empty($options['controls'])) {
			$additionalAttributes[] = 'controls';
		}
		if (!empty($options['autoplay'])) {
			$additionalAttributes[] = 'autoplay';
		}
		if (!empty($options['loop'])) {
			$additionalAttributes[] = 'loop';
		}

		return sprintf(
			'<audio %s><source src="%s" type="%s"></audio>>',
			implode(' ', $additionalAttributes),
			$file->getPublicUrl($usedPathsRelativeToCurrentScript),
			$file->getMimeType()
		);
	}
}