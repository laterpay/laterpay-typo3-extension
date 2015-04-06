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
 * Abstract wrap class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
abstract class tx_laterpay_wrapper_abstract implements tx_laterpay_wrapper_interface {
	/**
	 * Renderer instance.
	 *
	 * @var tx_laterpay_controller_abstract
	 */
	private $renderer;

	/**
	 * Array of key => value store template variables or something like
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Instance constructor.
	 */
	public function __construct() {
		$render = new tx_laterpay_controller_abstract(NULL);
		$this->setRenderer($render);
	}

	/**
	 * Getter for render.
	 *
	 * @return tx_laterpay_controller_abstract or any other setted render
	 */
	public function getRenderer() {
		return $this->renderer;
	}

	/**
	 * Setter for render.
	 *
	 * @param object $renderer Any render.
	 *
	 * @return void
	 */
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * Set array of arguments into wrapper.
	 *
	 * @param mixed $arguments Array of key -> value
	 *
	 * @return void
	 */
	public function setWrapperArguments($arguments) {
		foreach ($arguments as $key => $value) {
			$this->setWrapperArgument($key, $value);
		}
	}

	/**
	 * Add single argument into wrapper.
	 *
	 * @param string $key Key
	 * @param string $value Value
	 *
	 * @return void
	 */
	public function setWrapperArgument($key, $value) {
		$this->arguments[$key] = $value;
	}

	/**
	 * Remove single argument from wrapper.
	 *
	 * @param string $key Key
	 *
	 * @return void
	 */
	public function removeWrapperArgument($key) {
		if (isset($this->arguments[$key])) {
			unset($this->arguments[$key]);
		}
	}

	/**
	 * Wrap teaserContent into needed view.
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function render($teaserContent) {
		$preparedTeaserContent = $this->beforeRender($teaserContent);

		$wrappedTeaser = $this->preWrap();
		$wrappedTeaser .= $this->wrap($preparedTeaserContent);
		$wrappedTeaser .= $this->postWrap();

		$finalTeaserContent = $this->afterRender($wrappedTeaser);

		$content = preg_replace('/[\n\r]+/', '', $finalTeaserContent);
		$content = preg_replace('/>\s+</', '><', $content);

		return $content;
	}

	/**
	 * Main wrap action - working with teaser data (any additional changes for teaser text).
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function wrap($teaserContent) {
		return $teaserContent;
	}

	/**
	 * JavaScript files setter.
	 *
	 * @return void
	 */
	public function setJs() {
		// Load any JS if needed
		// You can do it in a way like:
		// $GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_<somename>'] = <path to JS file>
	}

	/**
	 * CSS files setter.
	 *
	 * @return void
	 */
	public function setCss() {
		// Load any CSS if needed
		// You can do it in a way like:
		// $GLOBALS['TSFE']->getPageRenderer()->addCssFile(<path to CSS file>);
	}

	/**
	 * Pre render operation (example: setting wrapper arguments into render).
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function beforeRender($teaserContent) {
		return $teaserContent;
	}

	/**
	 * Post render operation.
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function afterRender($teaserContent) {
		return $teaserContent;
	}
}
