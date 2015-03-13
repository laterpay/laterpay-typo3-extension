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
 * LaterPay test mode form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_testmode extends tx_laterpay_form_abstract {

	/**
	 * Implementation of abstract method.
	 *
	 * @return void
	 */
	public function init() {
		$this->setField('form',
			array(
				'validators' => array(
					'is_string',
					'cmp' => array(
						array(
							'eq' => 'laterpay_test_mode'
						)
					)
				)
			));

		$this->setField('action',
			array(
				'validators' => array(
					'is_string',
					'cmp' => array(
						array(
							'eq' => 'laterpay_account'
						)
					)
				)
			));

		$this->setField('invalid_credentials',
			array(
				'validators' => array(
					'is_int',
					'in_array' => array(
						0,
						1
					)
				),
				'filters' => array(
					'to_int'
				)
			));

		/*
		 * $this->setField(
		 * '_wpnonce',
		 * array(
		 * 'validators' => array(
		 * 'is_string',
		 * 'cmp' => array(
		 * array(
		 * 'ne' => null,
		 * ),
		 * ),
		 * ),
		 * )
		 * );
		 */

		$this->setField('plugin_is_in_visible_test_mode',
			array(
				'validators' => array(
					'is_int',
					'in_array' => array(
						0,
						1
					)
				),
				'filters' => array(
					'to_int'
				)
			));
	}
}

