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
 * LaterPay account controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_controller_admin_account extends tx_laterpay_controller_abstract {

	/**
	 * Load assets.
	 *
	 * @see tx_laterpay_controller_abstract::load_assets
	 *
	 * @return void
	 */
	public function loadAssets() {
		parent::loadAssets();
		// load page-specific JS
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-backend-account.js');

		$this->localizeScript('lpVars',
			array(
				'ajaxUtl' 				=> 'ajax.php',
				'i18nApiKeyInvalid'		=> tx_laterpay_helper_string::tr('The API key you entered is not a valid LaterPay API key!'),
				'i18nMerchantIdInvalid'	=> tx_laterpay_helper_string::tr('The Merchant ID you entered is not a valid LaterPay Merchant ID!'),
				'i18nPreventUnload'		=> tx_laterpay_helper_string::tr('LaterPay does not work properly with invalid API credentials.')
			));

		$this->doc->JScodeArray['ajaxurl'] = 'var ajaxurl = "ajax.php?ajaxID=txttlaterpayM1::account";' . LF;
	}

	/**
	 * Render page.
	 *
	 * @see tx_laterpay_controller_abstract::render_page
	 *
	 * @return string
	 */
	public function renderPage() {
		$this->loadAssets();
		$viewArgs = array(
			'sandbox_merchant_id'				=> tx_laterpay_config::getOption('laterpay_sandbox_merchant_id'),
			'sandbox_api_key' 					=> tx_laterpay_config::getOption('laterpay_sandbox_api_key'),
			'live_merchant_id' 					=> tx_laterpay_config::getOption('laterpay_live_merchant_id'),
			'live_api_key' 						=> tx_laterpay_config::getOption('laterpay_live_api_key'),
			'plugin_is_in_live_mode' 			=> $this->config->get('is_in_live_mode'),
			'plugin_is_in_visible_test_mode'	=> tx_laterpay_config::getOption('laterpay_is_in_visible_test_mode'),
			'top_nav' 							=> $this->getMenu(),
			'admin_menu' 						=> tx_laterpay_helper_view::getAdminMenu()
		);

		$this->assign('laterpay', $viewArgs);
		return $this->render('backend/account');
	}

	/**
	 * Process Ajax requests from account tab.
	 *
	 * @param mixed $params Params of request
	 * @param mixed $ajaxObj TYPO3AJAX instance
	 *
	 * @return void
	 */
	public function processAjaxRequests($params, &$ajaxObj) {
		$postForm = t3lib_div::_POST('form');
		$post = t3lib_div::_POST();
		if (! empty($postForm)) {
			switch ($postForm) {
				case 'laterpay_sandbox_merchant_id':
					$ajaxObj->setContent(self::updateMerchantId());
					break;

				case 'laterpay_sandbox_api_key':
					$ajaxObj->setContent(self::updateApiKey());
					break;

				case 'laterpay_live_merchant_id':
					$ajaxObj->setContent(self::updateMerchantId(TRUE));
					break;

				case 'laterpay_live_api_key':
					$ajaxObj->setContent(self::updateApiKey(TRUE));
					break;

				case 'laterpay_plugin_mode':
					$ajaxObj->setContent(self::updatePluginMode());
					break;

				case 'laterpay_test_mode':
					$ajaxObj->setContent(self::updatePluginVisibilityInTestMode());
					break;

				default:
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						));
			}
		} else {
			$ajaxObj->setContent(
				array(
						'success' => FALSE,
						'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
				));
		}
	}

	/**
	 * Update LaterPay Merchant ID, required for making test transactions
	 * against Sandbox or Live environments.
	 *
	 * @param null|bool $isLive isLive flag
	 *
	 * @return array
	 */
	protected static function updateMerchantId($isLive = NULL) {
		$merchantIdForm = new tx_laterpay_form_merchantid(t3lib_div::_POST());

		$merchantId 	= $merchantIdForm->getFieldValue('merchant_id');
		$merchantIdType = $isLive ? 'live' : 'sandbox';

		// result of function
		$fResult = NULL;

		if ($merchantIdForm->isValid()) {
			tx_laterpay_config::updateOption(sprintf('laterpay_%s_merchant_id', $merchantIdType), $merchantId);
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(tx_laterpay_helper_string::tr('%s Merchant ID verified and saved.'), ucfirst($merchantIdType))
			);
		} elseif (strlen($merchantId) == 0) {
			tx_laterpay_config::updateOption(sprintf('laterpay_%s_merchant_id', $merchantIdType), '');
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(tx_laterpay_helper_string::tr('The %s Merchant ID has been removed.'), ucfirst($merchantIdType))
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => sprintf(
					tx_laterpay_helper_string::tr('The Merchant ID you entered is not a valid LaterPay %s Merchant ID!'),
					ucfirst($merchantIdType))
			);
		}

		// @XXX
		return $fResult;
	}

	/**
	 * Update LaterPay API Key, required for making test transactions against
	 * Sandbox or Live environments.
	 *
	 * @param null|bool $isLive isLive flag
	 *
	 * @return array
	 */
	protected static function updateApiKey($isLive = NULL) {
		$apiKeyForm = new tx_laterpay_form_apikey(t3lib_div::_POST());

		$apiKey 			= $apiKeyForm->getFieldValue('api_key');
		$apiKeyType 		= $isLive ? 'live' : 'sandbox';
		$transactionType 	= $isLive ? 'REAL' : 'TEST';

		// result of function
		$fResult = NULL;

		if ($apiKeyForm->isValid()) {
			tx_laterpay_config::updateOption(sprintf('laterpay_%s_api_key', $apiKeyType), $apiKey);
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(tx_laterpay_helper_string::tr('Your %s API key is valid. You can now make %s transactions.'),
					ucfirst($apiKeyType), $transactionType)
			);
		} elseif (strlen($apiKey) == 0) {
			tx_laterpay_config::updateOption(sprintf('laterpay_%s_api_key', $apiKeyType), '');
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(tx_laterpay_helper_string::tr('The %s API key has been removed.'), ucfirst($apiKeyType))
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => sprintf(tx_laterpay_helper_string::tr('The API key you entered is not a valid LaterPay %s API key!'),
					ucfirst($apiKeyType))
			);
		}

		return $fResult;
	}

	/**
	 * Toggle LaterPay plugin mode between TEST and LIVE.
	 *
	 * @return array
	 */
	protected static function updatePluginMode() {
		$pluginModeForm = new tx_laterpay_form_pluginmode();

		// result of function
		$fResult = NULL;

		if (! $pluginModeForm->isValid(t3lib_div::_POST())) {
			$fResult = array(
				'success' => FALSE,
				'message' => tx_laterpay_helper_string::tr('Error occurred. Incorrect data provided.')
			);
		}

		$pluginMode = $pluginModeForm->getFieldValue('plugin_is_in_live_mode');
		$result = tx_laterpay_config::updateOption('laterpay_plugin_is_in_live_mode', $pluginMode);

		if ($result) {
			if (tx_laterpay_config::getOption('laterpay_plugin_is_in_live_mode')) {
				$fResult = array(
					'success' 	=> TRUE,
					'mode' 		=> 'live',
					'message' => tx_laterpay_helper_string::tr(
						'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.'
					)
				);
			} else {
				if (tx_laterpay_config::getOption('plugin_is_in_visible_test_mode')) {
					$fResult = array(
						'success' 	=> TRUE,
						'mode' 		=> 'test',
						'message' => tx_laterpay_helper_string::tr(
							'The LaterPay plugin is in visible TEST mode now. Payments are only simulated and not actually booked.'
						)
					);
				} else {
					$fResult = array(
						'success' 	=> TRUE,
						'mode' 		=> 'test',
						'message' => tx_laterpay_helper_string::tr(
							'The LaterPay plugin is in invisible TEST mode now. Payments are only simulated and not actually booked.'
						)
					);
				}
			}
		} else {
			$fResult = array(
				'success' 	=> FALSE,
				'mode' 		=> 'test',
				'message'	=> tx_laterpay_helper_string::tr('The LaterPay plugin needs valid API credentials to work.')
			);
		}

		return $fResult;
	}

	/**
	 * Toggle LaterPay plugin test mode between INVISIBLE and VISIBLE.
	 *
	 * @return array
	 */
	public static function updatePluginVisibilityInTestMode() {
		$pluginTestModeForm = new tx_laterpay_form_testmode();
		// result of function
		$fResult = NULL;

		if (! $pluginTestModeForm->isValid(t3lib_div::_POST())) {
			$fResult = array(
				'success' 	=> FALSE,
				'mode' 		=> 'test',
				'message'	=> tx_laterpay_helper_string::tr('An error occurred. Incorrect data provided.')
			);
		}

		$isInVisibleTestMode = $pluginTestModeForm->getFieldValue('plugin_is_in_visible_test_mode');
		$hasInvalidCredentials = $pluginTestModeForm->getFieldValue('invalid_credentials');

		if ($hasInvalidCredentials) {
			tx_laterpay_config::updateOption('laterpay_is_in_visible_test_mode', 0);

			$fResult = array(
				'success' 	=> FALSE,
				'mode' 		=> 'test',
				'message'	=> tx_laterpay_helper_string::tr('The LaterPay plugin needs valid API credentials to work.')
			);
		} else {
			tx_laterpay_config::updateOption('laterpay_is_in_visible_test_mode', $isInVisibleTestMode);

			if ($isInVisibleTestMode) {
				$message = tx_laterpay_helper_string::tr('The plugin is in <strong>visible</strong> test mode now.');
			} else {
				$message = tx_laterpay_helper_string::tr('The plugin is in <strong>invisible</strong> test mode now.');
			}

			$fResult = array(
				'success' 	=> TRUE,
				'mode' 		=> 'test',
				'message' 	=> $message
			);
		}

		return $fResult;
	}
}
