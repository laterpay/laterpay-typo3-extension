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
 * $Id: tca.php 27026 2009-11-26 13:12:51Z rupi $
 */

	// get extension confArr
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_laterpay']);
// ******************************************************************
// This is the standard TypoScript news table, tt_news
// ******************************************************************
$TCA['tt_laterpay'] = Array (
);
