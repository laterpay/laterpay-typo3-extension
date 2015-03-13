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
 * Teaser wrapper
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_wrapper_teaser extends tx_laterpay_wrapper_abstract {

	/**
	 * PreWrapper template
	 *
	 * @var string path to pre_wrap template
	 */
	private $preWrapTemplate = 'frontend/wrapper/teaser/pre_wrap';

	/**
	 * Wrapper template
	 *
	 * @var string path to wraper tempalte
	 */
	private $wrapTemplate = 'frontend/wrapper/teaser/wrap';

	/**
	 * PostWrapper template
	 *
	 * @var string path to post_wrap template
	 */
	private $postWrapTemplate = 'frontend/wrapper/teaser/post_wrap';

	/**
	 * After wrap action
	 *
	 * @return srting
	 */
	public function postWrap() {
		return $this->getRenderer()->getTextView($this->postWrapTemplate);
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
		return $this->getRenderer()->getTextView($this->preWrapTemplate);
	}

	/**
	 * Before render
	 *
	 * @param string $teaserContent Treaser content
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
