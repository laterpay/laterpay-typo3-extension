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
 * LaterPay pricing helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_pricing {

	/**
	 * Types of prices.
	 */
	const TYPE_GLOBAL_DEFAULT_PRICE 	= 'global default price';
	const TYPE_CATEGORY_DEFAULT_PRICE 	= 'category default price';
	const TYPE_INDIVIDUAL_PRICE 		= 'individual price';
	const TYPE_INDIVIDUAL_DYNAMIC_PRICE = 'individual price, dynamic';

	/**
	 * Status of published post
	 * @const string Status of post at time of publication.
	 */
	const STATUS_POST_PUBLISHED = 'publish';

	/**
	 * Price ranges.
	 */
	const PPU_MIN 			= 0.05;
	const PPU_MAX 			= 1.48;
	const PPUSIS_MAX 		= 5.00;
	const SIS_MIN 			= 1.49;
	const SIS_MAX 			= 149.99;

	const PRICE_PPU_END 	= 0.05;
	const PRICE_PPUSIS_END 	= 1.49;
	const PRICE_SIS_END 	= 5.01;

	const PRICE_START_DAY 	= 13;
	const PRICE_END_DAY 	= 18;

	const META_KEY 			= 'laterpay_post_prices';

	/**
	 * Get array of price ranges by revenue model (Pay-per-Use or Single Sale).
	 *
	 * @return array
	 */
	public static function getPriceRangesByRevenueModel() {
		return array(
			'ppu_min' 			=> self::PPU_MIN,
			'ppu_max' 			=> self::PPU_MAX,
			'ppusis_max' 		=> self::PPUSIS_MAX,
			'sis_min' 			=> self::SIS_MIN,
			'sis_max' 			=> self::SIS_MAX,
			'price_ppu_end' 	=> self::PRICE_PPU_END,
			'price_ppusis_end' 	=> self::PRICE_PPUSIS_END,
			'price_sis_end' 	=> self::PRICE_SIS_END,
			'price_start_day' 	=> self::PRICE_START_DAY,
			'price_end_day' 	=> self::PRICE_END_DAY,
		);
	}

	/**
	 * Return all posts that have a price applied.
	 *
	 * @return array
	 */
	public static function getAllPostsWithPrice() {
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', tx_laterpay_model_content::$contentTable, 'laterpay_price > 0');

		$this->logger->info(__METHOD__,
			array(
				'args' 		=> NULL,
				'query' 	=> $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
				'results' 	=> $result,
			)
		);

		return $result;
	}

	/**
	 * Calculate transitional price between start price and end price based on linear equation.
	 *
	 * @param mixed $postPrice Array of Postmeta see 'laterpayPostPrices'
	 * @param int $daysSincePublication Count of days after publication
	 *
	 * @return float
	 */
	private static function calculateTransitionalPrice($postPrice, $daysSincePublication) {
		$endPrice 		= $postPrice['end_price'];
		$startPrice 	= $postPrice['start_price'];
		$daysUntilEnd 	= $postPrice['transitional_period_end_after_days'];
		$daysUntilStart = $postPrice['change_start_price_after_days'];

		$coefficient 	= ($endPrice - $startPrice) / ($daysUntilEnd - $daysUntilStart);

		return $startPrice + ($daysSincePublication - $daysUntilStart) * $coefficient;
	}

	/**
	 * Get revenue model of content block price (Pay-per-Use or Single Sale).
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return string $revenueModel
	 */
	public static function getContentRevenueModel(tslib_cObj $contentObject) {
		$price 				= self::getContentPrice($contentObject);
		$revenueModel 		= isset($contentObject->data['laterpay_revenue_model']) ? $contentObject->data['laterpay_revenue_model'] : 'p2p';

		$ensuredRevenueModel = self::ensureValidRevenueModel($revenueModel, $price);

		return $ensuredRevenueModel;
	}

	/**
	 * Return the revenue model of the post.
	 * Validates and - if required - corrects the given combination of price and revenue model.
	 *
	 * @param string $revenueModel Model name
	 * @param float $price Price
	 *
	 * @return string $revenueModel
	 */
	public static function ensureValidRevenueModel($revenueModel, $price) {
		if ($revenueModel == 'ppu') {
			if ($price == 0.00 || ($price >= self::PPU_MIN && $price <= self::PPUSIS_MAX)) {
				return 'ppu';
			} else {
				return 'sis';
			}
		} else {
			if ($price >= self::SIS_MIN && $price <= self::SIS_MAX) {
				return 'sis';
			} else {
				return 'ppu';
			}
		}
	}

	/**
	 * Get content block price
	 *
	 * @param tslib_cObj $contentObject Conetnt object
	 *
	 * @return float $price
	 */
	public static function getContentPrice(tslib_cObj $contentObject) {
		$price = isset($contentObject->data['laterpay_price']) ? $contentObject->data['laterpay_price'] : 0;

		return (float) $price;
	}

	/**
	 * Return adjusted prices.
	 *
	 * @param float $start Start time
	 * @param float $end End time
	 *
	 * @return array
	 */
	public static function adjustDynamicPricePoints($start, $end) {
		$priceRangeType = 'ppu';

		if ($start >= self::PRICE_SIS_END || $end >= self::PRICE_SIS_END) {
			$priceRangeType = 'sis';
			if ($start != 0 && $start < self::PRICE_SIS_END) {
				$start = self::PRICE_SIS_END;
			}
			if ($end != 0 && $end < self::PRICE_SIS_END) {
				$end = self::PRICE_SIS_END;
			}
		} elseif (($start >= self::SIS_MIN && $start <= self::PPUSIS_MAX) || ($end >= self::SIS_MIN && $end <= self::PPUSIS_MAX)) {
			$priceRangeType = 'ppusis';
			if ($start != 0) {
				if ($start < self::SIS_MIN) {
					$start = self::SIS_MIN;
				}
				if ($start > self::PPUSIS_MAX) {
					$start = self::PPUSIS_MAX;
				}
			}

			if ($end != 0) {
				if ($end < self::SIS_MIN) {
					$end = self::SIS_MIN;
				}
				if ($end > self::PPUSIS_MAX) {
					$end = self::PPUSIS_MAX;
				}
			}
		} else {
			if ($start != 0) {
				if ($start < self::PPU_MIN) {
					$start = self::PPU_MIN;
				}
				if ($start > self::PPU_MAX) {
					$start = self::PPU_MAX;
				}
			}

			if ($end != 0) {
				if ($end < self::PPU_MIN) {
					$end = self::PPU_MIN;
				}
				if ($end > self::PPU_MAX) {
					$end = self::PPU_MAX;
				}
			}
		}

		return array(
			$start,
			$end,
			$priceRangeType
		);
	}

	/**
	 * Assign a valid amount to the price, if it is outside of the allowed range.
	 *
	 * @param float $price Price
	 *
	 * @return float
	 */
	public static function ensureValidPrice($price) {
		$validatedPrice = 0;

		// set all prices between 0.01 and 0.04 to lowest possible price of 0.05
		if ($price > 0 && $price < self::PPU_MIN) {
			$validatedPrice = self::PPU_MIN;
		}

		if ($price == 0 || ($price >= self::PPU_MIN && $price <= self::SIS_MAX)) {
			$validatedPrice = $price;
		}

		// set all prices greater 149.99 to highest possible price of 149.99
		if ($price > self::SIS_MAX) {
			$validatedPrice = self::SIS_MAX;
		}

		return $validatedPrice;
	}

	/**
	 * Get all bulk operations.
	 *
	 * @return mixed|null
	 */
	public static function getBulkOperations() {
		$operations = tx_laterpay_config::getOption('laterpay_bulk_operations');

		return $operations ? unserialize($operations) : NULL;
	}

	/**
	 * Get bulk operation data by id.
	 *
	 * @param int $id Id
	 *
	 * @return mixed|null
	 */
	public static function getBulkOperationDataById($id) {
		$operations = self::getBulkOperations();
		$data = NULL;

		if ($operations && isset($operations[$id])) {
			$data = $operations[$id]['data'];
		}

		return $data;
	}

	/**
	 * Save bulk operation.
	 *
	 * @param string $data Serialized bulk data
	 * @param string $message Message
	 *
	 * @return int $id id of new operation
	 */
	public static function saveBulkOperation($data, $message) {
		$operations = self::getBulkOperations();
		$operations = $operations ? $operations : array();

		// save bulk operation
		$operations[] = array(
			'data' 		=> $data,
			'message' 	=> $message,
		);
		tx_laterpay_config::updateOption('laterpay_bulk_operations', serialize($operations));

		end($operations);

		return key($operations);
	}

	/**
	 * Delete bulk operation by id.
	 *
	 * @param int $id Id
	 *
	 * @return bool
	 */
	public static function deleteBulkOperationById($id) {
		$wasDeleted = FALSE;
		$operations = self::getBulkOperations();

		if ($operations) {
			if (isset($operations[$id])) {
				unset($operations[$id]);
				$wasDeleted = TRUE;
				$operations = $operations ? $operations : '';
				tx_laterpay_config::updateOption('laterpay_bulk_operations', serialize($operations));
			}
		}

		return $wasDeleted;
	}

	/**
	 * Return the URL hash for a given URL.
	 *
	 * @param string $url Url
	 *
	 * @return string $hash
	 */
	public static function getHashByUrl($url) {
		return md5(md5($url));
	}
}
