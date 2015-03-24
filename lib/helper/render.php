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
 * LaterPay date render.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_render {

	/**
	 * Generate Javascript declaration of variable
	 *
	 * @param mixed $objectName Name of variable
	 * @param mixed $l10nVals Array of fields for creating JS object
	 *
	 * @return string
	 */
	public static function getLocalizeScript($objectName, $l10nVals) {
		foreach ((array)$l10nVals as $key => $value ) {
			if ( !is_scalar($value) ) {
				continue;
			}
			$l10nVals[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
		}

		$afterScript = $l10nVals['l10n_print_after'];
		if (isset($afterScript)) {
			unset($l10nVals['l10n_print_after']);
		}
		$script = 'var ' . $objectName . ' = ' . json_encode( $l10nVals ) . ';' . LF;
		$script .= $afterScript . LF;

		return $script;
	}
}
