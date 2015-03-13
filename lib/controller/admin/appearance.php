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
 * LaterPay appearance controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_controller_admin_appearance extends tx_laterpay_controller_abstract {

	/**
	 * Load assets
	 *
	 * @see tx_laterpay_controller_abstract::loadAssets()
	 *
	 * @return void
	 */
	public function loadAssets() {
		parent::loadAssets();

		// load page-specific JS
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/vendor/jquery.ezmark.min.js');
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-backend-appearance.js');

		$this->doc->JScodeArray['ajaxurl'] = 'var ajaxurl = "ajax.php?ajaxID=txttlaterpayM1::appearance";' . LF;
	}

	/**
	 * Render page
	 *
	 * @see tx_laterpay_controller_abstract::renderPage()
	 *
	 * @return string
	 */
	public function renderPage() {
		$this->loadAssets();

		$viewArgs = array(
			'plugin_is_in_live_mode' => $this->config->get('is_in_live_mode'),
			'show_teaser_content_only' => get_option('laterpay_teaser_content_only') == 1,
			'top_nav' => $this->getMenu(),
			'admin_menu' => tx_laterpay_helper_view::getAdminMenu(),
			'is_rating_enabled' => $this->config->get('ratings_enabled'),
			'purchase_button_positioned_manually' => get_option('laterpay_purchase_button_positioned_manually'),
			'time_passes_positioned_manually' => get_option('laterpay_time_passes_positioned_manually')
		);
		$this->assign('laterpay', $viewArgs);
		return $this->render('backend/appearance');
	}

	/**
	 * Process Ajax requests from appearance tab.
	 *
	 * @param mixed $params Input params
	 * @param mixed $ajaxObj TYPO3AJAX instance
	 *
	 * @return void
	 */
	public function processAjaxRequests($params, &$ajaxObj) {
		$post = t3lib_div::_POST();
		$postData = t3lib_div::_POST('form');

		// check for required capabilities to perform action
/* 		if (! current_user_can('activate_plugins')) {
			array(
					'success' => false,
					'message' => __('You don\'t have sufficient user capabilities to do this.', 'laterpay')
				));
		}

		if (function_exists('check_admin_referer')) {
			check_admin_referer('laterpay_form');
		}
 */
		switch ($postData) {
			// update presentation mode for paid content
			case 'paid_content_preview':
				$paidContentPreviewForm = new tx_laterpay_form_paidcontentpreview();

				if (! $paidContentPreviewForm->isValid($post)) {
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						)
					);
				} else {
					$result = update_option('laterpay_teaser_content_only',
						$paidContentPreviewForm->getFieldValue('paid_content_preview'));

					if ($result) {
						if (get_option('laterpay_teaser_content_only')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Visitors will now see only the teaser content of paid posts.', 'laterpay')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __(
										'Visitors will now see the teaser content of paid posts plus an excerpt of the real content under an overlay.',
										'laterpay')
								)
							);
						}
					}
				}
				break;

			// update rating functionality (on / off) for purchased items
			case 'ratings':
				$ratingsForm = new tx_laterpay_form_rating();

				if (! $ratingsForm->isValid($post)) {
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						)
					);
				} else {
					$result = update_option('laterpay_ratings', ! ! $ratingsForm->getFieldValue('enable_ratings'));

					if ($result) {
						if (get_option('laterpay_ratings')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Visitors can now rate the posts they have purchased.', 'laterpay')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('The rating of posts has been disabled.', 'laterpay')
								)
							);
						}
					}
				}
				break;

			case 'purchase_button_position':
				$purchaseButtonPosForm = new tx_laterpay_form_purchasebuttonposition($post);

				if (! $purchaseButtonPosForm->isValid()) {
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						)
					);
				} else {
					$result = update_option('laterpay_purchase_button_positioned_manually',
						! ! $purchaseButtonPosForm->getFieldValue('purchase_button_positioned_manually'));

					if ($result) {
						if (get_option('laterpay_purchase_button_positioned_manually')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Purchase buttons are now rendered at a custom position.', 'laterpay')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Purchase buttons are now rendered at their default position.', 'laterpay')
								)
							);
						}
					}
				}
				break;

			case 'time_passes_position':
				$timePassesPosForm = new tx_laterpay_form_timepassposition($post);

				if (! $timePassesPosForm->isValid()) {
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						)
					);
				} else {
					$result = update_option('laterpay_time_passes_positioned_manually',
						! ! $timePassesPosForm->getFieldValue('time_passes_positioned_manually'));

					if ($result) {
						if (get_option('laterpay_time_passes_positioned_manually')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Time passes are now rendered at a custom position.', 'laterpay')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => __('Time passes are now rendered at their default position.', 'laterpay')
								)
							);
						}
					}
				}
				break;

			default:
				$ajaxObj->setContent(
					array(
						'success' => FALSE,
						'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
					)
				);
		}
	}
}
