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
 * Version compatibility checker
 */
class tx_laterpay_compatibility implements t3lib_Singleton {
	/**
	 * Get instance of class.
	 *
	 * @return tx_laterpay_compatibility
	 */
	public static function getInstance() {
		return t3lib_div::makeInstance('tx_laterpay_compatibility');
	}

	/**
	 * Create this object.
	 */
	public function __construct() {
	}

	/**
	 * Convert version string to number, for example eg '6.2.9' -> 006002009
	 *
	 * @param string $version Version number in format x.x.x
	 *
	 * @return int version number
	 */
	public function versionToInt($version) {
		$verParts = explode('.', $version);

		return (int) sprintf('%03d%03d%03d', $verParts[0], $verParts[1], $verParts[2]);
	}
}
