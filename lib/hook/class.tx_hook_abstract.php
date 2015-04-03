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
 * Hook abstract class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_hook_abstract {

	/**
	 * Constructor of object.
	 */
	public function __construct() {
		$this->logger = tx_laterpay_core_logger::getInstance();
	}

	/**
	 * Get page URL.
	 *
	 * @return string
	 */
	public static function getPageUrl() {
		$host = 'http://' . $_SERVER['HTTP_HOST'];
		$url = $host . '/index.php?id=' . intval($GLOBALS['TSFE']->id);

		return $url;
	}

	/**
	 * Get after purchase URL for redirect.
	 *
	 * @param array $data Array of data
	 *
	 * @return string
	 */
	protected function getAfterPurchaseRedirectUrl(array $data) {
		$url = self::getPageUrl();
		if (! $url) {
			$this->logger->error(
				__METHOD__ . ' could not find an URL for the given content_id',
				array('data' => $data,)
			);

			return $url;
		}

		$url = add_query_arg($data, $url);

		return $url;
	}
}
