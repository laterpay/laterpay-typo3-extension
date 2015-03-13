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
//@XXX unused file

// @codingStandardsIgnoreStart

/**
 * LaterPay category price model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_categoryprice {

	/**
	 * Name of terms table.
	 *
	 * @var string
	 *
	 * @access public
	 */
	public $termTable;

	/**
	 * Name of prices table.
	 *
	 * @var string
	 *
	 * @access public
	 */
	public $tablePrices;

	/**
	 * Constructor for class LaterPay_Currency_Model, load table names.
	 */
	function __construct() {
		global $wpdb;
		$this->termTable = $wpdb->terms;
		$this->termTablePrices = $wpdb->prefix . 'laterpay_terms_price';
	}

	/**
	 * Get all categories with a defined category default price.
	 *
	 * @return array categories
	 */
	public function getCategoriesWithDefinedPrice() {
		global $wpdb;
		$sql = "
            SELECT
                tp.id AS id,
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price,
                tp.revenue_model AS revenue_model
            FROM
                {$this->termTable} AS tm
                LEFT JOIN
                    {$this->term_tablePrices} AS tp
                ON
                    tp.term_id = tm.term_id
            WHERE
                tp.term_id IS NOT NULL
            ORDER BY
                name
            ;
        ";
		
		$categories = $wpdb->getResults($sql);
		
		return $categories;
	}

	/**
	 * Get categories with defined category default prices by list of category ids.
	 *
	 * @param array $ids        	
	 *
	 * @return array categoryPriceData
	 */
	public function getCategoryPriceDataByCategoryIds($ids) {
		global $wpdb;
		
		$placeholders = array_fill(0, count($ids), '%d');
		$format = implode(', ', $placeholders);
		$sql = "
            SELECT
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price,
                tp.revenue_model AS revenue_model
            FROM
                {$this->termTable} AS tm
                LEFT JOIN
                    {$this->termTablePrices} AS tp
                ON
                    tp.term_id = tm.term_id
            WHERE
                tm.term_id IN ( {$format} )
                AND tp.term_id IS NOT NULL
            ORDER BY
                name
            ;
        ";
		$categoryPriceData = $wpdb->getResults($wpdb->prepare($sql, $ids));
		
		return $categoryPriceData;
	}

	/**
	 * Get categories without defined category default prices by search term.
	 *
	 * @param array $args
	 *        	query args for getCategories
	 *        	
	 * @return array $categories
	 */
	public function getCategoriesWithoutPriceByTerm($args) {
		$defaultArgs = array(
			'hide_empty' => false,
			'number' => 10
		);
		
		$args = wp_parse_args($args, $defaultArgs);
		
		add_filter('terms_clauses', array(
			$this,
			'filter_terms_clauses_for_categories_without_price'
		));
		$categories = get_categories($args);
		remove_filter('terms_clauses', array(
			$this,
			'filter_terms_clauses_for_categories_without_price'
		));
		
		return $categories;
	}

	/**
	 * Filter for getCategoriesWithoutPriceByTerm(), to load all categories without a price.
	 *
	 * @wp-hook terms_clauses
	 *
	 * @param array $clauses        	
	 *
	 * @return array $clauses
	 */
	public function filterTermsClausesForCategoriesWithoutPrice($clauses) {
		$clauses['join'] .= ' LEFT JOIN ' . $this->termTablePrices . ' AS tp ON tp.term_id = t.term_id ';
		$clauses['where'] .= ' AND tp.term_id IS NULL ';
		
		return $clauses;
	}

	/**
	 * Get categories by search term.
	 *
	 * @param string $term
	 *        	term string to find categories
	 * @param int $limit
	 *        	limit categories
	 *        	
	 * @deprecated please use getTerms( 'category', array( 'name__like' => '$term', 'number' => $limit, 'fields' => 'id=>name' )
	 *             );
	 *            
	 * @return array categories
	 */
	public function getCategoriesByTerm($term, $limit) {
		global $wpdb, $wp_version;
		
		if (version_compare($wp_version, '4.0', '>=')) {
			$term = $wpdb->esc_like($term);
		} else {
			$term = like_escape($term);
		}
		
		$term = esc_sql($term) . '%';
		$sql = "
            SELECT
                tm.term_id AS id,
                tm.name AS text
            FROM
                {$this->termTable} AS tm
            INNER JOIN
                {$wpdb->termTaxonomy} as tt
            ON
                tt.term_id = tm.term_id
            WHERE
                tm.name LIKE %s
            AND
                tt.taxonomy = 'category'
            ORDER BY
                name
            LIMIT
                %d
            ;
        ";
		$categories = $wpdb->get_results($wpdb->prepare($sql, $term, $limit));
		
		return $categories;
	}

	/**
	 * Set category default price.
	 *
	 * @param integer $idCategory
	 *        	id category
	 * @param float $price
	 *        	price for category
	 * @param string $revenueModel
	 *        	revenue model of category
	 * @param integer $id
	 *        	id price for category
	 *        	
	 * @return int|false number of rows affected / selected or false on error
	 */
	public function setCategoryPrice($idCategory, $price, $revenueModel, $id = 0) {
		global $wpdb;
		
		if (! empty($id)) {
			$success = $wpdb->update($this->termTablePrices, 
				array(
					'term_id' => $idCategory,
					'price' => $price,
					'revenue_model' => $revenueModel
				), array(
					'ID' => $id
				), array(
					'%d',
					'%f',
					'%s'
				), array(
					'%d'
				));
		} else {
			$success = $wpdb->insert($this->termTablePrices, 
				array(
					'term_id' => $idCategory,
					'price' => $price,
					'revenue_model' => $revenueModel
				), array(
					'%d',
					'%f',
					'%s'
				));
		}
		
		LaterPay_Helper_Cache::purgeCache();
		return $success;
	}

	/**
	 * Get price id by category id.
	 *
	 * @param integer $id
	 *        	id category
	 *        	
	 * @return integer id price
	 */
	public function getPriceIdByCategoryId($id) {
		global $wpdb;
		
		$sql = "
            SELECT
                id
            FROM
                {$this->termTablePrices}
            WHERE
                term_id = %d
            ;
        ";
		$price = $wpdb->get_row($wpdb->prepare($sql, $id));
		
		if (empty($price)) {
			return null;
		}
		
		return $price->id;
	}

	/**
	 * Get price by category id.
	 *
	 * @param integer $id
	 *        	category id
	 *        	
	 * @return float|null price category
	 */
	public function getPriceByCategoryId($id) {
		global $wpdb;
		
		$sql = "
            SELECT
                price
            FROM
                {$this->termTablePrices}
            WHERE
                term_id = %d
            ;
        ";
		$price = $wpdb->get_row($wpdb->prepare($sql, $id));
		
		if (empty($price)) {
			return null;
		}
		
		return $price->price;
	}

	/**
	 * Get revenue model by category id.
	 *
	 * @param integer $id
	 *        	category id
	 *        	
	 * @return string|null category renevue model
	 */
	public function getRevenueModelByCategoryId($id) {
		global $wpdb;
		
		$sql = "
            SELECT
                revenue_model
            FROM
                {$this->termTablePrices}
            WHERE
                term_id = %d
            ;
        ";
		$revenueModel = $wpdb->get_row($wpdb->prepare($sql, $id));
		
		if (empty($revenueModel)) {
			return null;
		}
		
		return $revenueModel->revenueModel;
	}

	/**
	 * Check, if category exists by getting the category id by category name.
	 *
	 * @param string $name
	 *        	name category
	 *        	
	 * @return integer categoryId
	 */
	public function checkExistenceOfCategoryByName($name) {
		global $wpdb;
		
		$sql = "
            SELECT
                tm.term_id AS id
            FROM
                {$this->termTable} AS tm
                RIGHT JOIN
                    {$this->termTablePrices} AS tp
                ON
                    tm.term_id = tp.term_id
            WHERE
                name = %s
            ;
        ";
		$category = $wpdb->get_row($wpdb->prepare($sql, $name));
		
		if (empty($category)) {
			return null;
		}
		
		return $category->id;
	}

	/**
	 * Delete price by category id.
	 *
	 * @param integer $id
	 *        	category id
	 *        	
	 * @return int|false the number of rows updated, or false on error
	 */
	public function deletePricesByCategoryId($id) {
		global $wpdb;
		
		$where = array(
			'term_id' => (int) $id
		);
		
		$success = $wpdb->delete($this->termTablePrices, $where, '%d');
		LaterPay_Helper_Cache::purgeCache();
		return $success;
	}

	/**
	 * Delete all category prices from table.
	 *
	 * @return int|false the number of rows updated, or false on error
	 */
	public function deleteAllCategoryPrices() {
		global $wpdb;
		
		$success = $wpdb->query("TRUNCATE TABLE " . $this->termTablePrices);
		
		return $success;
	}
}
// @codingStandardsIgnoreEnd