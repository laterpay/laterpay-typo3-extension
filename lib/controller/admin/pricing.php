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
	 * Load assests
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
			'make' => __('Make', 'laterpay'),
			'free' => __('free', 'laterpay'),
			'to' => __('to', 'laterpay'),
			'by' => __('by', 'laterpay'),
			'toGlobalDefaultPrice' => __('to global default price of', 'laterpay'),
			'toCategoryDefaultPrice' => __('to category default price of', 'laterpay'),
			'updatePrices' => __('Update Prices', 'laterpay'),
			'delete' => __('Delete', 'laterpay'),

			// time pass editor
			'confirmDeleteTimePass' => __('Every user, who owns this pass, will lose his access.', 'laterpay'),
			'voucherText' => __('allows purchasing this pass for', 'laterpay'),
			'timesRedeemed' => __('times redeemed.', 'laterpay')
		);

		// pass localized strings and variables to script
		$passesModel = new tx_laterpay_model_timepass();

		$passesList = (array) $passesModel->getAllTimePasses();
		$vouchersList = tx_laterpay_helper_voucher::getAllVouchers();
		$vouchersStatistic = tx_laterpay_helper_voucher::getAllVouchersStatistic();

		$this->localizeScript('lpVars',
			array(
				'locale' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'],
				'i18n' => $i18n,
				'globalDefaultPrice' => tx_laterpay_helper_view::formatNumber(get_option('laterpay_global_price')),
				'defaultCurrency' => get_option('laterpay_currency'),
				'inCategoryLabel' => __('All posts in category', 'laterpay'),
				'time_passes_list' => $this->getPassesJson($passesList),
				'vouchers_list' => json_encode($vouchersList),
				'vouchers_statistic' => json_encode($vouchersStatistic),
				'l10n_print_after' => 'lpVars.time_passes_list = JSON.parse(lpVars.time_passes_list);
                                            lpVars.vouchers_list = JSON.parse(lpVars.vouchers_list);
                                            lpVars.vouchers_statistic = JSON.parse(lpVars.vouchers_statistic);'
			));

		$this->doc->JScodeArray['ajaxurl'] = 'var ajaxurl = "ajax.php?ajaxID=txttlaterpayM1::pricing";' . LF;
	}

	/**
	 * Render page
	 *
	 * @see tx_laterpay_controller_abstract::render_page
	 *
	 * @return string
	 */
	public function renderPage() {
		$this->loadAssets();

// 		$categoryPriceModel = new tx_laterpay_model_categoryprice();
// 		$categoriesWithDefinedPrice = $categoryPriceModel->getCategoriesWithDefinedPrice();

		// time passes and vouchers data
		$passesModel = new tx_laterpay_model_timepass();
		$passesList = (array) $passesModel->getAllTimePasses();
		$vouchersList = tx_laterpay_helper_voucher::getAllVouchers();
		$vouchersStatistic = tx_laterpay_helper_voucher::getAllVouchersStatistic();

		// bulk price editor data
		$bulkActions = array(
			'set' => __('Set price of', 'laterpay'),
			'increase' => __('Increase price of', 'laterpay'),
			'reduce' => __('Reduce price of', 'laterpay'),
			'free' => __('Make free', 'laterpay'),
			'reset' => __('Reset', 'laterpay')
		);

		$bulkSelectors = array(
			'all' => __('All posts', 'laterpay')
		);

// 		$bulkCategories = get_categories();
// 		$bulkCategoriesWithPrice = tx_laterpay_helper_pricing::getCategoriesWithPrice($bulkCategories);
		$bulkSavedOperations = tx_laterpay_helper_pricing::getBulkOperations();

		$viewArgs = array(
			'top_nav' => $this->getMenu(),
			'admin_menu' => tx_laterpay_helper_view::getAdminMenu(),
			//$categoriesWithDefinedPrice,
			'categories_with_defined_price' => array(),
			'standard_currency' => get_option('laterpay_currency'),
			'plugin_is_in_live_mode' => $this->config->get('is_in_live_mode'),
			'global_default_price' => tx_laterpay_helper_view::formatNumber(get_option('laterpay_global_price')),
			'global_default_price_revenue_model' => get_option('laterpay_global_price_revenue_model'),
			'passes_list' => $passesList,
			'vouchers_list' => $vouchersList,
			'vouchers_statistic' => $vouchersStatistic,
			'bulk_actions' => $bulkActions,
			'bulk_selectors' => $bulkSelectors,
			'bulk_categories' => $bulkCategories,
			//$bulkCategoriesWithPrice,
			'bulk_categories_with_price' => array(),
			'bulk_saved_operations' => $bulkSavedOperations,
			'landing_page' => get_option('laterpay_landing_page'),
			'only_time_pass_purchases_allowed' => get_option('laterpay_only_time_pass_purchases_allowed')
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
		$post = t3lib_div::_POST();
		// save changes in submitted form
		if (! empty($postForm)) {
			// check for required capabilities to perform action
			/*
			 * if (! current_user_can('activate_plugins')) {
			 * wp_send_json(
			 * array(
			 * 'success' => false,
			 * 'message' => __("You don't have sufficient user capabilities to do this.", 'laterpay')
			 * ));
			 * }
			 */
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

/* 				case 'price_category_form_delete':
					$this->deleteCategoryDefaultPrice();
					break;
 */
/* 				case 'laterpay_get_category_prices':
					if (! array_key_exists('category_ids', $post)) {
						$post['category_ids'] = array();
					}
					$this->getCategoryPrices($post['category_ids']);
					break;
 */
				case 'bulk_price_form':
					$ajaxObj->setContent($this->changePostsPrice());
					break;

				case 'bulk_price_form_save':
					$ajaxObj->setContent($this->saveBulkOperation());
					break;

				case 'bulk_price_form_delete':
					$this->deleteBulkOperation();
					break;

/* 				case 'reset_post_publication_date':
					if (! empty($post['post_id'])) {
						$postId = get_post($post['post_id']);
						if ($postId != NULL) {
							tx_laterpay_helper_pricing::resetPostPublicationDate($postId);
							$ajaxObj->setContent(array('success' => TRUE));
						}
					}
					break;
 */
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

/* 				case 'laterpay_get_categories_with_price':

					// return categories that match a given search term
					if (isset($_POST['term'])) {
						$categoryPriceModel = new LaterPay_Model_CategoryPrice();
						$args = array();

						if (! empty($_POST['term'])) {
							$args['name__like'] = $_POST['term'];
						}

						wp_send_json($categoryPriceModel->getCategoriesWithoutPriceByTerm($args));
						die();
					}
					break;
 */
/* 				case 'laterpay_get_categories':

					// return categories
					$args = array(
						'hide_empty' => false
					);

					if (isset($_POST['term']) && ! empty($_POST['term'])) {
						$args['name__like'] = $_POST['term'];
					}

					$categories = get_categories($args);

					wp_send_json($categories);
					break;
*/

				case 'change_purchase_mode_form':
					$ajaxObj->setContent($this->changePurchaseMode());
					break;

				default:
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						));
			}
		} else {
			// invalid request
			$ajaxObj->setContent(
				array(
					'success' => FALSE,
					'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
				)
			);
		}
	}

	/**
	 * Update the currency used for all prices.
	 *
	 * @return void
	 */
/*	protected function updateCurrency() {
		$currencyForm = new LaterPay_Form_Currency();

		if (! $currencyForm->isValid($_POST)) {
			wp_send_json(
				array(
					'success' => FALSE,
					'message' => __('Error occurred. Incorrect data provided.', 'laterpay')
				));
		}

		update_option('laterpay_currency', $currencyForm->getFieldValue('laterpay_currency'));

		wp_send_json(
			array(
				'success' => true,
				'laterpay_currency' => get_option('laterpay_currency'),
				'message' => sprintf(__('The currency for this website is %s now.', 'laterpay'),
					get_option('laterpay_currency'))
			));
	}
*/

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
				'success' => FALSE,
				'laterpay_global_price' => get_option('laterpay_global_price'),
				'laterpay_price_revenue_model' => get_option('laterpay_global_price_revenue_model'),
				'message' => __('The price you tried to set is outside the allowed range of 0 or 0.05-149.99.', 'laterpay')
			);
		}

		$delocalizedGlobalPrice = $globalPriceForm->getFieldValue('laterpay_global_price');
		$globalPriceRevenueModel = $globalPriceForm->getFieldValue('laterpay_global_price_revenue_model');

		update_option('laterpay_global_price', $delocalizedGlobalPrice);
		update_option('laterpay_global_price_revenue_model', $globalPriceRevenueModel);

		$globalPrice = (float) get_option('laterpay_global_price');
		$localizedGlobalPrice = tx_laterpay_helper_view::formatNumber($globalPrice);
		$currencyModel = new tx_laterpay_model_currency();
		$currencyName = $currencyModel->getCurrencyNameByIso4217Code(get_option('laterpay_currency'));

		if ($globalPrice == 0) {
			$message = __('All posts are free by default now.', 'laterpay');
		} else {
			$message = sprintf(__('The global default price for all posts is %s %s now.', 'laterpay'), $localizedGlobalPrice,
				$currencyName);
		}

		return array(
			'success' => TRUE,
			'laterpay_global_price' => $localizedGlobalPrice,
			'laterpay_price_revenue_model' => $globalPriceRevenueModel,
			'message' => $message
		);
	}

	/**
	 * Set the category price, if a given category does not have a category
	 * price yet.
	 *
	 * @return void
	 */
/* 	protected function setCategoryDefaultPrice() {
		$priceCategoryForm = new LaterPay_Form_PriceCategory();

		if (! $priceCategoryForm->isValid($_POST)) {
			wp_send_json(
				array(
					'success' => FALSE,
					'message' => __('The price you tried to set is outside the allowed range of 0 or 0.05-149.99.', 'laterpay')
				));
		}

		$postCategoryId = $priceCategoryForm->getFieldValue('category_id');
		$category = $priceCategoryForm->getFieldValue('category');
		$term = get_term_by('name', $category, 'category');
		$categoryPriceRevenueModel = $priceCategoryForm->getFieldValue('laterpay_category_price_revenue_model');
		$updatedPostIds = null;

		if (! $term) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
				));
		}

		$categoryId = $term->termId;
		$categoryPriceModel = new LaterPay_Model_CategoryPrice();
		$categoryPriceId = $categoryPriceModel->getPriceIdByCategoryId($categoryId);
		$delocalizedCategoryPrice = $priceCategoryForm->getFieldValue('price');

		if (empty($categoryId)) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __('There is no such category on this website.', 'laterpay')
				));
		}

		if (! $postCategoryId) {
			$categoryPriceModel->setCategoryPrice($categoryId, $delocalizedCategoryPrice, $categoryPriceRevenueModel);
			$updatedPostIds = tx_laterpay_helper_pricing::applyCategoryPriceToPostsWithGlobalPrice($categoryId);
		} else {
			$categoryPriceModel->setCategoryPrice($categoryId, $delocalizedCategoryPrice, $categoryPriceRevenueModel,
				$categoryPriceId);
		}

		$currencyModel = new LaterPay_Model_Currency();
		$currencyName = $currencyModel->getCurrencyNameByIso4217Code(get_option('laterpay_currency'));
		$localizedCategoryPrice = LaterPay_Helper_View::formatNumber($delocalizedCategoryPrice);

		wp_send_json(
			array(
				'success' => true,
				'category' => $category,
				'price' => $localizedCategoryPrice,
				'currency' => get_option('laterpay_currency'),
				'category_id' => $categoryId,
				'revenue_model' => $categoryPriceRevenueModel,
				'updated_post_ids' => $updatedPostIds,
				'message' => sprintf(__('All posts in category %s have a default price of %s %s now.', 'laterpay'), $category,
					$localizedCategoryPrice, $currencyName)
			));
	}
 */
	/**
	 * Delete the category price for a given category.
	 *
	 * @return void
	 */
	/* protected function deleteCategoryDefaultPrice() {
		$priceCategoryDeleteForm = new LaterPay_Form_PriceCategory();

		if (! $priceCategoryDeleteForm->isValid($_POST)) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
				));
		}

		$categoryId = $priceCategoryDeleteForm->getFieldValue('category_id');

		// delete the categoryPrice
		$categoryPriceModel = new LaterPay_Model_CategoryPrice();
		$success = $categoryPriceModel->deletePricesByCategoryId($categoryId);

		if (! $success) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
				));
		}

		// get all posts with the deleted $categoryId and loop through them
		$postIds = tx_laterpay_helper_pricing::getPostIdsWithPriceByCategoryId($categoryId);
		foreach ($postIds as $postId) {
			// check, if the post has LaterPay pricing data
			$postPrice = get_post_meta($postId, 'laterpay_post_prices', true);
			if (! is_array($post_price)) {
				continue;
			}

			// check, if the post uses a category default price
			if ($postPrice['type'] !== tx_laterpay_helper_pricing::TYPE_CATEGORY_DEFAULT_PRICE) {
				continue;
			}

			// check, if the post has the deleted category_id as category
			// default price
			if ((int) $postPrice['category_id'] !== $categoryId) {
				continue;
			}

			// update post data
			tx_laterpay_helper_pricing::updatePostDataAfterCategoryDelete($postId);
		}

		wp_send_json(
			array(
				'success' => true,
				'message' => sprintf(__('The default price for category %s was deleted.', 'laterpay'),
					$priceCategoryDeleteForm->getFieldValue('category'))
			));
	}
*/

	/**
	 * Process Ajax requests for prices of applied categories.
	 *
	 * @param array $categoryIds
	 *
	 * @return void
	 */
/* 	protected function getCategoryPrices($categoryIds) {
		$categoriesPriceData = tx_laterpay_helper_pricing::getCategoryPriceDataByCategoryIds($categoryIds);

		wp_send_json($categoriesPriceData);
	}
 */
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
						'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
					);
				}
			}

			// get scope of posts to be processed from selector
			$posts = NULL;
// 			$categoryPriceModel = new LaterPay_Model_CategoryPrice();
			$selector = $bulkPriceForm->getFieldValue('bulk_selector');
			$action = $bulkPriceForm->getFieldValue('bulk_action');
			$changeUnit = $bulkPriceForm->getFieldValue('bulk_change_unit');
			$price = $bulkPriceForm->getFieldValue('bulk_price');
			$isPercent = ($changeUnit == 'percent');
			$defaultCurrency = get_option('laterpay_currency');
			$updateAll = ($selector === 'all');
			$categoryId = NULL;
			// flash message parts
			$messageParts = array(
				'all' => __('The prices of all posts', 'laterpay'),
				'category' => '',
				'have_been' => __('have been', 'laterpay'),
				'action' => __('set', 'laterpay'),
				'preposition' => __('to', 'laterpay'),
				'amount' => '',
				'unit' => ''
			);

			/* if (! $updateAll) {
				$categoryId = $bulkPriceForm->getFieldValue('bulk_category');

				if ($categoryId === NULL) {
					$categoryId = $bulkPriceForm->getFieldValue('bulk_category_with_price');
				}

				if ($categoryId !== NULL) {
					$categoryName = get_the_category_by_ID($categoryId);
					$posts = tx_laterpay_helper_pricing::getPostIdsWithPriceByCategoryId($categoryId);
					$messageParts['category'] = sprintf(__('%s %s', 'laterpay'), str_replace('_', ' ', $selector), $categoryName);
				}
			} else {
*/
			$posts = tx_laterpay_helper_pricing::getAllPostsWithPrice();
			/* } */

			$price = ($price === NULL) ? 0 : $price;
			$newPrice = tx_laterpay_helper_pricing::ensureValidPrice($price);

			// pre-post-processing actions - correct global and categories
			// default prices, set flash message parts;
			// run exactly once, independent of actual number of posts
			switch ($action) {
				case 'set':
					$this->updateGlobalAndCategoriesPricesWithNewPrice($newPrice);
					// set flash message parts
					$messageParts['action'] = __('set', 'laterpay');
					$messageParts['preposition'] = __('to', 'laterpay');
					$messageParts['amount'] = tx_laterpay_helper_view::formatNumber(
						tx_laterpay_helper_pricing::ensureValidPrice($newPrice));
					$messageParts['unit'] = $defaultCurrency;
					break;
				case 'increase':
					// Fall through
				case 'reduce':
					$isReduction = ($action === 'reduce');

					// process global price
					$globalPrice = get_option('laterpay_global_price');
					$changeAmount = $isPercent ? $globalPrice * $price / 100 : $price;
					$newPrice = $isReduction ? $globalPrice - $changeAmount : $globalPrice + $changeAmount;
					$globalPriceRevenue = tx_laterpay_helper_pricing::ensureValidRevenueModel(
						get_option('laterpay_global_price_revenue_model'), $newPrice);
					update_option('laterpay_global_price', tx_laterpay_helper_pricing::ensureValidPrice($newPrice));
					update_option('laterpay_global_price_revenue_model', $globalPriceRevenue);

					// process category default prices

// 					$categories = $categoryPriceModel->getCategoriesWithDefinedPrice();
// 					if ($categories) {
// 						foreach ($categories as $category) {
// 							$changeAmount = $isPercent ? $category->categoryPrice * $price / 100 : $price;
// 							$newPrice = $isReduction ? $category->categoryPrice - $changeAmount : $category->categoryPrice +
// 								$changeAmount;
// 							$newPrice = tx_laterpay_helper_pricing::ensureValidPrice($newPrice);
// 							$revenueModel = tx_laterpay_helper_pricing::ensureValidRevenueModel($category->revenueModel, $newPrice);
// 							$categoryPriceModel->setCategoryPrice($category->categoryId, $newPrice, $revenueModel, $category->id);
// 						}
// 					}

					// set flash message parts
					$messageParts['action'] = $isReduction ? __('decreased', 'laterpay') : __('increased', 'laterpay');
					$messageParts['preposition'] = __('by', 'laterpay');
					$messageParts['amount'] = $isPercent ? $price : LaterPay_Helper_View::formatNumber($price);
					$messageParts['unit'] = $isPercent ? '%' : $changeUnit;
					break;

				case 'free':
					/*if (! $updateAll && $categoryId !== NULL) {
						$categoryPriceId = $categoryPriceModel->getPriceIdByCategoryId($categoryId);
						$categoryPriceModel->setCategoryPrice($categoryId, $newPrice, 'ppu', $categoryPriceId);
					} else
*/
					if ($updateAll) {
						$this->updateGlobalAndCategoriesPricesWithNewPrice($newPrice);
					}
					$messageParts['all'] = __('All posts', 'laterpay');
					$messageParts['action'] = __('made free', 'laterpay');
					$messageParts['preposition'] = '';
					break;

				case 'reset':
					$messageParts['action'] = __('reset', 'laterpay');
					if ($updateAll) {
						/* $categoryPriceModel->deleteAllCategoryPrices(); */
						$newPrice = get_option('laterpay_global_price');
						// set flash message parts
						$messageParts['preposition'] = __('to global default price of', 'laterpay');
						$messageParts['amount'] = LaterPay_Helper_View::formatNumber($newPrice);
						$messageParts['unit'] = $defaultCurrency;
					}/*  else {
						$newPrice = $categoryPriceModel->getPriceByCategoryId($categoryId);
						// set flash message parts
						$messageParts['preposition'] = __('to category default price of', 'laterpay');
						$messageParts['amount'] = LaterPay_Helper_View::formatNumber($newPrice);
						$messageParts['unit'] = $defaultCurrency;
					}
*/
					break;

				default:
					wp_send_json(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						));
			}

			// update post prices
			if ($posts) {
				foreach ($posts as $post) {
					$postId = is_int($post) ? $post : $post->ID;
					$postMeta = get_post_meta($postId, 'laterpay_post_prices', TRUE);
					$metaValues = $postMeta ? $postMeta : array();

					$currentRevenueModel = isset($metaValues['revenue_model']) ? $metaValues['revenue_model'] : 'ppu';
					$currentPostPrice = tx_laterpay_helper_pricing::getPostPrice($postId);
					$currentPostType = tx_laterpay_helper_pricing::getPostPriceType($postId);
					$postTypeIsGlobal = ($currentPostType == tx_laterpay_helper_pricing::TYPE_GLOBAL_DEFAULT_PRICE);
					$postTypeIsCategory = ($currentPostType == tx_laterpay_helper_pricing::TYPE_CATEGORY_DEFAULT_PRICE);
					$isIndividual = (! $postTypeIsGlobal && ! $postTypeIsCategory);

					$newPrice = tx_laterpay_helper_pricing::ensureValidPrice($price);

					switch ($action) {
						case 'increase':
							//Fall through
						case 'reduce':
							if ($isIndividual) {
								$isReduction = ($action === 'reduce');
								$changeAmount = $isPercent ? $currentPostPrice * $price / 100 : $price;
								$newPrice = $isReduction ? $currentPostPrice - $changeAmount : $currentPostPrice + $changeAmount;
							}
							break;

						case 'free':
							/* if (! $updateAll && ! $isIndividual) {
								$metaValues['type'] = tx_laterpay_helper_pricing::TYPE_CATEGORY_DEFAULT_PRICE;
								$metaValues['category_id'] = $categoryId;
								$newPrice = $categoryPriceModel->getPriceByCategoryId($categoryId);
							}*/
							break;

						case 'reset':
							if ($updateAll) {
								$metaValues['type'] = tx_laterpay_helper_pricing::TYPE_GLOBAL_DEFAULT_PRICE;
								$newPrice = get_option('laterpay_global_price');
							} /* else {
								$metaValues['type'] = tx_laterpay_helper_pricing::TYPE_CATEGORY_DEFAULT_PRICE;
								$metaValues['category_id'] = $categoryId;
								$newPrice = $categoryPriceModel->getPriceByCategoryId($categoryId);
							}*/
							break;

						default:
					}

					// make sure the price is within the valid range
					$metaValues['price'] = tx_laterpay_helper_pricing::ensureValidPrice($newPrice);
					// adjust revenue model to new price, if required
					$metaValues['revenue_model'] = tx_laterpay_helper_pricing::ensureValidRevenueModel($currentRevenueModel,
						$metaValues['price']);

					// save updated pricing data
					/* update_post_meta($postId, 'laterpay_post_prices', $metaValues); */
				}
			}

			// render flash message
			return array(
				'success' => TRUE,
				'message' => trim(preg_replace('/\s+/', ' ', join(' ', $messageParts))) . '.'
			);
		}

		return array(
			'success' => FALSE,
			'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay'),
			'errors' => $bulkPriceForm->getErrors()
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
			get_option('laterpay_global_price_revenue_model'), $price);
		update_option('laterpay_global_price', $price);
		update_option('laterpay_global_price_revenue_model', $globalRevenueModel);

		// update all category prices
/*		$categoryPriceModel = new tx)laterpay_model_categoryprice();
		$categories = $categoryPriceModel->getCategoriesWithDefinedPrice();
		$revenueModel = tx_laterpay_helper_pricing::ensureValidRevenueModel('ppu', $price);
		if ($categories) {
			foreach ($categories as $category) {
				$categoryPriceModel->setCategoryPrice($category->categoryId, $price, $revenueModel, $category->id);
			}
		}
 */
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
				'data' => array(
					'id' => tx_laterpay_helper_pricing::saveBulkOperation($data, $bulkMessage),
					'message' => $saveBulkOperationForm->getFieldValue('bulk_message')
				),
				'message' => __('Bulk operation saved.', 'laterpay')
			);
		}

		return array(
			'success' => FALSE,
			'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay'),
			'errors' => $saveBulkOperationForm->getErrors()
		);
	}

	/**
	 * Delete bulk operation.
	 *
	 * @return void
	 */
/* 	protected function deleteBulkOperation() {
		$removeBulkOperationForm = new LaterPay_Form_BulkPrice($_POST);
		if ($removeBulkOperationForm->isValid()) {
			$bulkOperationId = $removeBulkOperationForm->getFieldValue('bulk_operation_id');

			$result = tx_laterpay_helper_pricing::deleteBulkOperationById($bulkOperationId);
			if ($result) {
				wp_send_json(
					array(
						'success' => TRUE,
						'message' => __('Bulk operation deleted.', 'laterpay')
					));
			}
		}

		wp_send_json(
			array(
				'success' => FALSE,
				'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
			));
	}
 */
	/**
	 * Render time pass HTML.
	 *
	 * @param mixed $args Input arguments
	 *
	 * @return string
	 */
	public function renderTimePass($args = array()) {
		// $defaults = LaterPay_Helper_TimePass::getDefaultOptions();
		// $args = array_merge( $defaults, $args );
		//if (! empty($args['pass_id'])) {
			// $args['url'] =
		// LaterPay_Helper_TimePass::getLaterpayPurchaseLink(
		// $args['pass_id'] );
		//}

		$this->assign('laterpay_pass', $args);
		$this->assign('laterpay',
			array(
				'standard_currency' => get_option('laterpay_currency'),
				'preview_post_as_visitor' => 1
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
		$passModel = new tx_laterpay_model_timepass();

		if ($savePassForm->isValid()) {
			$voucher = $savePassForm->getFieldValue('voucher');
			$data = $savePassForm->getFormValues(TRUE, NULL, array(
				'voucher'
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

			//$data['category_name'] = get_the_category_by_ID($data['access_category']);
			$data['price'] = tx_laterpay_helper_view::formatNumber($data['price']);

			$fResult = array(
				'success' => TRUE,
				'data' => $data,
				'vouchers' => tx_laterpay_helper_voucher::getTimePassVouchers($data['pass_id']),
				'html' => $this->renderTimePass($data),
				'message' => __('Pass saved.', 'laterpay')
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'errors' => $savePassForm->getErrors(),
				'message' => __('An error occurred when trying to save the pass. Please try again.', 'laterpay')
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
				'message' => __('Pass deleted.', 'laterpay')
			);
		} else {
			$fResult = array(
				'success' => FALSE,
				'message' => __('The selected pass was deleted already.', 'laterpay')
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
				'code' => LaterPay_Helper_Voucher::generateVoucherCode()
			));
	}

	/**
	 * Save landing page URL the user is forwarded to after redeeming a gift
	 * card voucher.
	 *
	 * @return array
	 */
	private function saveLandingPage() {
		$post = t3lib_div::_POST();

		$landingPageForm = new tx_laterpay_form_landingpage($post);

		if ($landingPageForm->isValid()) {
			// save URL and confirm with flash message, if the URL is valid
			update_option('laterpay_landing_page', $landingPageForm->getFieldValue('landing_url'));

			return array(
				'success' => TRUE,
				'message' => __('Landing page saved.', 'laterpay')
			);
		} else {
			// show an error message, if the provided URL is not valid
			return array(
				'success' => FALSE,
				'message' => __('The landing page you entered is not a valid URL.', 'laterpay'),
				'errors' => $landingPageForm->getErrors()
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
					'message' => __('You have to create a time pass, before you can disable individual purchases.')
				);
			}
		}

		update_option('laterpay_only_time_pass_purchases_allowed', $onlyTimePass);
		if ($fResult === NULL) {
			$fResult = array('success' => TRUE);
		}

		return $fResult;
	}
}
