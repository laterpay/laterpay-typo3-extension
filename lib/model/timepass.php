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
 * LaterPay time pass model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_timepass {

	/**
	 * Name of PostViews table.
	 *
	 * @var string
	 *
	 * @access public
	 */
	public $passesTable;

	/**
	 * Constructor for class LaterPay_Model_TimePass, load table name.
	 */
	public function __construct() {
		$this->passesTable = 'tt_laterpay_passes';
	}

	/**
	 * Get time pass data.
	 *
	 * @param int $timePassId Time pass id
	 *
	 * @return array $timePass array of time pass data
	 */
	public function getPassData($timePassId) {
		$timePass = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pass_id, duration, period, access_to, ' .
			'access_category, price, revenue_model, title, description',
			$this->passesTable, 'pass_id = ' . (int)$timePassId
		);

		return $timePass;
	}

	/**
	 * Update or create new time pass.
	 *
	 * @param array $data Payment data
	 *
	 * @return array $data Array of saved/updated time pass data
	 */
	public function updateTimePass(array $data) {
		$db = new t3lib_db();
		// leave only the required keys
		$data = array_intersect_key($data, tx_laterpay_helper_timepass::getDefaultOptions());

		// fill values that weren't set from defaults
		$data = array_merge(tx_laterpay_helper_timepass::getDefaultOptions(), $data);

		// pass_id is a primary key, set by autoincrement
		$timePassId = $data['pass_id'];
		unset($data['pass_id']);

		if (empty($timePassId)) {
			$q = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->passesTable, $data);
			$data['pass_id'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
		} else {
			$q = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->passesTable, 'pass_id = ' . (int)$timePassId, $data);
			$data['pass_id'] = $timePassId;
		}

		return $data;
	}

	/**
	 * Get all time passes.
	 *
	 * @return array $timePasses list of time passes
	 */
	public function getAllTimePasses() {
// 		$db = new t3lib_db();
		$timePasses = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pass_id, duration, period, access_to, ' .
			'access_category, price, revenue_model, title, description',
			$this->passesTable, '', '', 'title'
		);
		return $timePasses;
	}

	/**
	 * Get all time passes that apply to a given post by its category ids.
	 *
	 * @param null|array $termIds Array of category ids
	 * @param bool $exclude Categories to be excluded from the list
	 *
	 * @return array $timePasses list of time passes
	 */
	public function getTimePassesByCategoryIds($termIds = NULL, $exclude = NULL) {
		$where = '';
		if ($termIds) {
			$preparedIds = implode(',', $termIds);
			if ($exclude) {
				$where .= ' pt.access_category NOT IN ( ' . $preparedIds . ' ) AND pt.access_to = 1';
			} else {
				$where .= ' pt.access_category IN ( ' . $preparedIds . ' ) AND pt.access_to <> 1';
			}
			$where .= ' OR ';
		}
		$where .= 'pt.access_to = 0';
		$timePasses = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pass_id, duration, period, access_to, ' .
			'access_category, price, revenue_model, title, description',
			$this->passesTable . ' as pt', '', 'pt.access_to DESC, pt.price ASC'
		);

		return $timePasses;
	}

	/**
	 * Delete time pass by id.
	 *
	 * @param int $timePassId Time pass id
	 *
	 * @return int|false the number of rows updated, or false on error
	 */
	public function deleteTimePassById($timePassId) {
		$success = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->passesTable, 'pass_id = ' . (int) $timePassId);
		return $success;
	}

	/**
	 * Get count of existing time passes.
	 *
	 * @return int number of defined time passes
	 */
	public function getTimePassesCount() {
		$list = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('count(*) AS c_passes',
			$this->passesTable
		);
		return $list['c_passes'];
	}
}
