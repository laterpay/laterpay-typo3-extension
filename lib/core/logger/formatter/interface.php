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
 * LaterPay logger formatter interface.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
interface tx_laterpay_core_logger_formatter_interface {

	/**
	 * Formats a log record.
	 *
	 * @param array $record A record to format
	 *
	 * @return mixed The formatted record
	 */
	public function format(array $record);

	/**
	 * Formats a set of log records.
	 *
	 * @param array $records A set of records to format
	 *
	 * @return mixed The formatted set of records
	 */
	public function formatBatch(array $records);
}
