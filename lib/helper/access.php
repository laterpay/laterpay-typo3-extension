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
 * LaterPay access helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_access {

	public static $accessedContents = array();

	public static $accessedTimepasses = array();

	/**
	 * Check if user has active timepass
	 *
	 * @param mixed $timepassesIds list of timepasses ids
	 *
	 * @return mixed
	 */
	public static function checkIfHasActiveTimepasses($timepassesIds) {

		$needToAsk = array();
		foreach ($timepassesIds as $timepassId) {
			if (!in_array($timepassId, array_keys(self::$accessedTimepasses))) {
				$needToAsk[] = $timepassId;
			}
		}
		if (count($needToAsk)) {
			$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
			$laterpayClient = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
				$clientOptions['web_root'], $clientOptions['token_name']);

			$result = $laterpayClient->get_access($needToAsk);
			foreach ($result['articles'] as $id => $article) {
				self::$accessedTimepasses[$id] = $article['access'];
			}
		}
		return array_search(TRUE, self::$accessedTimepasses) !== FALSE;
	}

	/**
	 * Check if user has access to contents
	 *
	 * @param mixed $contentIds - array of ids for checking access
	 *
	 * @return mixed array of access
	 */
	public static function checkIfHasAccessToContent($contentIds) {

		$needToAsk = array();
		foreach ($contentIds as $contentId) {
			if (!in_array($contentId, array_keys(self::$accessedContents))) {
				$needToAsk[] = $contentId;
			}
		}

		if (count($needToAsk)) {
			$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
			$laterpayClient = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
				$clientOptions['web_root'], $clientOptions['token_name']);
			$result = $laterpayClient->get_access($needToAsk);

			foreach ($result['articles'] as $id => $article) {
				self::$accessedContents[$id] = $article['access'];
			}
		}
		$accessArray = array();
		foreach ($contentIds as $contentId) {
			$accessArray[$contentId] = self::$accessedContents[$contentId];
		}
		return $accessArray;
	}
}
