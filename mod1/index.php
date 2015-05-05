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

unset($MCONF);
require_once('conf.php');
require_once(PATH_typo3 . 'init.php');
if (tx_laterpay_compatibility::getInstance()->versionToInt(TYPO3_version) < 6002000) {
	require_once(PATH_typo3 . 'template.php');
}
if (!isset($MCONF)) {
	// @codingStandardsIgnoreStart
	require('conf.php');
	// @codingStandardsIgnoreEnd
}
	// DEFAULT initialization of a module [BEGIN]
$GLOBALS['LANG']->includeLLFile('EXT:laterpay/mod1/locallang.xml');

/**
 * Laterpaay controller for backend.
 */
class tx_laterpay_module1 extends t3lib_SCbase {
	protected $pageinfo;

	/**
	 * Initializes the module.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		$this->doc = t3lib_div::makeInstance('bigDoc');
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	public function menuConfig() {
		$this->MOD_MENU = array(
			'function' => array(
				'1' => tx_laterpay_helper_string::tr('Dashboard'),
				'2' => tx_laterpay_helper_string::tr('Pricing'),
				'3' => tx_laterpay_helper_string::tr('Appearance'),
				'4' => tx_laterpay_helper_string::tr('Account'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return void
	 */
	public function main() {
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($GLOBALS['BE_USER']->user['admin'] && !$this->id)) {
			$this->doc->backPath = $GLOBALS['BACK_PATH'];

			// load LaterPay-specific CSS
			$this->doc->addStyleSheet('laterpay-backend', t3lib_extMgm::extRelPath('laterpay') . 'res/css/laterpay-backend.css');
			$this->doc->addStyleSheet('fonts.googleapis.com', 'http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext');
			// load LaterPay-specific JS
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/vendor/jquery-1.11.2.min.js');
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-backend.js');

			$pageContent = $this->getModuleContent();

			// Draw the header.
			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode = '
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']) . '<br />' .
				$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.path') .
				': ' . t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], -50);

			$this->content .= $this->doc->startPage(tx_laterpay_helper_string::tr('title'));
			$this->content .= $this->doc->header(tx_laterpay_helper_string::tr('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section(
					'', $this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]',
					$this->MOD_SETTINGS['function'], $this->MOD_MENU['function']))
			);
			$this->content .= $this->doc->divider(5);
				// Render content:
			$this->content .= $pageContent;
				// Shortcut
			if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
				$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}

			$this->content .= $this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc->backPath = $GLOBALS['BACK_PATH'];

			$this->content .= $this->doc->startPage(tx_laterpay_helper_string::tr('title'));
			$this->content .= $this->doc->header(tx_laterpay_helper_string::tr('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML.
	 *
	 * @return void
	 */
	public function printContent() {
		//$this->doc->addStyleSheet();
		$this->content .= $this->doc->endPage();
		echo $this->doc->insertStylesAndJS($this->content);
	}

	/**
	 * Generates the module content.
	 *
	 * @return string
	 */
	protected function getModuleContent() {
		$content = '';
		switch ((string)$this->MOD_SETTINGS['function']) {
			case 1:
				$tController = new tx_laterpay_controller_admin_dashboard( $this->doc);
				$content = $this->doc->section(tx_laterpay_helper_string::tr('Dashboard') . ':', $tController->renderPage(), 0, 1);
				break;
			case 2:
				$tController = new tx_laterpay_controller_admin_pricing( $this->doc );
				$content = $this->doc->section(tx_laterpay_helper_string::tr('Pricing') . ':', $tController->renderPage(), 0, 1);
				break;
			case 3:
				$tController = new tx_laterpay_controller_admin_appearance( $this->doc);
				$content = $this->doc->section(tx_laterpay_helper_string::tr('Appearance') . ':', $tController->renderPage(), 0, 1);
				break;
			case 4:
				$tController = new tx_laterpay_controller_admin_account( $this->doc );
				$content = $this->doc->section(tx_laterpay_helper_string::tr('Account') . ':', $tController->renderPage(), 0, 1);
				break;
			default:
				throw new Exception('Bad input data');
		}
		return $content;
	}

	/**
	 * Process ajax request for account tab
	 *
	 * @param mixed $params Input params
	 * @param mixed $ajaxObj Typo3Ajax instance
	 *
	 * @return void
	 */
	public function accountProcessAjaxRequests($params, &$ajaxObj) {
		$ajaxObj->setContentFormat('json');
		$tController = new tx_laterpay_controller_admin_account( $this->doc );
		$tController->processAjaxRequests($params, $ajaxObj);
	}

	/**
	 * Process ajax request for pricing tab
	 *
	 * @param mixed $params Input params
	 * @param mixed $ajaxObj Typo3Ajax instance
	 *
	 * @return void
	 */
	public function pricingProcessAjaxRequests($params, &$ajaxObj) {
		$ajaxObj->setContentFormat('json');
		$tController = new tx_laterpay_controller_admin_pricing( $this->doc );
		$tController->processAjaxRequests($params, $ajaxObj);
	}

	/**
	 * Process ajax request for appearance tab
	 *
	 * @param mixed $params Input params
	 * @param mixed $ajaxObj Typo3Ajax instance
	 *
	 * @return void
	 */
	public function appearanceProcessAjaxRequests($params, &$ajaxObj) {
		$ajaxObj->setContentFormat('json');
		$tController = new tx_laterpay_controller_admin_appearance( $this->doc );
		$tController->processAjaxRequests($params, $ajaxObj);
	}

	/**
	 * Process ajax request for dashboar tab
	 *
	 * @param mixed $params Input params
	 * @param mixed $ajaxObj Typo3Ajax instance
	 *
	 * @return void
	 */
	public function dashboardProcessAjaxRequests($params, &$ajaxObj) {
		$ajaxObj->setContentFormat('json');
		$tController = new tx_laterpay_controller_admin_dashboard( $this->doc );
		$tController->processAjaxRequests($params, $ajaxObj);
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/laterpay/mod1/index.php'])) {
	// @codingStandardsIgnoreStart
	include($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/laterpay/mod1/index.php']);
	// @codingStandardsIgnoreEnd
}


if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_AJAX)) {
	define(LP_RENDER, 1);
	// Make instance:
	// @var $SOBE tx_laterpay_module1
	$SOBE = t3lib_div::makeInstance('tx_laterpay_module1');
	$SOBE->init();

		// Include files?
	foreach ($SOBE->include_once as $incFile) {
		// @codingStandardsIgnoreStart
		include($incFile);
		// @codingStandardsIgnoreEnd
	}

	$SOBE->main();
	$SOBE->printContent();
}
