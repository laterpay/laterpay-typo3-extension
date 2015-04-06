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
 * LaterPay bulk price form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_bulkprice extends tx_laterpay_form_abstract {

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
							'like' => 'bulk_price_form'
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
							'eq' => 'laterpay_pricing'
						)
					)
				)
			));

		$this->setField('bulk_operation_id',
			array(
				'validators' => array(
					'is_int'
				),
				'filters' => array(
					'to_int'
				),
				'can_be_null' => TRUE
			));

		$this->setField('bulk_message',
			array(
				'validators' => array(
					'is_string'
				),
				'filters' => array(
					'to_string'
				),
				'can_be_null' => TRUE
			));

		$this->setField('bulk_action',
			array(
				'validators' => array(
					'in_array' => array(
						'set',
						'increase',
						'reduce',
						'free',
						'reset'
					),
					'depends' => array(
						array(
							'field' => 'bulk_price',
							'value' => 'set',
							'conditions' => array(
								'cmp' => array(
									array(
										'lte' => 149.99,
										'gte' => 0.05
									),
									array(
										'eq' => 0
									)
								)
							)
						)
					)
				),
				'filters' => array(
					'to_string'
				)
			));

		$this->setField('bulk_selector',
			array(
				'validators' => array(
					'in_array' => array(
						'all',
						'in_category'
					)
				),
				'filters' => array(
					'to_string'
				)
			));

		$this->setField('bulk_category',
			array(
				'validators' => array(
					'is_int'
				),
				'filters' => array(
					'to_int'
				),
				'can_be_null' => TRUE
			));

		$this->setField('bulk_category_with_price',
			array(
				'validators' => array(
					'is_int'
				),
				'filters' => array(
					'to_int'
				),
				'can_be_null' => TRUE
			));

		$this->setField('bulk_price',
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
				),
				'can_be_null' => TRUE
			));

		$this->setField('bulk_change_unit',
			array(
				'validators' => array(
					'is_string',
					'in_array' => array(
						tx_laterpay_config::getOption('laterpay_currency'),
						'percent'
					)
				),
				'filters' => array(
					'to_string'
				),
				'can_be_null' => TRUE
			));
	}
}
