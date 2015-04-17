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
 * LaterPay dashboard helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_dashboard {

	const TIME_PASSES_WEEKS = 13;

	/**
	 * Helper function to load the cached data by a given file path.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $cacheData array with cached data or empty array on failure
	 */
	public static function getCacheData($options) {
		$filePath = $options['cache_file_path'];

		if (! file_exists($filePath)) {
			tx_laterpay_core_logger::getInstance()->error(__METHOD__ . ' - cache-file not found', array(
				'file_path' => $filePath,
			));

			return array();
		}

		$cacheData 		= file_get_contents($filePath);
		$unsCacheData 	= @unserialize($cacheData);
		if ($unsCacheData !== FALSE) {
			$cacheData = $unsCacheData;
			unset($unsCacheData);
		}

		if (! is_array($cacheData)) {
			tx_laterpay_core_logger::getInstance()->error(__METHOD__ . ' - invalid cache data',
				array(
					'file_path' 	=> $filePath,
					'cache_data' 	=> $cacheData,
				));

			return array();
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'file_path' 	=> $filePath,
				'cache_data' 	=> $cacheData,
			));

		return $cacheData;
	}

	/**
	 * Return the cache dir by a given strottime() timestamp.
	 *
	 * @param int|null $timestamp Default null will be set to strototime( 'today GMT' );
	 *
	 * @return string $cacheDir
	 */
	public static function getCacheDir($timestamp = NULL) {
		if ($timestamp === NULL) {
			$timestamp = strtotime('today GMT');
		}

		$cacheDir = tx_laterpay_config::getInstance()->get('cache_dir') . 'cron/' . gmdate('Y/m/d', $timestamp) . '/';

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'timestamp' => $timestamp,
				'cache_dir' => $cacheDir,
			));

		return $cacheDir;
	}

	/**
	 * Return the cache file name for the given days and item count.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return string $cacheFilename
	 */
	public static function getCacheFilename($options) {
		unset($options['start_timestamp']);

		$arrayValues 	= array_values($options);
		$cacheFilename 	= implode('-', $arrayValues) . '.cache';

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'options' 			=> $options,
				'cache_filename' 	=> $cacheFilename,
			));

		return $cacheFilename;
	}

	/**
	 * Check and sanitize a given interval.
	 *
	 * @param string $interval Day|week|2-weeks|month
	 *
	 * @return string $interval
	 */
	public static function getInterval($interval) {
		$allowedIntervals = array(
			'day',
			'week',
			'2-weeks',
			'month',
		);
		$interval = tx_laterpay_helper_string::sanitizeTextField((string) $interval);

		if (! in_array($interval, $allowedIntervals)) {
			$interval = 'week';
		}

		return $interval;
	}

	/**
	 * Return the endTimestamp by a given startTimestamp and interval.
	 *
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return int $endTimestamp
	 */
	public static function getEndTimestamp($startTimestamp, $interval = 'week') {
		if ($interval === 'week') {
			$endTimestamp = strtotime('-7 days', $startTimestamp);
		} else {
			if ($interval === '2-weeks') {
				$endTimestamp = strtotime('-14 days', $startTimestamp);
			} else {
				if ($interval === 'month') {
					$endTimestamp = strtotime('-30 days', $startTimestamp);
				} else {
					// $interval === 'day'
					$endTimestamp = strtotime('today', $startTimestamp);
				}
			}
		}

		return $endTimestamp;
	}

	/**
	 * Helper function to format the amount in most- / least-items.
	 *
	 * @param mixed $items Array of items
	 * @param int $decimal Count of digits after decimal point
	 *
	 * @return array $items
	 */
	public static function formatAmountValueMostLeastData($items, $decimal = 2) {
		foreach ($items as $key => $item) {
			$item['amount'] = number_format($item['amount'], $decimal);
			$items[$key] 	= $item;
		}

		return $items;
	}

	/**
	 * Return the GROUP BY statement for a given interval.
	 *
	 * @param string $interval Interval (month | day | hour)
	 *
	 * @return string $orderBy
	 */
	public static function getGroupBy($interval) {
		if ($interval === 'day') {
			return 'hour';
		} else {
			if ($interval === 'month') {
				return 'month';
			}
		}

		return 'day';
	}

	/**
	 * Return the ORDER BY statement for a given interval.
	 *
	 * @param string $interval Interval (month | day | hour)
	 *
	 * @return string $orderBy
	 */
	public static function getOrderBy($interval) {
		if ($interval === 'day') {
			return 'hour';
		} else {
			if ($interval === 'month') {
				return 'month';
			}
		}

		return 'date';
	}

	/**
	 * Build the sparkline by given db result with end and start timestamp.
	 *
	 * @param mixed $items Array of items
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array
	 */
	public static function buildSparkline($items, $startTimestamp, $interval = 'week') {
		$sparkline = array();

		if ($interval === 'day') {
			$itemsByHour 	= self::sortItemsByHour($items);
			$items 			= self::fillEmptyHours($itemsByHour, $startTimestamp);
		} else {
			$itemsByDay 	= self::sortItemsByDate($items);
			$days 			= self::getDaysAsArray($startTimestamp, $interval);
			$items 			= self::fillEmptyDays($itemsByDay, $days);
		}

		foreach ($items as $item) {
			$sparkline[] = $item['quantity'];
		}

		return array_reverse($sparkline);
	}

	/**
	 * Helper Function to convert a db result to diagram data.
	 *
	 * @param mixed $items Array of:
	 *        array(
	 *        stdClass Object (
	 *        [quantity] => 3
	 *        [day] => 27
	 *        ),
	 *        ..
	 *        )
	 * @param int $startTimestamp Start time stamp
	 * @param string $interval Interval
	 *
	 * @return array
	 *  Array(
	 *         'x' => [{key}, day-of-week-1],
	 *         'y' => [{key}, kpi-value-1]
	 *         );
	 */
	public static function convertHistoryResultToDiagramData($items, $startTimestamp, $interval = 'week') {
		$data = array(
			'x' => array(),
			'y' => array(),
		);

		if ($interval === 'day') {
			$itemsByHour 	= self::sortItemsByHour($items);
			$items 			= self::fillEmptyHours($itemsByHour, $startTimestamp);
		} else {
			$itemsByDay 	= self::sortItemsByDate($items);
			$days 			= self::getDaysAsArray($startTimestamp, $interval);
			$items 			= self::fillEmptyDays($itemsByDay, $days);
		}

		$key = 1;
		foreach ($items as $item) {
			if ($interval === 'day') {
				$data['x'][] = array(
					$key,
					$item['hour'],
				);

				$data['y'][] = array(
					$key,
					$item['quantity'],
				);
			} else {
				$data['x'][] = array(
					$key,
					date('D', strtotime($item['date'])),
				);

				$data['y'][] = array(
					$key,
					$item['quantity'],
				);
			}

			$key = $key + 1;
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'input' => $items,
				'result' => $data,
			));

		return $data;
	}

	/**
	 * Sort all given items of a db result by date.
	 *
	 * @param mixed $items Array of:
	 *        array(
	 *        stdClass Object (
	 *        [quantity] => 3
	 *        [day] => 27
	 *        [date] => 2014-10-27
	 *        [hour] => 1
	 *        ),
	 *        ..
	 *        )
	 *
	 * @return array $itemsByDate
	 */
	public static function sortItemsByDate($items) {
		if (empty($items)) {
			tx_laterpay_core_logger::getInstance()->warning(__METHOD__ . ' - empty items array');

			return array();
		}

		// sort all items by date
		$itemsByDate = array();
		foreach ($items as $item) {
			$itemsByDate[$item['date']] = $item;
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'input' 	=> $items,
				'output' 	=> $itemsByDate,
			));

		return $itemsByDate;
	}

	/**
	 * Sort all given items of a db result by hour.
	 *
	 * @param mixed $items Array of:
	 *        array(
	 *        stdClass Object (
	 *        [quantity] => 3
	 *        [day] => 27
	 *        [date] => 2014-10-27
	 *        [hour] => 1
	 *        ),
	 *        ..
	 *        )
	 *
	 * @return array $itemsByHour
	 */
	public static function sortItemsByHour($items) {
		if (empty($items)) {
			tx_laterpay_core_logger::getInstance()->warning(__METHOD__ . ' - empty items array');

			return array();
		}

		$itemsByHour = array();
		foreach ($items as $item) {
			$itemsByHour[$item['hour']] = $item;
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'input' 	=> $items,
				'output' 	=> $itemsByHour,
			));

		return $itemsByHour;
	}

	/**
	 * Return an array with all days within the given start and end timestamp.
	 *
	 * @param int $startTimestamp Start time stamp
	 * @param int $interval Interval
	 *
	 * @return array $lastDays
	 */
	public static function getDaysAsArray($startTimestamp, $interval) {
		$lastDays = array();

		if ($interval === 'week') {
			$days = 8;
		} else {
			if ($interval === '2-weeks') {
				$days = 15;
			} else {
				$days = 31;
			}
		}

		for ($i = 0; $i < $days; $i ++) {
			$timestamp 	= strtotime('-' . $i . ' days', $startTimestamp);

			$item 		= array(
				'date' 		=> date('Y-m-d', $timestamp),
				'dayName' 	=> date('D', $timestamp),
			);

			$lastDays[] = $item;
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'end_timestamp' 			=> $startTimestamp,
				'formatted_end_timestamp' 	=> date('Y-m-d', $startTimestamp),
				'interval' 					=> $interval,
				'last_days' 				=> $lastDays,
			));

		return $lastDays;
	}

	/**
	 * Helper function to fill a db result sorted by day with quantity = 0, if the day is missing.
	 *
	 * @param mixed $items Array of items
	 * @param mixed $lastDays Array of last days
	 *
	 * @return array
	 */
	public static function fillEmptyDays($items, $lastDays) {
		foreach ($lastDays as $dayItem) {
			$date = $dayItem->date;

			if (! array_key_exists($date, $items)) {
				$item = array(
					'quantity' 	=> 0,
					'date' 		=> $date,
				);

				$items[$date] = $item;
			}
		}

		ksort($items);

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'items' 	=> $items,
				'last_days' => $lastDays,
			));

		return $items;
	}

	/**
	 * Helper function to fill a db result sorted by hour with quantity = 0, if the hour is missing.
	 *
	 * @param mixed $items Array of items
	 * @param int $startTimestamp Start time stamp
	 *
	 * @return array
	 */
	public static function fillEmptyHours($items, $startTimestamp) {
		$filledItems = array();

		for ($hour = 0; $hour < 24; $hour ++) {
			if (! array_key_exists($hour, $items)) {
				$item = array(
					'hour' 		=> $hour,
					'day' 		=> date('d', $startTimestamp),
					'date' 		=> date('Y-m-d', $startTimestamp),
					'quantity' 	=> 0,
				);
			} else {
				$item = $items[$hour];
			}

			$filledItems[$hour] = $item;
		}

		tx_laterpay_core_logger::getInstance()->info(__METHOD__,
			array(
				'input' 	=> $items,
				'output' 	=> $filledItems,
			));

		return $filledItems;
	}

	/**
	 * Prepare params for time passes graph.
	 *
	 * @param mixed $passId Pass id
	 *
	 * @return array
	 */
	public static function timePassExpiryDiagram($passId) {
		$data = array(
			'x' => array(),
			'y' => array(),
		);

		$expiry = tx_laterpay_helper_timepass::getTimePassExpiryByWeeks($passId, self::TIME_PASSES_WEEKS);

		// add expiry data for the given number of weeks
		$key = 0;
		while ($key <= self::TIME_PASSES_WEEKS) {
			$data['x'][] = array(
				$key,
				(string) $key,
			);

			$data['y'][] = array(
				$key,
				$expiry[$key],
			);

			$key ++;
		}

		return $data;
	}
}
