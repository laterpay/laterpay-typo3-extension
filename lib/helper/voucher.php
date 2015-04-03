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
 * LaterPay vouchers helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_voucher {

	/**
	 * Default length of voucher code.
	 */
	const VOUCHER_CODE_LENGTH = 6;

	/**
	 * Chars allowed in voucher code.
	 */
	const VOUCHER_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Name of option to update if voucher is a gift.
	 */
	const GIFT_CODES_OPTION = 'laterpay_gift_codes';

	/**
	 * Name of statistic option to update if voucher is a gift.
	 */
	const GIFT_STAT_OPTION = 'laterpay_gift_statistic';

	/**
	 * Name of option to update if voucher is NOT a gift.
	 */
	const VOUCHER_CODES_OPTION = 'laterpay_voucher_codes';

	/**
	 * Name of statistic option to update if voucher is NOT a gift.
	 */
	const VOUCHER_STAT_OPTION = 'laterpay_voucher_statistic';

	/**
	 * Generate random voucher code.
	 *
	 * @param int $length Voucher code length
	 *
	 * @return string voucher code
	 */
	public static function generateVoucherCode($length = self::VOUCHER_CODE_LENGTH) {
		$voucherCode 	= '';
		$possibleChars 	= self::VOUCHER_CHARS;

		for ($i = 0; $i < $length; $i ++) {
			mt_srand();
			$rand = mt_rand(0, strlen($possibleChars) - 1);
			$voucherCode .= substr($possibleChars, $rand, 1);
		}

		return $voucherCode;
	}

	/**
	 * Save vouchers for current pass.
	 *
	 * @param int $passId PassId
	 * @param mixed $vouchersData Vaucher data
	 * @param bool $noExplode NoExplode flag
	 * @param bool $isGift IsGift flag
	 *
	 * @return void
	 */
	public static function savePassVouchers($passId, $vouchersData, $noExplode = FALSE, $isGift = FALSE) {
		$vouchers 		= self::getAllVouchers($isGift);
		$newVouchers 	= array();
		$optionName 	= $isGift ? self::GIFT_CODES_OPTION : self::VOUCHER_CODES_OPTION;

		if ($vouchersData && is_array($vouchersData)) {
			foreach ($vouchersData as $voucher) {
				if ($noExplode) {
					$newVouchers = $vouchersData;
					break;
				}

				list($code, $price) = explode('|', $voucher);

				// format and save price
				$price 				= number_format((float) str_replace(',', '.', $price), 2);
				$newVouchers[$code] = $price;
			}
		}

		if (! $newVouchers) {
			unset($vouchers[$passId]);
		} else {
			$vouchers[$passId] = $newVouchers;
		}

		// save new voucher data
		update_option($optionName, $vouchers);

		// actualize voucher statistic
		self::actualizeVoucherStatistic($isGift);
	}

	/**
	 * Get voucher codes of current time pass.
	 *
	 * @param int $passId PassId
	 * @param bool $isGift IsGift flag
	 *
	 * @return array
	 */
	public static function getTimePassVouchers($passId, $isGift = FALSE) {
		$vouchers = self::getAllVouchers($isGift);
		if (! isset($vouchers[$passId])) {
			return array();
		}

		return $vouchers[$passId];
	}

	/**
	 * Get all vouchers.
	 *
	 * @param bool $isGift IsGift flag
	 *
	 * @return array of vouchers
	 */
	public static function getAllVouchers($isGift = FALSE) {
		$optionName = $isGift ? self::GIFT_CODES_OPTION : self::VOUCHER_CODES_OPTION;
		$vouchers 	= get_option($optionName);
		if (! $vouchers || ! is_array($vouchers)) {
			update_option($optionName, '');

			return array();
		}

		return $vouchers;
	}

	/**
	 * Delete voucher code.
	 *
	 * @param int $passId PassId
	 * @param string $code Code of vaucher
	 * @param bool $isGift Flag isGift
	 *
	 * @return void
	 */
	public static function deleteVoucherCode($passId, $code = NULL, $isGift = FALSE) {
		$passVouchers = self::getTimePassVouchers($passId, $isGift);
		if ($passVouchers && is_array($passVouchers)) {
			if ($code) {
				unset($passVouchers[$code]);
			} else {
				$passVouchers = array();
			}
		}

		self::savePassVouchers($passId, $passVouchers, TRUE, $isGift = FALSE);
	}

	/**
	 * Check, if voucher code exists and return passId and new price.
	 *
	 * @param string $code Vaucher code
	 * @param bool $isGift Flag isGift
	 *
	 * @return mixed $voucherData
	 */
	public static function checkVoucherCode($code, $isGift = FALSE) {
		$vouchers = self::getAllVouchers($isGift);

		// search code
		foreach ($vouchers as $passId => $passVouchers) {
			foreach ($passVouchers as $voucherCode => $voucherPrice) {
				if ($code === $voucherCode) {
					$voucherData = array(
						'pass_id' 	=> $passId,
						'code' 		=> $voucherCode,
						'price' 	=> $voucherPrice,
					);

					return $voucherData;
				}
			}
		}

		return NULL;
	}

	/**
	 * Check, if given time passes have vouchers.
	 *
	 * @param mixed $passes Array of time passes
	 * @param bool $isGift Flag isGift
	 *
	 * @return bool $hasVouchers
	 */
	public static function passesHaveVouchers($passes, $isGift = FALSE) {
		$hasVouchers = FALSE;

		if ($passes && is_array($passes)) {
			foreach ($passes as $pass) {
				$pass = (array) $pass;
				if (self::getTimePassVouchers($pass['pass_id'], $isGift)) {
					$hasVouchers = TRUE;
					break;
				}
			}
		}

		return $hasVouchers;
	}

	/**
	 * Actualize voucher statistic.
	 *
	 * @param bool $isGift Flag isGift
	 *
	 * @return void
	 */
	public static function actualizeVoucherStatistic($isGift = FALSE) {
		$vouchers = self::getAllVouchers($isGift);
		$statistic = self::getAllVouchersStatistic($isGift);
		$result = $statistic;
		$optionName = $isGift ? self::GIFT_STAT_OPTION : self::VOUCHER_STAT_OPTION;

		foreach ($statistic as $passId => $statisticData) {
			if (! isset($vouchers[$passId])) {
				unset($result[$passId]);
			} else {
				foreach ($statisticData as $code => $_) {
					if (! isset($vouchers[$passId][$code])) {
						unset($result[$passId][$code]);
					}
				}
			}
		}

		// update voucher statistics
		update_option($optionName, $result);
	}

	/**
	 * Update voucher statistic.
	 *
	 * @param int $passId Time pass id
	 * @param string $code Voucher code
	 * @param bool $isGift Flag isGift
	 *
	 * @return bool success or error
	 */
	public static function updateVoucherStatistic($passId, $code, $isGift = FALSE) {
		$passVouchers = self::getTimePassVouchers($passId, $isGift);
		$optionName = $isGift ? self::GIFT_STAT_OPTION : self::VOUCHER_STAT_OPTION;

		// check, if such a voucher exists
		if ($passVouchers && isset($passVouchers[$code])) {
			// get all voucher statistics for this pass
			$voucherStatisticData = self::getTimePassVouchersStatistic($passId, $isGift);

			// check, if statistic is empty
			if ($voucherStatisticData) {
				// increment counter by 1, if statistic exists
				$voucherStatisticData[$code] += 1;
			} else {
				// create new data array, if statistic is empty
				$voucherStatisticData[$code] = 1;
			}

			$statistic = self::getAllVouchersStatistic($isGift);
			$statistic[$passId] = $voucherStatisticData;

			update_option($optionName, $statistic);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get time pass voucher statistic by time pass id.
	 *
	 * @param int $passId Time pass id
	 * @param bool $isGift Flag isGift
	 *
	 * @return array $statistic
	 */
	public static function getTimePassVouchersStatistic($passId, $isGift = FALSE) {
		$statistic = self::getAllVouchersStatistic($isGift);

		if (isset($statistic[$passId])) {
			return $statistic[$passId];
		}

		return array();
	}

	/**
	 * Get statistics for all vouchers.
	 *
	 * @param bool $isGift Flag isGift
	 *
	 * @return array $statistic
	 */
	public static function getAllVouchersStatistic($isGift = FALSE) {
		$optionName = $isGift ? self::GIFT_STAT_OPTION : self::VOUCHER_STAT_OPTION;
		$statistic 	= get_option($optionName);
		if (! $statistic || ! is_array($statistic)) {
			update_option($optionName, '');

			return array();
		}

		return $statistic;
	}

	/**
	 * Get gift code usages count.
	 *
	 * @param mixed $code Gift code
	 *
	 * @return null
	 */
	public static function getGiftCodeUsagesCount($code) {
		$usages = get_option('laterpay_gift_codes_usages');

		return $usages && isset($usages[$code]) ? $usages[$code] : 0;
	}

	/**
	 * Update gift code usage count.
	 *
	 * @param mixed $code Gift code
	 *
	 * @return bool
	 */
	public static function updateGiftCodeUsages($code) {
		$usages = get_option('laterpay_gift_codes_usages');
		if (! $usages) {
			$usages = array();
		}

		isset($usages[$code]) ? $usages[$code] += 1 : $usages[$code] = 1;

		update_option('laterpay_gift_codes_usages', $usages);

		return TRUE;
	}

	/**
	 * Check if gift code usages exceed limit.
	 *
	 * @param mixed $code Gift code
	 *
	 * @return bool
	 */
	public static function checkGiftCodeUsagesLimit($code) {
		$limit 	= get_option('laterpay_maximum_redemptions_per_gift_code');
		$usages = self::getGiftCodeUsagesCount($code);
		if (($usages + 1) <= $limit) {
			return TRUE;
		}

		return FALSE;
	}
}
