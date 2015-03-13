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
 * Abstract class for log handler
 */
abstract class tx_laterpay_core_logger_handler_abstract implements tx_laterpay_core_logger_handler_interface {

	/**
	 * Formatter
	 * @var FormatterInterface
	 */
	protected $formatter;

	/**
	 * Array of processors
	 * @var array Array of processors for record
	 */
	protected $processors = array();

	/**
	 * Logger level
	 *
	 * @see tx_laterpay_core_logger
	 *
	 * @var int Level of record to handle
	 */
	protected $level = tx_laterpay_core_logger::DEBUG;

	/**
	 * Constructor of object
	 *
	 * @param int $level Logger level
	 */
	public function __construct($level = tx_laterpay_core_logger::DEBUG) {
		$this->level = $level;
	}

	/**
	 * Hanlder for array of records
	 *
	 * @param array $records Description
	 *
	 * @return void
	 */
	public function handleBatch(array $records) {
		foreach ($records as $record) {
			$this->handle($record);
		}
	}

	/**
	 * Get formatted to string record
	 *
	 * @param array $record Log message
	 *
	 * @return str
	 */
	protected function getFormatted(array $record) {
		$output = "%datetime%:%pid%.%channel%.%level_name%: %message% %context%\n";
		foreach ($record as $var => $val) {
			$output = str_replace('%' . $var . '%', $this->convertToString($val), $output);
		}

		return $output;
	}

	/**
	 * Closes the handler.
	 * This will be called automatically when the object is destroyed.
	 *
	 * @return void
	 */
	public function close() {
	}

	/**
	 * Destructor of object
	 *
	 * @return void
	 */
	public function __destruct() {
		// @codingStandardsIgnoreStart
		try {
			$this->close();
		} catch (Exception $e) {
			//do nothing
		}
		//@codingStandardsIgnoreEnd
	}

	/**
	 * Convert data into string
	 *
	 * @param mixed $data Input data
	 *
	 * @return string
	 */
	protected function convertToString($data) {
		if ((NULL === $data) || (is_scalar($data))) {
			return (string) $data;
		}

		if (version_compare(PHP_VERSION, '5.4.0', '>=') && defined('JSON_UNESCAPED_SLASHES') && defined('JSON_UNESCAPED_UNICODE')) {
			return json_encode($this->normalize($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		return str_replace('\\/', '/', json_encode($this->normalize($data)));
	}

	/**
	 * Data normalization.
	 *
	 * @param mixed $data Input data
	 *
	 * @return mixed
	 */
	protected function normalize($data) {
		if (is_bool($data) || is_null($data)) {
			return var_export($data, TRUE);
		}

		if ((NULL === $data) || (is_scalar($data))) {
			return $data;
		}

		if (is_array($data) || $data instanceof Traversable) {
			$normalized = array();

			foreach ($data as $key => $value) {
				$normalized[$key] = $this->normalize($value);
			}

			return $normalized;
		}

		if ($data instanceof DateTime) {
			return $data->format('Y-m-d H:i:s.u');
		}

		if (is_object($data)) {
			return sprintf('[object] (%s: %s)', get_class($data), json_encode($data));
		}

		if (is_resource($data)) {
			return '[resource]';
		}

		return '[unknown(' . gettype($data) . ')]';
	}

	/**
	 * Is needed to handle or not.
	 *
	 * @param array $record Record data
	 *
	 * @return bool
	 */
	public function isHandling(array $record) {
		return $record['level'] >= $this->level;
	}

	/**
	 * Push processor to array of processors
	 *
	 * @param mixed $callback Callable new processor which must be added into processors list
	 *
	 * @return self
	 */
	public function pushProcessor($callback) {
		if (! is_callable($callback)) {
			throw new \InvalidArgumentException(
				'Processors must be valid callables (callback or object with an __invoke method), ' . var_export($callback, TRUE) .
				' given');
		}
		array_unshift($this->processors, $callback);

		return $this;
	}

	/**
	 * Remove first processor from stack
	 *
	 * @return callable first processor from stack
	 */
	public function popProcessor() {
		if (! $this->processors) {
			throw new \LogicException('You tried to pop from an empty processor stack.');
		}

		return array_shift($this->processors);
	}

	/**
	 * Set formatter
	 *
	 * @param tx_laterpay_core_logger_formatter_interface $formatter Formatter object
	 *
	 * @return self
	 */
	public function setFormatter(tx_laterpay_core_logger_formatter_interface $formatter) {
		$this->formatter = $formatter;
		return $this;
	}

	/**
	 * Get formatter object
	 *
	 * @return tx_laterpay_core_logger_formatter_interface current or default formatter
	 */
	public function getFormatter() {
		if (! $this->formatter) {
			$this->formatter = $this->getDefaultFormatter();
		}

		return $this->formatter;
	}

	/**
	 * Sets minimum logging level at which this handler will be triggered.
	 *
	 * @param int $level Log level
	 *
	 * @return self
	 */
	public function setLevel($level) {
		$this->level = $level;
		return $this;
	}

	/**
	 * Gets minimum logging level at which this handler will be triggered.
	 *
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Gets the default formatter.
	 *
	 * @return tx_laterpay_core_logger_formatter_interface
	 */
	protected function getDefaultFormatter() {
		return new tx_laterpay_core_logger_formatter_normalizer();
	}
}
