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

// @codingStandardsIgnoreStart
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.tx_hook_abstract.php';
// @codingStandardsIgnoreEnd

/**
 * Callback catcher class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_callback_catcher extends tx_hook_abstract {

	/**
	 * Pre render hook
	 *
	 * @return void
	 */
	public function preRenderHook() {
		// if we in admin part - nothing to do here
		if (TYPO3_MODE == 'BE') {
			return;
		}
			// get/set token if needed
		$this->createToken();

		$this->buyPost();
	}

	/**
	 * Create token
	 *
	 * @return void
	 */
	public function createToken() {
		// @TODO : fix browscap data
		// $browser_supports_cookies = tx_laterpay_helper_browser::browserSupportsCookies();
		// $browser_is_crawler = tx_laterpay_helper_browser::isCrawler();
		$browserSupportsCookies = TRUE;
		$browserIsCrawler = FALSE;

		$context = array(
			'support_cookies' => $browserSupportsCookies,
			'is_crawler' => $browserIsCrawler
		);

		// @TODO : uncoment when logger will be available
		// $this->logger->info(
		// __METHOD__,
		// $context
		// );

		// don't assign tokens to crawlers and other user agents that can't handle cookies
		if (! $browserSupportsCookies || $browserIsCrawler) {
			return;
		}

		$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
		$laterpayClient = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
			$clientOptions['web_root'], $clientOptions['token_name']);
		$lpToken = t3lib_div::_GET('lptoken');
		if (isset($lpToken)) {
			$laterpayClient->set_token($lpToken, TRUE);
		}

		if (! $laterpayClient->has_token()) {
			$laterpayClient->acquire_token();
		}
	}

	/**
	 * Buy post
	 *
	 * @return void
	 */
	public function buyPost() {
		$lpBuy = t3lib_div::_GET('buy');
		if (! isset($lpBuy)) {
			return;
		}

		// data to create and hash-check the URL
		$urlData = array(
			'post_id' => t3lib_div::_GET('post_id'),
			'id_currency' => t3lib_div::_GET('id_currency'),
			'price' => t3lib_div::_GET('price'),
			'date' => t3lib_div::_GET('date'),
			'buy' => t3lib_div::_GET('buy'),
			'ip' => t3lib_div::_GET('ip'),
			'revenue_model' => t3lib_div::_GET('revenue_model')
		);

		$url = $this->getAfterPurchaseRedirectUrl($urlData);
		$hash = tx_laterpay_helper_pricing::getHashByUrl($url);

		// update lptoken, if we got it
		$lpToken = t3lib_div::_GET('lptoken');
		if (isset($lpToken)) {
			$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
			$client = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
				$clientOptions['web_root'], $clientOptions['token_name']);
			$client->set_token($lpToken);
		}

		$postId = abs((int) $urlData['post_id']);

		// check, if the parameters of $_GET are valid and not manipulated
		if ($hash === t3lib_div::_GET('hash')) {
			$data = array(
				'post_id' => $postId,
				'id_currency' => t3lib_div::_GET('id_currency'),
				'price' => t3lib_div::_GET('price'),
				'date' => t3lib_div::_GET('date'),
				'ip' => t3lib_div::_GET('ip'),
				'revenue_model' => t3lib_div::_GET('revenue_model'),
				'hash' => t3lib_div::_GET('hash')
			);

			// @TODO : uncomment when time will come
			// $this->logger->info(
			// __METHOD__ . ' - set payment history',
			// $data
			// );
			$paymentHistoryModel = new tx_laterpay_model_payment_history();
			$paymentHistoryModel->setPaymentHistory($data);
		}
		$redirectUrl = self::getPageUrl();

		t3lib_utility_Http::redirect($redirectUrl);
		exit();
	}
}
