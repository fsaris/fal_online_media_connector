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
use TYPO3\CMS\Core\Resource\FileReference;
use MiniFranske\FalOnlineMediaConnector\Helpers\YouTubeHelper;

/**
 * Class VideoTagRenderer
 */
class YouTubeRenderer extends YouTubeHelper implements FileRendererInterface {

	/**
	 * Cached OnlineMediaIds [fileUid => id]
	 *
	 * @var array
	 */
	protected $onlineMediaIdCache = array();

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
		return $file->getMimeType() === 'video/youtube' || $file->getExtension() === 'ytb';
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
		$urlParams = array('autohide=1');
		if (!isset($options['controls']) || !empty($options['controls'])) {
			$urlParams[] = 'controls=2';
		}
		if (!empty($options['autoplay'])) {
			$urlParams[] = 'autoplay=1';
		}
		if (!empty($options['loop'])) {
			$urlParams[] = 'loop=1';
		}
		$urlParams[] = 'showinfo=' . (int)!empty($options['showinfo']);

		if ($file instanceof FileReference) {
			$orgFile = $file->getOriginalFile();
		} else {
			$orgFile = $file;
		}

		$videoId = $this->getOnlineMediaId($orgFile);
		$attributes = array(
			'src' => sprintf('//www.youtube.com/embed/%s?%s', $videoId, implode('&amp;', $urlParams)),
		);
		$width = (int)$width;
		if (!empty($width)) {
			$attributes['width'] = $width;
		}
		$height = (int)$height;
		if (!empty($height)) {
			$attributes['height'] = $height;
		}
		if (is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->config['config']['doctype'] !== 'html5') {
			$attributes['frameborder'] = '0';
		}
		$output = '';
		foreach ($attributes as $key => $value) {
			$output .= $key . '="' . $value . '"';
		}

		// wrap in div so you can make is responsive
		return '<div class="video-container"><iframe ' . $output . ' allowfullscreen></iframe></div>';
	}
}