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
 * LaterPay abstract controller.
 */
class tx_laterpay_controller_abstract
{

	/**
	 * Template instance.
	 *
	 * @var template
	 */
	public $doc;
	/**
	 * Constructor of class
	 *
	 * @param mixed $doc Template object instance
	 */
	public function __construct( $doc ) {
		$this->doc = $doc;
		// assign the config to the views
		$this->config = tx_laterpay_config::getInstance();
		$this->logger = tx_laterpay_core_logger::getInstance();
		$this->assign( 'config', $this->config );

		$this->initialize();
	}

	/**
	 * Function which will be called on constructor and can be overwritten by child class.
	 *
	 * @return void
	 */
	protected function initialize() {
	}

	/**
	 * Load all assets on boot-up.
	 *
	 * @return void
	 */
	public function loadAssets() {
	}

	/**
	 * Render HTML file.
	 *
	 * @param string $file File to get HTML string
	 *
	 * @return void
	 */
	public function render ($file) {
		foreach ($this->variables as $key => $value) {
			${$key} = $value;
		}

		$viewFile = t3lib_extMgm::extPath('laterpay') . 'view/' . $file . '.phtml';
		if (! file_exists($viewFile)) {
			$msg = sprintf(tx_laterpay_helper_string::tr('%s : <code>%s</code> not found'),
					__METHOD__, __FILE__);

			$this->logger->error(__METHOD__ . ' - ' . $msg,
					array(
						'view_file' => $viewFile,
					));

			return;
		}

		$this->logger->info(__METHOD__ . ' - ' . $file, $this->variables);

		// @codingStandardsIgnoreStart
		ob_start();
		include ($viewFile);
		$out = ob_get_contents();
		ob_end_clean();
		// @codingStandardsIgnoreEnd

		return $out;
	}

	/**
	 * Assign variable for substitution in templates.
	 *
	 * @param string $variable Name variable to assign
	 * @param mixed $value Value variable for assign
	 *
	 * @return void
	 */
	public function assign ($variable, $value) {
		$this->variables[$variable] = $value;
	}

	/**
	 * Get HTML from file.
	 *
	 * @param string $file File to get HTML string
	 *
	 * @return string $html html output as string
	 */
	public function getTextView ($file) {
		foreach ($this->variables as $key => $value) {
			${$key} = $value;
		}

		$viewFile = $extPath = t3lib_extMgm::extPath('laterpay') . 'view/' . $file . '.phtml';
		if (!file_exists($viewFile)) {
			$msg = sprintf(tx_laterpay_helper_string::tr('%s : <code>%s</code> not found'),
					__METHOD__, $file);

			$this->logger->error(__METHOD__ . ' - ' . $msg,
					array(
						'view_file' => $viewFile,
					));

			return '';
		}

		$this->logger->info(__METHOD__ . ' - ' . $file, $this->variables);

		// @codingStandardsIgnoreStart
		ob_start();
		include ($viewFile);
		$html = ob_get_contents();
		ob_end_clean();
		//@codingStandardsIgnoreEnd

		return $html;
	}

	/**
	 * Render the navigation for the plugin backend.
	 *
	 * @param string $file File Name
	 *
	 * @return string $html
	 */
	public function getMenu($file = NULL) {
		return '';
	}

	/**
	 * Generate Javascript declaration of variable.
	 *
	 * @param mixed $objectName Name of variable
	 * @param mixed $l10nVals Array of fields for creating JS object
	 *
	 * @return void
	 */
	public function localizeScript($objectName, $l10nVals) {
		$this->doc->JScodeArray[$objectName] = tx_laterpay_helper_render::getLocalizeScript($objectName, $l10nVals);
	}
}
