<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Avoid that this block is loaded in frontend or within upgrade wizards
if (TYPO3_MODE === 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'LaterpayGmbh.' . $_EXTKEY,
		'system',
		'laterpay',
		'',
		array('Laterpay' => 'index'),
		array(
			'access' => 'admin',
			'icon' => 'EXT:'. $_EXTKEY . '/ext_icon.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf'
		)
	);
}
