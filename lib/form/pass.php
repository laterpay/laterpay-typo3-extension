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
 * LaterPay time pass form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_pass extends tx_laterpay_form_abstract {

	/**
	 * Implementation of abstract method.
	 *
	 * @return void
	 */
	public function init() {
		$this->setField('pass_id',
			array(
				'validators' => array(
					'is_int'
				),
				'filters' => array(
					'to_int',
					'unslash'
				)
			));

		$this->setField('duration',
			array(
				'validators' => array(
					'is_int'
				),
				'filters' => array(
					'to_int',
					'unslash'
				)
			));

		$this->setField('period',
			array(
				'validators' => array(
					'is_int',
					'in_array' => array_keys(tx_laterpay_helper_timepass::getPeriodOptions())
				),
				'filters' => array(
					'to_int',
					'unslash'
				),
				'can_be_null' => FALSE
			));

		$this->setField('access_to',
			array(
				'validators' => array(
					'is_int',
					'in_array' => array_keys(tx_laterpay_helper_timepass::getAccessOptions())
				),
				'filters' => array(
					'to_int',
					'unslash'
				),
				'can_be_null' => FALSE
			));

		/*
		 * $this->setField(
		 * 'access_category',
		 * array(
		 * 'validators' => array(
		 * 'is_int',
		 * ),
		 * 'filters' => array(
		 * 'to_int',
		 * 'unslash',
		 * )
		 * )
		 * );
		 */

		$this->setField('price',
			array(
				'validators' => array(
					'is_float'
				),
				'filters' => array(
					'replace' => array(
						'type' => 'str_replace',
						'search' => ',',
						'replace' => '.'
					),
					'format_num' => 2,
					'to_float'
				)
			));

		$this->setField('revenue_model',
			array(
				'validators' => array(
					'is_string',
					'in_array' => array(
						'sis'
					)
				),
				'filters' => array(
					'to_string'
				),
				'can_be_null' => TRUE
			));

		$this->setField('title',
			array(
				'validators' => array(
					'is_string'
				),
				'filters' => array(
					'to_string',
					'unslash'
				)
			));

		$this->setField('description',
			array(
				'validators' => array(
					'is_string'
				),
				'filters' => array(
					'to_string',
					'unslash'
				)
			));

		$this->setField('voucher',
			array(
				'validators' => array(
					'is_array'
				),
				'can_be_null' => TRUE
			));
	}
}
