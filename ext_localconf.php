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

/**
 * $Id$
 */

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
// hooks on content production
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('HTML',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('TEXT',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('CASE',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('CLEARGIF',           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('COBJ_ARRAY',         'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('COA' ,               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('COA_INT' ,           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('USER',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('USER_INT',           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('FILE',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('IMAGE',              'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('IMG_RESOURCE',       'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('IMGTEXT',            'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('CONTENT',            'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('RECORDS',            'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('HMENU',              'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('CTABLE',             'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('OTABLE',             'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('COLUMNS',            'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('HRULER',             'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('CASEFUNC',           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('LOAD_REGISTER',      'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('RESTORE_REGISTER',   'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('FORM',               'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('SEARCHRESULT',       'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('PHP_SCRIPT',         'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('PHP_SCRIPT_INT',     'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('PHP_SCRIPT_EXT',     'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('TEMPLATE',           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('FLUIDTEMPLATE',      'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('MULTIMEDIA',         'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('MEDIA',              'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('SWFOBJECT',          'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('QTOBJECT',           'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array('SVG',                'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = 'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['lplogger'] = 'EXT:laterpay/lib/core/logger.php:&tx_laterpay_core_logger->handlersLoadAssets';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']['lplogger'] = 'EXT:laterpay/lib/core/logger.php:&tx_laterpay_core_logger->handlersFlush';
// Callback url catcher
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = 'EXT:laterpay/lib/hook/class.tx_callback_catcher.php:&tx_callback_catcher->preRenderHook';

// Plugin action catcher
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = 'EXT:laterpay/lib/hook/class.tx_action_catcher.php:&tx_action_catcher->catchLaterpayAction';

// Preview selector
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][] = 'EXT:laterpay/lib/hook/class.tx_content_replacer.php:&tx_content_replacer->addPreviewModeSelector';

// Evaluations for price
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_evaluation_price'] = 'EXT:laterpay/lib/evaluation/price.php';

// Pre database check of values
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:laterpay/lib/hook/class.tx_field_checker.php:&tx_field_checker';

// register ajax Scripts
$TYPO3_CONF_VARS['BE']['AJAX']['txttlaterpayM1::account'] = t3lib_extMgm::extPath('laterpay') . 'mod1/index.php:tx_laterpay_module1->accountProcessAjaxRequests';
$TYPO3_CONF_VARS['BE']['AJAX']['txttlaterpayM1::pricing'] = t3lib_extMgm::extPath('laterpay') . 'mod1/index.php:tx_laterpay_module1->pricingProcessAjaxRequests';
$TYPO3_CONF_VARS['BE']['AJAX']['txttlaterpayM1::appearance'] = t3lib_extMgm::extPath('laterpay') . 'mod1/index.php:tx_laterpay_module1->appearanceProcessAjaxRequests';
$TYPO3_CONF_VARS['BE']['AJAX']['txttlaterpayM1::dashboard'] = t3lib_extMgm::extPath('laterpay') . 'mod1/index.php:tx_laterpay_module1->dashboardProcessAjaxRequests';
// @codingStandardsIgnoreStart
require_once (t3lib_extMgm::extPath('laterpay').'lib/class.tx_laterpay_compatibility.php');
require_once (t3lib_extMgm::extPath('laterpay').'lib/laterpay_function.php');
// @codingStandardsIgnoreEnd

