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
 * Interface for wrappers.
 */
interface tx_laterpay_wrapper_interface {
	/**
	 * Prewrap hook.
	 *
	 * @return string
	 */
	public function preWrap();

	/**
	 * Wrap hook.
	 *
	 * @param string $teaserContent Teaser content
	 *
	 * @return string
	 */
	public function wrap($teaserContent);

	/**
	 * Postwrap hook.
	 *
	 * @return string
	 */
	public function postWrap();
}
