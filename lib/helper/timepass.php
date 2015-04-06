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
 * LaterPay time pass helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_timepass {
	const PASS_TOKEN = 'tlp';

	/**
	 * Get time pass default options.
	 *
	 * @param string $key Option name
	 *
	 * @return mixed option value | array of options
	 */
	public static function getDefaultOptions($key = NULL) {
		// Default time range. Used during passes creation.
		$defaults = array(
			'pass_id' 			=> '0',
			'duration' 			=> '1',
			'period' 			=> '1',
			'access_to' 		=> '0',
			'access_category' 	=> '',
			'price' 			=> '0.99',
			'revenue_model' 	=> 'ppu',
			'title'				=> tx_laterpay_helper_string::tr('24-Hour Pass', 'laterpay'),
			'description'		=> tx_laterpay_helper_string::tr('24 hours access to all content on this website', 'laterpay')
		);

		if (isset($key)) {
			if (isset($defaults[$key])) {
				return $defaults[$key];
			}
		}

		return $defaults;
	}

	/**
	 * Get valid time pass durations.
	 *
	 * @param string $key Option name
	 *
	 * @return mixed option value | array of options
	 */
	public static function getDurationOptions($key = NULL) {
		$durations = array(
			1 => 1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10,
			11,
			12,
			13,
			14,
			15,
			16,
			17,
			18,
			19,
			20,
			21,
			22,
			23,
			24,
		);

		if (isset($key)) {
			if (isset($durations[$key])) {
				return $durations[$key];
			}
		}

		return $durations;
	}

	/**
	 * Get valid time pass periods.
	 *
	 * @param string $key Option name
	 * @param bool $pluralized Flag
	 *
	 * @return mixed option value | array of options
	 */
	public static function getPeriodOptions($key = NULL, $pluralized = FALSE) {
		// single periods
		$periods = array(
			tx_laterpay_helper_string::tr('Hour', 'laterpay'),
			tx_laterpay_helper_string::tr('Day', 'laterpay'),
			tx_laterpay_helper_string::tr('Week', 'laterpay'),
			tx_laterpay_helper_string::tr('Month', 'laterpay'),
			tx_laterpay_helper_string::tr('Year', 'laterpay')
		);

		// pluralized periods
		$periodsPluralized = array(
			tx_laterpay_helper_string::tr('Hours', 'laterpay'),
			tx_laterpay_helper_string::tr('Days', 'laterpay'),
			tx_laterpay_helper_string::tr('Weeks', 'laterpay'),
			tx_laterpay_helper_string::tr('Months', 'laterpay'),
			tx_laterpay_helper_string::tr('Years', 'laterpay')
		);

		$selectedArray = $pluralized ? $periodsPluralized : $periods;

		if (isset($key)) {
			if (isset($selectedArray[$key])) {
				return $selectedArray[$key];
			}
		}

		return $selectedArray;
	}

	/**
	 * Get valid time pass revenue models.
	 *
	 * @param string $key Otion name
	 *
	 * @return mixed option value | array of options
	 */
	public static function getRevenueModelOptions($key = NULL) {
		$revenues = array(
			'ppu' => tx_laterpay_helper_string::tr('later', 'laterpay'),
			'sis' => tx_laterpay_helper_string::tr('immediately', 'laterpay')
		);

		if (isset($key)) {
			if (isset($revenues[$key])) {
				return $revenues[$key];
			}
		}

		return $revenues;
	}

	/**
	 * Get valid scope of time pass options.
	 *
	 * @param string $key Option name
	 *
	 * @return mixed option value | array of options
	 */
	public static function getAccessOptions($key = NULL) {
		$accessTo = array(
			tx_laterpay_helper_string::tr('All content', 'laterpay')
		);

		if (isset($key)) {
			if (isset($accessTo[$key])) {
				return $accessTo[$key];
			}
		}

		return $accessTo;
	}

	/**
	 * Get short time pass description.
	 *
	 * @param mixed $timePass Time pass data as array
	 * @param bool $fullInfo Need to display full info
	 *
	 * @return string short time pass description
	 */
	public static function getDescription($timePass = array(), $fullInfo = FALSE) {
		$details = array();

		if (! $timePass) {
			$timePass['duration'] 	= self::getDefaultOptions('duration');
			$timePass['period'] 	= self::getDefaultOptions('period');
			$timePass['access_to'] 	= self::getDefaultOptions('access_to');
		}

		$currency					= tx_laterpay_config::getOption('laterpay_currency');

		$details['duration']		= $timePass['duration'] . ' ' .
			self::getPeriodOptions($timePass['period'], $timePass['duration'] > 1);
		$details['access']			= tx_laterpay_helper_string::tr('access to', 'laterpay') . ' ' . self::getAccessOptions($timePass['access_to']);

		// also display category, price, and revenue model, if fullInfo flag is used
		if ($fullInfo) {
			if ($timePass['access_to'] > 0) {
				$categoryId 			= $timePass['access_category'];
				$details['category'] 	= '"' . get_the_category_by_ID($categoryId) . '"';
			}

			$details['price']			= tx_laterpay_helper_string::tr('for', 'laterpay') . ' ' .
				tx_laterpay_helper_view::formatNumber($timePass['price']) . ' ' .
				strtoupper($currency);
			$details['revenue']			= '(' . strtoupper($timePass['revenue_model']) . ')';
		}

		return implode(' ', $details);
	}

	/**
	 * Get time pass select options by type.
	 *
	 * @param string $type Type of select
	 *
	 * @return string of options
	 */
	public static function getSelectOptions($type) {
		$optionsHtml = '';
		$defaultValue = NULL;

		switch ($type) {
			case 'duration':
				$elements 		= self::getDurationOptions();
				$defaultValue 	= self::getDefaultOptions('duration');
				break;

			case 'period':
				$elements 		= self::getPeriodOptions();
				$defaultValue 	= self::getDefaultOptions('period');
				break;

			case 'access':
				$elements 		= self::getAccessOptions();
				$defaultValue 	= self::getDefaultOptions('access_to');
				break;

			default:
				return $optionsHtml;
		}

		if ($elements && is_array($elements)) {
			foreach ($elements as $id => $name) {
				if ($id == $defaultValue) {
					$optionsHtml .= '<option selected="selected" value="' . $id . '">' . $name . '</option>';
				} else {
					$optionsHtml .= '<option value="' . $id . '">' . $name . '</option>';
				}
			}
		}

		return $optionsHtml;
	}

	/**
	 * Get tokenized time pass id.
	 *
	 * @param string $untokenizedTimePassId Untokenized time pass id
	 *
	 * @return array $result
	 */
	public static function getTokenizedTimePassId($untokenizedTimePassId) {
		return sprintf('%s_%s', self::PASS_TOKEN, $untokenizedTimePassId);
	}

	/**
	 * Get untokenized time pass id.
	 *
	 * @param string $tokenizedTimePassId Tokenized time pass id
	 *
	 * @return int|null pass id
	 */
	public static function getUntokenizedTimePassId($tokenizedTimePassId) {
		$timePassParts = explode('_', $tokenizedTimePassId);
		if ($timePassParts[0] === self::PASS_TOKEN) {
			return $timePassParts[1];
		}

		return NULL;
	}

	/**
	 * Get all tokenized time pass ids.
	 *
	 * @param mixed $timePasses Array of time passes
	 *
	 * @return array $result
	 */
	public static function getTokenizedTimePassIds($timePasses = NULL) {
		if (! isset($timePasses)) {
			$timePasses = self::getAllTimePasses();
		}

		$result = array();
		foreach ($timePasses as $timePass) {
			$result[] = self::getTokenizedTimePassId($timePass->passId);
		}

		return $result;
	}

	/**
	 * Get all time passes for a given post.
	 *
	 * @param int $postId Post id
	 * @param mixed $timePassesWithAccess Array of ids of time passes with access
	 *
	 * @return array $timePasses
	 */
	public static function getTimePassesListByPostId($postId, $timePassesWithAccess = NULL) {
		$model = new tx_laterpay_model_timepass();

		if ($postId !== NULL) {
			// get all post categories
			$postCategories = get_the_category($postId);
			$postCategoryIds = NULL;

			// get category ids
			foreach ($postCategories as $category) {
				$postCategoryIds[] = $category->termId;
				// get category parents and include them in the ids array as well
				$parentId = get_category($category->termId)->parent;
				while ($parentId) {
					$postCategoryIds[] = $parentId;
					$parentId = get_category($parentId)->parent;
				}
			}

			// get list of time passes that cover this post
			$timePasses = (array) $model->getTimePassesByCategoryIds($postCategoryIds);
		} else {
			$timePasses = (array) $model->getTimePassesByCategoryIds();
		}

		// correct result, if we have purchased time passes
		if ($timePassesWithAccess) {
			// check, if user has access to the current post with time pass
			$hasAccess = FALSE;
			foreach ($timePasses as $timePass) {
				if (in_array($timePass->passId, $timePassesWithAccess)) {
					$hasAccess = TRUE;
					break;
				}
			}

			if ($hasAccess) {
				// categories with access (type 2)
				$coveredCategories = array(
					'included' => array(),
					'excluded' => NULL
				);
				// excluded categories (type 1)
				$excludedCategories = array();

				// go through time passes with access and find covered and excluded categories
				foreach ($timePassesWithAccess as $timePassWithAccessId) {
					$timePassWithAccessData = (array) $model->getPassData($timePassWithAccessId);
					$accessCategory = $timePassWithAccessData['access_category'];
					$accessType = $timePassWithAccessData['access_to'];
					if ($accessType == 2) {
						$coveredCategories['included'][] = $accessCategory;
					} else {
						if ($accessType == 1) {
							$excludedCategories[] = $accessCategory;
						} else {
							return array();
						}
					}
				}

				// case: full access, except for specific categories
				if ($excludedCategories) {
					foreach ($excludedCategories as $excludedCategoryId) {
						// search for excluded category in covered categories
						$hasCoveredCategory = array_search($excludedCategoryId, $coveredCategories);
						if ($hasCoveredCategory !== FALSE) {
							return array();
						} else {
							// if more than 1 time pass with excluded category was purchased,
							// and if its values are not matched, then all categories are covered
							if (isset($coveredCategories['excluded']) && ($coveredCategories['excluded'] !== $excludedCategoryId)) {
								return array();
							}
							// store the only category not covered
							$coveredCategories['excluded'] = $excludedCategoryId;
						}
					}
				}

				// get data without covered categories or only excluded
				if (isset($coveredCategories['excluded'])) {
					$timePasses = $model->getTimePassesByCategoryIds(array(
						$coveredCategories['excluded']
					));
				} else {
					$timePasses = $model->getTimePassesByCategoryIds($coveredCategories['included'], TRUE);
				}
			}
		}

		return (array) $timePasses;
	}

	/**
	 * Get all time passes.
	 *
	 * @return array of time passes
	 */
	public static function getAllTimePasses() {
		$model = new tx_laterpay_model_timepass();

		return $model->getAllTimePasses();
	}

	/**
	 * Get time pass data by id.
	 *
	 * @param int $timePassId Time pass id
	 *
	 * @return array
	 */
	public static function getTimePassById($timePassId) {
		$model = new tx_laterpay_model_timepass();

		if ($timePassId) {
			return $model->getPassData((int) $timePassId);
		}

		return array();
	}

	/**
	 * Get the LaterPay purchase link for a time pass.
	 *
	 * @param int $timePassId Pass id
	 * @param mixed $data Additional data as array
	 *
	 * @return string url || empty string if something went wrong
	 */
	public static function getLaterpayPurchaseLink($timePassId, $data = NULL) {
		$timePassModel = new tx_laterpay_model_timepass();

		$timePass = (array) $timePassModel->getPassData($timePassId);
		if (empty($timePass)) {
			return '';
		}

		if (! isset($data)) {
			$data = array();
		}

		$currency = tx_laterpay_config::getOption('laterpay_currency');
		$currencyModel 	= new tx_laterpay_model_currency();
		$price 			= isset($data['price']) ? $data['price'] : $timePass['price'];
		$revenueModel 	= tx_laterpay_helper_pricing::ensureValidRevenueModel($timePass['revenue_model'], $price);

		$clientOptions 	= tx_laterpay_helper_config::getPhpClientOptions();
		$client 		= new tx_laterpay_client($clientOptions['cp_key'], $clientOptions['api_key'], $clientOptions['api_root'],
			$clientOptions['web_root'], $clientOptions['token_name']);

		$link 			= isset($data['link']) ? $data['link'] : get_permalink();

		// prepare URL
		$urlParams = array(
			'pass_id' 		=> self::getTokenizedTimePassId($timePassId),
			'id_currency' 	=> $currencyModel->getCurrencyIdByIso4217Code($currency),
			'price' 		=> $price,
			'date' 			=> time(),
			'ip' 			=> ip2long($_SERVER['REMOTE_ADDR']),
			'revenue_model' => $revenueModel,
			'link' 			=> $link,
		);

		$url	= tx_laterpay_helper_string::addQueryArg(array_merge($urlParams, $data), $link);
		$hash 	= tx_laterpay_helper_pricing::getHashByUrl($url);
		$url 	= $url . '&hash=' . $hash;

		// parameters for LaterPay purchase form
		$params = array(
			'pricing' 	=> $currency . ($price * 100),
			'expiry' 	=> '+' . self::getTimePassExpiryTime($timePass),
			'vat' 		=> tx_laterpay_config::getInstance()->get('currency.default_vat'),
			'url' 		=> $url,
			'title' 	=> isset($data['voucher']) ? $timePass['title'] . ', Code: ' . $data['voucher'] : $timePass['title'],
		);
		if (isset($data['voucher'])) {
			$params['article_id'] = '[#' . $data['voucher'] . ']';
		} else {
			$params['article_id'] = self::getTokenizedTimePassId($timePassId);
		}

		if ($revenueModel == 'sis') {
			// Single Sale purchase
			return $client->getBuyUrl($params);
		} else {
			// Pay-per-Use purchase
			return $client->getAddUrl($params);
		}
	}

	/**
	 * Get time pass expiry time.
	 *
	 * @param mixed $timePass Time pass data as array
	 *
	 * @return array
	 */
	protected static function getTimePassExpiryTime($timePass) {
		switch ($timePass['period']) {
			// hours
			case 0:
				$time = $timePass['duration'] * 60 * 60;
				break;

			// days
			case 1:
				$time = $timePass['duration'] * 60 * 60 * 24;
				break;

			// weeks
			case 2:
				$time = $timePass['duration'] * 60 * 60 * 24 * 7;
				break;

			// months
			case 3:
				$time = $timePass['duration'] * 60 * 60 * 24 * 31;
				break;

			// years
			case 4:
				$time = $timePass['duration'] * 60 * 60 * 24 * 365;
				break;

			default:
				$time = 0;
		}

		return $time;
	}

	/**
	 * Get time passes statistics.
	 *
	 * @return array return summary and individual statistics
	 */
	public static function getTimePassesStatistic() {
		$historyModel 		= new tx_laterpay_model_payment_history();
		$timePasses 		= self::getAllTimePasses();
		$summaryActive 		= 0;
		$summaryUnredeemed 	= 0;
		$summaryRevenue 	= 0;
		$summarySold 		= 0;

		if ($timePasses) {
			foreach ($timePasses as $timePass) {
				$timePass 			= (array) $timePass;
				$timePassHistory 	= $historyModel->getTimePassHistory($timePass['pass_id']);

				// in seconds
				$duration 			= self::getTimePassExpiryTime($timePass);

				// calculate time pass KPIs
				// total value of purchased time passes
				$committedRevenue = 0;

				// number of unredeemed gift codes
				$unredeemed = 0;

				// number of active time passes
				$active = 0;

				if ($timePassHistory && is_array($timePassHistory)) {
					foreach ($timePassHistory as $hist) {
						$hasUnredeemed = FALSE;
						$committedRevenue += $hist->price;

						// check, if there are unredeemed gift codes
						if ($hist->code && ! tx_laterpay_helper_voucher::getGiftCodeUsagesCount($hist->code)) {
							$hasUnredeemed = TRUE;
							$unredeemed ++;
							$summaryUnredeemed ++;
						}

						// check, if pass is still active
						if (! $hasUnredeemed) {
							$startDate = strtotime($hist->date);
							$currentDate = time();
							if (($startDate + $duration) > $currentDate) {
								$active ++;
								$summaryActive ++;
							}
						}
					}
				} else {
					$timePassHistory = array();
				}

				$timePassStatistics = array(
					'data' 				=> $timePass,
					'active' 			=> tx_laterpay_helper_view::formatNumber($active, FALSE),
					'sold' 				=> tx_laterpay_helper_view::formatNumber(count($timePassHistory), FALSE),
					'unredeemed' 		=> tx_laterpay_helper_view::formatNumber($unredeemed, FALSE),
					'committed_revenue' => number_format($committedRevenue, 2),
				);

				$statistic['individual'][$timePass['pass_id']] = $timePassStatistics;
			}
		}

		// calculate summary statistics
		$timePassesHistory = $historyModel->getTimePassHistory();

		if ($timePassesHistory && is_array($timePassesHistory)) {
			$summarySold = count($timePassesHistory);
			foreach ($timePassesHistory as $hist) {
				$summaryRevenue += $hist->price;
			}
		}

		$statistic['summary'] = array(
			'active' 			=> tx_laterpay_helper_view::formatNumber($summaryActive, FALSE),
			'sold' 				=> tx_laterpay_helper_view::formatNumber($summarySold, FALSE),
			'unredeemed' 		=> tx_laterpay_helper_view::formatNumber($summaryUnredeemed, FALSE),
			'committed_revenue' => number_format($summaryRevenue, 2),
		);

		return $statistic;
	}

	/**
	 * Get number of expiring time passes for each week, week numbers determined by ticks parameter.
	 *
	 * @param int $timePassId Pass id | 0 or null for all time passes
	 * @param int $ticks Period in weeks
	 *
	 * @return array
	 */
	public static function getTimePassExpiryByWeeks($timePassId, $ticks) {
		$historyModel 	= new tx_laterpay_model_payment_history();
		$data 			= array();
		$duration 		= 0;

		// initialize array
		if (! $ticks) {
			return $data;
		} else {
			$i = 0;
			while ($i <= $ticks) {
				$data[] = 0;
				$i ++;
			}
		}

		if ($timePassId) {
			// get history for one given time pass
			$timePass 	= (array) self::getTimePassById($timePassId);
			$duration 	= self::getTimePassExpiryTime($timePass);
			$history 	= $historyModel->getTimePassHistory($timePassId);
		} else {
			// get history for all time passes
			$history 	= $historyModel->getTimePassHistory();
		}

		if ($history && is_array($history)) {
			// in seconds
			$weekDuration 	= 7 * 24 * 60 * 60;
			$currentDate 	= time();

			// get expiry data for each time pass
			foreach ($history as $hist) {
				$key = 0;
				$startDate = strtotime($hist->date);

				// determine expiry date of time pass
				if (! $duration) {
					$timePassId = $hist->passId;
					$timePass 	= (array) self::getTimePassById($timePassId);
					$expiryDate = $startDate + self::getTimePassExpiryTime($timePass);
				} else {
					$expiryDate = $startDate + $duration;
				}

				// get week in which time pass expires, if time pass is active
				if ($expiryDate > $currentDate) {
					$weekNumber = 1;

					while (($startDate + $weekNumber * $weekDuration) < $expiryDate) {
						$weekNumber ++;
						$key ++;
					}

					if (! $hist->code) {
						$data[$key] ++;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Get count of existing time passes.
	 *
	 * @return int count of time passes
	 */
	public static function getTimePassesCount() {
		$model = new tx_laterpay_model_timepass();

		return $model->getTimePassesCount();
	}
}
