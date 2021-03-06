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

	private $gettedAcceses = array();

	private $addedViews = array();

	private $cssAndJsSetted = FALSE;

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
		// set Javascript and CSS
		if (!$this->cssAndJsSetted && TYPO3_MODE != 'BE') {
			$this->setJsAndCss();
			$this->cssAndJsSetted = TRUE;
		}

		// get page id
		$pageId = $this->getPageId($contentObject);

		// get content block id
		$id = $this->getId($contentObject);

		$needToWrap = $this->needToWrap();

		// if we get data from tt_content (default table for content) and page_id,id pair was not processed earlier
		// that possibly need to replace main content by teaser
		if (
			$needToWrap && ($tsKey == tx_laterpay_model_content::$contentTable) &&
			(
				! isset($this->processedContent[$pageId]) ||
				! in_array($id, $this->processedContent[$pageId]))
		) {
			if (tx_laterpay_helper_pricing::isPurchasable($contentObject)) {
				if (!in_array($pageId, $this->gettedAcceses)) {
					$this->loadPageAccesses($pageId);
					$this->gettedAcceses[] = $pageId;
				}
			}

			if ($this->isPaymentNeeded($contentObject)) {
				//Add view into statistic.
				$this->addStatisticView($contentObject);

				$this->replaceContent($contentObject);

				// added key pair into processed array
				if (isset($this->processedContent[$pageId])) {
					array_push($this->processedContent[$pageId], $id);
				} else {
					$this->processedContent[$pageId] = array(
						$id,
					);
				}
			}
		}

		// system object render
		$content = $contentObject->getContentObject($name)->render($conf);

		if (isset($this->processedContent[$pageId]) && in_array($id, $this->processedContent[$pageId])) {
			return htmlspecialchars_decode($content);
		} else {
			return $content;
		}
	}

	/**
	 * Is purchase needed or not.
	 *
	 * @param object $contentObject Conetnt object
	 *
	 * @return bool
	 */
	public function isPaymentNeeded($contentObject) {
		if (tx_laterpay_helper_timepass::userHasActiveTimepass()) {
			return FALSE;
		}

		$id = $this->getId($contentObject);

		if (tx_laterpay_helper_pricing::isPurchasable($contentObject)) {
			$GLOBALS['TSFE']->set_no_cache();
			$this->logger->info(__METHOD__ . ' - Cache disabled for page', array('id' => $id));
		} else {
			return FALSE;
		}

		$results = tx_laterpay_helper_access::checkIfHasAccessToContent(array($id));

		return !$results[$id];
	}

	/**
	 * Replace paid content by teaser.
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return void
	 */
	public function replaceContent(tslib_cObj $contentObject) {
		if ($contentObject->data['laterpay_teaser']) {
			$laterpayTeaser = $contentObject->data['laterpay_teaser'];
		} else {
			$laterpayTeaser = tx_laterpay_helper_content::getTeaser($contentObject->data['bodytext']);
			tx_laterpay_model_content::updateContentData($this->getId($contentObject), array('laterpay_teaser' => $laterpayTeaser));
		}

		if (!tx_laterpay_config::getOption('laterpay_teaser_content_only')) {
			$fullContent = tx_laterpay_helper_string::truncate(
				$contentObject->data['bodytext'],
				tx_laterpay_helper_string::determineNumberOfWords($contentObject->data['bodytext']),
				array(
						'html'  => TRUE,
						'words' => TRUE,
				)
			);
		} else {
			$fullContent = NULL;
		}

		$wrapper     = $this->getWrapper($contentObject, $laterpayTeaser);
		$purchaseUrl = $this->getPurchaseUrl($contentObject);

		// set variables into wrapper
		$wrapper->setWrapperArgument('price', tx_laterpay_helper_pricing::getContentPrice($contentObject));
		$wrapper->setWrapperArgument('purchaseURL', $purchaseUrl);
		$wrapper->setWrapperArgument('revenueModel', tx_laterpay_helper_pricing::getContentRevenueModel($contentObject));
		$wrapper->setWrapperArgument('isInVisibleTestMode', tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE) ? : NULL);
		$wrapper->setWrapperArgument('previewAsVisitor', tx_laterpay_helper_user::previewAsVisitor());
		$wrapper->setWrapperArgument('fullContent', $fullContent);

		$contentObject->data['bodytext'] = $wrapper->render($laterpayTeaser);

		if (! tx_laterpay_helper_timepass::userHasActiveTimepass()) {
			$this->addTimePassesList($contentObject);
		}

		$wrapper->setJs();
		$wrapper->setCss();
	}

	/**
	 * Page id getter.
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return int
	 */
	public function getPageId(tslib_cObj $contentObject) {
		return $contentObject->data['pid'];
	}

	/**
	 * Get content id
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return int
	 */
	public function getId(tslib_cObj $contentObject) {
		return $contentObject->data['uid'];
	}

	/**
	 * Prototype method which must return wrapper for teaser content
	 * type of wrapper based on current configuration.
	 *
	 * @param tslib_cObj $contentObject Content object
	 * @param string $teaserText Teaser text
	 *
	 * @return object
	 */
	public function getWrapper(tslib_cObj $contentObject, $teaserText) {
		$wraperName = 'tx_laterpay_wrapper_';
		if (tx_laterpay_config::getOption('laterpay_teaser_content_only')) {
			$wraperName .= 'teaser';
		} else {
			$wraperName .= 'block';
		}

		$wrapper = new $wraperName();

		return $wrapper;
	}

	/**
	 * Get header.
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return multitype:
	 */
	public function getHeader(tslib_cObj $contentObject) {
		return $contentObject->data['header'];
	}

	/**
	 * Get purchase URL.
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return string
	 */
	public function getPurchaseUrl(tslib_cObj $contentObject) {
		$contentBlockId = $this->getId($contentObject);
		$config = tx_laterpay_config::getInstance();

		$currency      = $config->get(tx_laterpay_config::REG_LATERPAY_CURRENCY);
		$price         = tx_laterpay_helper_pricing::getContentPrice($contentObject);
		$revenueModel  = tx_laterpay_helper_pricing::getContentRevenueModel($contentObject);
		$currencyModel = new tx_laterpay_model_currency();

		$clientOptions = tx_laterpay_helper_config::getPhpClientOptions();
		$client        = new LaterPay_Client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
			$clientOptions['web_root'], $clientOptions['token_name']);

		// data to register purchase after redirect from LaterPay
		$urlParams = array(
			'post_id'       => $contentBlockId,
			'id_currency'   => $currencyModel->getCurrencyNameByIso4217Code($currency),
			'price'         => $price,
			'date'          => time(),
			'buy'           => 'TRUE',
			'ip'            => ip2long($_SERVER['REMOTE_ADDR']),
			'revenue_model' => $revenueModel,
		);

		$url  = $this->getAfterPurchaseRedirectUrl($urlParams);
		$hash = tx_laterpay_helper_pricing::getHashByUrl($url);

		// parameters for LaterPay purchase form
		$params = array(
			'article_id' => $contentBlockId,
			'pricing'    => $currency . ($price * 100),
			'url'        => $url . '&hash=' . $hash,
			'title'      => $this->getHeader($contentObject)
		);

		$this->logger->info(
			__METHOD__,
			$params
		);

		if ($revenueModel == 'sis') {
			// Single Sale purchase
			return $client->get_buy_url($params);
		} else {
			// Pay-per-Use purchase
			return $client->get_add_url($params);
		}
	}

	/**
	 * Add tab with selector for preview mode (user or admin).
	 *
	 * @param mixed $params Page parameters.
	 * @param t3lib_PageRenderer $caller Object caller. As default this t3lib_PageRenderer.
	 *
	 * @return void
	 */
	public function addPreviewModeSelector(&$params, t3lib_PageRenderer $caller) {
		// if we are in admin part - nothing to do here
		if (TYPO3_MODE == 'BE') {
			return;
		}

		// action is only allowed for admin
		if (!tx_laterpay_helper_user::isAdmin()) {
			return;
		}

		$render = new tx_laterpay_controller_abstract(NULL);
		$render->assign('preview_as_visitor', tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_AS_VISITOR));
		$render->assign('hide_statistics_pane', tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_STATISTICS_TAB_IS_HIDDEN));

		$tab = $render->getTextView('frontend/page/select_preview_mode_tab');

		$params['bodyContent'] .= $tab;
	}

	/**
	 * Is it needed to wrap content or not.
	 *
	 * @return bool
	 */
	protected function needToWrap() {
		$liveMode = tx_laterpay_config::getOption(tx_laterpay_config::REG_IS_IN_LIVE_MODE);

		if (tx_laterpay_helper_user::isAdmin()) {
			if (!tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_AS_VISITOR)) {
				return FALSE;
			}
		} elseif (!$liveMode) {
			// if in test mode and test mode is invisible
			if (!tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * JS and CSS setter.
	 *
	 * @return void
	 */
	public function setJsAndCss() {
		// set basic Javascript
		$GLOBALS['TSFE']->getPageRenderer()->addJsFile(t3lib_extMgm::siteRelPath('laterpay') . 'res/js/laterpay-jquery-noconflict.js', $type = 'text/javascript', $compress = TRUE, $forceOnTop = TRUE);
		$GLOBALS['TSFE']->getPageRenderer()->addJsFile('//code.jquery.com/jquery-1.11.1.js', $type = 'text/javascript', $compress = TRUE, $forceOnTop = TRUE);

		$config = tx_laterpay_config::getInstance();
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_yui'] = $config->getInstance()->get(tx_laterpay_config::LATERPAY_YUI_JS);
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_yui.']['external'] = 1;

		$js = t3lib_extMgm::siteRelPath('laterpay') . 'res/js/laterpay-post-view.js';
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay'] = $js;
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay.']['external'] = 1;

		// set basic CSS
		$css = t3lib_extMgm::siteRelPath('laterpay') . 'res/css/laterpay-post-view.css';
		$GLOBALS['TSFE']->getPageRenderer()->addCssFile($css);
	}

	/**
	 * Add timepasses list into bodytext
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return void
	 */
	public function addTimePassesList(tslib_cObj &$contentObject) {

		$timepasses = tx_laterpay_helper_timepass::getAllTimePasses();
		$link = $this->getPageUrl();
		if (count($timepasses)) {
			$renderer = new tx_laterpay_controller_abstract(NULL);

			foreach ($timepasses as $key => $timepass) {
				$timepasses[$key]['laterpayURL'] = tx_laterpay_helper_timepass::getLaterpayPurchaseLink($timepass['pass_id'], array('link' => $link));
			}

			$renderer->assign('renderer', $renderer);
			$renderer->assign('time_passes', $timepasses);
			$renderer->assign('isInVisibleTestMode', tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE) ? : NULL);
			$renderer->assign('previewAsVisitor', tx_laterpay_helper_user::previewAsVisitor());

			$renderedPasses = $renderer->getTextView('frontend/timepass/list');
			$preparedPasses = preg_replace('/[\n\r]+/', '', $renderedPasses);
			$preparedPasses = preg_replace('/>\s+</', '><', $preparedPasses);

			$contentObject->data['bodytext'] .= $preparedPasses;
		}
	}

	/**
	 * Add new view into statistic
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return void
	 */
	public function addStatisticView(tslib_cObj $contentObject) {
		$id = $this->getId($contentObject);
		$pageId = $this->getPageId($contentObject);

		if (! isset($this->addedViews[$pageId]) || ! in_array($id, $this->addedViews[$pageId])) {
			tx_laterpay_helper_statistic::addContentView($contentObject);
			if (!isset($this->addedViews[$pageId])) {
				$this->addedViews[$pageId] = array();
			}

			$this->addedViews[$pageId][] = $id;
		}
	}

	/**
	 * Ask if user has access to contents on page
	 *
	 * @param int $pageId id of page to check accesses for content
	 *
	 * @return void
	 */
	public function loadPageAccesses($pageId) {
		$contents = tx_laterpay_model_content::getPurchasableContentForPage($pageId);
		$ids = array();
		foreach ($contents as $content) {
			$ids[] = $content['uid'];
		}

		if (count($ids)) {
			tx_laterpay_helper_access::checkIfHasAccessToContent($ids);
		}
	}
}
