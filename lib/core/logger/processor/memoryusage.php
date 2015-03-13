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
 * Injects memory_get_usage in all records
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_processor_memoryusage extends tx_laterpay_core_logger_processor_memory implements
	tx_laterpay_core_logger_processor_interface {

	/**
	 * Record processor
	 *
	 * @param array $record Record data
	 *
	 * @return array processed record
	 */
	public function process(array $record) {
		$bytes = memory_get_usage($this->realUsage);
		$formatted = $this->formatBytes($bytes);

		$record['extra'] = array_merge($record['extra'], array(
			'memory_usage' => $formatted
		));

		return $record;
	}
}
