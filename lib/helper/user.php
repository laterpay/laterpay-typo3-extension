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
 * LaterPay user helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_user {

	/**
	 * Is current user admin or not.
	 *
	 * @return bool
	 */
	public static function isAdmin() {

		if (isset($GLOBALS['BE_USER']) and $GLOBALS['BE_USER']) {
			$isAdmin = (bool) isset($GLOBALS['BE_USER']->user['admin']) ? $GLOBALS['BE_USER']->user['admin'] : FALSE;
			$hasAccess = $GLOBALS['BE_USER']->check('modules', 'laterpay');
			return $isAdmin or $hasAccess;
		}
		return FALSE;
	}

	/**
	 * Only admin can preview as visitor.
	 *
	 * @return bool
	 */
	public static function previewAsVisitor() {
		if (!self::isAdmin()) {
			return FALSE;
		}

		return (bool) get_option(tx_laterpay_config::REG_LATERPAY_PREVIEW_AS_VISITOR);
	}

}


