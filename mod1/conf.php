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

define('TYPO3_MOD_PATH', '../typo3conf/ext/laterpay/mod1/');
$BACK_PATH = '../../../../typo3/';

	// DO NOT REMOVE OR CHANGE THESE 2 LINES:
$MCONF['name'] = 'txttlaterpayM1';
$MCONF['script'] = '_DISPATCH';

$MCONF['access'] = 'user,group';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:laterpay/mod1/locallang_mod.xml';
