<?php
/*
 * LaterPay content model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_field_checker {

	/**
	 * Hook which checking, if price and revenue model selected correctly. If no, then rewrite incorrect values in database;
	 * 
	 * @param string $status - new or update
	 * @param string $table - table name
	 * @param int $contentId - uid of content row in database
	 * @param array $fieldArray - array of changed fields
	 * @param t3lib_TCEmain $caller - object from which this hook was called ()
	 * 
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $contentId, $fieldArray, $caller)
	{
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
			
			// round price
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

