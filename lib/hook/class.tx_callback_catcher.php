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
	 * Hook handler for ['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']
	 * Flush all buffered by handlers content
	 *
	 * @param mixed $parameters Array of input parameters
				'jsLibs'               => &$jsLibs,
				'jsFiles'              => &$jsFiles,
				'jsFooterFiles'        => &$jsFooterFiles,
				'cssFiles'             => &$cssFiles,
				'headerData'           => &$this->headerData,
				'footerData'           => &$this->footerData,
				'jsInline'             => &$jsInline,
				'cssInline'            => &$cssInline,
				'xmlPrologAndDocType'  => &$this->xmlPrologAndDocType,
				'htmlTag'              => &$this->htmlTag,
				'headTag'              => &$this->headTag,
				'charSet'              => &$this->charSet,
				'metaCharsetTag'       => &$this->metaCharsetTag,
				'shortcutTag'          => &$this->shortcutTag,
				'inlineComments'       => &$this->inlineComments,
				'baseUrl'              => &$this->baseUrl,
				'baseUrlTag'           => &$this->baseUrlTag,
				'favIcon'              => &$this->favIcon,
				'iconMimeType'         => &$this->iconMimeType,
				'titleTag'             => &$this->titleTag,
				'title'                => &$this->title,
				'metaTags'             => &$metaTags,
				'jsFooterInline'       => &$jsFooterInline,
				'jsFooterLibs'         => &$jsFooterLibs,
				'bodyContent'          => &$this->bodyContent
	 * @param object $pObj Instance t3lib_PageRenderer
	 *
	 * @return void
	 */
	public function preRenderHook(&$parameters, &$pObj) {
		// if we in admin part - nothing to do here
		if (TYPO3_MODE == 'BE') {
			return;
		}
		$pObj->addJsInlineCode(
			'laterpay-post-view',
			tx_laterpay_helper_render::getLocalizeScript('lpVars', array(
					'ajaxUrl' => 'ajax.php',
				)
			)
		);

		// get / set token if needed
		$this->createToken();

		if (t3lib_div::_GET('post_id')) {
			$this->buyPost();
		}

		if (t3lib_div::_GET('pass_id')) {
			$this->buyTimepass();
		}
	}

	/**
	 * Create token.
	 *
	 * @return void
	 */
	public function createToken() {
		$browserSupportsCookies = tx_laterpay_helper_browser::browserSupportsCookies();
		$browserIsCrawler = tx_laterpay_helper_browser::isCrawler();

		$context = array(
			'support_cookies' => $browserSupportsCookies,
			'is_crawler'      => $browserIsCrawler,
		);

		$this->logger->info(
			__METHOD__,
			$context
		);

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
	 * Buy post.
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
			'post_id'     => t3lib_div::_GET('post_id'),
			'id_currency' => t3lib_div::_GET('id_currency'),
			'price'       => t3lib_div::_GET('price'),
			'date'        => t3lib_div::_GET('date'),
			'buy'         => t3lib_div::_GET('buy'),
			'ip'          => t3lib_div::_GET('ip'),
			'revenue_model' => t3lib_div::_GET('revenue_model'),
		);

		$url  = $this->getAfterPurchaseRedirectUrl($urlData);
		$hash = tx_laterpay_helper_pricing::getHashByUrl($url);

		// update lptoken, if we got it
		$lpToken = t3lib_div::_GET('lptoken');
		if (isset($lpToken)) {
			$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
			$client        = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
				$clientOptions['web_root'], $clientOptions['token_name']);

			$client->set_token($lpToken);
		}

		$postId = abs((int) $urlData['post_id']);

		// check, if the parameters of $_GET are valid and not manipulated
		if ($hash === t3lib_div::_GET('hash')) {
			$data = array(
				'post_id'     => $postId,
				'id_currency' => t3lib_div::_GET('id_currency'),
				'price'       => t3lib_div::_GET('price'),
				'date'        => t3lib_div::_GET('date'),
				'ip'          => t3lib_div::_GET('ip'),
				'revenue_model' => t3lib_div::_GET('revenue_model'),
				'hash' => t3lib_div::_GET('hash'),
			);

			$this->logger->info(
				__METHOD__ . ' - set payment history',
				$data
			);

			$paymentHistoryModel = new tx_laterpay_model_payment_history();
			$paymentHistoryModel->setPaymentHistory($data);
		}

		$redirectUrl = self::getPageUrl();
		t3lib_utility_Http::redirect($redirectUrl);

		exit();
	}

	/**
	 * Buy timepass.
	 *
	 * @return void
	 */
	public function buyTimepass() {
		if ( ! t3lib_div::_GET('pass_id') && ! t3lib_div::_GET('link') ) {
			return;
		}

		// data to create and hash-check the URL
		$urlData = array(
			'pass_id'       => t3lib_div::_GET('pass_id'),
			'id_currency'   => t3lib_div::_GET('id_currency'),
			'price'         => t3lib_div::_GET('price'),
			'date'          => t3lib_div::_GET('date'),
			'ip'            => t3lib_div::_GET('ip'),
			'revenue_model' => t3lib_div::_GET('revenue_model'),
			'link'          => t3lib_div::_GET('link'),
		);

		$link    = $urlData['link'];
		$url     = tx_laterpay_helper_string::addQueryArg($urlData, $link);
		$hash    = tx_laterpay_helper_pricing::getHashByUrl( $url );

		$passId = tx_laterpay_helper_timepass::getUntokenizedTimePassId( $urlData['pass_id'] );
		if ($hash === t3lib_div::_GET('hash')) {
			// save payment history
			$data = array(
				'id_currency'   => t3lib_div::_GET('id_currency'),
				'price'         => t3lib_div::_GET('price'),
				'date'          => t3lib_div::_GET('date'),
				'ip'            => t3lib_div::_GET('ip'),
				'hash'          => t3lib_div::_GET('hash'),
				'revenue_model' => t3lib_div::_GET('revenue_model'),
				'pass_id'       => $passId,
			);

			$this->logger->info(
				__METHOD__ . ' - set payment history',
				$data
			);

			$paymentHistoryModel = new tx_laterpay_model_payment_history();
			$paymentHistoryModel->setPaymentHistory( $data );
		}

		t3lib_utility_Http::redirect( $link );
		exit;
	}
}
