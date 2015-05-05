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
 * LaterPay pricing controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_controller_admin_pricing extends tx_laterpay_controller_abstract {

	/**
	 * Load assets.
	 *
	 * @see tx_laterpay_controller_abstract::loadAssets()
	 *
	 * @return void
	 */
	public function loadAssets() {
		parent::loadAssets();

		// load page-specific CSS
		$this->doc->addStyleSheet('laterpaycssselect2' . $fileIndex,
			t3lib_extMgm::extRelPath('laterpay') . 'res/css/vendor/select2.min.css');

		// load page-specific JS
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/vendor/select2.min.js');
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-backend-pricing.js');

		// translations
		$i18n = array(
			// bulk price editor
			'make'                   => tx_laterpay_helper_string::tr('Make'),
			'free'                   => tx_laterpay_helper_string::tr('free'),
			'to'                     => tx_laterpay_helper_string::tr('to'),
			'by'                     => tx_laterpay_helper_string::tr('by'),
			'toGlobalDefaultPrice'   => tx_laterpay_helper_string::tr('to global default price of'),
			'toCategoryDefaultPrice' => tx_laterpay_helper_string::tr('to category default price of'),
			'updatePrices'           => tx_laterpay_helper_string::tr('Update Prices'),
			'delete'                 => tx_laterpay_helper_string::tr('Delete'),

			// time pass editor
			'confirmDeleteTimePass' => tx_laterpay_helper_string::tr('Every user, who owns this pass, will lose his access.'),
			'voucherText'           => tx_laterpay_helper_string::tr('allows purchasing this pass for'),
			'timesRedeemed'         => tx_laterpay_helper_string::tr('times redeemed.')
		);

		// pass localized strings and variables to script
		$passesModel = new tx_laterpay_model_timepass();

		$passesList        = (array) $passesModel->getAllTimePasses();
		$vouchersList      = tx_laterpay_helper_voucher::getAllVouchers();
		$vouchersStatistic = tx_laterpay_helper_voucher::getAllVouchersStatistic();
		$this->localizeScript('lpVars',
			array(
				'locale'             => $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'],
				'i18n'               => $i18n,
				'globalDefaultPrice' => tx_laterpay_helper_view::formatNumber(tx_laterpay_config::getOption('laterpay_global_price')),
				'defaultCurrency'    => $this->config->get(tx_laterpay_config::REG_LATERPAY_CURRENCY),
				'inCategoryLabel'    => tx_laterpay_helper_string::tr('All posts in category'),
				'time_passes_list'   => $this->getPassesJson($passesList),
				'vouchers_list'      => json_encode($vouchersList),
				'vouchers_statistic' => json_encode($vouchersStatistic),
				'l10n_print_after'   => 'lpVars.time_passes_list = JSON.parse(lpVars.time_passes_list);
                                            lpVars.vouchers_list = JSON.parse(lpVars.vouchers_list);
                                            lpVars.vouchers_statistic = JSON.parse(lpVars.vouchers_statistic);',
			));

		$this->doc->JScodeArray['ajaxurl'] = 'var ajaxurl = "ajax.php?ajaxID=txttlaterpayM1::pricing";' . LF;
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

		// time passes and vouchers data
		$passesModel       = new tx_laterpay_model_timepass();
		$passesList        = (array) $passesModel->getAllTimePasses();
		$vouchersList      = tx_laterpay_helper_voucher::getAllVouchers();
		$vouchersStatistic = tx_laterpay_helper_voucher::getAllVouchersStatistic();

		// bulk price editor data
		$bulkActions = array(
			'set'      => tx_laterpay_helper_string::tr('Set price of'),
			'increase' => tx_laterpay_helper_string::tr('Increase price of'),
			'reduce'   => tx_laterpay_helper_string::tr('Reduce price of'),
			'free'     => tx_laterpay_helper_string::tr('Make free'),
			'reset'    => tx_laterpay_helper_string::tr('Reset')
		);

		$bulkSelectors = array(
			'all' => tx_laterpay_helper_string::tr('All posts')
		);

		$bulkSavedOperations = tx_laterpay_helper_pricing::getBulkOperations();

		$viewArgs = array(
			'top_nav'                            => $this->getMenu(),
			'admin_menu'                         => tx_laterpay_helper_view::getAdminMenu(),
			'categories_with_defined_price'      => array(),
			'standard_currency'                  => tx_laterpay_config::getOption('laterpay_currency'),
			'plugin_is_in_live_mode'             => $this->config->get('is_in_live_mode'),
			'global_default_price'               => tx_laterpay_helper_view::formatNumber(tx_laterpay_config::getOption('laterpay_global_price')),
			'global_default_price_revenue_model' => tx_laterpay_config::getOption('laterpay_global_price_revenue_model'),
			'passes_list'                        => $passesList,
			'vouchers_list'                      => $vouchersList,
			'vouchers_statistic'                 => $vouchersStatistic,
			'bulk_actions'                       => $bulkActions,
			'bulk_selectors'                     => $bulkSelectors,
			'bulk_categories'                    => $bulkCategories,
			'bulk_categories_with_price'         => array(),
			'bulk_saved_operations'              => $bulkSavedOperations,
			'landing_page'                       => tx_laterpay_config::getOption('laterpay_landing_page'),
			'only_time_pass_purchases_allowed'   => tx_laterpay_config::getOption('laterpay_only_time_pass_purchases_allowed')
		);

		$this->assign('laterpay', $viewArgs);

		return $this->render('backend/pricing');
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
		$post     = t3lib_div::_POST();

		// save changes in submitted form
		if (! empty($postForm)) {
			switch ($postForm) {
				case 'currency_form':
					$this->updateCurrency();
					break;

				case 'global_price_form':
					$ajaxObj->setContent($this->updateGlobalDefaultPrice());
					break;

				case 'price_category_form':
					$this->setCategoryDefaultPrice();
					break;

				case 'bulk_price_form':
					$ajaxObj->setContent($this->changePostsPrice());
					break;

				case 'bulk_price_form_save':
					$ajaxObj->setContent($this->saveBulkOperation());
					break;

				case 'bulk_price_form_delete':
					$this->deleteBulkOperation();
					break;

				case 'time_pass_form_save':
					$ajaxObj->setContent($this->passFormSave());
					break;

				case 'time_pass_delete':
					$ajaxObj->setContent($this->passDelete());
					break;

				case 'generate_voucher_code':
					$this->generateVoucherCode();
					break;

				case 'save_landing_page':
					$ajaxObj->setContent($this->saveLandingPage());
					break;

				case 'change_purchase_mode_form':
					$ajaxObj->setContent($this->changePurchaseMode());
					break;

				default:
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						));
			}
		} else {
			// invalid request
			$ajaxObj->setContent(
				array(
					'success' => FALSE,
					'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
				)
			);
		}
	}

	/**
	 * Update the global price.
	 * The global price is applied to every posts by default, if
	 * - it is > 0 and
	 * - there isn't a more specific price for a given post.
	 *
	 * @return array
	 */
	protected function updateGlobalDefaultPrice() {
		$post = t3lib_div::_POST();

		$globalPriceForm = new tx_laterpay_form_globalprice();

		if (! $globalPriceForm->isValid($post)) {
			return array(
				'success'                      => FALSE,
				'laterpay_global_price'        => tx_laterpay_config::getOption('laterpay_global_price'),
				'laterpay_price_revenue_model' => tx_laterpay_config::getOption('laterpay_global_price_revenue_model'),
				'message'                      => tx_laterpay_helper_string::tr('The price you tried to set is outside the allowed range of 0 or 0.05-149.99.')
			);
		}

		$delocalizedGlobalPrice  = $globalPriceForm->getFieldValue('laterpay_global_price');
		$globalPriceRevenueModel = $globalPriceForm->getFieldValue('laterpay_global_price_revenue_model');

		tx_laterpay_config::updateOption('laterpay_global_price', $delocalizedGlobalPrice);
		tx_laterpay_config::updateOption('laterpay_global_price_revenue_model', $globalPriceRevenueModel);

		$globalPrice          = (float) tx_laterpay_config::getOption('laterpay_global_price');
		$localizedGlobalPrice = tx_laterpay_helper_view::formatNumber($globalPrice);
		$currencyModel        = new tx_laterpay_model_currency();
		$currencyName         = $currencyModel->getCurrencyNameByIso4217Code(tx_laterpay_config::getOption('laterpay_currency'));

		if ($globalPrice == 0) {
			$message = tx_laterpay_helper_string::tr('All posts are free by default now.');
		} else {
			$message = sprintf(tx_laterpay_helper_string::tr('The global default price for all posts is %s %s now.'), $localizedGlobalPrice,
				$currencyName);
		}

		return array(
			'success'                      => TRUE,
			'laterpay_global_price'        => $localizedGlobalPrice,
			'laterpay_price_revenue_model' => $globalPriceRevenueModel,
			'message'                      => $message,
		);
	}

	/**
	 * Update post prices in bulk.
	 *
	 * This function does not change the price type of a post.
	 * It gets the price type of each post to be updated and updates the
	 * associated individual price, category default
	 * price, or global default price.
	 * It also ensures that the resulting price and revenue model is valid.
	 *
	 * @return array
	 */
	protected function changePostsPrice() {
		$post = t3lib_div::_POST();

		$bulkPriceForm = new tx_laterpay_form_bulkprice($post);

		if ($bulkPriceForm->isValid()) {
			$bulkOperationId = $bulkPriceForm->getFieldValue('bulk_operation_id');

			if ($bulkOperationId !== NULL) {
				$operationData = tx_laterpay_helper_pricing::getBulkOperationDataById($bulkOperationId);

				if (! $bulkPriceForm->isValid($operationData)) {
					return array(
						'success' => FALSE,
						'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
					);
				}
			}

			// get scope of posts to be processed from selector
			$posts      = NULL;
			$selector   = $bulkPriceForm->getFieldValue('bulk_selector');
			$action     = $bulkPriceForm->getFieldValue('bulk_action');
			$changeUnit = $bulkPriceForm->getFieldValue('bulk_change_unit');
			$price      = $bulkPriceForm->getFieldValue('bulk_price');
			$isPercent  = ($changeUnit == 'percent');
			$defaultCurrency = tx_laterpay_config::getOption('laterpay_currency');
			$updateAll  = ($selector === 'all');
			$categoryId = NULL;

			// flash message parts
			$messageParts = array(
				'all'         => tx_laterpay_helper_string::tr('The prices of all posts'),
				'category'    => '',
				'have_been'   => tx_laterpay_helper_string::tr('have been'),
				'action'      => tx_laterpay_helper_string::tr('set'),
				'preposition' => tx_laterpay_helper_string::tr('to'),
				'amount'      => '',
				'unit'        => '',
			);

			$posts = tx_laterpay_helper_pricing::getAllPostsWithPrice();

			$price    = ($price === NULL) ? 0 : $price;
			$newPrice = tx_laterpay_helper_pricing::ensureValidPrice($price);

			// pre-post-processing actions - correct global and categories
			// default prices, set flash message parts;
			// run exactly once, independent of actual number of posts
			switch ($action) {
				case 'set':
					$this->updateGlobalAndCategoriesPricesWithNewPrice($newPrice);

					// set flash message parts
					$messageParts['action']      = tx_laterpay_helper_string::tr('set');
					$messageParts['preposition'] = tx_laterpay_helper_string::tr('to');
					$messageParts['amount']      = tx_laterpay_helper_view::formatNumber(
						tx_laterpay_helper_pricing::ensureValidPrice($newPrice)
					);
					$messageParts['unit'] = $defaultCurrency;
					break;

				case 'increase':
					// fall through
				case 'reduce':
					$isReduction = ($action === 'reduce');

					// process global price
					$globalPrice        = tx_laterpay_config::getOption('laterpay_global_price');
					$changeAmount       = $isPercent ? $globalPrice * $price / 100 : $price;
					$newPrice           = $isReduction ? $globalPrice - $changeAmount : $globalPrice + $changeAmount;
					$globalPriceRevenue = tx_laterpay_helper_pricing::ensureValidRevenueModel(
						tx_laterpay_config::getOption('laterpay_global_price_revenue_model'),
						$newPrice
					);
					tx_laterpay_config::updateOption('laterpay_global_price', tx_laterpay_helper_pricing::ensureValidPrice($newPrice));
					tx_laterpay_config::updateOption('laterpay_global_price_revenue_model', $globalPriceRevenue);

					// set flash message parts
					$messageParts['action']      = $isReduction ? tx_laterpay_helper_string::tr('decreased') : tx_laterpay_helper_string::tr('increased');
					$messageParts['preposition'] = tx_laterpay_helper_string::tr('by');
					$messageParts['amount']      = $isPercent ? $price : LaterPay_Helper_View::formatNumber($price);
					$messageParts['unit']        = $isPercent ? '%' : $changeUnit;
					break;

				case 'free':
					if ($updateAll) {
						$this->updateGlobalAndCategoriesPricesWithNewPrice($newPrice);
					}
					$messageParts['all']         = tx_laterpay_helper_string::tr('All posts');
					$messageParts['action']      = tx_laterpay_helper_string::tr('made free');
					$messageParts['preposition'] = '';
					break;

				case 'reset':
					$messageParts['action'] = tx_laterpay_helper_string::tr('reset');
					if ($updateAll) {
						$newPrice = tx_laterpay_config::getOption('laterpay_global_price');

						// set flash message parts
						$messageParts['preposition'] = tx_laterpay_helper_string::tr('to global default price of');
						$messageParts['amount']      = LaterPay_Helper_View::formatNumber($newPrice);
						$messageParts['unit']        = $defaultCurrency;
					}
					break;

				default:
					wp_send_json(
						array(
							'success' => FALSE,
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						));
			}

			// update post prices
			if ($posts) {
				foreach ($posts as $post) {
					$postId   = is_int($post) ? $post : $post->ID;
					$postMeta = get_post_meta($postId, 'laterpay_post_prices', TRUE);
					$metaValues = $postMeta ? $postMeta : array();

					$currentRevenueModel = isset($metaValues['revenue_model']) ? $metaValues['revenue_model'] : 'ppu';
					$currentPostPrice    = tx_laterpay_helper_pricing::getPostPrice($postId);
					$currentPostType     = tx_laterpay_helper_pricing::getPostPriceType($postId);
					$postTypeIsGlobal    = ($currentPostType == tx_laterpay_helper_pricing::TYPE_GLOBAL_DEFAULT_PRICE);
					$postTypeIsCategory  = ($currentPostType == tx_laterpay_helper_pricing::TYPE_CATEGORY_DEFAULT_PRICE);
					$isIndividual        = (! $postTypeIsGlobal && ! $postTypeIsCategory);

					$newPrice = tx_laterpay_helper_pricing::ensureValidPrice($price);

					switch ($action) {
						case 'increase':
							// Fall through
						case 'reduce':
							if ($isIndividual) {
								$isReduction  = ($action === 'reduce');
								$changeAmount = $isPercent ? $currentPostPrice * $price / 100 : $price;
								$newPrice     = $isReduction ? $currentPostPrice - $changeAmount : $currentPostPrice + $changeAmount;
							}
							break;

						case 'free':
							break;

						case 'reset':
							if ($updateAll) {
								$metaValues['type'] = tx_laterpay_helper_pricing::TYPE_GLOBAL_DEFAULT_PRICE;
								$newPrice = tx_laterpay_config::getOption('laterpay_global_price');
							}
							break;

						default:
					}

					// make sure the price is within the valid range
					$metaValues['price'] = tx_laterpay_helper_pricing::ensureValidPrice($newPrice);

					// adjust revenue model to new price, if required
					$metaValues['revenue_model'] = tx_laterpay_helper_pricing::ensureValidRevenueModel($currentRevenueModel,
						$metaValues['price']);
				}
			}

			// render flash message
			return array(
				'success' => TRUE,
				'message' => trim(preg_replace('/\s+/', ' ', join(' ', $messageParts))) . '.',
			);
		}

		return array(
			'success' => FALSE,
			'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.'),
			'errors' => $bulkPriceForm->getErrors(),
		);
	}

	/**
	 * Update global and category default prices with new price.
	 *
	 * @param mixed $price Price value
	 *
	 * @return void
	 */
	protected function updateGlobalAndCategoriesPricesWithNewPrice($price) {
		$globalRevenueModel = tx_laterpay_helper_pricing::ensureValidRevenueModel(
			tx_laterpay_config::getOption('laterpay_global_price_revenue_model'), $price);
		tx_laterpay_config::updateOption('laterpay_global_price', $price);
		tx_laterpay_config::updateOption('laterpay_global_price_revenue_model', $globalRevenueModel);
	}

	/**
	 * Save bulk operation.
	 *
	 * @return array
	 */
	protected function saveBulkOperation() {
		$post = t3lib_div::_POST();

		$saveBulkOperationForm = new tx_laterpay_form_bulkprice($post);
		if ($saveBulkOperationForm->isValid()) {
			// create data array
			$data = $saveBulkOperationForm->getFormValues(TRUE, 'bulk_', array(
				'bulk_message'
			));
			$bulkMessage = $saveBulkOperationForm->getFieldValue('bulk_message');

			return array(
				'success' => TRUE,
				'data'    => array(
					'id'      => tx_laterpay_helper_pricing::saveBulkOperation($data, $bulkMessage),
					'message' => $saveBulkOperationForm->getFieldValue('bulk_message')
				),
				'message' => tx_laterpay_helper_string::tr('Bulk operation saved.')
			);
		}

		return array(
			'success' => FALSE,
			'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.'),
			'errors'  => $saveBulkOperationForm->getErrors(),
		);
	}

	/**
	 * Render time pass HTML.
	 *
	 * @param mixed $args Input arguments
	 *
	 * @return string
	 */
	public function renderTimePass($args = array()) {
		$this->assign('laterpay_pass', $args);
		$this->assign('laterpay',
			array(
				'standard_currency'       => tx_laterpay_config::getOption('laterpay_currency'),
				'preview_post_as_visitor' => 1,
			));

		$string = $this->getTextView('backend/partials/time_pass');

		return $string;
	}

	/**
	 * Save bulk operation.
	 *
	 * @return array
	 */
	protected function passFormSave() {
		$post = t3lib_div::_POST();

		// result of function
		$fResult = NULL;

		$savePassForm = new tx_laterpay_form_pass($post);
		$passModel    = new tx_laterpay_model_timepass();

		if ($savePassForm->isValid()) {
			$voucher = $savePassForm->getFieldValue('voucher');
			$data = $savePassForm->getFormValues(TRUE, NULL, array(
				'voucher',
			));

			// check and set revenue model
			if (! isset($data['revenue_model'])) {
				$data['revenue_model'] = 'ppu';
			}

			// ensure valid revenue model
			$data['revenue_model'] = tx_laterpay_helper_pricing::ensureValidRevenueModel($data['revenue_model'], $data['price']);

			// update time pass data or create new time pass
			$data = $passModel->updateTimePass($data);

			// save vouchers for this pass
			tx_laterpay_helper_voucher::savePassVouchers($data['pass_id'], $voucher);

			$data['price'] = tx_laterpay_helper_view::formatNumber($data['price']);

			$fResult = array(
				'success'  => TRUE,
				'data'     => $data,
				'vouchers' => tx_laterpay_helper_voucher::getTimePassVouchers($data['pass_id']),
				'html'     => $this->renderTimePass($data),
				'message'  => tx_laterpay_helper_string::tr('Pass saved.')
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'errors'  => $savePassForm->getErrors(),
				'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save the pass. Please try again.')
			);
		}

		return $fResult;
	}

	/**
	 * Remove pass by pass_id.
	 *
	 * @return array
	 */
	protected function passDelete() {
		$postData = t3lib_div::_POST('pass_id');

		// result of function
		$fResult = NULL;
		if (isset($postData)) {
			$passId = $postData;
			$passModel = new tx_laterpay_model_timepass();

			// remove pass
			$passModel->deleteTimePassById($passId);

			// remove vouchers
			tx_laterpay_helper_voucher::deleteVoucherCode($passId);

			$fResult = array(
				'success' => TRUE,
				'message' => tx_laterpay_helper_string::tr('Pass deleted.')
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => tx_laterpay_helper_string::tr('The selected pass was deleted already.')
			);
		}

		return $fResult;
	}

	/**
	 * Get JSON array of passes list with defaults.
	 *
	 * @param mixed $passesList Input array for convert to json
	 *
	 * @return array
	 */
	private function getPassesJson($passesList = NULL) {
		$passesArray = array(
			0 => tx_laterpay_helper_timepass::getDefaultOptions()
		);

		foreach ($passesList as $pass) {
			$pass = (array) $pass;

			if (isset($pass['access_category']) && $pass['access_category']) {
				$pass['category_name'] = get_the_category_by_ID($pass['access_category']);
			}

			$passesArray[$pass['pass_id']] = $pass;
		}

		$passesArray = json_encode($passesArray);

		return $passesArray;
	}

	/**
	 * Get generated voucher code.
	 *
	 * @return void
	 */
	private function generateVoucherCode() {
		// generate voucher code
		wp_send_json(
			array(
				'success' => TRUE,
				'code'    => LaterPay_Helper_Voucher::generateVoucherCode(),
			));
	}

	/**
	 * Save landing page URL the user is forwarded to after redeeming a gift card voucher.
	 *
	 * @return array
	 */
	private function saveLandingPage() {
		$post = t3lib_div::_POST();

		$landingPageForm = new tx_laterpay_form_landingpage($post);

		if ($landingPageForm->isValid()) {
			// save URL and confirm with flash message, if the URL is valid
			tx_laterpay_config::updateOption('laterpay_landing_page', $landingPageForm->getFieldValue('landing_url'));

			return array(
				'success' => TRUE,
				'message' => tx_laterpay_helper_string::tr('Landing page saved.')
			);
		} else {
			// show an error message, if the provided URL is not valid
			return array(
				'success' => FALSE,
				'message' => tx_laterpay_helper_string::tr('The landing page you entered is not a valid URL.'),
				'errors'  => $landingPageForm->getErrors(),
			);
		}
	}

	/**
	 * Switch plugin between allowing
	 * (1) individual purchases and time pass purchases, or
	 * (2) time pass purchases only.
	 * Do nothing and render an error message, if no time pass is defined when
	 * trying to switch to time pass only mode.
	 *
	 * @return array
	 */
	private function changePurchaseMode() {
		$postVal = t3lib_div::_POST('only_time_pass_purchase_mode');

		// result of function
		$fResult = NULL;

		if (isset($postVal)) {
			// allow time pass purchases only
			$onlyTimePass = 1;
		} else {
			// allow individual and time pass purchases
			$onlyTimePass = 0;
		}

		if ($onlyTimePass == 1) {
			if (! tx_laterpay_helper_timepass::getTimePassesCount()) {
				$fResult = array(
					'success' => FALSE,
					'message' => tx_laterpay_helper_string::tr('You have to create a time pass, before you can disable individual purchases.')
				);
			}
		}

		tx_laterpay_config::updateOption('laterpay_only_time_pass_purchases_allowed', $onlyTimePass);
		if ($fResult === NULL) {
			$fResult = array('success' => TRUE);
		}

		return $fResult;
	}
}
