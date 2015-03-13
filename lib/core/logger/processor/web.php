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
 * LaterPay core logger processor web.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_processor_web implements tx_laterpay_core_logger_processor_interface {

	/**
	 * Server data array
	 * @var array|\ArrayAccess
	 */
	protected $serverData;

	/**
	 * Extra fileds
	 *
	 * @var array
	 */
	protected $extraFields = array(
		'url' => 'REQUEST_URI',
		'ip' => 'REMOTE_ADDR',
		'http_method' => 'REQUEST_METHOD',
		'server' => 'SERVER_NAME',
		'referrer' => 'HTTP_REFERER'
	);

	/**
	 * Constructor of object
	 *
	 * @param array|\ArrayAccess $serverData Array or object w/ ArrayAccess that provides access to the $_SERVER data
	 * @param array|null $extraFields Extra field names to be added (all available by default)
	 */
	public function __construct($serverData = NULL, array $extraFields = NULL) {
		if ($serverData === NULL) {
			$this->serverData = & $_SERVER;
		} elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
			$this->serverData = $serverData;
		} else {
			throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
		}

		if ($extraFields !== NULL) {
			foreach (array_keys($this->extraFields) as $fieldName) {
				if (! in_array($fieldName, $extraFields)) {
					unset($this->extraFields[$fieldName]);
				}
			}
		}
	}

	/**
	 * Record processor
	 *
	 * @param array $record Record data
	 *
	 * @return array processed record
	 */
	public function process(array $record) {
		// skip processing if for some reason request data is not present (CLI or wonky SAPIs)
		if (! isset($this->serverData['REQUEST_URI'])) {
			return $record;
		}

		$record['extra'] = $this->appendExtraFields($record['extra']);

		return $record;
	}

	/**
	 * Add extra filed
	 *
	 * @param string $extraName Name of field
	 * @param string $serverName Server name
	 *
	 * @return $this
	 */
	public function addExtraField($extraName, $serverName) {
		$this->extraFields[$extraName] = $serverName;

		return $this;
	}

	/**
	 * Append extra field
	 *
	 * @param array $extra Name of extra filed
	 *
	 * @return array
	 */
	private function appendExtraFields(array $extra) {
		foreach ($this->extraFields as $extraName => $serverName) {
			$extra[$extraName] = isset($this->serverData[$serverName]) ? $this->serverData[$serverName] : NULL;
		}

		if (isset($this->serverData['UNIQUE_ID'])) {
			$extra['unique_id'] = $this->serverData['UNIQUE_ID'];
		}

		return $extra;
	}
}
