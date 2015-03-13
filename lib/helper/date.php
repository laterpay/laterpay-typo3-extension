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
 * LaterPay date helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_date {

	/**
	 * Get a 'before' search, starting at 23:59:59.
	 *
	 * @param int $timestamp Unix time stamp
	 *
	 * @return array $after
	 */
	public static function getDateQueryBeforeEndOfDay($timestamp) {
		return array(
			'day' => date('d', $timestamp),
			'month' => date('m', $timestamp),
			'year' => date('Y', $timestamp),
			'hour' => 23,
			'minute' => 59,
			'second' => 59
		);
	}

	/**
	 * Get an 'after' search, starting at 00:00:00.
	 *
	 * @param int $timestamp Unix timestamp
	 *
	 * @return array $after
	 */
	public static function getDateQueryAfterStartOfDay($timestamp) {
		return array(
			'day' => date('d', $timestamp),
			'month' => date('m', $timestamp),
			'year' => date('Y', $timestamp),
			'hour' => 0,
			'minute' => 0,
			'second' => 0
		);
	}
}
