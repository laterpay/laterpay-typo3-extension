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
 * LaterPay core logger processor introspection.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_processor_introspection implements tx_laterpay_core_logger_processor_interface {

	/**
	 * Log level
	 *
	 * @var int level of records to log
	 */
	private $level;

	/**
	 * Internal flag
	 *
	 * @var array
	 */
	private $skipClassesPartials;

	/**
	 * Constructor of object
	 *
	 * @param int $level Log level
	 * @param array $skipClassesPartials Flags of skipping partial classes
	 */
	public function __construct($level = tx_laterpay_core_logger::DEBUG, array $skipClassesPartials = array()) {
		$this->level = $level;
		$this->skipClassesPartials = $skipClassesPartials;
	}

	/**
	 * Process record data
	 *
	 * @param array $record Record data
	 *
	 * @return array processed record
	 */
	public function process(array $record) {

		// return, if the level is not high enough
		if ($record['level'] < $this->level) {
			return $record;
		}

		$trace = debug_backtrace();

		// skip first since it's always the current method
		array_shift($trace);
		// the call_user_func call is also skipped
		array_shift($trace);

		$i = 0;

		while (isset($trace[$i]['class'])) {
			foreach ($this->skipClassesPartials as $part) {
				if (strpos($trace[$i]['class'], $part) !== FALSE) {
					$i ++;
					continue 2;
				}
			}
			break;
		}

		if (isset($trace[$i - 1]['file'])) {
			$record['extra']['file'] = $trace[$i - 1]['file'];
		} else {
			$record['extra']['file'] = NULL;
		}
		if (isset($trace[$i - 1]['line'])) {
			$record['extra']['line'] = $trace[$i - 1]['line'];
		} else {
			$record['extra']['line'] = NULL;
		}
		if (isset($trace[$i]['class'])) {
			$record['extra']['class'] = $trace[$i]['class'];
		} else {
			$record['extra']['class'] = NULL;
		}
		if (isset($trace[$i]['function'])) {
			$record['extra']['function'] = $trace[$i]['function'];
		} else {
			$record['extra']['function'] = NULL;
		}

		return $record;
	}
}
