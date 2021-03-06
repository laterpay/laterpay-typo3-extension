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
 * Do nothing with log data.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_handler_null extends tx_laterpay_core_logger_handler_abstract {

	/**
	 * Constructor of object
	 *
	 * @param int $level The minimum logging level at which this handler will be triggered
	 */
	public function __construct($level = tx_laterpay_core_logger::DEBUG) {
		parent::__construct($level, FALSE);
	}

	/**
	 * Handles a record.
	 *
	 * All records may be passed to this method, and the handler should discard
	 * those that it does not want to handle.
	 *
	 * The return value of this function controls the bubbling process of the handler stack.
	 * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
	 * calling further handlers in the stack with a given log record.
	 *
	 * @param array $record The record to handle
	 *
	 * @return bool
	 */
	public function handle(array $record) {
		return TRUE;
	}
}
