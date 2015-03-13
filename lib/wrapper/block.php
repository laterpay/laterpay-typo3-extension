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
 * Block wrapper
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_wrapper_block extends tx_laterpay_wrapper_abstract {

	/**
	 * Wrapper template
	 *
	 * @var string path to wraper tempalte
	 */
	private $wrapTemplate = 'frontend/wrapper/block/wrap';

	/**
	 * After wrap action
	 *
	 * @return srting
	 */
	public function postWrap() {
		return '';
	}

	/**
	 * Wrap action
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function wrap($teaserContent) {
		$this->getRenderer()->assign('content', $teaserContent);
		return $this->getRenderer()->getTextView($this->wrapTemplate);
	}

	/**
	 * Pre wrap action
	 *
	 * @return string
	 */
	public function preWrap() {
		return '';
	}

	/**
	 * Before render
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @see tx_laterpay_wrapper_abstract->beforeRender
	 *
	 * @return string
	 */
	public function beforeRender($teaserContent) {
		foreach ($this->arguments as $varName => $varValue) {
			$this->getRenderer()->assign($varName, $varValue);
		}

		return $teaserContent;
	}
}
