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
$extensionPath = t3lib_extMgm::extPath('laterpay');
// add browscap directory into directories list;
set_include_path(
	get_include_path() . PATH_SEPARATOR . $extensionPath . DIRECTORY_SEPARATOR . 'lib' .
	DIRECTORY_SEPARATOR . 'laterpay'
);
// @codingStandardsIgnoreStart
require_once $extensionPath . 'lib/laterpay/autoload.php';
require_once $extensionPath . 'lib/browscap/Browscap.php';
// @codingStandardsIgnoreEnd

return array(

	'tx_laterpay_compatibility' => $extensionPath . 'lib/class.tx_laterpay_compatibility.php',

	'tx_laterpay_config' => $extensionPath . 'lib/class.tx_laterpay_config.php',

	'tx_laterpay_controller_abstract' => $extensionPath . 'lib/controller/abstract.php',
	'tx_laterpay_controller_admin_account' => $extensionPath . 'lib/controller/admin/account.php',
	'tx_laterpay_controller_admin_appearance' => $extensionPath . 'lib/controller/admin/appearance.php',
	'tx_laterpay_controller_admin_dashboard' => $extensionPath . 'lib/controller/admin/dashboard.php',
	'tx_laterpay_controller_admin_pricing' => $extensionPath . 'lib/controller/admin/pricing.php',

	'tx_laterpay_helper_view' => $extensionPath . 'lib/helper/view.php',
	'tx_laterpay_helper_timepass' => $extensionPath . 'lib/helper/timepass.php',
	'tx_laterpay_helper_pricing' => $extensionPath . 'lib/helper/pricing.php',
	'tx_laterpay_helper_voucher' => $extensionPath . 'lib/helper/voucher.php',
	'tx_laterpay_helper_dashboard' => $extensionPath . 'lib/helper/dashboard.php',
	'tx_laterpay_helper_date' => $extensionPath . 'lib/helper/date.php',
	'tx_laterpay_helper_config' => $extensionPath . 'lib/helper/config.php',
	'tx_laterpay_helper_browser' => $extensionPath . 'lib/helper/browser.php',
	'tx_laterpay_helper_render' => $extensionPath . 'lib/helper/render.php',
	'tx_laterpay_helper_string' => $extensionPath . 'lib/helper/string.php',
	'tx_laterpay_helper_content' => $extensionPath . 'lib/helper/content.php',
	'tx_laterpay_helper_user' => $extensionPath . 'lib/helper/user.php',
	'tx_laterpay_helper_statistic' => $extensionPath . 'lib/helper/statistic.php',
	'tx_laterpay_helper_access' => $extensionPath . 'lib/helper/access.php',

	'tx_laterpay_wrapper_interface' => $extensionPath . 'lib/interface/wrapper.php',
	'tx_laterpay_wrapper_abstract' => $extensionPath . 'lib/wrapper/abstract.php',
	'tx_laterpay_wrapper_teaser' => $extensionPath . 'lib/wrapper/teaser.php',
	'tx_laterpay_wrapper_block' => $extensionPath . 'lib/wrapper/block.php',

	'tx_laterpay_form_abstract' => $extensionPath . 'lib/form/abstract.php',
	'tx_laterpay_form_merchantid' => $extensionPath . 'lib/form/merchantid.php',
	'tx_laterpay_form_apikey' => $extensionPath . 'lib/form/apikey.php',
	'tx_laterpay_form_testmode' => $extensionPath . 'lib/form/testmode.php',
	'tx_laterpay_form_pluginmode' => $extensionPath . 'lib/form/pluginmode.php',
	'tx_laterpay_form_pass' => $extensionPath . 'lib/form/pass.php',
	'tx_laterpay_form_globalprice' => $extensionPath . 'lib/form/globalprice.php',
	'tx_laterpay_form_bulkprice' => $extensionPath . 'lib/form/bulkprice.php',
	'tx_laterpay_form_landingpage' => $extensionPath . 'lib/form/landingpage.php',
	'tx_laterpay_form_timepassposition' => $extensionPath . 'lib/form/timepassposition.php',
	'tx_laterpay_form_rating' => $extensionPath . 'lib/form/rating.php',
	'tx_laterpay_form_purchasebuttonposition' => $extensionPath . 'lib/form/purchasebuttonposition.php',
	'tx_laterpay_form_paidcontentpreview' => $extensionPath . 'lib/form/paidcontentpreview.php',

	'tx_laterpay_model_timepass' => $extensionPath . 'lib/model/timepass.php',
	'tx_laterpay_model_currency' => $extensionPath . 'lib/model/currency.php',
	'tx_laterpay_model_query_abstract' => $extensionPath . 'lib/model/query/abstract.php',
	'tx_laterpay_model_payment_history' => $extensionPath . 'lib/model/payment/history.php',
	'tx_laterpay_model_post_view' => $extensionPath . 'lib/model/post/view.php',
	'tx_laterpay_model_content' => $extensionPath . 'lib/model/content.php',

	'tx_laterpay_core_logger' => $extensionPath . 'lib/core/logger.php',
	'tx_laterpay_core_logger_formatter_html' => $extensionPath . 'lib/core/logger/formatter/html.php',
	'tx_laterpay_core_logger_formatter_interface' => $extensionPath . 'lib/core/logger/formatter/interface.php',
	'tx_laterpay_core_logger_formatter_normalizer' => $extensionPath . 'lib/core/logger/formatter/normalizer.php',
	'tx_laterpay_core_logger_handler_abstract' => $extensionPath . 'lib/core/logger/handler/abstract.php',
	'tx_laterpay_core_logger_handler_interface' => $extensionPath . 'lib/core/logger/handler/interface.php',
	'tx_laterpay_core_logger_handler_null' => $extensionPath . 'lib/core/logger/handler/null.php',
	'tx_laterpay_core_logger_handler_typo3' => $extensionPath . 'lib/core/logger/handler/typo3.php',
	'tx_laterpay_core_logger_handler_wordpress' => $extensionPath . 'lib/core/logger/handler/wordpress.php',
	'tx_laterpay_core_logger_processor_interface' => $extensionPath . 'lib/core/logger/processor/interface.php',
	'tx_laterpay_core_logger_processor_introspection' => $extensionPath . 'lib/core/logger/processor/introspection.php',
	'tx_laterpay_core_logger_processor_memory' => $extensionPath . 'lib/core/logger/processor/memory.php',
	'tx_laterpay_core_logger_processor_memorypeakusage' => $extensionPath . 'lib/core/logger/processor/memorypeakusage.php',
	'tx_laterpay_core_logger_processor_memoryusage' => $extensionPath . 'lib/core/logger/processor/memoryusage.php',
	'tx_laterpay_core_logger_processor_web' => $extensionPath . 'lib/core/logger/processor/web.php',

	'tx_laterpay_evaluate_price' => $extensionPath . 'lib/evaluate/price.php',
);
