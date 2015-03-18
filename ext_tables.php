<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

$TCA['tt_laterpay'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:tt_laterpay',
		'label' => 'title',
		'label_alt' => $confArr['label_alt'] . ($confArr['label_alt2'] ? ',' . $confArr['label_alt2'] : ''),
		'label_alt_force' => $confArr['label_alt_force'],
		'default_sortby' => 'ORDER BY datetime DESC',
		'crdate' => 'crdate',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'type' => 'type',
		'typeicon_column' => 'type',
		'thumbnail' => 'image',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.png ',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php'
	)
);

$TCA['tt_content']['ctrl']['laterpay_teaser'] = 'teaser';
$TCA['tt_content']['ctrl']['laterpay_price'] = 'item price';

$TCA['tt_content']['columns'] += array(
	'laterpay_teaser' => array(
		'label' => 'Teaser',
		'config' => array(
			'type' => 'text',
			'cols' => '40',
			'rows' => '15'
		),
		'defaultExtras' => 'richtext[]'
	)
);

$TCA['tt_content']['columns'] += array(
	'laterpay_price' => array(
		'label' => 'Price &euro;',
		'config' => array(
			'type' => 'input',
			'default' => '0.25',
			'eval' => 'tx_laterpay_evaluate_price'
		)
	)
);
$TCA['tt_content']['columns'] += array(
	'laterpay_revenue_model' => array(
		'label' => 'Revenue Model Price',
		'config' => array(
			'type' => 'radio',
			'default' => 'ppu',
			'items' => array(
					array('PPU','ppu'),
					array('SIS','sis')
			)
		)
	)
);

$TCA['tt_content']['palettes']['1001'] = array(
	'showitem' => 'laterpay_price,laterpay_revenue_model',
	'canNotCollapse' => TRUE
);

t3lib_extMgm::addToAllTCAtypes('tt_content', 'laterpay_teaser;;1001;;', '', 'after:bodytext');

if (TYPO3_MODE == 'BE') {
	if (t3lib_div::int_from_ver(TYPO3_version) >= 4000000) {
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4002000) {
// 			t3lib_extMgm::addModulePath('txlaterpayM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
			t3lib_extMgm::addModule('tools', 'txttlaterpayM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		}
	}
}

