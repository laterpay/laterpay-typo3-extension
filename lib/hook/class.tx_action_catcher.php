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
 * Inner actions catcher class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_action_catcher extends tx_hook_abstract {

	/**
	 * LaterPay post Ajax action catcher.
	 *
	 * @return void
	 */
	public function catchLaterpayAction() {
		// if we are in admin part - nothing to do here
		if (TYPO3_MODE == 'BE') {
			return;
		}

		$action = t3lib_div::_POST('laterpayAction');

		if (!empty($action)) {
			if (method_exists($this, $action)) {
				$this->performAction($action);
			}
		}
	}

	/**
	 * Action performer.
	 *
	 * @param string $actionName name of action to perform
	 *
	 * @return void
	 */
	private function performAction($actionName) {
		$result = $this->{$actionName}();
		echo json_encode($result);
		exit;
	}

	/**
	 * Action answer for changing preview type.
	 *
	 * @return mixed
	 */
	private function postStatisticTogglePreview() {
		// action is only allowed for admin
		if (!tx_laterpay_helper_user::isAdmin()) {
			return;
		}

		$previewAsVisitor = t3lib_div::_POST('preview_post');
		if ($previewAsVisitor === NULL) {
			exit;
		}

		tx_laterpay_config::updateOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_AS_VISITOR, $previewAsVisitor);

		return array(
			'success' => TRUE,
			'message' => '',
		);
	}

	/**
	 * Action answer for changing statistics tab visibility.
	 *
	 * @return mixed
	 */
	private function postStatisticVisibility() {
		// action is only allowed for admin
		if (!tx_laterpay_helper_user::isAdmin()) {
			return;
		}

		$statisticTabIsHidden = t3lib_div::_POST('hide_statistics_pane');
		if ($statisticTabIsHidden === NULL) {
			exit;
		}

		tx_laterpay_config::updateOption(tx_laterpay_config::REG_LATERPAY_STATISTICS_TAB_IS_HIDDEN, $statisticTabIsHidden);

		return array(
			'success' => TRUE,
			'message' => '',
		);
	}
}
