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
 * LaterPay payment history model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_payment_history extends tx_laterpay_model_query_abstract {

	protected $table = 'tt_laterpay_payment_history';
	protected $joinTable = 'tt_content';

	/**
	 * Contains the join-args to get the postTitle
	 *
	 * @var array
	 */
	protected $postJoin = array();

	/**
	 * Constructor for class tx_laterpay_payments_history_model, load table names.
	 */
	public function __construct() {
		parent::__construct();
		$this->postJoin = array(
			array(
				'type' => 'INNER',
				'fields' => array(
					'header'
				),
				'table' => $this->joinTable,
				'on' => array(
					'field' => 'uid',
					'join_field' => 'post_id',
					'compare' => '='
				)
			)
		);
	}

	/**
	 * Save payment to payment history.
	 *
	 * @param mixed $data Array of payment data
	 *
	 * @return void
	 */
	public function setPaymentHistory($data) {
		if (get_option('laterpay_plugin_is_in_live_mode')) {
			$mode = 'live';
		} else {
			$mode = 'test';
		}
		$queryParams = array(
			'mode' => $mode,
			'post_id' => $data['post_id'],
			'currency_id' => $data['id_currency'],
			'price' => $data['price'],
			'date' => date('Y-m-d H:i:s', $data['date']),
			'ip' => $data['ip'],
			'hash' => $data['hash'],
			'revenue_model' => $data['revenue_model'],
			'pass_id' => $data['pass_id'],
			'code' => $data['code']
		);
		$payment = $this->getPaymentByHash($mode, $data['hash']);
		if (empty($payment)) {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$this->table,
				$queryParams
			);
			$this->logger->info(__METHOD__,
				array(
					'args' => $queryParams,
					'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
					'results' => $payment
				)
			);
		}
	}

	/**
	 * Get the user statistics.
	 *
	 * @param mixed $args Array of params
	 *
	 * @return array $results
	 */
	public function getUserStats($args = array()) {
		$defaultArgs = array(
			'order_by' => 'quantity',
			'order' => 'DESC',
			'group_by' => 'ip',
			'fields' => array(
				'post_id',
				'ip',
				'COUNT(ip)  AS quantity',
				'SUM(price) AS amount'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getResults($args);
	}

	/**
	 * Get the history.
	 *
	 * @param mixed $args Array of params
	 *
	 * @return array $results
	 */
	public function getHistory($args = array()) {
		$defaultArgs = array(
			'order' => 'ASC',
			'fields' => array(
				'COUNT(*)       AS quantity',
				'DATE(date)     AS date',
				'DAY(date)      AS day',
				'MONTH(date)    AS month',
				'HOUR(date)     AS hour'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getResults($args);
	}

	/**
	 * Get the revenue history.
	 *
	 * @param mixed $args Array of params
	 *
	 * @return array $results
	 */
	public function getRevenueHistory($args = array()) {
		$defaultArgs = array(
			'group_by' => 'currency_id',
			'order' => 'ASC',
			'fields' => array(
				'currency_id',
				'SUM(price)     AS amount',
				'COUNT(*)       AS quantity',
				'DATE(date)     AS date',
				'DAY(date)      AS day',
				'MONTH(date)    AS month',
				'HOUR(date)     AS hour'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getResults($args);
	}

	/**
	 * Get the total history by post id.
	 *
	 * @param int $postId Post id
	 *
	 * @return array history
	 */
	public function getTotalHistoryByPostId($postId) {
		if (get_option('laterpay_plugin_is_in_live_mode')) {
			$mode = 'live';
		} else {
			$mode = 'test';
		}

		$args = array(
			'fields' => array(
				'currency_id',
				'SUM(price) AS sum',
				'COUNT(id)  AS quantity'
			),
			'where' => array(
				'mode' => (string) $mode,
				'post_id' => (int) $postId
			),
			'group_by' => 'currency_id'
		);

		return $this->getResults($args);
	}

	/**
	 * Get today's history by post id.
	 *
	 * @param int $postId Post id
	 *
	 * @return array history
	 */
	public function getTodaysHistoryByPostId($postId) {
		if (get_option('laterpay_plugin_is_in_live_mode')) {
			$mode = 'live';
		} else {
			$mode = 'test';
		}

		$today = strtotime('today GMT');

		$args = array(
			'where' => array(
				'post_id' => (int) $postId,
				'mode' => $mode,
				'date' => array(
					array(
						// end of today
						'before' => tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($today),
						// start of today
						'after' => tx_laterpay_helper_date::getDateQueryAfterStartOfDay($today)
					)

				)
			),
			'group_by' => 'currency_id',
			'fields' => array(
				$this->table . '.currency_id',
				'SUM(' . $this->table . '.price) AS sum',
				'COUNT(' . $this->table . '.id)  AS quantity'
			),
			'join' => $this->postJoin
		);

		return $this->getResults($args);
	}

	/**
	 * Get the posts that generated the least revenue.
	 *
	 * @param mixed $args Array of params
	 * @param int $startTimestamp Start time stamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getLeastRevenueGeneratingPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'group_by' => 'post_id',
			'order_by' => 'amount',
			'order' => 'ASC',
			'fields' => array(
				'post_id',
				'SUM(price) AS amount'
			),
			'limit' => 10,
			'join' => $this->postJoin
		);
		$args = array_merge($defaultArgs, $args);

		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}

		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['post_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);
			$data['amount'] = round($data['amount'], 2);
			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get the posts that generated the most revenue x days back.
	 * Leave end and start timestamp empty to fetch the results without sparkline.
	 *
	 * @param mixed $args Array of params
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getMostRevenueGeneratingPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'group_by' => 'post_id',
			'order_by' => 'amount',
			'order' => 'DESC',
			'fields' => array(
				'post_id',
				'SUM(price) AS amount'
			),
			'limit' => 10,
			'join' => $this->postJoin
		);

		$args = array_merge($defaultArgs, $args);

		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}
		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['post_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);
			$data['amount'] = round($data['amount'], 2);
			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get last 30 days' history by post id.
	 *
	 * @param int $postId Post id
	 *
	 * @return array history
	 */
	public function getLast30DaysHistoryByPostId($postId) {
		$today = strtotime('today GMT');
		$monthAgo = strtotime('-1 month');

		$args = array(
			'fields' => array(
				'currency_id',
				'DATE(date) AS date',
				'SUM(price) AS sum',
				'COUNT(id)  AS quantity'
			),
			'where' => array(
				'mode' => (get_option('laterpay_plugin_is_in_live_mode')) ? 'live' : 'test',
				'post_id' => (int) $postId,
				'date' => array(
					array(
						'before' => tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($today),
						'after' => tx_laterpay_helper_date::getDateQueryAfterStartOfDay($monthAgo)
					)
				)
			),
			'group_by' => 'currency_id, DATE(date)',
			'order_by' => 'currency_id, DATE(date)'
		);

		return $this->getResults($args);
	}

	/**
	 * Get payment by hash.
	 *
	 * @param string $mode Mode (live or test)
	 * @param string $hash Hash for date payment
	 *
	 * @return array payment
	 */
	public function getPaymentByHash($mode, $hash) {
		$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery(
			'id',
			$this->table,
			'mode = :mode AND hash = :hash'
		);
		$queryParams = array(
			':mode' => $mode,
			':hash' => $hash
		);
		$statement->execute($queryParams);
		$row = $statement->fetch(t3lib_db_PreparedStatement::FETCH_ASSOC);
		$statement->free();
		$this->logger->info(__METHOD__,
			array(
					'args' => $queryParams,
					'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
					'results' => $row
			)
		);
		return $row;
	}

	/**
	 * Get number of purchased items.
	 *
	 * @param mixed $args Array of params
	 *
	 * @return array $result
	 */
	public function getTotalItemsSold($args = array()) {
		$defaultArgs = array(
			'fields' => array(
				'COUNT(id) AS quantity'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getRow($args);
	}

	/**
	 * Get the sum of the prices of the purchased items.
	 *
	 * @param mixed $args Array of params
	 *
	 * @return array $result
	 */
	public function getTotalRevenueItems($args = array()) {
		$defaultArgs = array(
			'fields' => array(
				'SUM(price) AS amount'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getRow($args);
	}

	/**
	 * Get the most sold posts x days back.
	 * By default with max. 10 posts.
	 * Leave end- and start timestamp empty to fetch the results without sparkline.
	 *
	 * @param mixed $args Array of params
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getBestSellingPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'fields' => array(
				'post_id',
				'COUNT(*) AS amount'
			),
			'group_by' => 'post_id',
			'order_by' => 'amount',
			'order' => 'DESC',
			'limit' => 10,
			'join' => $this->postJoin
		);
		$args = array_merge($defaultArgs, $args);

		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}

		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['post_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);
			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get the least sold posts x days back.
	 * By default with max. 10 posts.
	 * Leave end- and start timestamp empty to fetch the results without sparkline.
	 *
	 * @param mixed $args Array of params
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getLeastSellingPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'fields' => array(
				'post_id',
				'COUNT(*)   AS amount'
			),
			'group_by' => 'post_id',
			'order_by' => 'amount',
			'order' => 'ASC',
			'limit' => 10,
			'join' => $this->postJoin
		);
		$args = array_merge($defaultArgs, $args);

		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}

		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['post_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);
			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get sparkline data for the given $postId for x days back.
	 *
	 * @param int $postId Post id
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $sparkline
	 */
	public function getSparkline($postId, $startTimestamp, $interval) {
		$endTimestamp = tx_laterpay_helper_dashboard::getEndTimestamp($startTimestamp, $interval);

		$args = array(
			'fields' => array(
				'DAY(date)  AS day',
				'MONTH(date) AS month',
				'DATE(date) AS date',
				'HOUR(date) AS hour',
				'COUNT(*)   AS quantity'
			),
			'where' => array(
				'date' => array(
					array(
						'after' => tx_laterpay_helper_date::getDateQueryAfterStartOfDay($endTimestamp),
						'before' => tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($startTimestamp)
					)
				),
				'post_id' => (int) $postId
			),
			'group_by' => 'DAY(date)',
			'order_by' => 'DATE(date)'
		);

		if ($interval === 'day') {
			$args['group_by'] = 'HOUR(date)';
			$args['order_by'] = 'HOUR(date)';
		}

		$results = $this->getResults($args);
		return tx_laterpay_helper_dashboard::buildSparkline($results, $startTimestamp, $interval);
	}

	/**
	 * Get time pass history
	 *
	 * @param mixed $passId Pass id
	 *
	 * @return mixed
	 */
	public function getTimePassHistory($passId = NULL) {
		$paramValues = array(':live' => 'live');
		$where = 'mode = :live';
		if ($passId) {
			$sql .= ' AND pass_id = :pass_id';
			$paramValues[':pass_id'] = $passId;
		} else {
			$sql .= ' AND pass_id <> 0';
		}

		$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery(
			'pass_id, price, date, code', $this->table,
			'', 'date ASC', '', $paramValues
		);
		$statement->execute();
		$results = $statement->fetchAll(t3lib_db_PreparedStatement::FETCH_ASSOC);
		$statement->free();
		$this->logger->info(__METHOD__,
			array(
				'args' => $paramValues,
				'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
				'results' => $results
			)
		);

		return $results;
	}
}
