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
 * LaterPay content model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_field_checker {

	/**
	 * Hook which checking, if price and revenue model selected correctly, also create auto teaser if needed. If no, then rewrite incorrect values in database.
	 *
	 * @param string $status Is item new or only updated.
	 * @param string $table Table name data from which will be updated.
	 * @param int $contentId Identifyer (uid) of content row in database.
	 * @param mixed $fieldArray Array of changed fields.
	 * @param t3lib_TCEmain $caller Object from which this hook was called.
	 *
	 * @return void
	 */
	// @codingStandardsIgnoreStart
	public function processDatamap_postProcessFieldArray($status, $table, $contentId, &$fieldArray, t3lib_TCEmain $caller) {
	// @codingStandardsIgnoreEnd
		if ($table != tx_laterpay_model_content::$contentTable) {
			return;
		}
		// $fieldArray not empty only if something changed
		$searchArray = array_keys($fieldArray);

		$priceFieldName   = 'laterpay_price';
		$revenueFieldName = 'laterpay_revenue_model';
		$teaserFieldName  = 'laterpay_teaser';

		$content = tx_laterpay_model_content::getContentData($contentId);

		if (!$content) {
			$content = $fieldArray;
		}

		if (in_array($priceFieldName, $searchArray) or in_array($revenueFieldName, $searchArray)) {
			$revenueModel      = isset($fieldArray[$revenueFieldName]) ? $fieldArray[$revenueFieldName] : $content[$revenueFieldName];
			$price             = isset($fieldArray[$priceFieldName]) ? $fieldArray[$priceFieldName] : $content[$priceFieldName];
			$roundedPrice      = round($price, 2);
			$validRevenueModel = tx_laterpay_helper_pricing::ensureValidRevenueModel($revenueModel, $price);

			if ($validRevenueModel != $revenueModel or $price != $roundedPrice) {
				$fieldArray[$revenueFieldName] = $validRevenueModel;
				$fieldArray[$priceFieldName]   = $roundedPrice;
			}
		}
		$teaserStr = trim(strip_tags($fieldArray[$teaserFieldName]));
		if (empty($teaserStr)) {
			$fieldArray[$teaserFieldName] = tx_laterpay_helper_content::getTeaser($content['bodytext']);
		}
	}
}
