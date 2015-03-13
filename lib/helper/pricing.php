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
	const TYPE_GLOBAL_DEFAULT_PRICE = 'global default price';

	const TYPE_CATEGORY_DEFAULT_PRICE = 'category default price';

	const TYPE_INDIVIDUAL_PRICE = 'individual price';

	const TYPE_INDIVIDUAL_DYNAMIC_PRICE = 'individual price, dynamic';

	/**
	 * Status of published post
	 * @const string Status of post at time of publication.
	 */
	const STATUS_POST_PUBLISHED = 'publish';

	/**
	 * Price ranges.
	 */
	const PPU_MIN = 0.05;

	const PPU_MAX = 1.48;

	const PPUSIS_MAX = 5.00;

	const SIS_MIN = 1.49;

	const SIS_MAX = 149.99;

	const PRICE_PPU_END = 0.05;

	const PRICE_PPUSIS_END = 1.49;

	const PRICE_SIS_END = 5.01;

	const PRICE_START_DAY = 13;

	const PRICE_END_DAY = 18;

	const META_KEY = 'laterpay_post_prices';

	/**
	 * Get array of price ranges by revenue model (Pay-per-Use or Single Sale).
	 *
	 * @return array
	 */
	public static function getPriceRangesByRevenueModel() {
		return array(
			'ppu_min' => self::PPU_MIN,
			'ppu_max' => self::PPU_MAX,
			'ppusis_max' => self::PPUSIS_MAX,
			'sis_min' => self::SIS_MIN,
			'sis_max' => self::SIS_MAX,
			'price_ppu_end' => self::PRICE_PPU_END,
			'price_ppusis_end' => self::PRICE_PPUSIS_END,
			'price_sis_end' => self::PRICE_SIS_END,
			'price_start_day' => self::PRICE_START_DAY,
			'price_end_day' => self::PRICE_END_DAY
		);
	}

	/**
	 * Check, if the current post or a given post is purchasable.
	 *
	 * @param null|WP_Post $post
	 *
	 * @return null|bool true|false (null if post is free)
	 */
// 	public static function isPurchasable($post = null) {
// 		if (! is_a($post, 'WP_POST')) {
// 			// load the current post in $GLOBAL['post']
// 			$post = get_post();
// 			if ($post === null) {
// 				return false;
// 			}
// 		}

// 		// check, if the current post price is not 0
// 		$price = self::getPostPrice($post->ID);
// 		if ($price == 0) {
// 			// return null for this case
// 			return null;
// 		}

// 		return true;
// 	}

	/**
	 * Return all posts that have a price applied.
	 *
	 * @return array
	 */
	public static function getAllPostsWithPrice() {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', 'laterpay_price > 0');
	}

	/**
	 * Return all postIds with a given categoryId that have a price applied.
	 *
	 * @param int $categoryId
	 *
	 * @return array
	 */
// 	public static function getPostIdsWithPriceByCategoryId($categoryId) {
// 		$laterpayCategoryModel = new LaterPay_Model_CategoryPrice();
// 		$config = laterpayGetPluginConfig();
// 		$ids = array(
// 			$categoryId
// 		);

// 		// get all childs for $categoryId
// 		$categoryChildren = get_categories(array(
// 			'child_of' => $categoryId
// 		));

// 		foreach ($categoryChildren as $category) {
// 			// filter ids with category prices
// 			if (! $laterpayCategoryModel->getCategoryPriceDataByCategoryIds($category->termId)) {
// 				$ids[] = (int) $category->termId;
// 			}
// 		}

// 		$postArgs = array(
// 			'fields' => 'ids',
// 			'meta_query' => array(
// 				array(
// 					'meta_key' => self::META_KEY
// 				)
// 			),
// 			'category__in' => $ids,
// 			'cat' => $categoryId,
// 			'posts_per_page' => '-1',
// 			'post_type' => $config->get('content.enabled_post_types')
// 		);
// 		$posts = get_posts($postArgs);

// 		return $posts;
// 	}

	/**
	 * Apply the global default price to a post.
	 *
	 * @param int $postId
	 *
	 * @return bool true|false
	 */
// 	public static function applyGlobalDefaultPriceToPost($postId) {
// 		$globalDefaultPrice = get_option('laterpay_global_price');

// 		if ($globalDefaultPrice == 0) {
// 			return false;
// 		}

// 		$post = get_post($postId);
// 		if ($post === null) {
// 			return false;
// 		}

// 		$postPrice = array();
// 		$postPrice['type'] = self::TYPE_GLOBAL_DEFAULT_PRICE;

// 		return update_post_meta($postId, self::META_KEY, $postPrice);
// 	}

	/**
	 * Apply the 'category default price' to all posts with a 'global default price' by a given categoryId.
	 *
	 * @param int $categoryId
	 *
	 * @return array $updatedPostIds all updated postIds
	 */
// 	public static function applyCategoryPriceToPostsWithGlobalPrice($categoryId) {
// 		$updatedPostIds = array();
// 		$postIds = self::getPostIdsWithPriceByCategoryId($categoryId);

// 		foreach ($postIds as $postId) {
// 			$postPrice = get_post_meta($postId, self::META_KEY, true);

// 			// check, if the post uses a global default price

// 			if (is_array($postPrice) &&
// 				 (! array_key_exists('type', $postPrice) || $postPrice['type'] !== self::TYPE_GLOBAL_DEFAULT_PRICE)) {
// 				if (! self::checkIfCategoryHasParentWithPrice($categoryId)) {
// 					continue;
// 				}
// 			}

// 			$success = self::applyCategoryDefaultPriceToPost($postId, $categoryId);
// 			if ($success) {
// 				$updatedPostIds[] = $postId;
// 			}
// 		}

// 		return $updatedPostIds;
// 	}

	/**
	 * Apply a given category default price to a given post.
	 *
	 * @param int $postId
	 * @param int $categoryId
	 * @param bool $strict Checks, if the given categoryId is assigned to the postId
	 *
	 * @return bool true|false
	 */
// 	public static function applyCategoryDefaultPriceToPost($postId, $categoryId, $strict = false) {
// 		$post = get_post($postId);
// 		if ($post === null) {
// 			return false;
// 		}

// 		// check, if the post has the given categoryId
// 		if ($strict && ! has_category($categoryId, $post)) {
// 			return false;
// 		}

// 		$postPrice = array(
// 			'type' => self::TYPE_CATEGORY_DEFAULT_PRICE,
// 			'category_id' => (int) $categoryId
// 		);

// 		return update_post_meta($postId, self::META_KEY, $postPrice);
// 	}

	/**
	 * Get the post price type.
	 * Returns global default price or individual price, if no valid type is set.
	 *
	 * @param int $postId
	 *
	 * @return string $postPriceType
	 */
// 	public static function getPostPriceType($postId) {
// 		$cacheKey = 'laterpay_post_price_type_' . $postId;

// 		// get the price from the cache, if it exists
// 		$postPriceType = wp_cache_get($cacheKey, 'laterpay');
// 		if ($postPriceType) {
// 			return $postPriceType;
// 		}

// 		$postPrice = get_post_meta($postId, self::META_KEY, true);
// 		if (! is_array($postPrice)) {
// 			$postPrice = array();
// 		}
// 		$postPriceType = array_key_exists('type', $postPrice) ? $postPrice['type'] : '';

// 		// set a price type (global default price or individual price), if the returned post price type is invalid
// 		switch ($postPriceType) {
// 			case self::TYPE_INDIVIDUAL_PRICE:
// 			case self::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
// 			case self::TYPE_CATEGORY_DEFAULT_PRICE:
// 			case self::TYPE_GLOBAL_DEFAULT_PRICE:
// 				break;

// 			default:
// 				$globalDefaultPrice = get_option('laterpay_global_price');
// 				if ($globalDefaultPrice > 0) {
// 					$postPriceType = self::TYPE_GLOBAL_DEFAULT_PRICE;
// 				} else {
// 					$postPriceType = self::TYPE_INDIVIDUAL_PRICE;
// 				}
// 				break;
// 		}

// 		// cache the post price type
// 		wp_cache_set($cacheKey, $postPriceType, 'laterpay');

// 		return (string) $postPriceType;
// 	}

	/**
	 * Get the current price for a post with dynamic pricing scheme defined.
	 *
	 * @param WP_Post $post
	 *
	 * @return float price
	 */
// 	public static function getDynamicPrice($post) {
// 		$postPrice = get_post_meta($post->ID, self::META_KEY, true);
// 		$daysSincePublication = self::dynamicPriceDaysAfterPublication($post);
// 		$priceRangeType = $postPrice['price_range_type'];

// 		if ($postPrice['change_start_price_after_days'] >= $daysSincePublication) {
// 			$price = $postPrice['start_price'];
// 		} else {
// 			if ($postPrice['transitional_period_end_after_days'] <= $daysSincePublication ||
// 				 $postPrice['transitional_period_end_after_days'] == 0) {
// 				$price = $postPrice['end_price'];
// 			} else { // transitional period between start and end of dynamic price change
// 				$price = self::calculateTransitionalPrice($postPrice, $daysSincePublication);
// 			}
// 		}

// 		// detect revenue model by price range
// 		$roundedPrice = round($price, 2);

// 		switch ($priceRangeType) {
// 			case 'ppu':
// 				if ($roundedPrice < self::PPU_MIN) {
// 					if (abs(self::PRICE_SIS_END - $roundedPrice) < $roundedPrice) {
// 						$roundedPrice = self::PPU_MIN;
// 					} else {
// 						$roundedPrice = 0;
// 					}
// 				} else
// 					if ($roundedPrice > self::PPU_MAX) {
// 						$roundedPrice = self::PPU_MAX;
// 					}
// 				break;
// 			case 'sis':
// 				if ($roundedPrice < self::priceSisEnd) {
// 					if (abs(self::PRICE_SIS_END - $roundedPrice) < $roundedPrice) {
// 						$roundedPrice = self::PRICE_SIS_END;
// 					} else {
// 						$roundedPrice = 0;
// 					}
// 				}
// 				if ($roundedPrice > self::SIS_MAX) {
// 					$roundedPrice = self::SIS_MAX;
// 				}
// 				break;
// 			case 'ppusis':
// 				if ($roundedPrice > self::PPUSIS_MAX) {
// 					$roundedPrice = self::PPUSIS_MAX;
// 				} else
// 					if ($roundedPrice < self::SIS_MIN) {
// 						if (abs(self::SIS_MIN - $roundedPrice) < $roundedPrice) {
// 							$roundedPrice = self::SIS_MIN;
// 						} else {
// 							$roundedPrice = 0.00;
// 						}
// 					}
// 				break;
// 			default:
// 				break;
// 		}

// 		return number_format($roundedPrice, 2);
// 	}

	/**
	 * Get the current days count since publication.
	 *
	 * @param WP_Post $post
	 *
	 * @return int days
	 */
// 	public static function dynamicPriceDaysAfterPublication($post) {
// 		$daysSincePublication = 0;

// 		// unpublished posts always have 0 days after publication
// 		if ($post->postStatus != self::STATUS_POST_PUBLISHED) {
// 			return $daysSincePublication;
// 		}

// 		if (function_exists('date_diff')) {
// 			$dateTime = new DateTime(date('Y-m-d'));
// 			$daysSincePublication = $dateTime->diff(new DateTime(date('Y-m-d', strtotime($post->postDate))))->format('%a');
// 		} else {
// 			$d1 = strtotime(date('Y-m-d'));
// 			$d2 = strtotime($post->postDate);
// 			$diffSecs = abs($d1 - $d2);
// 			$daysSincePublication = floor($diffSecs / (3600 * 24));
// 		}

// 		return $daysSincePublication;
// 	}

	/**
	 * Calculate transitional price between start price and end price based on linear equation.
	 *
	 * @param mixed $postPrice Array of Postmeta see 'laterpayPostPrices'
	 * @param int $daysSincePublication Count of days after publication
	 *
	 * @return float
	 */
	private static function calculateTransitionalPrice($postPrice, $daysSincePublication) {
		$endPrice = $postPrice['end_price'];
		$startPrice = $postPrice['start_price'];
		$daysUntilEnd = $postPrice['transitional_period_end_after_days'];
		$daysUntilStart = $postPrice['change_start_price_after_days'];

		$coefficient = ($endPrice - $startPrice) / ($daysUntilEnd - $daysUntilStart);

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
		$price = self::getContentPrice($contentObject);
		$revenueModel = isset($contentObject->data['laterpay_revenue_model']) ? $contentObject->data['laterpay_revenue_model'] : 'p2p';

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
	 * Return data for dynamic prices.
	 * Can be values already set or defaults.
	 *
	 * @param WP_Post $post
	 * @param null $price
	 *
	 * @return array
	 */
// 	public static function getDynamicPrices($post, $price = null) {
// 		if (! LaterPay_Helper_User::can('laterpay_edit_individual_price', $post)) {
// 			return;
// 		}

// 		$postPrices = get_post_meta($post->ID, 'laterpay_post_prices', true);
// 		if (! is_array($postPrices)) {
// 			$postPrices = array();
// 		}

// 		$postPrice = array_key_exists('price', $postPrices) ? (float) $postPrices['price'] : self::getPostPrice($post->ID);
// 		if ($price !== null) {
// 			$postPrice = $price;
// 		}

// 		$startPrice = array_key_exists('start_price', $postPrices) ? (float) $postPrices['start_price'] : '';
// 		$endPrice = array_key_exists('end_price', $postPrices) ? (float) $postPrices['end_price'] : '';
// 		$reachEndPriceAfterDays = array_key_exists('reach_end_price_after_days', $postPrices) ? (float) $postPrices['reach_end_price_after_days'] : '';
// 		$changeStartPriceAfterDays = array_key_exists('change_start_price_after_days', $postPrices) ? (float) $postPrices['change_start_price_after_days'] : '';
// 		$transitionalPeriodEndAfterDays = array_key_exists('transitional_period_end_after_days', $postPrices) ? (float) $postPrices['transitional_period_end_after_days'] : '';

// 		// return dynamic pricing widget start values
// 		if (($startPrice === '') && ($price !== null)) {
// 			if ($postPrice > self::PPUSIS_MAX) {
// 				// Single Sale (sis), if price >= 5.01
// 				$endPrice = self::PRICE_SIS_END;
// 			} elseif ($postPrice > self::SIS_MIN) {
// 				// Single Sale or Pay-per-Use, if 1.49 >= price <= 5.00
// 				$endPrice = self::PRICE_PPUSIS_END;
// 			} else {
// 				// Pay-per-Use (ppu), if price <= 1.48
// 				$endPrice = self::PRICE_PPU_END;
// 			}

// 			$dynamicPricingData = array(
// 				array(
// 					'x' => 0,
// 					'y' => $postPrice
// 				),
// 				array(
// 					'x' => self::PRICE_START_DAY,
// 					'y' => $postPrice
// 				),
// 				array(
// 					'x' => self::PRICE_END_DAY,
// 					'y' => $endPrice
// 				),
// 				array(
// 					'x' => 30,
// 					'y' => $endPrice
// 				)
// 			);
// 		} elseif ($transitionalPeriodEndAfterDays === '') {
// 			$dynamicPricingData = array(
// 				array(
// 					'x' => 0,
// 					'y' => $startPrice
// 				),
// 				array(
// 					'x' => $changeStartPriceAfterDays,
// 					'y' => $startPrice
// 				),
// 				array(
// 					'x' => $reachEndPriceAfterDays,
// 					'y' => $endPrice
// 				)
// 			);
// 		} else {
// 			$dynamicPricingData = array(
// 				array(
// 					'x' => 0,
// 					'y' => $startPrice
// 				),
// 				array(
// 					'x' => $changeStartPriceAfterDays,
// 					'y' => $startPrice
// 				),
// 				array(
// 					'x' => $transitionalPeriodEndAfterDays,
// 					'y' => $endPrice
// 				),
// 				array(
// 					'x' => $reachEndPriceAfterDays,
// 					'y' => $endPrice
// 				)
// 			);
// 		}

// 		// get number of days since publication to render an indicator in the dynamic pricing widget
// 		$daysAfterPublication = self::dynamicPriceDaysAfterPublication($post);

// 		$result = array(
// 			'values' => $dynamicPricingData,
// 			'price' => array(
// 				'pubDays' => $daysAfterPublication,
// 				'todayPrice' => $price
// 			)
// 		);

// 		return $result;
// 	}

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
	 * Select categories from a given list of categories that have a category default price
	 * and return an array of their ids.
	 *
	 * @param array $categories
	 *
	 * @return array
	 */
// 	public static function getCategoriesWithPrice($categories) {
// 		$categoriesWithPrice = array();
// 		$ids = array();

// 		if (is_array($categories)) {
// 			foreach ($categories as $category) {
// 				$ids[] = $category->termId;
// 			}
// 		}

// 		if ($ids) {
// 			$laterpayCategoryModel = new LaterPay_Model_CategoryPrice();
// 			$categoriesWithPrice = $laterpayCategoryModel->getCategoryPriceDataByCategoryIds($ids);
// 		}

// 		return $categoriesWithPrice;
// 	}

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
		$operations = get_option('laterpay_bulk_operations');

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
			'data' => $data,
			'message' => $message
		);
		update_option('laterpay_bulk_operations', serialize($operations));

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
				update_option('laterpay_bulk_operations', serialize($operations));
			}
		}

		return $wasDeleted;
	}

	/**
	 * Reset post publication date.
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
// 	public static function resetPostPublicationDate($post) {
// 		$actualDate = date('Y-m-d H:i:s');
// 		$actualDateGmt = gmdate('Y-m-d H:i:s');
// 		$postUpdateData = array(
// 			'ID' => $post->ID,
// 			'post_date' => $actualDate,
// 			'post_date_gmt' => $actualDateGmt
// 		);

// 		wp_update_post($postUpdateData);
// 	}

	/**
	 * Return the URL hash for a given URL.
	 *
	 * @param string $url Url
	 *
	 * @return string $hash
	 */
	public static function getHashByUrl($url) {
		return md5(md5($url) /*. wp_salt()*/ );
	}

	/**
	 * Get posts by category price id with meta check
	 *
	 * @param int $categoryId
	 *
	 * @return array post ids
	 */
// 	public static function getPostsByCategoryPriceId($categoryId) {
// 		$ids = array();
// 		$posts = self::getAllPostsWithPrice();
// 		$parents = array();

// 		// get all parents
// 		$parentId = get_category($categoryId)->parent;
// 		while ($parentId) {
// 			$parents[] = $parentId;
// 			$parentId = get_category($parentId)->parent;
// 		}

// 		foreach ($posts as $post) {
// 			$meta = get_post_meta($post->ID, self::META_KEY, true);
// 			if (! is_array($meta)) {
// 				continue;
// 			}

// 			if (array_key_exists('category_id', $meta) &&
// 				 ($categoryId == $meta['category_id'] || in_array($meta['category_id'], $parents))) {
// 				$ids[$post->ID] = $meta;
// 			}
// 		}

// 		return $ids;
// 	}

	/**
	 * Actualize post data after category delete
	 *
	 * @param
	 *        	$postId
	 *
	 * @return void
	 */
// 	public static function updatePostDataAfterCategoryDelete($postId) {
// 		$categoryPriceModel = new LaterPay_Model_CategoryPrice();
// 		$postCategories = wp_get_post_categories($postId);
// 		$parents = array();

// 		// add parents
// 		foreach ($postCategories as $categoryId) {
// 			$parentId = get_category($categoryId)->parent;
// 			while ($parentId) {
// 				$parents[] = $parentId;
// 				$parentId = get_category($parentId)->parent;
// 			}
// 		}

// 		// merge category ids
// 		$postCategories = array_merge($postCategories, $parents);

// 		if (empty($postCategories)) {
// 			// apply the global default price as new price, if no other post categories are found
// 			self::applyGlobalDefaultPriceToPost($postId);
// 		} else {
// 			// load all category prices by the given categoryIds
// 			$categoryPriceData = $categoryPriceModel->getCategoryPriceDataByCategoryIds($postCategories);

// 			if (count($categoryPriceData) < 1) {
// 				// no other category prices found for this post
// 				self::applyGlobalDefaultPriceToPost($postId);
// 			} else {
// 				// find the category with the highest price and assign its categoryId to the post
// 				$price = 0;
// 				$newCategoryId = null;

// 				foreach ($categoryPriceData as $data) {
// 					if ($data->categoryPrice > $price) {
// 						$price = $data->categoryPrice;
// 						$newCategoryId = $data->categoryId;
// 					}
// 				}

// 				self::applyCategoryDefaultPriceToPost($postId, $newCategoryId);
// 			}
// 		}
// 	}

	/**
	 * Get category price data by category ids.
	 *
	 * @param
	 *        	$categoryIds
	 *
	 * @return array
	 */
// 	public static function getCategoryPriceDataByCategoryIds($categoryIds) {
// 		$result = array();

// 		if (is_array($categoryIds) && count($categoryIds) > 0) {
// 			// this array will prevent category prices from duplication
// 			$idsUsed = array();
// 			$laterpayCategoryModel = new LaterPay_Model_CategoryPrice();
// 			$categoryPriceData = $laterpayCategoryModel->getCategoryPriceDataByCategoryIds($categoryIds);
// 			// add prices data to results array
// 			foreach ($categoryPriceData as $category) {
// 				$idsUsed[] = $category->categoryId;
// 				$result[] = (array) $category;
// 			}

// 			// loop through each category and check, if it has a category price
// 			// if not, then try to get the parent category's category price
// 			foreach ($categoryIds as $categoryId) {
// 				$hasPrice = false;
// 				foreach ($categoryPriceData as $category) {
// 					if ($category->categoryId == $categoryId) {
// 						$hasPrice = true;
// 						break;
// 					}
// 				}

// 				if (! $hasPrice) {
// 					$parentId = get_category($categoryId)->parent;
// 					while ($parentId) {
// 						$parentData = $laterpayCategoryModel->getCategoryPriceDataByCategoryIds($parentId);
// 						if (! $parentData) {
// 							$parentId = get_category($parentId)->parent;
// 							continue;
// 						}
// 						$parentData = (array) $parentData[0];
// 						if (! in_array($parentData['category_id'], $idsUsed)) {
// 							$idsUsed[] = $parentData['category_id'];
// 							$result[] = $parentData;
// 						}
// 						break;
// 					}
// 				}
// 			}
// 		}

// 		return $result;
// 	}

	/**
	 * Check if category has parent category with category price setted
	 *
	 * @param
	 *        	$categoryId
	 *
	 * @return bool
	 */
// 	public static function checkIfCategoryHasParentWithPrice($categoryId) {
// 		$laterpayCategoryModel = new LaterPay_Model_CategoryPrice();
// 		$hasPrice = false;

// 		// get parent id with price
// 		$parentId = get_category($categoryId)->parent;
// 		while ($parentId) {
// 			$categoryPrice = $laterpayCategoryModel->getCategoryPriceDataByCategoryIds($parentId);
// 			if (! $categoryPrice) {
// 				$parentId = get_category($parentId)->parent;
// 				continue;
// 			}
// 			$hasPrice = $parentId;
// 			break;
// 		}

// 		return $hasPrice;
// 	}

	/**
	 * Get category parents
	 *
	 * @param
	 *        	$categoryId
	 *
	 * @return array of parent categories ids
	 */
// 	public static function getCategoryParents($categoryId) {
// 		$parents = array();

// 		$parentId = get_category($categoryId)->parent;
// 		while ($parentId) {
// 			$parents[] = $parentId;
// 			$parentId = get_category($parentId)->parent;
// 		}

// 		return $parents;
// 	}
}
