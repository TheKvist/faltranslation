<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'FAL translation',
	'description' => 'Work around https://forge.typo3.org/issues/57272',
	'category' => 'misc',
	'author' => 'Jan Kiesewetter',
	'author_email' => 'jan@t3easy.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '2.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.1-7.6.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);