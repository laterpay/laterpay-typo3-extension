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
 * LaterPay core logger processor memory.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_processor_memory {

	/**
	 * Real usage falg
	 * @var boolean If true, get the real size of memory allocated from system. Else, only the memory used by emalloc() is
	 *      reported.
	 */
	protected $realUsage;

	/**
	 * Use Rormatting flag
	 * @var boolean If true, then format memory size to human readable string (MB, KB, B depending on size).
	 */
	protected $useFormatting;

	/**
	 * Constructor of object
	 *
	 * @param bool $realUsage Set this to true to get the real size of memory allocated from system
	 * @param bool $useFormatting If true, then format memory size to human readable string (MB, KB, B depending on size)
	 */
	public function __construct($realUsage = TRUE, $useFormatting = TRUE) {
		$this->realUsage = (boolean) $realUsage;
		$this->useFormatting = (boolean) $useFormatting;
	}

	/**
	 * Formats bytes into a human readable string if $this->useFormatting is true, otherwise return $bytes as is.
	 *
	 * @param int $bytes Bytes
	 *
	 * @return string|int Formatted string if $this->useFormatting is true, otherwise return $bytes as is
	 */
	protected function formatBytes($bytes) {
		$bytes = (int) $bytes;

		if (! $this->useFormatting) {
			return $bytes;
		}

		if ($bytes > 1024 * 1024) {
			return round($bytes / 1024 / 1024, 2) . ' MB';
		} elseif ($bytes > 1024) {
			return round($bytes / 1024, 2) . ' KB';
		}

		return $bytes . ' B';
	}
}
