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
 * LaterPay global price class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_globalprice extends tx_laterpay_form_abstract {

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
							'eq' => 'global_price_form'
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

		$this->setField('laterpay_global_price_revenue_model',
			array(
				'validators' => array(
					'is_string',
					'in_array' => array(
						'ppu',
						'sis'
					),
					'depends' => array(
						array(
							'field' => 'laterpay_global_price',
							'value' => 'sis',
							'conditions' => array(
								'cmp' => array(
									array(
										'lte' => 149.99,
										'gte' => 1.49
									)
								)
							)
						),
						array(
							'field' => 'laterpay_global_price',
							'value' => 'ppu',
							'conditions' => array(
								'cmp' => array(
									array(
										'lte' => 5.00,
										'gte' => 0.05
									),
									array(
										'eq' => 0.00
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

		$this->setField('laterpay_global_price',
			array(
				'validators' => array(
					'is_float',

					// TODO: this is just a dirty hack to allow saving Single Sale prices
					'cmp' => array(
						array(
							'lte' => 149.99,
							'gte' => 0.05
						),
						array(
							'eq' => 0.00
						)
					)
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
	}
}
