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
 * LaterPay query helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_query_abstract {

	/**
	 * Query arguments
	 * @var array
	 */
	protected $queryArgs = array();

	/**
	 * Table name
	 * @var string
	 */
	protected $table = '';

	/**
	 * Short alias for table
	 * @var string
	 */
	protected $tableShort = '';

	/**
	 * Contains array of :param => value
	 *
	 * @var array
	 */
	protected $whereParamValues = array();

	/**
	 * Constructor of class
	 */
	public function __construct() {
		// assign the config to the views
		$this->config = tx_laterpay_config::getInstance();
		$this->logger = tx_laterpay_core_logger::getInstance();
	}

	/**
	 * Build from part of query
	 *
	 * @return string $sql
	 */
	public function buildFrom() {
		$sql = ' FROM ' . $this->table;
		if ($this->tableShort !== '') {
			$sql .= ' AS ' . $this->tableShort;
		}

		return $sql;
	}

	/**
	 * Get row suffix
	 *
	 * @return string $suffix
	 */
	public function getRowSuffix() {
		$suffix = '';
		if (! empty($this->shortFrom)) {
			$suffix = $this->shortFrom . '.';
		} else {
			if (! empty($this->from)) {
				$suffix = $this->from . '.';
			}
		}
		return $suffix;
	}

	/**
	 * Build a INNER/LEFT/RIGHT JOIN clause to a query.
	 *
	 * @param mixed $joins Array of data:
	 *        	array(
	 *        	array(
	 *        	'type' => 'INNER',
	 *        	'fields'=> array(),
	 *        	'table' => '',
	 *        	'on' => array(
	 *        	'field' => '',
	 *        	'join_field'=> '',
	 *        	'compare' => '='
	 *        	)
	 *        	)
	 *        	...
	 *        	)
	 *
	 * @return string $sql
	 */
	public function buildJoin($joins) {
		$sql = '';

		if (empty($joins)) {
			return $sql;
		}

		foreach ($joins as $index => $join) {
			if (! is_array($join)) {
				continue;
			}

			$table = $join['table'] . '_' . $index;

			$sql .= ' ' . strtoupper($join['type']) . ' JOIN ' . $join['table'] . ' AS ' . $table;
			$sql .= $this->buildJoinOn($join, $table);

			$this->queryArgs['fields'] = array_merge($this->buildJoinFields($join, $table), $this->queryArgs['fields']);
		}

		return $sql;
	}

	/**
	 * Builds the join "ON"-Statement.
	 *
	 * @param mixed $join Array of fields for join
	 * @param string $table Name of table for join
	 *
	 * @return string $sql
	 */
	protected function buildJoinOn($join, $table) {
		$field1 = $table . '.' . $join['on']['field'];
		$compare = $join['on']['compare'];
		$field2 = ($this->tableShort !== '') ? $this->tableShort : $this->table;
		$field2 .= '.' . $join['on']['join_field'];

		return ' ON ' . $field1 . ' ' . $compare . ' ' . $field2;
	}

	/**
	 * Builds the join fields with table-prefix.
	 *
	 * @param mixed $join Array if fields for join
	 * @param string $table Name of table for join
	 *
	 * @return array $fields
	 */
	protected function buildJoinFields($join, $table) {
		$fields = array();
		if (empty($join['fields'])) {
			$fields[] = $table . '.*';
		} else {
			foreach ($join['fields'] as $field) {
				$fields[] = $table . '.' . $field;
			}
		}
		return $fields;
	}

	/**
	 * Build a LIMIT clause to a query.
	 *
	 * @param int $limit Limit for select
	 *
	 * @return string $sql
	 */
	public static function buildLimit($limit) {
		if (empty($limit)) {
			return '';
		}

		return ' LIMIT ' . tx_laterpay_helper_string::toAbsInt($limit) . ' ';
	}

	/**
	 * Build a ORDER BY clause to a query.
	 *
	 * @param string $orderBy Order by
	 * @param string $order Order (Asc | Dec)
	 *
	 * @return string $sql
	 */
	public function buildOrderBy($orderBy, $order = 'ASC') {
		if (empty($orderBy)) {
			return '';
		}
		$sql = ' ORDER BY ' . $this->getRowSuffix() . $orderBy;
		if (! in_array($order, array(
			'ASC',
			'DESC'
		))) {
			$order = 'ASC';
		}

		return $sql . ' ' . $order . ' ';
	}

	/**
	 * Build a GROUP BY clause to a query.
	 *
	 * @param string $group Name of group
	 *
	 * @return string $sql
	 */
	public function buildGroupBy($group) {
		if (empty($group)) {
			return '';
		}

		return ' GROUP BY ' . $group;
	}

	/**
	 * Build a SELECT clause to a query.
	 *
	 * @param mixed $fields Array of fields for select
	 *
	 * @return string $sql
	 */
	public function buildSelect($fields = array()) {
		if (empty($fields)) {
			return ' SELECT * ';
		}

		return ' SELECT ' . implode(', ', $fields);
	}

	/**
	 * Convert date to string
	 *
	 * @param mixed $datetime Array of data parts
	 *
	 * @return string
	 */
	protected function dateTimeToStr($datetime) {
		$datetime = array_map( 'tx_laterpay_helper_string::toAbsInt', $datetime );

		if ( ! isset( $datetime['year'] ) ) {
			$datetime['year'] = gmdate( 'Y', $now );
		}
		if ( ! isset( $datetime['month'] ) ) {
			$datetime['month'] = 1;
		}
		if ( ! isset( $datetime['day'] ) ) {
			$datetime['day'] = 1;
		}
		if ( ! isset( $datetime['hour'] ) ) {
			$datetime['hour'] = 0;
		}
		if ( ! isset( $datetime['minute'] ) ) {
			$datetime['minute'] = 0;
		}
		if ( ! isset( $datetime['second'] ) ) {
			$datetime['second'] = 0;
		}
		return sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $datetime['year'], $datetime['month'], $datetime['day'], $datetime['hour'], $datetime['minute'], $datetime['second'] );

	}

	/**
	 * Build a WHERE clause to a query.
	 *
	 * @param mixed $where Array of where data
	 *
	 * @return string $sql
	 */
	public function buildWhere($where = array()) {
		$sql = '';

		$this->whereParamValues = array();

		foreach ($where as $key => $value) {
// 			$type = (array_key_exists($key, $this->fieldTypes)) ? $this->fieldTypes[$key] : '%s';
			if (is_array($value)) {
				if (isset($value['after'])) {
					$tmpSql = $this->getRowSuffix() . $key . ' > :' . $key;
					$this->whereParamValues[':' . $key] =  $this->dateTimeToStr($value['after']);
				}
				if (isset($value['before'])) {
					$tmpSql = $this->getRowSuffix() . $key . ' < :' . $key;
					$this->whereParamValues[':' . $key] =  $this->dateTimeToStr($value['before']);
				}
			} else {
				$tmpSql = $this->getRowSuffix() . $key . ' = :' . $key;
				$this->whereParamValues[':' . $key] = $value;
			}
			if (!empty($sql)) {
				$sql .= ' AND ' . $tmpSql;
			}
		}
		return $sql;
	}

	/**
	 * Get the results of a query.
	 *
	 * @param mixed $args Array of arguments for build select
	 *
	 * @return array $results
	 */
	public function getResults($args = array()) {
		$statement = $this->createQuery($args);
		$statement->execute();
		$results = $statement->fetchAll(t3lib_db_PreparedStatement::FETCH_ASSOC);
		$statement->free();
		$this->logger->info(__METHOD__,
			array(
				'args' => $this->queryArgs,
				'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
				'results' => $results
			)
		);

		return $results;
	}

	/**
	 * Get a single row-result of a query.
	 *
	 * @param mixed $args Array of arguments
	 *
	 * @return array $result
	 */
	public function getRow($args = array()) {
		$statement = $this->createQuery($args);
		$statement->execute();
		$results = $statement->fetch(t3lib_db_PreparedStatement::FETCH_ASSOC);
		$statement->free();

		$this->logger->info(__METHOD__,
			array(
				'args' => $this->queryArgs,
				'query' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
				'results' => $results
			));

		return $results;
	}

	/**
	 * Create a query.
	 *
	 * @param mixed $args Array of arguments for build of select query
	 *
	 * @return string $query
	 */
	public function createQuery($args = array()) {
		$defaultArgs = array(
			'fields' => array(
				'*'
			),
			'limit' => '',
			'group_by' => '',
			'order_by' => '',
			'order' => '',
			'join' => array(),
			'where' => array()
		);

		$this->queryArgs = array_merge($defaultArgs, $args);

		$join = $this->buildJoin($this->queryArgs['join']);

		$where = $this->buildWhere($this->queryArgs['where']);
		$from = $this->buildFrom();
		$select = $this->buildSelect($this->queryArgs['fields']);
		$group = $this->buildGroupBy($this->queryArgs['group_by']);
		$order = $this->buildOrderBy($this->queryArgs['order_by'], $this->queryArgs['order']);
		$limit = $this->buildLimit($this->queryArgs['limit']);

		$query = '';
		$query .= $select;
		$query .= $from;
		$query .= $join;
		$query .= (strlen($where) > 0 ? ' WHERE ' . $where : '');
		$query .= $group;
		$query .= $order;
		$query .= $limit;

		$preparedStatement = t3lib_div::makeInstance('t3lib_db_PreparedStatement', $query, $this->table, array());
		/* @var $preparedStatement t3lib_db_PreparedStatement */

		// bind values to parameters
		foreach ($this->whereParamValues as $key => $value) {
			$preparedStatement->bindValue($key, $value, t3lib_db_PreparedStatement::PARAM_AUTOTYPE);
		}

		// return prepared statement
		return $preparedStatement;
	}
}
