<?php
/**
 * LaterPay content model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_model_content {

	/**
	 * Name of PostViews table.
	 *
	 * @var string
	 *
	 * @access public
	 */
	public static $contentTable = 'tt_content';


	/**
	 * Get content block.
	 *
	 * @param int $contentId content block 
	 *
	 * @return array $contentBlock array of time pass data
	 */
	public static function getContentData($contentId) {
		$data = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', self::$contentTable, 'uid = ' . (int)$contentId);

		return $data;
	}

	/**
	 * Update content table
	 * 
	 * @param type $contentId
	 * 
	 * @return type
	 */
	public static function updateContentData($contentId, $fieldsToUpdate) {
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(self::$contentTable, 'uid = ' . (int)$contentId, $fieldsToUpdate);

		return mysql_affected_rows($res);
	}

}
