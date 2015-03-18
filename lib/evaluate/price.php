<?php
/*
 * LaterPay content model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_evaluate_price
{
	/**
	 * method which return JS code value evaluation in admin part of content edit
	 * 
	 * @return string
	 */
	function returnFieldJS() {
		return '
			if(value < '. tx_laterpay_helper_pricing::PPU_MIN . ' && value != 0)
			{
				alert("Price must be more or equal than ' . tx_laterpay_helper_pricing::PPU_MIN . ' EUR");
				value = "' . tx_laterpay_helper_pricing::PPU_MIN . '";
			}
			else if(value > ' . tx_laterpay_helper_pricing::SIS_MAX . ')
			{
				alert("Price must be lower or equal than ' . tx_laterpay_helper_pricing::SIS_MAX . ' EUR");
				value = "' . tx_laterpay_helper_pricing::SIS_MAX . '";
			}
			return value;
		';
	}
	
	/**
	 * php evaluation of getted value in project
	 * 
	 * @param string $value - value from form
	 * @param type $is_in
	 * @param boolean $set - set to database or not
	 * 
	 * @return float
	 */
	function evaluateFieldValue($value, $is_in, &$set) {
		return round( tx_laterpay_helper_pricing::ensureValidPrice((float) $value) ,2);
	}
}