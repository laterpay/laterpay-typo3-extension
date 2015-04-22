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
 * LaterPay price evaluator.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_evaluate_price {

	/**
	 * Return JS code value evaluation in admin part of content edit.
	 *
	 * @return string
	 */
	public function returnFieldJs() {
		return '
			if (value < ' . tx_laterpay_helper_pricing::PPU_MIN . ' && value != 0) {
				alert("Price must be equal to or greater than ' . tx_laterpay_helper_pricing::PPU_MIN . ' EUR");
				value = "' . tx_laterpay_helper_pricing::PPU_MIN . '";
			} else if (value > ' . tx_laterpay_helper_pricing::SIS_MAX . ') {
				alert("Price must be equal to or less than ' . tx_laterpay_helper_pricing::SIS_MAX . ' EUR");
				value = "' . tx_laterpay_helper_pricing::SIS_MAX . '";
			}

			return value;
		';
	}

	/**
	 * PHP evaluation of get value in project.
	 *
	 * @param string $value Value from form
	 * @param mixed $isIn Field configuration from TCA array.
	 * @param bool $set Set to database or not.
	 *
	 * @return float
	 */
	public function evaluateFieldValue($value, $isIn, &$set) {
		return sprintf('%.2f', round( tx_laterpay_helper_pricing::ensureValidPrice((float) $value), 2));
	}
}
