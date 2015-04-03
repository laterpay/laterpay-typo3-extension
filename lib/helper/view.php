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
 * LaterPay view helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_view {

	/**
	 * Get links to be rendered in plugin backend navigation.
	 *
	 * @return array
	 */
	public static function getAdminMenu() {
		return array(
			'dashboard' => array(
				'url' 		=> 'laterpay-dashboard-tab',
				'title' 	=> __('Dashboard <sup class="lp_is-beta">beta</sup>', 'laterpay'),
				'submenu' 	=> array(
					'url' 		=> '#',
					'title' 	=> __('Time Passes', 'laterpay'),
					'id' 		=> 'lp_js_switchDashboardView',
					'data' 	=> array(
						'view' 	=> 'time-passes',
						'label' => __('Standard KPIs', 'laterpay'),
					),
				),
			),
			'pricing' 	=> array(
				'url' 		=> 'laterpay-pricing-tab',
				'title' 	=> __('Pricing', 'laterpay'),
			),
			'appearance' => array(
				'url' 		=> 'laterpay-appearance-tab',
				'title' 	=> __('Appearance', 'laterpay'),
			),
			'account' 	=> array(
				'url' 		=> 'laterpay-account-tab',
				'title' 	=> __('Account', 'laterpay'),
			),
		);
	}

	/**
	 * Get date of next day.
	 *
	 * @param string $date Date
	 *
	 * @return string $nextDay
	 */
	protected static function getNextDay($date) {
		$nextDay = date('Y-m-d',
			mktime(date('H', strtotime($date)), date('i', strtotime($date)), date('s', strtotime($date)),
				date('m', strtotime($date)), date('d', strtotime($date)) + 1, date('Y', strtotime($date))));

		return $nextDay;
	}

	/**
	 * Get date a given number of days prior to a given date.
	 *
	 * @param string $date Date
	 * @param int $ago Number of days ago
	 *
	 * @return string $priorDate
	 */
	protected static function getDateDaysAgo($date, $ago = 30) {
		$ago = absint($ago);
		$priorDate = date('Y-m-d',
			mktime(date('H', strtotime($date)), date('i', strtotime($date)), date('s', strtotime($date)),
				date('m', strtotime($date)), date('d', strtotime($date)) - $ago, date('Y', strtotime($date))));

		return $priorDate;
	}

	/**
	 * Get the statistics data for the last 30 days as string, joined by a given
	 * delimiter.
	 *
	 * @param mixed $statistic Array of statistic data
	 * @param string $type Type
	 * @param string $delimiter Delimiter
	 *
	 * @return string
	 */
	public static function getDaysStatisticsAsString($statistic, $type = 'quantity', $delimiter = ',') {
		$today = date('Y-m-d');
		$date = self::getDateDaysAgo(date($today), 30);

		$result = '';
		while ($date <= $today) {
			if ($result !== '') {
				$result .= $delimiter;
			}

			if (isset($statistic[$date])) {
				$result .= $statistic[$date][$type];
			} else {
				$result .= '0';
			}

			$date = self::getNextDay($date);
		}

		return $result;
	}

	/**
	 * Check, if plugin is fully functional.
	 *
	 * @return bool
	 */
	public static function pluginIsWorking() {
		$isInLiveMode 			= get_option('laterpay_plugin_is_in_live_mode');
		$sandboxApiKey 			= get_option('laterpay_sandbox_api_key');
		$liveApiKey 			= get_option('laterpay_live_api_key');
		$isInVisibleTestMode 	= get_option('laterpay_is_in_visible_test_mode');

		// check, if plugin operates in live mode and Live API key exists
		if ($isInLiveMode && empty($liveApiKey)) {
			return FALSE;
		}

		// check, if plugin is not in live mode and Sandbox API key exists
		if (! $isInLiveMode && empty($sandboxApiKey)) {
			return FALSE;
		}

		// check, if plugin is not in live mode and is in visible test mode
		if (! $isInLiveMode && $isInVisibleTestMode) {
			return TRUE;
		}

		// check, if plugin is not in live mode and current user has sufficient capabilities
		if (! $isInLiveMode && ! LaterPay_Helper_User::can('laterpay_read_post_statistics', NULL, FALSE)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Remove extra spaces from string.
	 *
	 * @param string $string Input data
	 *
	 * @return string
	 */
	public static function removeExtraSpaces($string) {
		$string = trim(preg_replace('/>\s+</', '><', $string));
		$string = preg_replace('/\n\s*\n/', '', $string);

		return $string;
	}

	/**
	 * Format number based on its type.
	 *
	 * @param float $number Number
	 * @param bool $isMonetary Flag
	 *
	 * @return string $formatted
	 */
	public static function formatNumber($number, $isMonetary = TRUE) {
		// delocalize number
		$number = (float) str_replace(',', '.', $number);

		if ($isMonetary) {
			// format monetary values
			if ($number < 200) {
				// format values up to 200 with two digits
				// 200 is used to make sure the maximum Single Sale price of
				// 149.99 is still formatted with two digits
				$formatted = number_format($number, 2);
			} elseif ($number >= 200 && $number < 10000) {
				// format values between 200 and 10,000 without digits
				$formatted = number_format($number, 0);
			} else {
				// reduce values above 10,000 to thousands and format them with one digit
				$formatted = number_format($number / 1000, 1) . __('k', 'laterpay');
				// 'k' = short for kilo (thousands)
			}
		} else {
			// format count values
			if ($number < 10000) {
				$formatted = number_format($number);
			} else {
				// reduce values above 10,000 to thousands and format them with one digit
				$formatted = number_format($number / 1000, 1) . __('k', 'laterpay');
				// 'k' = short for kilo (thousands)
			}
		}

		return $formatted;
	}

	/**
	 * Check, if purchase link should be hidden.
	 *
	 * @return bool
	 */
	public static function purchaseLinkIsHidden() {
		$isHidden = get_option('laterpay_only_time_pass_purchases_allowed') && get_option('laterpay_teaser_content_only');

		return $isHidden;
	}

	/**
	 * Check, if purchase button should be hidden.
	 *
	 * @return bool
	 */
	public static function purchaseButtonIsHidden() {
		$isHidden = get_option('laterpay_only_time_pass_purchases_allowed');

		return $isHidden;
	}
}
