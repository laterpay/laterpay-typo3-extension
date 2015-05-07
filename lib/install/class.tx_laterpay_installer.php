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
 * LaterPay post installer.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_installer {
	/**
	 * Post install hook
	 *
	 * @param mixed $params Some parameters
	 * @param t3lib_tsStyleConfig $styleConfig configuration object
	 *
	 * @return void
	 */
	public function postInstall($params, t3lib_tsStyleConfig $styleConfig) {
		//creating of cli user if needed
		$usersTable = 'be_users';
		$cliUsername = '_cli_scheduler';

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $usersTable, '`username` = "' . $cliUsername . '"');

		if (!$result) {
			$values = array(
				'username' => $cliUsername,
				'pid'      => 0,
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery($usersTable, $values);
		}

		//creating of task if needed

		$tasksTable = 'tx_scheduler_task';
		$classname = 'tx_laterpay_scheduler_clearviewdata';

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $tasksTable, '`classname` = "' . $classname . '"');

		if (!$result) {
			$values = array(
				'classname' => $classname,
				'crdate'    => time(),
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery($tasksTable, $values);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $tasksTable, '`classname` = "' . $classname . '"');
			$tasksObj = new tx_laterpay_scheduler_clearviewdata();
			$tasksObj->setTaskUid($result['uid']);
			$tasksObj->registerRecurringExecution(time() + 1, '86400');

			$values = array(
				'serialized_task_object' => serialize($tasksObj),
			);

			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($tasksTable, 'uid = ' . $result['uid'], $values);
		}
	}
}

