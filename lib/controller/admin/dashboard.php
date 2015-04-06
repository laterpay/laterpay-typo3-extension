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
 * LaterPay dashboard controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_controller_admin_dashboard extends tx_laterpay_controller_abstract {

	/**
	 * Sections are used by the Ajax laterpay_get_dashboard callback.
	 * Every section is mapped to a private method within this controller.
	 *
	 * @var array
	 */
	private $ajaxSections = array(
		'converting_items' 				=> 'convertingItems',
		'selling_items' 				=> 'sellingItems',
		'revenue_items' 				=> 'revenueItems',
		'most_least_converting_items' 	=> 'mostLeastConvertingItems',
		'most_least_selling_items' 		=> 'mostLeastSellingItems',
		'most_least_revenue_items' 		=> 'mostLeastRevenueItems',
		'metrics' 						=> 'metrics',
		'time_passes_expiry' 			=> 'timePassesExpiry',
	);

	private $cacheFileExists;

	private $cacheFileIsBroken;

	private $ajaxNonce = 'laterpay_dashboard';

	/**
	 * Load assets
	 *
	 * @see tx_laterpay_controller_abstract::loadAssets
	 *
	 * @return void
	 */
	public function loadAssets() {
		parent::loadAssets();

		// load page-specific JS
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/vendor/lp_jquery.flot.js');
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/vendor/jquery.peity.min.js');
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('laterpay') . 'res/js/laterpay-backend-dashboard.js');

		$this->logger->info(__METHOD__);

		// pass localized strings and variables to script
		$i18n = array(
			'endingIn' 	=> _x('ending in', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay'),
			'month' 	=> _x('month', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay'),
			'months' 	=> _x('months', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay'),
			'weeksLeft' => _x('weeks left', 'used in wp_localize_script as x-axis label for loadTimePassLifecycles()', 'laterpay'),
			'noData' 	=> __('No data available', 'laterpay'),
			'tooltips' 	=> array(
				'day' 		=> array(
					'next' 		=> __('Show next day', 'laterpay'),
					'prev' 		=> __('Show previous day', 'laterpay'),
				),
				'week' 		=> array(
					'next' 		=> __('Show next 8 days', 'laterpay'),
					'prev' 		=> __('Show previous 8 days', 'laterpay'),
				),
				'2-weeks' 	=> array(
					'next' 		=> __('Show next 2 weeks', 'laterpay'),
					'prev' 		=> __('Show previous 2 weeks', 'laterpay'),
				),
				'month' 	=> array(
					'next' 		=> __('Show next month', 'laterpay'),
					'prev' 		=> __('Show previous month', 'laterpay'),
				)
			)
		);

		// get maximum number of expiring time passes per week across all time passes to scale the y-axis
		// of the timepass diagrams
		$maxYvalue = max(
			tx_laterpay_helper_timepass::getTimePassExpiryByWeeks(
				NULL, tx_laterpay_helper_dashboard::TIME_PASSES_WEEKS
			)
		);

		$this->localizeScript('lpVars',
			array(
				'ajaxUrl' 	=> 'ajax.php?ajaxID=txttlaterpayM1::dashboard',
				'nonces' 	=> array(
					'dashboard' => '',
				),
				'submenu' 	=> array(
					'view' 		=> array(
						'standard' 	=> 'standard-kpis',
						'passes' 	=> 'time-passes',
					)
				),
				'locale' 	=> 'en_US',
				'i18n' 		=> $i18n,
				'maxYValue' => $maxYvalue,
			)
		);

		$this->doc->JScodeArray['ajaxurl'] = 'var ajaxurl = "ajax.php?ajaxID=txttlaterpayM1::dashboard";' . LF;
	}

	/**
	 * Render page.
	 *
	 * @see tx_laterpay_controller_abstract::renderPage
	 *
	 * @return string
	 */
	public function renderPage() {
		$this->loadAssets();

		$postViews = new tx_laterpay_model_post_view();
		$postViewsArgs = array(
			'fields' => array(
				'MIN(date) as end_timestamp'
			)
		);
		$endTimestamp = $postViews->getResults($postViewsArgs);
		$endTimestamp = strtotime($endTimestamp[0]->endTimestamp);

		$viewArgs = array(
			'plugin_is_in_live_mode' 	=> $this->config->get('is_in_live_mode'),
			'top_nav' 					=> $this->getMenu(),
			'admin_menu' 				=> tx_laterpay_helper_view::getAdminMenu(),
			'currency' 					=> get_option('laterpay_currency'),
			'end_timestamp' 			=> $endTimestamp,
			'interval_start' 			=> strtotime('-1 days'),
			'interval_end' 				=> strtotime('-8 days'),
			'cache_file_exists' 		=> $this->cacheFileExists,
			'cache_file_is_broken' 		=> $this->cacheFileIsBroken,
			'passes' 					=> tx_laterpay_helper_timepass::getTimePassesStatistic(),
		);

		$this->assign('laterpay', $viewArgs);

		return $this->render('backend/dashboard');
	}

	/**
	 * Process Ajax requests from account tab.
	 *
	 * @param mixed $params Params of request
	 * @param mixed $ajaxObj TYPO3AJAX instance
	 *
	 * @return void
	 */
	public function processAjaxRequests($params, &$ajaxObj) {
		$postAction = t3lib_div::_POST('action');
		if (! empty($postAction)) {
			switch ($postAction) {
				case 'laterpay_get_dashboard_data':
					$ajaxObj->setContent($this->ajaxGetDashboardData());
					break;

				default:
					$ajaxObj->setContent(
						array(
								'success' => FALSE,
								//'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
						)
					);
			}
		} else {
			$ajaxObj->setContent(
				array(
						'success' => FALSE,
						//'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
				));
		}
	}

	/**
	 * Ajax callback to refresh the dashboard data.
	 *
	 * @wp-hook wpAjaxLaterpayGetDashboardData
	 *
	 * @return array
	 */
	public function ajaxGetDashboardData() {
		$validationResult = $this->validateAjaxSectionCallback();
		if ($validationResult !== TRUE) {
			return $validationResult;
		}

		$options 	= $this->getAjaxRequestOptions(t3lib_div::_POST());
		$section 	= $this->ajaxSections[$options['section']];
		$data 		= $this->$section($options);

		$response = array(
			'data' 		=> $data,
			'success' 	=> TRUE,
		);

		if ($this->config->get('debug_mode')) {
			$response['options'] = $options;
		}

		return $response;
	}

	/**
	 * Callback for wp-cron to refresh today's dashboard data.
	 * The cron job provides two parameters for {x} days back and {n} count of items to
	 * register your own cron with custom parameters to cache data.
	 *
	 * @param int $startTimestamp Start time stamp
	 * @param int $count Count of records
	 * @param string $interval Interval string
	 *
	 * @return void
	 */
	public function refreshDasboardData($startTimestamp = NULL, $count = 10, $interval = 'week') {
		set_time_limit(0);

		if ($startTimestamp === NULL) {
			$startTimestamp = strtotime('today GMT');
		}

		$args = array(
			'start_timestamp' 	=> $startTimestamp,
			'count' 			=> (int) $count,
			'interval' 			=> $interval,
		);

		foreach ($this->ajaxSections as $section) {
			$args['section'] 	= $section;
			$options 			= $this->getAjaxRequestOptions($args);
			$data 				= $this->$section($options);

			$this->logger->info(__METHOD__ . ' - ' . $section, $options);

			tx_laterpay_helper_dashboard::refreshCacheData($options, $data);
		}
	}

	/**
	 * Internal function to load the conversion data as diagram.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function convertingItems($options) {
		$postViewsModel = new tx_laterpay_model_post_view();
		$convertingItems = $postViewsModel->getHistory($options['query_args'], $options['interval']);

		$historyModel = new tx_laterpay_model_payment_history();

		if ($options['revenue_model'] !== 'all') {
			$options['query_args']['where']['revenue_model'] = $options['revenue_model'];
		}

		$sellingItems = $historyModel->getHistory($options['query_args'], $options['interval']);

		if ($options['interval'] === 'day') {
			$convertingItems 	= tx_laterpay_helper_dashboard::sortItemsByHour($convertingItems);
			$convertingItems 	= tx_laterpay_helper_dashboard::fillEmptyHours($convertingItems, $options['start_timestamp']);

			$sellingItems 		= tx_laterpay_helper_dashboard::sortItemsByHour($sellingItems);
			$sellingItems 		= tx_laterpay_helper_dashboard::fillEmptyHours($sellingItems, $options['start_timestamp']);
		} else {
			$days 				= tx_laterpay_helper_dashboard::getDaysAsArray($options['start_timestamp'], $options['interval']);

			$convertingItems 	= tx_laterpay_helper_dashboard::sortItemsByDate($convertingItems);
			$convertingItems 	= tx_laterpay_helper_dashboard::fillEmptyDays($convertingItems, $days);

			$sellingItems 		= tx_laterpay_helper_dashboard::sortItemsByDate($sellingItems);
			$sellingItems 		= tx_laterpay_helper_dashboard::fillEmptyDays($sellingItems, $days);
		}

		$diagramData = array();
		foreach ($convertingItems as $date => $convertingItem) {
			$sellingItem 	= $sellingItems[$date];
			$data 			= $convertingItem;

			if ($convertingItem->quantity == 0) {
				$data->quantity = 0;
			} else {
				// purchases on {date|hour} / views on {date|hour} * 100
				$data->quantity = $sellingItem->quantity / $convertingItem->quantity * 100;
			}

			$diagramData[$date] = $data;
		}

		$convertedDiagramData = tx_laterpay_helper_dashboard::convertHistoryResultToDiagramData($diagramData,
			$options['start_timestamp'], $options['interval']);

		$context = array(
			'options' 					=> $options,
			'converting_items' 			=> $convertingItems,
			'selling' 					=> $sellingItems,
			'diagram_data' 				=> $diagramData,
			'converted_diagram_data' 	=> $convertedDiagramData,
		);

		$this->logger->info(__METHOD__, $context);

		return $convertedDiagramData;
	}

	/**
	 * Internal function to load the sales data as diagram.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function sellingItems($options) {
		$historyModel = new tx_laterpay_model_payment_history();

		if ($options['revenue_model'] !== 'all') {
			$options['query_args']['where']['revenue_model'] = $options['revenue_model'];
		}

		$sellingItems = $historyModel->getHistory($options['query_args']);
		$data = tx_laterpay_helper_dashboard::convertHistoryResultToDiagramData($sellingItems, $options['start_timestamp'],
			$options['interval']);

		$this->logger->info(__METHOD__,
			array(
				'options' => $options,
				'data' => $data,
			));

		return $data;
	}

	/**
	 * Internal function to load the revenue data items as diagram.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function revenueItems($options) {
		$historyModel = new tx_laterpay_model_payment_history();

		if ($options['revenue_model'] !== 'all') {
			$options['query_args']['where']['revenue_model'] = $options['revenue_model'];
		}

		$revenueItem = $historyModel->getRevenueHistory($options['query_args']);
		$data = tx_laterpay_helper_dashboard::convertHistoryResultToDiagramData($revenueItem, $options['start_timestamp'],
			$options['interval']);

		$this->logger->info(__METHOD__,
			array(
				'options' 	=> $options,
				'data' 		=> $data,
			));

		return $data;
	}

	/**
	 * Internal function to load the most / least converting items by given options.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function mostLeastConvertingItems($options) {
		$postViewsModel = new tx_laterpay_model_post_view();

		$most 			= $postViewsModel->getMostViewedPosts($options['most_least_query'], $options['start_timestamp'],
							$options['interval']);
		$least 			= $postViewsModel->getLeastViewedPosts($options['most_least_query'], $options['start_timestamp'],
							$options['interval']);

		$data 			= array(
							'most' 	=> tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($most, 1),
							'least' => tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($least, 1),
							'unit' 	=> '%',
						);

		$this->logger->info(__METHOD__,
			array(
				'options' 	=> $options,
				'data' 		=> $data,
			));

		return $data;
	}

	/**
	 * Internal function to load the most / least selling items by given options.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function mostLeastSellingItems($options) {
		$historyModel = new tx_laterpay_model_payment_history();

		if ($options['revenue_model'] !== 'all') {
			$options['query_args']['where']['revenue_model'] = $options['revenue_model'];
		}

		$most 	= $historyModel->getBestSellingPosts($options['most_least_query'], $options['start_timestamp'],
					$options['interval']);
		$least 	= $historyModel->getLeastSellingPosts($options['most_least_query'], $options['start_timestamp'],
					$options['interval']);

		$data 	= array(
					'most' 	=> tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($most, 0),
					'least' => tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($least, 0),
					'unit' 	=> '',
				);

		$this->logger->info(__METHOD__,
			array(
				'options' 	=> $options,
				'data' 		=> $data,
			));

		return $data;
	}

	/**
	 * Internal function to load the most / least revenue generating items by given options.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function mostLeastRevenueItems($options) {
		$historyModel = new tx_laterpay_model_payment_history();

		if ($options['revenue_model'] !== 'all') {
			$options['query_args']['where']['revenue_model'] = $options['revenue_model'];
		}

		$most 	= $historyModel->getMostRevenueGeneratingPosts($options['most_least_query'], $options['start_timestamp'],
					$options['interval']);
		$least 	= $historyModel->getLeastRevenueGeneratingPosts($options['most_least_query'], $options['start_timestamp'],
					$options['interval']);

		$data 	= array(
			'most' 	=> tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($most, 0),
			'least' => tx_laterpay_helper_dashboard::formatAmountValueMostLeastData($least, 0),
			'unit' 	=> get_option('laterpay_currency'),
		);

		$this->logger->info(__METHOD__,
			array(
				'options' 	=> $options,
				'data' 		=> $data,
			));

		return $data;
	}

	/**
	 * Internal function to load the expiring time passes as diagram.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function timePassesExpiry($options) {
		$timePassExpiryDiagram = tx_laterpay_helper_dashboard::timePassExpiryDiagram($options['pass_id']);

		return $timePassExpiryDiagram;
	}

	/**
	 * Internal function to load KPIs by given options.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $data
	 */
	private function metrics($options) {
		$postArgs = array(
			'where' => $options['query_where'],
		);

		$historyArgs = $postArgs;
		if ($options['revenue_model'] !== 'all') {
			$historyArgs['where']['revenue_model'] = $options['revenue_model'];
		}

		$historyModel 	= new tx_laterpay_model_payment_history();
		$postViewsModel = new tx_laterpay_model_post_view();

		// get the user stats for the given parameters
		$userStats 		= $historyModel->getUserStats($historyArgs);
		$totalCustomers = count($userStats);
		$newCustomers 	= 0;

		foreach ($userStats as $stat) {
			if ((int) $stat->quantity === 1) {
				$newCustomers += 1;
			}
		}

		if ($totalCustomers > 0) {
			$newCustomers = $newCustomers * 100 / $totalCustomers;
		}

		$totalItemsSold = $historyModel->getTotalItemsSold($historyArgs);
		$totalItemsSold = $totalItemsSold->quantity;

		$impressions = $postViewsModel->getTotalPostImpression($postArgs);
		$impressions = $impressions->quantity;

		$totalRevenueItems = $historyModel->getTotalRevenueItems($historyArgs);
		$totalRevenueItems = $totalRevenueItems->amount;

		$avgPurchase = 0;
		if ($totalItemsSold > 0) {
			$avgPurchase = $totalRevenueItems / $totalItemsSold;
		}

		$conversion = 0;
		if ($impressions > 0) {
			$conversion = ($totalItemsSold / $impressions) * 100;
		}

		$avgItemsSold = 0;
		if ($totalItemsSold > 0) {
			if ($options['interval'] === 'week') {
				$diff = 7;
			} else {
				if ($options['interval'] === '2-weeks') {
					$diff = 14;
				} else {
					if ($options['interval'] === 'month') {
						$diff = 30;
					} else {
						// hour
						$diff = 24;
					}
				}
			}

			$avgItemsSold = $totalItemsSold / $diff;
		}

		$data = array(
			// column 1 - conversion metrics
			'impressions' 		=> tx_laterpay_helper_view::formatNumber($impressions, FALSE),
			'conversion' 		=> number_format($conversion, 1),
			'new_customers' 	=> number_format($newCustomers, 0),

			// column 2 - sales metrics
			'avg_items_sold' 	=> number_format($avgItemsSold, 1),
			'total_items_sold' 	=> tx_laterpay_helper_view::formatNumber($totalItemsSold, FALSE),

			// column 3 - revenue metrics
			'avg_purchase' 		=> number_format($avgPurchase, 2),
			'total_revenue' 	=> tx_laterpay_helper_view::formatNumber($totalRevenueItems),
		);

		$this->logger->info(__METHOD__,
			array(
				'options' 	=> $options,
				'data' 		=> $data,
			));

		return $data;
	}

	/**
	 * Internal function to add the query options to the options array.
	 *
	 * @param mixed $options Array of options
	 *
	 * @return array $options
	 */
	private function getQueryOptions($options) {
		$endTimestamp = tx_laterpay_helper_dashboard::getEndTimestamp($options['start_timestamp'], $options['interval']);
		$where = array(
			'date' => array(
				array(
					'before' 	=> tx_laterpay_helper_date::getDateQueryBeforeEndOfDay($options['start_timestamp']),
					'after' 	=> tx_laterpay_helper_date::getDateQueryAfterStartOfDay($endTimestamp),
				)
			)
		);

		// add the query options to the options array
		$options['query_args'] = array(
			'order_by' 	=> tx_laterpay_helper_dashboard::getOrderBy($options['interval']),
			'group_by' 	=> tx_laterpay_helper_dashboard::getGroupBy($options['interval']),
			'where' 	=> $where,
		);

		$options['most_least_query'] = array(
			'where' => $where,
			'limit' => $options['count']
		);

		$options['query_where'] = $where;

		return $options;
	}

	/**
	 * Internal function to convert the $_POST request vars to an options array for the Ajax callbacks.
	 *
	 * @param mixed $postArgs Array of arguments
	 *
	 * @return array $options
	 */
	private function getAjaxRequestOptions($postArgs = array()) {
		$interval = 'week';
		if (isset($postArgs['interval'])) {
			$interval = tx_laterpay_helper_dashboard::getInterval($postArgs['interval']);
		}

		$count = 10;
		if (isset($postArgs['count'])) {
			$count = toabsint($postArgs['count']);
		}

		$revenueModel = 'all';
		if (isset($postArgs['revenue_model']) && in_array($postArgs['revenue_model'], array(
			'ppu',
			'sis',
		))) {
			$revenueModel = $postArgs['revenue_model'];
		}

		$startTimestamp = strtotime('yesterday GMT');
		if (isset($postArgs['start_timestamp'])) {
			$startTimestamp = $postArgs['start_timestamp'];
		}

		$refresh = FALSE;
		if (isset($postArgs['refresh'])) {
			$refresh = (bool) $postArgs['refresh'];
		}

		$passId = 0;
		if (isset($postArgs['pass_id'])) {
			$passId = (int) $postArgs['pass_id'];
		}

		$section = (string) $postArgs['section'];

		// initial options
		$options = array(
			// request data
			'start_timestamp' 	=> $startTimestamp,
			'interval' 			=> $interval,
			'count' 			=> $count,
			'section' 			=> $section,
			'revenue_model' 	=> $revenueModel,
			'pass_id' 			=> $passId,
		);

		$cacheDir 		= tx_laterpay_helper_dashboard::getCacheDir($startTimestamp);
		$cacheFilename 	= tx_laterpay_helper_dashboard::getCacheFilename($options);
		if ($refresh || ! file_exists($cacheDir . $cacheFilename)) {
			// refresh the cache, if refresh == false and the file doesn't exist
			$refresh = TRUE;
		}

		// cache data
		$options['refresh'] 		= $refresh;
		$options['cache_filename'] 	= $cacheFilename;
		$options['cache_dir'] 		= $cacheDir;
		$options['cache_file_path'] = $cacheDir . $cacheFilename;

		$options = $this->getQueryOptions($options);

		return $options;
	}

	/**
	 * Internal function to check the section parameter on Ajax requests.
	 *
	 * @return array | bool
	 */
	private function validateAjaxSectionCallback() {
		$fResult 	= TRUE;
		$post 		= t3lib_div::_POST();

		if (! isset($post['section'])) {
			$fResult = array(
				'message' 	=> __('Error, missing section on request', 'laterpay'),
				'step' 		=> 3,
			);
		} elseif (! array_key_exists($post['section'], $this->ajaxSections)) {
			$fResult = array(
				'message' 	=> sprintf(__('Section is not allowed <code>%s</code>', 'laterpay'), $post['section']),
				'step' 		=> 4,
			);
		} elseif (! method_exists($this, $this->ajaxSections[$post['section']])) {
			$fResult = array(
				'message' 	=> sprintf(__('Invalid section <code>%s</code>', 'laterpay'), $post['section']),
				'step' 		=> 4,
			);
		}
		return $fResult;
	}

}
