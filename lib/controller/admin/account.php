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
				'i18nApiKeyInvalid' 	=> __('The API key you entered is not a valid LaterPay API key!', 'laterpay'),
				'i18nMerchantIdInvalid' => __('The Merchant ID you entered is not a valid LaterPay Merchant ID!', 'laterpay'),
				'i18nPreventUnload' 	=> __('LaterPay does not work properly with invalid API credentials.', 'laterpay'),
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
			'sandbox_merchant_id' 				=> get_option('laterpay_sandbox_merchant_id'),
			'sandbox_api_key' 					=> get_option('laterpay_sandbox_api_key'),
			'live_merchant_id' 					=> get_option('laterpay_live_merchant_id'),
			'live_api_key' 						=> get_option('laterpay_live_api_key'),
			'plugin_is_in_live_mode' 			=> $this->config->get('is_in_live_mode'),
			'plugin_is_in_visible_test_mode' 	=> get_option('laterpay_is_in_visible_test_mode'),
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
			// check for required capabilities to perform action
			/*
			 * if (! current_user_can('activate_plugins')) {
			 * wp_send_json(
			 * array(
			 * 'success' => false,
			 * 'message' => __(
			 * "You don't have sufficient user capabilities to do this.",
			 * 'laterpay')
			 * ));
			 * }
			 */

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
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						));
			}
		} else {
			$ajaxObj->setContent(
				array(
						'success' => FALSE,
						'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
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
			update_option(sprintf('laterpay_%s_merchant_id', $merchantIdType), $merchantId);
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(__('%s Merchant ID verified and saved.', 'laterpay'), ucfirst($merchantIdType))
			);
		} elseif (strlen($merchantId) == 0) {
			update_option(sprintf('laterpay_%s_merchant_id', $merchantIdType), '');
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(__('The %s Merchant ID has been removed.', 'laterpay'), ucfirst($merchantIdType))
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => sprintf(
					__('The Merchant ID you entered is not a valid LaterPay %s Merchant ID!', 'laterpay'),
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
			update_option(sprintf('laterpay_%s_api_key', $apiKeyType), $apiKey);
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(__('Your %s API key is valid. You can now make %s transactions.', 'laterpay'),
					ucfirst($apiKeyType), $transactionType)
			);
		} elseif (strlen($apiKey) == 0) {
			update_option(sprintf('laterpay_%s_api_key', $apiKeyType), '');
			$fResult = array(
				'success' => TRUE,
				'message' => sprintf(__('The %s API key has been removed.', 'laterpay'), ucfirst($apiKeyType))
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => sprintf(__('The API key you entered is not a valid LaterPay %s API key!', 'laterpay'),
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
				'message' => __('Error occurred. Incorrect data provided.', 'laterpay')
			);
		}

		$pluginMode = $pluginModeForm->getFieldValue('plugin_is_in_live_mode');
		$result = update_option('laterpay_plugin_is_in_live_mode', $pluginMode);

		if ($result) {
			if (get_option('laterpay_plugin_is_in_live_mode')) {
				$fResult = array(
					'success' 	=> TRUE,
					'mode' 		=> 'live',
					'message' 	=> __(
						'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.',
						'laterpay')
				);
			} else {
				if (get_option('plugin_is_in_visible_test_mode')) {
					$fResult = array(
						'success' 	=> TRUE,
						'mode' 		=> 'test',
						'message' 	=> __(
							'The LaterPay plugin is in visible TEST mode now. Payments are only simulated and not actually booked.',
							'laterpay')
					);
				} else {
					$fResult = array(
						'success' 	=> TRUE,
						'mode' 		=> 'test',
						'message' 	=> __(
							'The LaterPay plugin is in invisible TEST mode now. Payments are only simulated and not actually booked.',
							'laterpay')
					);
				}
			}
		} else {
			$fResult = array(
				'success' 	=> FALSE,
				'mode' 		=> 'test',
				'message' 	=> __('The LaterPay plugin needs valid API credentials to work.', 'laterpay')
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
				'message' 	=> __('An error occurred. Incorrect data provided.', 'laterpay')
			);
		}

		$isInVisibleTestMode = $pluginTestModeForm->getFieldValue('plugin_is_in_visible_test_mode');
		$hasInvalidCredentials = $pluginTestModeForm->getFieldValue('invalid_credentials');

		if ($hasInvalidCredentials) {
			update_option('laterpay_is_in_visible_test_mode', 0);

			$fResult = array(
				'success' 	=> FALSE,
				'mode' 		=> 'test',
				'message' 	=> __('The LaterPay plugin needs valid API credentials to work.', 'laterpay')
			);
		} else {
			update_option('laterpay_is_in_visible_test_mode', $isInVisibleTestMode);

			if ($isInVisibleTestMode) {
				$message = __('The plugin is in <strong>visible</strong> test mode now.', 'laterpay');
			} else {
				$message = __('The plugin is in <strong>invisible</strong> test mode now.', 'laterpay');
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
