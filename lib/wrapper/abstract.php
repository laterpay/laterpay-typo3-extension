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
	 * Renderer instance
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
	 * Instance constructor
	 */
	public function __construct() {
		$render = new tx_laterpay_controller_abstract(NULL);
		$this->setRenderer($render);
	}

	/**
	 * Getter for render
	 *
	 * @return tx_laterpay_controller_abstract or any other setted render
	 */
	public function getRenderer() {
		return $this->renderer;
	}

	/**
	 * Getter for render
	 *
	 * @param object $renderer Any render.
	 *
	 * @return void
	 */
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * Set array of arguments into wrapper
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
	 * Add single argument into wrapper
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
	 * Remove single argument from wrapper
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
	 * Wrap teaserContent into needed view
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

		// @TODO : find a way to avoid such action in Typo3
		$content = preg_replace('/[\n\r]+/', '', $finalTeaserContent);
		$content = preg_replace('/>\s+</', '><', $content);

		return $content;
	}

	/**
	 * Main wrap action - working with teaser data (any additonal changes for teaser text)
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function wrap($teaserContent) {
		return $teaserContent;
	}

	/**
	 * JavaScript files setter
	 *
	 * @TODO : investigate - is there any other ways
	 *
	 * @return void
	 */
	public function setJs() {
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_jquery'] = 'http://code.jquery.com/jquery-1.11.1.js';
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_jquery.']['external'] = 1;

		$config = tx_laterpay_config::getInstance();
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_yui'] = $config->getInstance()->get(tx_laterpay_config::LATERPAY_YUI_JS);
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay_yui.']['external'] = 1;

		$js = t3lib_extMgm::siteRelPath('laterpay') . 'res/js/laterpay-post-view.js';
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay'] = $js;
		$GLOBALS['TSFE']->pSetup['includeJS.']['laterpay.']['external'] = 1;
	}

	/**
	 * CSS files setter
	 *
	 * @TODO : investigate - is there any other ways
	 *
	 * @return void
	 */
	public function setCss() {
		$css = t3lib_extMgm::siteRelPath('laterpay') . 'res/css/laterpay-post-view.css';
		$GLOBALS['TSFE']->getPageRenderer()->addCssFile($css);
	}

	/**
	 * Pre render operation (example: setting wrapper arguments into render)
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function beforeRender($teaserContent) {
		return $teaserContent;
	}

	/**
	 * Post render operation
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function afterRender($teaserContent) {
		return $teaserContent;
	}
}
