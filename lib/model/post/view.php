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
 * LaterPay post views model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_post_view extends tx_laterpay_model_query_abstract {

	/**
	 * Contains the join args to get the postTitle.
	 *
	 * @var array
	 */
	protected $postJoin = array();

	/**
	 * Name of PostViews table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * {@inhertidoc}
	 */
	protected $tableShort = 'wplpv';

	protected $joinTable = 'tt_content';

	/**
	 * Constructor for class tx_laterpay_post_views_model, load table name.
	 */
	public function __construct() {
		parent::__construct();
		$this->table = 'tt_laterpay_content_views';

		$this->postJoin = array(
			array(
				'type' => 'INNER',
				'fields' => array(
					'header as post_title'
				),
				'table' => $this->joinTable,
				'on' => array(
					'field' => 'uid',
					'join_field' => 'content_id',
					'compare' => '='
				)
			)
		);
	}

	/**
	 * Add the 'date' column to the allowed columns.
	 *
	 * @param mixed $columns Array of columns
	 *
	 * @return array $columns
	 */
	public function addDateQueryColumn($columns) {
		$columns[] = 'date';
		$columns[] = $this->table . '.date';

		return $columns;
	}

	/**
	 * Get post views.
	 *
	 * @param int $postId Post id
	 *
	 * @return array views
	 */
	public function getPostViewData($postId) {
		$where = array(
			'post_id' => (int) $postId
		);

		return $this->getResults($where);
	}

	/**
	 * Add new view into history.
	 *
	 * @param mixed $data view data
	 *
	 * @return void
	 */
	public function updateContentViews($data) {

		$queryParams = array(
			'content_id' => $data['content_id'],
			'ip' => $data['ip'],
			'date' => date('Y-m-d H:i:s'),
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$this->table,
			$queryParams
		);
		$this->logger->info(__METHOD__,
			array(
				'args' => $queryParams,
				'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
				'results' => ''
			)
		);
	}

	/**
	 * Get the history.
	 *
	 * @param mixed $args Array of arguments
	 *
	 * @return array $results
	 */
	public function getHistory($args = array()) {

		if (!is_array($args)) {
			$args = array();
		}

		$defaultArgs = array(
			'order' => 'ASC',
			'fields' => array(
				'count(*)     AS quantity',
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
	 * Get last 30 days' history by post id.
	 *
	 * @param int $postId Post id
	 *
	 * @return array $results
	 */
	public function getLast30DaysHistory($postId) {
		$today = strtotime('today GMT');
		$monthAgo = strtotime('-1 month');

		$args = array(
			'where' => array(
				'post_id' => (int) $postId,
				'date' => array(
					array(
							// end of today
						'before' => tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($today),
						'after' => tx_laterpay_helper_date::getDateQueryAfterStartOfDay($monthAgo)
					)
				)
			),
			'order_by' => 'DATE(date)',
			'order' => 'ASC',
			'group_by' => 'DATE(date)',
			'fields' => array(
				'DATE(date) AS date',
				'COUNT(*) as quantity'
			)
		);

		return $this->getResults($args);
	}

	/**
	 * Get number of page views of posts that are purchasable.
	 *
	 * @param mixed $args Array of arguemnts
	 *
	 * @return array $result
	 */
	public function getTotalPostImpression($args = array()) {
		$defaultArgs = array(
			'fields' => array(
				'COUNT(*) AS quantity'
			)
		);
		$args = array_merge($defaultArgs, $args);

		return $this->getRow($args);
	}

	/**
	 * Get most viewed posts x days back.
	 * By default top 10 posts.
	 * Leave end- and start-timestamp empty to fetch the results without sparkline.
	 *
	 * @param mixed $args Array of arguments
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getMostViewedPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'fields' => array(
				'content_id',
				'COUNT(*) AS quantity'
			),
			'group_by' => 'content_id',
			'order_by' => 'quantity',
			'order' => 'DESC',
			'limit' => 10,
			'join' => $this->postJoin
		);
		$args = array_merge($defaultArgs, $args);

		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}

		// fetch the total count of post views
		$totalQuantity = $this->getTotalPostImpression(array(
			'where' => $args['where']
		));
		$totalQuantity = $totalQuantity->quantity;

		$this->logger->info(__METHOD__, array(
			'total_quantity' => $totalQuantity
		));

		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['content_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);

			// % amount
			$data['amount'] = $data['quantity'] / $totalQuantity * 100;

			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get least viewed posts x days back.
	 * By default a maximum of 10 posts.
	 * Leave end and start timestamp empty to fetch the results without sparkline.
	 *
	 * @param mixed $args Array of arguemnts
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $results
	 */
	public function getLeastViewedPosts($args = array(), $startTimestamp = NULL, $interval = 'week') {
		$defaultArgs = array(
			'fields' => array(
				'content_id',
				'COUNT(*) AS quantity'
			),
			'group_by' => 'content_id',
			'order_by' => 'quantity',
			'order' => 'ASC',
			'limit' => 10,
			'join' => $this->postJoin
		);

		$args = array_merge($defaultArgs, $args);
		$results = $this->getResults($args);

		if ($startTimestamp === NULL) {
			return $results;
		}

		$totalQuantity = $this->getTotalPostImpression(array(
			'where' => $args['where']
		));
		$totalQuantity = $totalQuantity['quantity'];

		$this->logger->info(__METHOD__, array(
			'total_quantity' => $totalQuantity
		));

		foreach ($results as $key => $data) {
			// the sparkline for the last x days
			$sparkline = $this->getSparkline($data['content_id'], $startTimestamp, $interval);
			$data['sparkline'] = implode(',', $sparkline);

			// % amount
			$data['amount'] = $data['quantity'] / $totalQuantity * 100;

			$results[$key] = $data;
		}

		return $results;
	}

	/**
	 * Get today's history by post id.
	 *
	 * @param int $postId Post id
	 *
	 * @return array history
	 */
	public function getTodaysHistory($postId) {
		$today = strtotime('today GMT');
		$args = array(
			'fields' => array(
				'SUM(count) AS quantity'
			),
			'where' => array(
				'post_id' => (int) $postId,
				'date' => array(
					array(
						// end of today
						'before' => tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($today),
						// start of today
						'after' => tx_laterpay_helper_date::getDateQueryAfterStartOfDay($today)
					)
				)
			),
			'join' => $this->postJoin
		);

		return $this->getResults($args);
	}

	/**
	 * Get sparkline data for the given $postId for x days back.
	 *
	 * @param int $postId Post Id
	 * @param int $startTimestamp Start timestamp
	 * @param string $interval Interval
	 *
	 * @return array $sparkline
	 */
	public function getSparkline($postId, $startTimestamp, $interval = 'week') {
		$endTimestamp = tx_laterpay_helper_dashboard::getEndTimestamp($startTimestamp, $interval);

		$args = array(
			'fields' => array(
				'DAY(date)      AS day',
				'MONTH(date)    AS month',
				'DATE(date)     AS date',
				'HOUR(date)     AS hour',
				'COUNT(*)     AS quantity'
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
		} else {
			if ($interval === 'month') {
				$args['group_by'] = 'WEEK(date)';
				$args['order_by'] = 'WEEK(date)';
			}
		}
		$results = $this->getResults($args);

		return tx_laterpay_helper_dashboard::buildSparkline($results, $startTimestamp, $interval);
	}
}
