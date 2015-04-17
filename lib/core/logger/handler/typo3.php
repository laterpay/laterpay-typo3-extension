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
 * Do nothing with log data.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_handler_typo3 extends tx_laterpay_core_logger_handler_abstract {


	/**
	 * Template for debug output
	 *
	 * @var string
	 */
	const DEBUG_TABLE_TEMPLATE = '
		<div id="lp_js_debugger" class="lp_debugger lp_is-hidden">
			<header id="lp_js_toggleDebuggerVisibility" class="lp_debugger-header">
				<a href="#" class="lp_debugger__close-link lp_right" data-icon="l"></a>
				<div class="lp_debugger-header__text lp_right">%s</div>
				<h2 data-icon="a" class="lp_debugger-header__title">%s</h2>
			</header>

			<ul id="lp_js_debuggerTabs" class="lp_debugger-tabs lp_clearfix">
				<li class="lp_js_debuggerTabItem lp_is-selected lp_debugger-tabs__item">
					<a href="#" class="lp_debugger-tabs__link">%s<span class="lp_debugger-tabs__count">%s</span></a>
				</li>
				%s
			</ul>

			<ul class="lp_debugger-content-list">
				<li class="lp_js_debuggerContent lp_debugger-content">
					<ul class="lp_debugger-content-list">
						%s
					</ul>
				</li>
				%s
			</ul>
		</div>
	';


	/**
	 * Template for debug output for tabs
	 *
	 * @var string
	 */
	const DEBUG_TABS_TEMPLATE = '
					<li class="lp_js_debuggerTabItem lp_debugger-tabs__item">
						<a href="#" class="lp_debugger-tabs__link">%s</a>
					</li>
	';

	/**
	 * Template for debug output for content as table
	 *
	 * @var string
	 */
	const DEBUG_CONTENT_TABLE_TEMPLATE = '
					<li class="lp_js_debuggerContent lp_debugger-content lp_is-hidden">
						<table class="lp_debugger-content__table">
							%s
						</table>
					</li>
	';

	/**
	 * Template for debug output for content in table
	 *
	 * @var string
	 */
	const DEBUG_CONTENT_TEMPLATE = '
								<tr>
									<th class="lp_debugger-content__table-th">%s</th>
									<td class="lp_debugger-content__table-td">%s</td>
								</tr>

	';

	/**
	 * Internal buffer
	 *
	 * @var array
	 */
	protected $records = array();

	/**
	 * Config object
	 *
	 * @var tx_laterpay_config
	 */
	protected $config = NULL;

	/**
	 * Constructor of object
	 *
	 * @param int $level The minimum logging level at which this handler will be triggered
	 */
	public function __construct($level = tx_laterpay_core_logger::DEBUG) {
		parent::__construct($level, FALSE);
		$this->config = tx_laterpay_config::getInstance();
	}

	/**
	 * Handles a record.
	 *
	 * All records may be passed to this method, and the handler should discard
	 * those that it does not want to handle.
	 *
	 * The return value of this function controls the bubbling process of the handler stack.
	 * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
	 * calling further handlers in the stack with a given log record.
	 *
	 * @param array $record The record to handle
	 *
	 * @return bool
	 */
	public function handle(array $record) {
		$this->records[] = $record;

		return TRUE;
	}

	/**
	 * Get array of tabs
	 *
	 * @return array $tabs
	 */
	protected function getTabs() {
		// @codingStandardsIgnoreStart
		return array(
				array(
						'name'		=> tx_laterpay_helper_string::tr('Requests'),
						'content'	=> array_merge(t3lib_div::_GET(), t3lib_div::_POST()),
				),
				array(
						'name'		=> tx_laterpay_helper_string::tr('Session'),
						'content'	=> isset( $_SESSION ) ? $_SESSION : array(),
				),
				array(
						'name'		=> sprintf( tx_laterpay_helper_string::tr('Cookies<span class="lp_debugger-tabs__count">%s</span>'), count( $_COOKIE ) ),
						'content'	=> $_COOKIE,
				),
				array(
						'name'		=> tx_laterpay_helper_string::tr('System Config'),
						'content'	=> $this->getSystemInfo(),
				),
				array(
						'name'		=> tx_laterpay_helper_string::tr('Plugin Config'),
						'content'	=> $this->config->getAll(),
				),
		);
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Get system info as array
	 *
	 * @return array
	 */
	protected function getSystemInfo() {
		$systemInfo = array(
				'Typo3 version'		=> $TYPO_VERSION,
				'PHP version'		=> PHP_VERSION,
				'PHP memory limit'	=> ini_get( 'memory_limit' ),
				'PHP modules'		=> implode( ', ', get_loaded_extensions() ),
				'Web server info'	=> $_SERVER['SERVER_SOFTWARE'],
		);

		return $systemInfo;
	}

	/**
	 * Load all assets
	 *
	 * @param object $renderer Instance of t3lib_PageRenderer class
	 *
	 * @return void
	 */
	public function loadAssets($renderer) {
		$href = t3lib_extMgm::extRelPath('laterpay') . 'res/css/laterpay-debugger.css';
		error_log (__METHOD__ . var_export($href, TRUE) . PHP_EOL, 3, '/vagrant/main_form.log');

		if (strpos($href, '://') !== FALSE || substr($href, 0, 1) === '/') {
			$file = $href;
		} else {
			$file = $GLOBALS['BACK_PATH'] . $href;
		}

		$renderer->addCssFile($file, 'stylesheet', 'screen', 'lpdebugger');
		$renderer->addJsFile($GLOBALS['BACK_PATH'] . t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-debugger.js');
	}

	/**
	 * Flush all buffered records and return as string
	 *
	 * @return string
	 */
	public function flushRecords() {
		$tabsNamesAndContents = $this->getTabs();
		$tabs = '';
		$tabsContent = '';

		foreach ($tabsNamesAndContents  as $key => $tab ) {
			if ( empty( $tab['content'] ) ) {
				continue;
			}
			$tabs .= sprintf(self::DEBUG_TABS_TEMPLATE, tx_laterpay_helper_string::tr($tab['name']));

			$tabsInternal = '';
			foreach ( $tab['content'] as $key => $value  ) {
				$tabsInternal .= sprintf(self::DEBUG_CONTENT_TEMPLATE, $key, var_export($value, TRUE));
			}

			$tabsContent .= sprintf(self::DEBUG_CONTENT_TABLE_TEMPLATE, $tabsInternal);
		}

		$out = sprintf(
			self::DEBUG_TABLE_TEMPLATE,
			sprintf( tx_laterpay_helper_string::tr('%s Memory Usage'), number_format( memory_get_peak_usage() / pow( 1024, 2 ), 1 ) . ' MB' ),
			tx_laterpay_helper_string::tr('Debugger'),
			tx_laterpay_helper_string::tr('Messages'),
			count( $this->records ),
			$tabs,
			$this->getFormatter()->formatBatch( $this->records ),
			$tabsContent
		);

		return $out;
	}
}
