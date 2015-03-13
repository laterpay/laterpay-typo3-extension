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
 * LaterPay API key form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_form_apikey extends tx_laterpay_form_abstract {

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
							'like' => 'api_key'
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

		$this->setField('api_key',
			array(
				'validators' => array(
					'is_string',
					'match' => '/[a-z0-9]{32}/'
				),
				'filters' => array(
					'to_string',
					'text'
				),
				'not_strict_name' => TRUE
			));
	}
}

