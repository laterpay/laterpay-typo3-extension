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
 * Content replacer class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_content_replacer extends tx_hook_abstract {

	/**
	 * Processed content
	 *
	 * @var array storage for page ids and ids of processed content
	 */
	private $processedContent = array();

	/**
	 * Function used on hook wich build and wrap content block
	 *
	 * @param string $name Name of content type
	 * @param mixed $conf Configuration array
	 * @param string $tsKey Table storage key
	 * @param tslib_cObj $contentObject Content object,
	 *
	 * @return string
	 */
	public function cObjGetSingleExt($name, $conf, $tsKey, tslib_cObj $contentObject) {
		// get page id
		$pageId = $this->getPageId($contentObject);
		// get content block id
		$id = $this->getId($contentObject);

		// if we get data from tt_content (default table for content) and page_id,id pair was not processed earlier
		// that possibly need to replace main content by teaser
		if (($tsKey == 'tt_content') and
			(! isset($this->processedContent[$pageId]) or ! in_array($this->processedContent[$pageId], $id))) {
			if ($this->isPaymentNeeded($contentObject)) {
				$this->replaceContent($contentObject);
				// added key pair into processed array
				if (isset($this->processedContent[$pageId])) {
					array_push($this->processedContent[$pageId], $id);
				} else {
					$this->processedContent[$pageId] = array(
						$id
					);
				}
			}
		}
		// system object render
		$content = $contentObject->getContentObject($name)->render($conf);

		if (isset($this->processedContent[$pageId]) and in_array($id, $this->processedContent[$pageId])) {
			return htmlspecialchars_decode($content);
		} else {
			return $content;
		}
	}

	/**
	 * Is payemnt needed or not
	 *
	 * @param object $contentObject Conetnt object
	 *
	 * @return bool
	 */
	public function isPaymentNeeded($contentObject) {
		$id = $this->getId($contentObject);
		// Additional checks must be added here
		$price = tx_laterpay_helper_pricing::getContentPrice($contentObject);

		if (! $price) {
			return FALSE;
		}

		$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
		$laterpayClient = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
			$clientOptions['web_root'], $clientOptions['token_name']);
		$result = $laterpayClient->get_access(array(
			$id
		));

		if (! empty($result) && isset($result['articles'][$id])) {
			$access = $result['articles'][$id]['access'];
		}

		if ($access) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Replace payed content by teaser
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return void
	 */
	public function replaceContent(tslib_cObj $contentObject) {
		$wrapper = $this->getWrapper($contentObject, $contentObject->data['laterpay_teaser']);
		$purchaseUrl = $this->getPurchaseUrl($contentObject);

		// Set variables into wrapper
		$wrapper->setWrapperArgument('price', tx_laterpay_helper_pricing::getContentPrice($contentObject));
		$wrapper->setWrapperArgument('purchaseURL', $purchaseUrl);
		$wrapper->setWrapperArgument('revenueModel', tx_laterpay_helper_pricing::getContentRevenueModel($contentObject));

		$contentObject->data['bodytext'] = $wrapper->render($contentObject->data['laterpay_teaser']);

		$wrapper->setJs();
		$wrapper->setCss();
	}

	/**
	 * Page id getter
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return int
	 */
	public function getPageId(tslib_cObj $contentObject) {
		return $contentObject->data['pid'];
	}

	/**
	 * Id getter
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return int
	 */
	public function getId(tslib_cObj $contentObject) {
		return $contentObject->data['uid'];
	}

	/**
	 * Prototype Method wich must return wrapper for teaser content
	 * type of wrapper based on current configurations
	 *
	 * @param tslib_cObj $contentObject Content object
	 * @param string $teaserText Teaser text
	 *
	 * @return object
	 */
	public function getWrapper(tslib_cObj $contentObject, $teaserText) {
		$wraperName = 'tx_laterpay_wrapper_';
		if (get_option('laterpay_teaser_content_only')) {
			$wraperName .= 'teaser';
		} else {
			$wraperName .= 'block';
		}
		$wrapper = new $wraperName();
		return $wrapper;
	}

	/**
	 * Get header
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return multitype:
	 */
	public function getHeader(tslib_cObj $contentObject) {
		return $contentObject->data['header'];
	}

	/**
	 * Get purchase url
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return string
	 */
	public function getPurchaseUrl(tslib_cObj $contentObject) {
		ini_set('display_errors', 'on');
		// @TODO : check if block with such ID exists
		$contentBlockId = $this->getId($contentObject);
		$config = tx_laterpay_config::getInstance();

		$currency = $config->get(tx_laterpay_config::REG_LATERPAY_CURRENCY);
		$price = tx_laterpay_helper_pricing::getContentPrice($contentObject);
		$revenueModel = tx_laterpay_helper_pricing::getContentRevenueModel($contentObject);

		$currencyModel = new tx_laterpay_model_currency();
		$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
		$client = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
			$clientOptions['web_root'], $clientOptions['token_name']);

		// data to register purchase after redirect from LaterPay
		$urlParams = array(
			'post_id' => $contentBlockId,
			'id_currency' => $currencyModel->getCurrencyNameByIso4217Code($currency),
			'price' => $price,
			'date' => time(),
			'buy' => 'true',
			'ip' => ip2long($_SERVER['REMOTE_ADDR']),
			'revenue_model' => $revenueModel
		);

		$url = $this->getAfterPurchaseRedirectUrl($urlParams);
		$hash = tx_laterpay_helper_pricing::getHashByUrl($url);

		// parameters for LaterPay purchase form
		$params = array(
			'article_id' => $contentBlockId,
			'pricing' => $currency . ($price * 100),
			'vat' => $config->get(tx_laterpay_config::CURRENCY_DEFAULT_VAT),
			'url' => $url . '&hash=' . $hash,
			'title' => $this->getHeader($contentObject)
		);

		// $this->logger->info(
		// __METHOD__,
		// $params
		// );

		if ($revenueModel == 'sis') {
			// Single Sale purchase
			return $client->get_buy_url($params);
		} else {
			// Pay-per-Use purchase
			return $client->get_add_url($params);
		}
	}
}
