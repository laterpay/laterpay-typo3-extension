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
 * LaterPay currency model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_currency {

	/**
	 * Contains all currencies.
	 *
	 * @var array
	 */
	protected $currencies = array(
		array(
			'id' => 2,
			'short_name' => 'EUR',
			'full_name' => 'Euro'
		)
	);

	/**
	 * Constructor for class LaterPay_Currency_Model.
	 */
	public function __construct() {
	}

	/**
	 * Get currencies.
	 *
	 * @return array currencies
	 */
	public function getCurrencies() {
		return $this->currencies;
	}

	/**
	 * Get short name by currencyId.
	 *
	 * @param int $currencyId Currency id
	 *
	 * @return string $shortName
	 */
	public function getShortNameByCurrencyId($currencyId) {
		$shortName = NULL;

		foreach ($this->currencies as $currency) {
			if ((int) $currency['id'] === (int) $currencyId) {
				$shortName = $currency['short_name'];
				break;
			}
		}

		return $shortName;
	}

	/**
	 * Get currency id by ISO 4217 currency code.
	 *
	 * @param string $name ISO 4217 currency code
	 *
	 * @return int|null $currencyId
	 */
	public function getCurrencyIdByIso4217Code($name) {
		$currencyId = NULL;

		foreach ($this->currencies as $currency) {
			if ($currency['short_name'] === $name) {
				$currencyId = $currency['id'];
				break;
			}
		}

		return $currencyId;
	}

	/**
	 * Get full name of currency by ISO 4217 currency code.
	 *
	 * @param string $name ISO 4217 currency code
	 *
	 * @return string $fullName
	 */
	public function getCurrencyNameByIso4217Code($name) {
		$fullName = '';

		foreach ($this->currencies as $currency) {
			if ($currency['short_name'] === $name) {
				$fullName = $currency['full_name'];
				break;
			}
		}

		return $fullName;
	}
}
