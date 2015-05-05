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
	 * Load assets.
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
	 * Render page.
	 *
	 * @see tx_laterpay_controller_abstract::renderPage()
	 *
	 * @return string
	 */
	public function renderPage() {
		$this->loadAssets();

		$viewArgs = array(
			'plugin_is_in_live_mode'              => $this->config->get('is_in_live_mode'),
			'show_teaser_content_only'            => tx_laterpay_config::getOption('laterpay_teaser_content_only') == 1,
			'top_nav'                             => $this->getMenu(),
			'admin_menu'                          => tx_laterpay_helper_view::getAdminMenu(),
			'is_rating_enabled'                   => $this->config->get('ratings_enabled'),
			'purchase_button_positioned_manually' => tx_laterpay_config::getOption('laterpay_purchase_button_positioned_manually'),
			'time_passes_positioned_manually'     => tx_laterpay_config::getOption('laterpay_time_passes_positioned_manually'),
			'teaser_percentage_of_content'        => tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT),
			'teaser_min_words_count'              => tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN),
			'teaser_max_words_count'              => tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX),
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

		switch ($postData) {
			// update presentation mode for paid content
			case 'paid_content_preview':
				$paidContentPreviewForm = new tx_laterpay_form_paidcontentpreview();

				if (! $paidContentPreviewForm->isValid($post)) {
					$ajaxObj->setContent(
						array(
							'success' => FALSE,
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						)
					);
				} else {
					$result = tx_laterpay_config::updateOption('laterpay_teaser_content_only',
						$paidContentPreviewForm->getFieldValue('paid_content_preview'));

					if ($result) {
						if (tx_laterpay_config::getOption('laterpay_teaser_content_only')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Visitors will now see only the teaser content of paid posts.')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr(
										'Visitors will now see the teaser content of paid posts plus an excerpt of the real content under an overlay.'
									)
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
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						)
					);
				} else {
					$result = tx_laterpay_config::updateOption('laterpay_ratings', ! ! $ratingsForm->getFieldValue('enable_ratings'));

					if ($result) {
						if (tx_laterpay_config::getOption('laterpay_ratings')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Visitors can now rate the posts they have purchased.')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('The rating of posts has been disabled.')
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
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						)
					);
				} else {
					$result = tx_laterpay_config::updateOption('laterpay_purchase_button_positioned_manually',
						! ! $purchaseButtonPosForm->getFieldValue('purchase_button_positioned_manually'));

					if ($result) {
						if (tx_laterpay_config::getOption('laterpay_purchase_button_positioned_manually')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Purchase buttons are now rendered at a custom position.')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Purchase buttons are now rendered at their default position.')
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
							'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
						)
					);
				} else {
					$result = tx_laterpay_config::updateOption('laterpay_time_passes_positioned_manually',
						! ! $timePassesPosForm->getFieldValue('time_passes_positioned_manually'));

					if ($result) {
						if (tx_laterpay_config::getOption('laterpay_time_passes_positioned_manually')) {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Time passes are now rendered at a custom position.')
								)
							);
						} else {
							$ajaxObj->setContent(
								array(
									'success' => TRUE,
									'message' => tx_laterpay_helper_string::tr('Time passes are now rendered at their default position.')
								)
							);
						}
					}
				}
				break;

			case 'auto_teaser_configuration':
				$array = $this->updateAutoTeaserConfiguration($ajaxObj);
				$ajaxObj->setContent(
					$array
				);
				break;

			default:
				$ajaxObj->setContent(
					array(
						'success' => FALSE,
						'message' => tx_laterpay_helper_string::tr('An error occurred when trying to save your settings. Please try again.')
					)
				);
		}
	}

	/**
	 * Set auto teaser generation configurations.
	 *
	 * @return type
	 */
	protected function updateAutoTeaserConfiguration() {
		$percentageFieldName = 'teaser_percentage_of_content';
		$minWordsFieldName   = 'teaser_min_words_count';
		$maxWordsFieldName   = 'teaser_max_words_count';

		$success       = TRUE;
		$errorMessages = array();
		$errors        = array();

		$percentage = t3lib_div::_POST($percentageFieldName);
		$minWords   = t3lib_div::_POST($minWordsFieldName);
		$maxWords   = t3lib_div::_POST($maxWordsFieldName);

		if (!is_numeric($percentage) or $percentage % 1 > 0 or 1 > $percentage or $percentage > 100) {
			$success = FALSE;
			$errorMessages[$percentageFieldName] = tx_laterpay_helper_string::tr('Percentage must be a positive integer between 1 and 100');
			$errors[] = $percentageFieldName;
		}

		if (!is_numeric($minWords) or $minWords < 0 ) {
			$success = FALSE;
			$errorMessages[$minWordsFieldName] = tx_laterpay_helper_string::tr('Min count of words must be a positive integer greater than 0');
			$errors[] = $minWordsFieldName;
		}

		if (!is_numeric($maxWords) or $maxWords < 0 ) {
			$success = FALSE;
			$errorMessages[$maxWordsFieldName] = tx_laterpay_helper_string::tr('Max count of words must be a positive integer greater than 0');
			$errors[] = $maxWordsFieldName;
		}

		if ($maxWords <= $minWords) {
			$success = FALSE;
			$errorMessages[$maxWordsFieldName] = tx_laterpay_helper_string::tr('Max count of words must be greater than min count of words');
			$errors[] = $maxWordsFieldName;
		}

		if ($success) {
			$message = tx_laterpay_helper_string::tr('New values saved successfully');
			tx_laterpay_config::updateOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT, $percentage);
			tx_laterpay_config::updateOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX, $maxWords);
			tx_laterpay_config::updateOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN, $minWords);
		}

		return array(
			'success'       => $success,
			'message'       => $message,
			'error_message' => $errorMessages,
			'errors'        => $errors,
		);
	}
}
