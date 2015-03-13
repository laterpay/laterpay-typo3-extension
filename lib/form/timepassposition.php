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
 * LaterPay time passes position form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_timepassposition extends tx_laterpay_form_abstract {

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
							'eq' => 'time_passes_position'
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
							'eq' => 'laterpay_appearance'
						)
					)
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
		 * )
		 * )
		 * );
		 */

		$this->setField('time_passes_positioned_manually',
			array(
				'validators' => array(
					'is_int',
					'in_array' => array(
						1
					)
				),
				'filters' => array(
					'to_int'
				),
				'can_be_null' => TRUE
			));
	}
}
