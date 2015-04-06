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
 * LaterPay logger HTML formatter.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger_formatter_html extends tx_laterpay_core_logger_formatter_normalizer {

	/**
	 * Format a set of log records.
	 *
	 * @param array $records A set of records to format
	 *
	 * @return mixed The formatted set of records
	 */
	public function formatBatch(array $records) {
		$message = '';
		foreach ($records as $record) {
			$message .= $this->format($record);
		}

		return $message;
	}

	/**
	 * Format a log record.
	 *
	 * @param array $record A record to format
	 *
	 * @return mixed The formatted record
	 */
	public function format(array $record) {
		$output  = '<li class="lp_debugger-content">';
		$output .= '<table class="lp_js_debuggerContentTable lp_debugger-content__table lp_is-hidden">';
		// generate thead of log record
		$output .= $this->addHeadRow((string) $record['message'], $record['level']);

		// generate tbody of log record with details
		$output .= '<tbody class="lp_js_logEntryDetails lp_debugger-content__table-body" style="display:none;">';
		$output .= '<tr><td class="lp_debugger-content__table-td" colspan="2"><table class="lp_debugger-content__table">';

		if ($record['context']) {
			foreach ($record['context'] as $key => $value) {
				$output .= $this->addRow($key, $this->convertToString($value));
			}
		}

		if ($record['extra']) {
			foreach ($record['extra'] as $key => $value) {
				$output .= $this->addRow($key, $this->convertToString($value));
			}
		}

		$output .= '</td></tr></table>';
		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</li>';
		return $output;
	}

	/**
	 * Create the header row for a log record.
	 *
	 * @param string $message Log message
	 * @param int $level Log level
	 *
	 * @return string
	 */
	private function addHeadRow($message = '', $level) {
		$showDetailsLink = '<a href="#" class="lp_js_toggleLogDetails" data-icon="l">' . tx_laterpay_helper_string::tr('Details', 'laterpay') . '</a>';

		$html = '<thead class="lp_js_debuggerContentTableTitle lp_debugger-content__table-title">' . LF .
			'<tr>' . LF .
			'  <td class="lp_debugger-content__table-td"><span class="lp_debugger__log-level lp_debugger__log-level--' .
			$level . ' lp_vectorIcon"></span>' . $message . '</td>' . LF .
			'  <td class="lp_debugger-content__table-td">' . $showDetailsLink . '</td>' . LF .
			'</tr>' . LF .
			'</thead>' . LF;

		return $html;
	}

	/**
	 * Create an HTML table row.
	 *
	 * @param string $th Row header content
	 * @param string $td Row standard cell content
	 * @param bool $escapeTd False if td content must not be HTML escaped
	 *
	 * @return string
	 */
	private function addRow($th, $td = ' ', $escapeTd = TRUE) {
		$th = htmlspecialchars($th, ENT_NOQUOTES, 'UTF-8');

		if ($escapeTd) {
			$td = htmlspecialchars($td, ENT_NOQUOTES, 'UTF-8');
		}

		$html = '<tr>' . LF .
			'<th class="lp_debugger-content__table-th" title="' . $th . '">' . $th . '</th>' . LF .
			'  <td class="lp_debugger-content__table-td">' . $td . '</td>' . LF .
			'</tr>' . LF;
		return $html;
	}

	/**
	 * Convert data into string
	 *
	 * @param mixed $data Data
	 *
	 * @return sting
	 */
	protected function convertToString($data) {
		if ((NULL === $data) || (is_scalar($data))) {
			return (string) $data;
		}

		$data = $this->normalize($data);
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		return str_replace('\\/', '/', json_encode($data));
	}
}
