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
 * LaterPay browser helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_browser {

	/**
	 * Instance of Browscap
	 * @var Browscap $browscap library
	 */
	protected static $browscap = NULL;

	/**
	 * Browser information
	 * @var array|null $browser information
	 */
	protected static $browser = NULL;

	/**
	 * Return object of all browscap library.
	 *
	 * @return object
	 */
	public static function phpBrowscap() {
		$config = tx_laterpay_config::getInstance();

		if (empty(self::$browscap)) {
			self::$browscap = new Browscap($config->get('cache_dir'));
			self::$browscap->doAutoUpdate = $config->get('browscap.autoupdate');

			if ($config->has('browscap.manually_updated_copy')) {
				self::$browscap->localFile = $config->get('browscap.manually_updated_copy');
			}

			self::$browscap->silent = $config->get('browscap.silent');
		}

		return self::$browscap;
	}

	/**
	 * Return array of all browser infos.
	 *
	 * @usage $browserInfo = php_browser_info();
	 *
	 * @return array
	 */
	public static function phpBrowserInfo() {
		if (is_null(self::$browser)) {
			self::$browser = self::phpBrowscap()->getBrowser(NULL, TRUE);
		}

		return (array) self::$browser;
	}

	/**
	 * Conditional to test for cookie support.
	 *
	 * @return bool
	 */
	public static function browserSupportsCookies() {
		$browserInfo = self::phpBrowserInfo();

		if (empty($browserInfo)) {
			return TRUE;
		}

		if (isset($browserInfo['Cookies'])) {
			if ($browserInfo['Cookies'] == 1 || $browserInfo['Cookies'] == 'true') {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Conditional to test for crawler.
	 *
	 * @param string $version Specific browser version
	 *
	 * @return bool
	 */
	public static function isCrawler($version = '') {
		$browserInfo = self::phpBrowserInfo();

		if (empty($browserInfo)) {
			return FALSE;
		}

		if (isset($browserInfo['Crawler']) && ($browserInfo['Crawler'] == 1 || $browserInfo['Crawler'] == 'true')) {
			if ($version == '') {
				return TRUE;
			} elseif ($browserInfo['MajorVer'] == $version) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}
