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
 * LaterPay content helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_content {

	/**
	 * Return teaser from some text.
	 *
	 * @param string $text Text of content
	 *
	 * @return string
	 */
	public static function getTeaser($text) {
		$textWithoutTags		= strip_tags($text);
		$preparedText			= preg_replace('/\s{2,}/', ' ', $textWithoutTags);

		$totalCountOfWords		= tx_laterpay_helper_string::determineNumberOfWords($preparedText);

		$percentage				= tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT);
		$min					= tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN);
		$max					= tx_laterpay_config::getOption(tx_laterpay_config::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX);

		$neededWordsCount		= floor($totalCountOfWords * $percentage / 100);

		if ($neededWordsCount < $min) {
			$neededWordsCount = $min;
		} elseif ($neededWordsCount > $max) {
			$neededWordsCount = $max;
		}

		return tx_laterpay_helper_string::limitWords($preparedText, $neededWordsCount);
	}
}
