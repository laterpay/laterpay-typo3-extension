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
 * LaterPay core logger handler interface.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
interface tx_laterpay_core_logger_handler_interface {

	/**
	 * Checks whether the given record will be handled by this handler.
	 *
	 * This is mostly done for performance reasons, to avoid calling processors for nothing.
	 *
	 * Handlers should still check the record levels within handle(), returning false in isHandling()
	 * is no guarantee that handle() will not be called, and isHandling() might not be called
	 * for a given record.
	 *
	 * @param array $record Records
	 *
	 * @return bool
	 */
	public function isHandling(array $record);

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
	 * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
	 *         false means the record was either not processed or that this handler allows bubbling.
	 */
	public function handle(array $record);

	/**
	 * Handles a set of records at once.
	 *
	 * @param array $records The records to handle (an array of record arrays)
	 *
	 * @return void
	 */
	public function handleBatch(array $records);

	/**
	 * Adds a processor in the stack.
	 *
	 * @param mixed $callback Callback
	 *
	 * @return self
	 */
	public function pushProcessor($callback);

	/**
	 * Removes the processor on top of the stack and returns it.
	 *
	 * @return callable
	 */
	public function popProcessor();

	/**
	 * Sets the formatter.
	 *
	 * @param tx_laterpay_core_logger_formatter_interface $formatter Formetter object
	 *
	 * @return self
	 */
	public function setFormatter(tx_laterpay_core_logger_formatter_interface $formatter);

	/**
	 * Gets the formatter.
	 *
	 * @return tx_laterpay_core_logger_formatter_interface
	 */
	public function getFormatter();

	/**
	 * Flush all buffered records and return as string
	 *
	 * @return string
	 */
	public function flushRecords();

	/**
	 * Load all assets
	 *
	 * @param object $renderer Instance of t3lib_PageRenderer class
	 *
	 * @return void
	 */
	public function loadAssets($renderer);
}
