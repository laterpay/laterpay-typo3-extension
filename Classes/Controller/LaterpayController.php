<?php
namespace LaterpayGmbh\Laterpay\Controller;

/**
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
 * Module 'about' shows some standard information for TYPO3 CMS: About-text, version number and so on.
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author Steffen Kamper <steffen@typo3.org>
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class LaterpayController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\CMS\About\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;

	/**
	 * Main action: Show standard information
	 *
	 * @return void
	 */
	public function indexAction() {
		die('xx');
		var_dump($this->extensionRepository);
		$extensions = $this->extensionRepository->findAllLoaded();
		echo __FILE__ . ':' . __LINE__.PHP_EOL;
		$this->view
			->assign('TYPO3Version', TYPO3_version)
			->assign('TYPO3CopyrightYear', TYPO3_copyright_year)
			->assign('TYPO3UrlDonate', TYPO3_URL_DONATE)
			->assign('loadedExtensions', $extensions);
		echo __FILE__ . ':' . __LINE__.PHP_EOL;
		die('xxx');
	}

}
