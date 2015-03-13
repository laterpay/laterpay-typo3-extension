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
 * LaterPay logger formatter normalizer.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_formatter_normalizer implements tx_laterpay_core_logger_formatter_interface {

	/**
	 * Simple data format
	 *
	 * @const string default date format
	 */
	const SIMPLE_DATE = 'H:i:s j.m.Y';

	/**
	 * Current date format
	 *
	 * @var string date format
	 */
	protected $dateFormat;

	/**
	 * Constructor of object
	 *
	 * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
	 */
	public function __construct($dateFormat = NULL) {
		$this->dateFormat = ($dateFormat === NULL) ? self::SIMPLE_DATE : $dateFormat;
	}

	/**
	 * Equile to normalize method
	 *
	 * @param array $record Record data
	 *
	 * @return array
	 */
	public function format(array $record) {
		return $this->normalize($record);
	}

	/**
	 * Format all data in input array
	 *
	 * @param array $records Array of records data to normalize
	 *
	 * @return array
	 */
	public function formatBatch(array $records) {
		foreach ($records as $key => $record) {
			$records[$key] = $this->format($record);
		}

		return $records;
	}

	/**
	 * Transform record into normalized form.
	 *
	 * @param mixed $data Incoming variable for normalizing
	 *
	 * @return string
	 */
	protected function normalize($data) {
		if ((NULL === $data) || (is_scalar($data))) {
			return $data;
		}

		if (is_array($data) || $data instanceof \Traversable) {
			$normalized = array();

			$count = 1;
			foreach ($data as $key => $value) {
				if ($count ++ >= 1000) {
					$normalized['...'] = 'Over 1000 items, aborting normalization';
					break;
				}
				$normalized[$key] = $this->normalize($value);
			}

			return $normalized;
		}

		if ($data instanceof \DateTime) {
			return $data->format($this->dateFormat);
		}

		if (is_object($data)) {
			if ($data instanceof Exception) {
				return $this->normalizeException($data);
			}

			return sprintf('[object] (%s: %s)', get_class($data), $this->toJson($data, TRUE));
		}

		if (is_resource($data)) {
			return '[resource]';
		}

		return '[unknown(' . gettype($data) . ')]';
	}

	/**
	 * Special method for normalizing exception.
	 *
	 * @param Exception $e Exception object
	 *
	 * @return string
	 */
	protected function normalizeException(Exception $e) {
		$data = array(
			'class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile() . ':' . $e->getLine()
		);

		$trace = $e->getTrace();
		foreach ($trace as $frame) {
			if (isset($frame['file'])) {
				$data['trace'][] = $frame['file'] . ':' . $frame['line'];
			} else {
				$data['trace'][] = json_encode($frame);
			}
		}

		$previous = $e->getPrevious();
		if ($previous) {
			$data['previous'] = $this->normalizeException($previous);
		}

		return $data;
	}

	/**
	 * Convert variable into JSON.
	 *
	 * @param mixed $data Input data array
	 * @param bool $ignoreErrors Ignore errors or not
	 *
	 * @return string
	 */
	protected function toJson($data, $ignoreErrors = FALSE) {
		// suppress jsonEncode errors since it's twitchy with some inputs
		if ($ignoreErrors) {
			if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
				return @json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			}

			return @json_encode($data);
		}

		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		return json_encode($data);
	}
}
