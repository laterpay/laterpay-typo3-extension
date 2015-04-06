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
 * LaterPay config helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_config {

	private static $options = array();

	/**
	 * Get options for LaterPay PHP client.
	 *
	 * @return array
	 */
	public static function getPhpClientOptions() {
		$config = tx_laterpay_config::getInstance();
		if (empty(self::$options)) {
			if ($config->get(tx_laterpay_config::IS_IN_LIVE_MODE)) {
				self::$options['cp_key'] = tx_laterpay_config::getOption('laterpay_live_merchant_id');
				self::$options['api_key'] = tx_laterpay_config::getOption('laterpay_live_api_key');
				self::$options['api_root'] = tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_LIVE_BACKEND_API_URL);
				self::$options['web_root'] = tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_LIVE_DIALOG_API_URL);
			} else {
				self::$options['cp_key'] = tx_laterpay_config::getOption('laterpay_sandbox_merchant_id');
				self::$options['api_key'] = tx_laterpay_config::getOption('laterpay_sandbox_api_key');
				self::$options['api_root'] = tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_SANDBOX_BACKEND_API_URL);
				self::$options['web_root'] = tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_SANDBOX_DIALOG_API_URL);
			}

			self::$options['token_name'] 	= $config->get(tx_laterpay_config::API_TOKEN_NAME);
		}

		return self::$options;
	}
}
