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
 * LaterPay clear post view task.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_scheduler_clearviewdata extends tx_scheduler_Task {
	/**
	 * Execute removal of logged page views that are older than three months.
	 *
	 * @return bool
	 */
	public function execute() {
		$model = new tx_laterpay_model_post_view();

		$date = new DateTime();
		$date->modify('-3 months');

		$model->removeOldRecords($date);

		return TRUE;
	}
}
