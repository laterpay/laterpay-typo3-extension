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
 * LaterPay statistics helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_statistic {

	/**
	 * Content view inserter
	 *
	 * @param tslib_cObj $contentObject Content object
	 *
	 * @return void
	 */
	public static function addContentView(tslib_cObj $contentObject) {

		if (tx_laterpay_helper_pricing::isPurchasable($contentObject)) {
			$data = array(
				'content_id' => $contentObject->data['uid'],
				'ip' => ip2long($_SERVER['REMOTE_ADDR']),
			);
			$model = new tx_laterpay_model_post_view();
			$model->updateContentViews($data);
		}
	}
}
