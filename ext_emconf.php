<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "fal_online_media_connector"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Online Media Connector',
	'description' => 'With Online Media Connector you can add YouTube and Vimeo videos to a content element just like you alrady know from normal images. It also brings a registry so you can add a connector/helper for other media types.',
	'category' => 'misc',
	'author' => 'Frans Saris',
	'author_email' => 'franssaris@gmail.com',
	'state' => 'alpha',
	'internal' => '',
	'clearCacheOnLoad' => 1,
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.4 - 6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);