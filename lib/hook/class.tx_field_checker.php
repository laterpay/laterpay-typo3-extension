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
	 * Hook which checking, if price and revenue model selected correctly. If no, then rewrite incorrect values in database.
	 * 
	 * @param string $status Is item new or only updated.
	 * @param string $table Table name data from which will be updated.
	 * @param int $contentId Identifyer (uid) of content row in database.
	 * @param mixed $fieldArray Array of changed fields.
	 * @param t3lib_TCEmain $caller Object from which this hook was called.
	 * 
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $contentId, $fieldArray, $caller) {
		if($table != tx_laterpay_model_content::$contentTable)
		{
			return ;
		}
		// $fieldArray not empty only if something changed
		$searchArray = array_keys($fieldArray);

		$priceFieldName = 'laterpay_price';
		$revenueFieldName = 'laterpay_revenue_model';

		if(in_array($priceFieldName,$searchArray) or in_array($revenueFieldName, $searchArray))
		{
			$insertArray = array();

			$content = tx_laterpay_model_content::getContentData($contentId);
			$revenueModel = isset($fieldArray[$revenueFieldName]) ? $fieldArray[$revenueFieldName] : $content[$revenueFieldName];
			$price        = isset($fieldArray[$priceFieldName]) ? $fieldArray[$priceFieldName] : $content[$priceFieldName];

			$roundedPrice = round($price, 2);

			$validRevenueModel = tx_laterpay_helper_pricing::ensureValidRevenueModel($revenueModel,$price);

			if($validRevenueModel != $revenueModel or $price != $roundedPrice)
			{
				$insertArray[$revenueFieldName] = $validRevenueModel;
				$insertArray[$priceFieldName]   = $roundedPrice;

				tx_laterpay_model_content::updateContentData($contentId, $insertArray);
			}
		}
	}
}