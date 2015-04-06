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
 * LaterPay abstract form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
abstract class tx_laterpay_form_abstract {

	/**
	 * Form fields
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Validation errors
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Array of no strict names
	 *
	 * @var array
	 */
	protected $nostrict;

	/**
	 * Default filters set
	 *
	 * @var array
	 */
	public static $filters = array(
		// sanitize string value
		'text' => 'tx_laterpay_helper_string::sanitizeTextField',

		// convert to int, abs
		'to_int' => 'tx_laterpay_helper_string::toAbsInt',

		// convert to string
		'to_string' => 'strval',

		// convert to float
		'to_float' => 'floatval',

		// replace part of value with other
		// params:
		// type - replace type (str_replace, preg_replace)
		// search - searched value or pattern
		// replace - replacement
		'replace' => array(
			'tx_laterpay_form_abstract',
			'replace',
		),

		// format number with given decimal places
		'format_num' => 'number_format',

		// strip slashes
		'unslash' => 'tx_laterpay_helper_string::unslash'
	);

	/**
	 * Constructor of object.
	 *
	 * @param mixed $data Initial data
	 */
	public final function __construct($data = array()) {
		// call init method from child class
		$this->init();

		// set data to form, if specified
		if (! empty($data)) {
			$this->setData($data);
		}
	}

	/**
	 * Init form.
	 *
	 * @return void
	 */
	abstract protected function init();

	/**
	 * Set new field, options for its validation, and filter options
	 * (sanitizer).
	 *
	 * @param mixed $name Filed name
	 * @param mixed $options Options
	 *
	 * @return bool field was created or already exists
	 */
	public function setField($name, $options = array()) {
		$fields = $this->getFields();

		// check, if field already exists
		if (isset($fields[$name])) {
			return FALSE;
		} else {
			// field name
			$data = array();

			// validators
			$data['validators'] = isset($options['validators']) ? $options['validators'] : array();

			// filters (sanitize)
			$data['filters'] = isset($options['filters']) ? $options['filters'] : array();

			// default value
			$data['value'] = isset($options['default_value']) ? $options['default_value'] : NULL;

			// do not apply filters to null value
			$data['can_be_null'] = isset($options['can_be_null']) ? $options['can_be_null'] : FALSE;

			// name not strict, value searched in data by part of the name (for dynamic params)
			if (isset($options['not_strict_name']) && $options['not_strict_name']) {
				$this->setNostrict($name);
			}

			$this->saveFieldData($name, $data);
		}

		return TRUE;
	}

	/**
	 * Save data in field.
	 *
	 * @param mixed $name Name of field
	 * @param mixed $data Data
	 *
	 * @return void
	 */
	protected function saveFieldData($name, $data) {
		$this->fields[$name] = $data;
	}

	/**
	 * Get all fields.
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Get all filters.
	 *
	 * @return array
	 */
	protected function getFilters() {
		return self::$filters;
	}

	/**
	 * Get field value.
	 *
	 * @param mixed $fieldName Field name
	 *
	 * @return mixed
	 */
	public function getFieldValue($fieldName) {
		$fields = $this->getFields();

		if (isset($fields[$fieldName])) {
			return $fields[$fieldName]['value'];
		}

		return NULL;
	}

	/**
	 * Set value for field.
	 *
	 * @param mixed $fieldName Field name
	 * @param mixed $value Value
	 *
	 * @return void
	 */
	protected function setFieldValue($fieldName, $value) {
		$this->fields[$fieldName]['value'] = $value;
	}

	/**
	 * Add field name to nostrict array.
	 *
	 * @param mixed $name Name
	 *
	 * @return void
	 */
	protected function setNostrict($name) {
		if (! isset($this->nostrict)) {
			$this->nostrict = array();
		}

		array_push($this->nostrict, $name);
	}

	/**
	 * Check, if field value is null and can be null.
	 *
	 * @param mixed $field Field name
	 *
	 * @return bool
	 */
	protected function checkIfFieldCanBeNull($field) {
		$fields = $this->getFields();

		if ($fields[$field]['can_be_null']) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Add condition to the field validation.
	 *
	 * @param mixed $field Field name
	 * @param mixed $condition Conditional as array
	 *
	 * @return void
	 */
	public function addValidation($field, $condition = array()) {
		$fields = $this->getFields();

		if (isset($fields[$field])) {
			if (is_array($condition) && ! empty($condition)) {
				// condition should be correct
				array_push($fields[$field]['validators'], $condition);
			}
		}
	}

	/**
	 * Validate data in fields.
	 *
	 * @param mixed $data Data array
	 *
	 * @return bool is data valid
	 */
	public function isValid($data = array()) {
		$this->errors = array();
		// set data to the form, if any data was passed
		if (! empty($data)) {
			$this->setData($data);
		}

		$fields = $this->getFields();

		// validation logic
		if (is_array($fields)) {
			foreach ($fields as $name => $field) {
				$validators = $field['validators'];

				foreach ($validators as $validatorKey => $validatorValue) {
					$validatorOption = is_int($validatorKey) ? $validatorValue : $validatorKey;
					$validatorParams = is_int($validatorKey) ? NULL : $validatorValue;

					// continue loop, if field can be null and has null value
					if ($this->checkIfFieldCanBeNull($name) && $this->getFieldValue($name) === NULL) {
						continue;
					}

					$isValid = $this->validateValue($field['value'], $validatorOption, $validatorParams);
					if (! $isValid) {
						// data not valid
						$this->errors[] = array(
							'name' 		=> $name,
							'value' 	=> $field['value'],
							'validator' => $validatorOption,
							'options' 	=> $validatorParams,
						);
					}
				}
			}
		}

		return empty($this->errors);
	}

	/**
	 * Get validation errors.
	 *
	 * @return multitype:
	 */
	public function getErrors() {
		$aux 			= $this->errors;
		$this->errors 	= array();

		return $aux;
	}

	/**
	 * Apply filters to form data.
	 *
	 * @return void
	 */
	protected function sanitize() {
		$fields = $this->getFields();

		// get all form filters
		if (is_array($fields)) {
			foreach ($fields as $name => $field) {
				$filters = $field['filters'];

				foreach ($filters as $filterKey => $filterValue) {
					$filterOption = is_int($filterKey) ? $filterValue : $filterKey;
					$filterParams = is_int($filterKey) ? NULL : $filterValue;

					// continue loop, if field can be null and has null value
					if ($this->checkIfFieldCanBeNull($name) && $this->getFieldValue($name) === NULL) {
						continue;
					}

					$this->setFieldValue($name,
						$this->sanitizeValue($this->getFieldValue($name), $filterOption, $filterParams));
				}
			}
		}
	}

	/**
	 * Apply filter to the value.
	 *
	 * @param mixed $value Value
	 * @param mixed $filter Filter
	 * @param mixed $filterParams Filter params
	 *
	 * @return mixed
	 */
	public function sanitizeValue($value, $filter, $filterParams = NULL) {
		// get filters
		$filters = $this->getFilters();

		// sanitize value according to selected filter
		$sanitizer = isset($filters[$filter]) ? $filters[$filter] : '';

		if ($sanitizer && is_callable($sanitizer)) {
			if ($filterParams) {
				$value = call_user_func($sanitizer, $value, $filterParams);
			} else {
				$value = call_user_func($sanitizer, $value);
			}
		}

		return $value;
	}

	/**
	 * Call strReplace with array of options.
	 *
	 * @param mixed $value Value
	 * @param mixed $options Options
	 *
	 * @return mixed
	 */
	public static function replace($value, $options) {
		if (is_array($options) && isset($options['type']) && is_callable($options['type'])) {
			$value = $options['type']($options['search'], $options['replace'], $value);
		}

		return $value;
	}

	/**
	 * Validate value by selected validator and optionally by its value.
	 *
	 * @param mixed $value Value
	 * @param mixed $validator Validator
	 * @param mixed $validatorParams Validator params
	 *
	 * @return bool
	 */
	public function validateValue($value, $validator, $validatorParams = NULL) {
		$isValid = FALSE;

		switch ($validator) {
			// compare value with set
			case 'cmp':
				if ($validatorParams && is_array($validatorParams)) {
					// OR realization, all validators inside validators set used
					// like AND
					// if at least one set correct then validation passed
					foreach ($validatorParams as $validatorsSet) {
						foreach ($validatorsSet as $operator => $param) {
							$isValid = $this->compareValues($operator, $value, $param);

							// if comparison is not valid break the loop and go to the next validation set
							if (! $isValid) {
								break;
							}
						}

						// if comparison is valid after full validation, set check then do not need to check others
						if ($isValid) {
							break;
						}
					}
				}
				break;

			// check, if value is an int
			case 'is_int':
				$isValid = is_int($value);
				break;

			// check, if value is a string
			case 'is_string':
				$isValid = is_string($value);
				break;

			// check, if value is a float
			case 'is_float':
				$isValid = is_float($value);
				break;

			// check string length
			case 'strlen':
				if ($validatorParams && is_array($validatorParams)) {
					foreach ($validatorParams as $extraValidator => $validatorData) {
						// recursively call extra validator
						$isValid = $this->validateValue(strlen($value), $extraValidator, $validatorData);

						// break loop if something is not valid
						if (! $isValid) {
							break;
						}
					}
				}
				break;

			// check, if value is in array
			case 'in_array':
				if ($validatorParams && is_array($validatorParams)) {
					$isValid = in_array($value, $validatorParams);
				}
				break;

			// check, if value is an array
			case 'is_array':
				$isValid = is_array($value);
				break;

			case 'match':
				if ($validatorParams && ! is_array($validatorParams)) {
					$isValid = preg_match($validatorParams, $value);
				}
				break;

			case 'match_url':
				$isValid = preg_match_all(
					'/[-a-zA-Z0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)?/i', $value);
				break;

			case 'depends':
				if ($validatorParams && is_array($validatorParams)) {
					// get all dependencies
					foreach ($validatorParams as $dependency) {
						// if dependency matches
						if (! isset($dependency['value']) || $value === $dependency['value']) {
							// loop for dependencies conditions and check, if all of them are valid
							foreach ($dependency['conditions'] as $vkey => $vparams) {
								$extraValidator = is_int($vkey) ? $vparams : $vkey;
								$validatorData 	= is_int($vkey) ? NULL : $vparams;

								// recursively call extra validator
								$isValid = $this->validateValue($this->getFieldValue($dependency['field']), $extraValidator,
									$validatorData);

								// break loop if something is not valid
								if (! $isValid) {
									break;
								}
							}

							// dependency matched -> break process
							break;
						} else {
							$isValid = TRUE;
						}
					}
				}
				break;
			case 'post_exist':
				$post = t3lib_div::_POST($value);
				$isValid = $post !== NULL;
				break;
			default:
				// incorrect validator specified, do nothing
		}

		return $isValid;
	}

	/**
	 * Compare two values.
	 *
	 * @param mixed $comparisonOperator Comparation operator
	 * @param mixed $firstValue Left value
	 * @param mixed $secondValue Right value
	 *
	 * @return bool
	 */
	protected function compareValues($comparisonOperator, $firstValue, $secondValue) {
		$result = FALSE;

		switch ($comparisonOperator) {
			// equal ===
			case 'eq':
				$result = ($firstValue === $secondValue);
				break;

			// not equal !==
			case 'ne':
				$result = ($firstValue !== $secondValue);
				break;

			// greater than >
			case 'gt':
				$result = ($firstValue > $secondValue);
				break;

			// greater than or equal to >=
			case 'gte':
				$result = ($firstValue >= $secondValue);
				break;

			// less than <
			case 'lt':
				$result = ($firstValue < $secondValue);
				break;

			// less than or equal to <=
			case 'lte':
				$result = ($firstValue <= $secondValue);
				break;

			// search, if string is present in value
			case 'like':
				$result = (strpos($firstValue, $secondValue) !== FALSE);
				break;

			default:
				// incorrect comparison operator, do nothing
		}

		return $result;
	}

	/**
	 * Set data into fields and sanitize it.
	 *
	 * @param mixed $data Data
	 *
	 * @return $this
	 */
	public function setData($data) {
		$fields = $this->getFields();

		// set data and sanitize it
		if (is_array($data)) {
			foreach ($data as $name => $value) {
				// set only, if name field was created
				if (isset($fields[$name])) {
					$this->setFieldValue($name, $value);
					continue;
				} elseif (isset($this->nostrict) && is_array($this->nostrict)) {
					// if field name is not strict
					foreach ($this->nostrict as $fieldName) {
						if (strpos($name, $fieldName) !== FALSE) {
							$this->setFieldValue($fieldName, $value);
							break;
						}
					}
				}
			}

			// sanitize data, if filters were specified
			$this->sanitize();
		}

		return $this;
	}

	/**
	 * Get form values.
	 *
	 * @param bool $notNull Get only not null values
	 * @param string $prefix Get values with selected prefix
	 * @param mixed $exclude Array of names for exclude
	 *
	 * @return array
	 */
	public function getFormValues($notNull = FALSE, $prefix = NULL, $exclude = array()) {
		$fields = $this->getFields();
		$data = array();

		foreach ($fields as $name => $fieldData) {
			if ($notNull && ($fieldData['value'] === NULL)) {
				continue;
			}

			if ($prefix && (strpos($name, $prefix) === FALSE)) {
				continue;
			}

			if (is_array($exclude) && in_array($name, $exclude)) {
				continue;
			}

			$data[$name] = $fieldData['value'];
		}

		return $data;
	}
}
