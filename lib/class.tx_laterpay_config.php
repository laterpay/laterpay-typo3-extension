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
 * Laterpay config storage
 */
class tx_laterpay_config implements t3lib_Singleton {
	const PLUGIN_NAME 							= 'laterpay';
	const PLUGIN_NAME_SPACE 					= 'tx_laterpay';
	const PLUGIN_DIR_PATH 						= 'plugin_dir_path';
	const PLUGIN_FILE_PATH 						= 'plugin_file_path';
	const PLUGIN_BASE_NAME 						= 'plugin_base_name';
	const PLUGIN_URL 							= 'plugin_url';
	const VIEW_DIR 								= 'view_dir';
	const CACHE_DIR 							= 'cache_dir';
	const CSS_URL 								= 'css_url';
	const JS_URL 								= 'js_url';
	const IMAGE_URL 							= 'image_url';
	const IS_IN_LIVE_MODE 						= 'is_in_live_mode';
	const RATINGS_ENABLED 						= 'ratings_enabled';
	const DEBUG_MODE 							= 'debug_mode';
	const SCRIPT_DEBUG_MODE 					= 'script_debug_mode';
	const LATERPAY_YUI_JS 						= 'laterpay_yui_js';

	const API_SANDBOX_BACKEND_API_URL 			= 'api.sandbox_backend_api_url';
	const API_SANDBOX_DIALOG_API_URL 			= 'api.sandbox_dialog_api_url ';
	const API_LIVE_BACKEND_API_URL 				= 'api.live_backend_api_url';
	const API_LIVE_DIALOG_API_URL 				= 'api.live_dialog_api_url';
	const API_MERCHANT_BACKEND_URL 				= 'api.merchant_backend_url';

	const API_TOKEN_NAME 						= 'api.token_name';
	const API_SANDBOX_MERCHANT_ID 				= 'api.sandbox_merchant_id';
	const API_SANDBOX_API_KEY 					= 'api.sandbox_api_key';

	const CURRENCY_DEFAULT 						= 'currency.default';
	const CURRENCY_DEFAULT_PRICE 				= 'currency.default_price';

	const CONTENT_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT 	= 'content.auto_generated_teaser_content_word_count';

	const CONTENT_PREVIEW_PERCENTAGE_OF_CONTENT = 'content.preview_percentage_of_content';
	const CONTENT_PREVIEW_WORD_COUNT_MIN 		= 'content.preview_word_count_min';
	const CONTENT_PREVIEW_WORD_COUNT_MAX 		= 'content.preview_word_count_max';

	const CONTENT_SHOW_PURCHASE_BUTTON 			= 'content.show_purchase_button';

	const CONTENT_ENABLED_POST_TYPES 			= 'content.enabled_post_types';

	const LOGGING_ACCESS_LOGGING_ENABLED 		= 'logging.access_logging_enabled';

	const BROWSCAP_AUTOUPDATE 					= 'browscap.autoupdate';
	const BROWSCAP_SILENT 						= 'browscap.silent';
	const BROWSCAP_MANUALLY_UPDATED_COPY 		= 'browscap.manually_updated_copy';

	const REG_IS_IN_LIVE_MODE 					= 'laterpay_plugin_is_in_live_mode';

	const REG_LATERPAY_RATINGS 					= 'laterpay_ratings';

	const REG_LATERPAY_SANDBOX_BACKEND_API_URL 	= 'laterpay_sandbox_backend_api_url ';
	const REG_LATERPAY_SANDBOX_DIALOG_API_URL 	= 'laterpay_sandbox_dialog_api_url  ';
	const REG_LATERPAY_LIVE_BACKEND_API_URL 	= 'laterpay_live_backend_api_url    ';
	const REG_LATERPAY_LIVE_DIALOG_API_URL 		= 'laterpay_live_dialog_api_url     ';

	const REG_LATERPAY_API_MERCHANT_BACKEND_URL = 'laterpay_api_merchant_backend_url';

	const REG_LATERPAY_ENABLED_POST_TYPES 		= 'laterpay_enabled_post_types';

	const REG_LATERPAY_TEASER_CONTENT_WORD_COUNT = 'laterpay_teaser_content_word_count';

	const REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT 	= 'laterpay_preview_excerpt_percentage_of_content';
	const REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN 			= 'laterpay_preview_excerpt_word_count_min';
	const REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX 			= 'laterpay_preview_excerpt_word_count_max';

	const REG_LATERPAY_SHOW_PURCHASE_BUTTON 	= 'laterpay_show_purchase_button';

	const REG_LATERPAY_ACCESS_LOGGING_ENABLED 	= 'laterpay_access_logging_enabled';

	const REG_LATERPAY_CURRENCY 				= 'laterpay_currency';

	const REG_LATERPAY_PREVIEW_AS_VISITOR 		= 'laterpay_preview_as_visitor';
	const REG_LATERPAY_STATISTICS_TAB_IS_HIDDEN = 'laterpay_statistic_tab_is_hidden';

	const REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE 	= 'laterpay_is_in_visible_test_mode';

	/**
	 * Registry of TYPO3.
	 *
	 * @var t3lib_Registry
	 */
	protected $registry = NULL;

	/**
	 * List of properties.
	 *
	 * @type array
	 */
	protected $properties = array();

	/**
	 * Parent object.
	 *
	 * Used if a name is not available in this instance.
	 *
	 * @type LaterPay_Model_Config
	 */
	protected $parent = NULL;

	/**
	 * Record of deleted properties.
	 *
	 * Prevents access to the parent object's properties after deletion in this
	 * instance.
	 *
	 * @see get() @type array
	 */
	protected $deleted = array();

	/**
	 * Write and delete protection.
	 *
	 * @see freeze()
	 * @see is_frozen() @type bool
	 */
	protected $frozen = FALSE;

	/**
	 * Get instance
	 *
	 * @return tx_laterpay_compatibility
	 */
	public static function getInstance () {
		return t3lib_div::makeInstance('tx_laterpay_config');
	}

	/**
	 * Creates this object.
	 */
	public function __construct () {
		$this->initConfig();
	}


	/**
	 * Create initial plugin's settings.
	 *
	 * @return LaterPay_Model_Config
	 */
	public function initConfig() {
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$config = $this;

		$extPath = t3lib_extMgm::extPath('laterpay');
		$extRealPath = t3lib_extMgm::extRelPath('laterpay');

		// plugin default settings for paths and directories
		$config->set(self::PLUGIN_DIR_PATH, $extPath);
		$config->set(self::PLUGIN_FILE_PATH, __FILE__);
		$config->set(self::PLUGIN_BASE_NAME, 'laterpay');
		$config->set(self::PLUGIN_URL, $extRealPath);
		$config->set(self::VIEW_DIR, $extPath . 'views/');
		$config->set(self::CACHE_DIR, $extPath . 'cache/');

		$config->set(self::CSS_URL, $extRealPath . 'res/css/');
		$config->set(self::JS_URL, $extRealPath . 'res/js/');
		$config->set(self::IMAGE_URL, $extRealPath . 'res/img/');

		// plugin modes
		$config->set(
			self::IS_IN_LIVE_MODE,
			(bool) $registry->get(self::PLUGIN_NAME_SPACE, self::REG_IS_IN_LIVE_MODE, FALSE)
		);
		$config->set(
			self::RATINGS_ENABLED,
			(bool) $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_RATINGS, FALSE)
		);
		$config->set(self::DEBUG_MODE, defined('LP_DEBUG') && LP_DEBUG);
		$config->set(self::SCRIPT_DEBUG_MODE, defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);

		if ($config->get(self::IS_IN_LIVE_MODE)) {
			$laterpaySrc = 'https://lpstatic.net/combo?yui/3.17.2/build/yui/yui-min.js&client/1.0.0/config.js';
		} elseif ( $config->get(self::SCRIPT_DEBUG_MODE) ) {
			$laterpaySrc = 'https://sandbox.lpstatic.net/combo?yui/3.17.2/build/yui/yui.js&client/1.0.0/config-sandbox.js';
		} else {
			$laterpaySrc = 'https://sandbox.lpstatic.net/combo?yui/3.17.2/build/yui/yui-min.js&client/1.0.0/config-sandbox.js';
		}
		$config->set(self::LATERPAY_YUI_JS, $laterpaySrc);

		// make sure all API variables are set
		if (! $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_SANDBOX_BACKEND_API_URL)) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_SANDBOX_BACKEND_API_URL,
				'https://api.sandbox.laterpaytest.net'
			);
		}

		if (! $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_SANDBOX_DIALOG_API_URL)) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_SANDBOX_DIALOG_API_URL,
				'https://web.sandbox.laterpaytest.net'
			);
		}

		if ( !$registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_LIVE_BACKEND_API_URL)) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_LIVE_BACKEND_API_URL,
				'https://api.laterpay.net'
			);
		}

		if ( ! $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_LIVE_DIALOG_API_URL)) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_LIVE_DIALOG_API_URL,
				'https://web.laterpay.net'
			);
		}

		if (! $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_API_MERCHANT_BACKEND_URL)) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_API_MERCHANT_BACKEND_URL,
				'https://merchant.laterpay.net/'
			);
		}

		// auto teaser generation
		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT) == NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT,
				25
			);
		}

		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN) == NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN,
				26
			);
		}

		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX) == NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX,
				200
			);
		}

		/**
		 * LaterPay API endpoints and API default settings.
		 *
		 * @var array
		 */
		$defaultApiSettings = array(
			self::API_SANDBOX_BACKEND_API_URL 	=> $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_SANDBOX_BACKEND_API_URL),
			self::API_SANDBOX_DIALOG_API_URL 	=> $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_SANDBOX_DIALOG_API_URL),
			self::API_LIVE_BACKEND_API_URL 		=> $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_LIVE_BACKEND_API_URL),
			self::API_LIVE_DIALOG_API_URL 		=> $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_LIVE_DIALOG_API_URL),
			self::API_MERCHANT_BACKEND_URL 		=> $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_API_MERCHANT_BACKEND_URL),
		);

		/**
		 * Plugin filter for manipulating the API endpoint URLs.
		 *
		 * @param array $api_settings
		 *
		 * @return array $api_settings
		*/
		// non-editable settings for the LaterPay API
		$apiSettings[self::API_TOKEN_NAME] 			= 'token';
		$apiSettings[self::API_SANDBOX_MERCHANT_ID] = 'LaterPay-WordPressDemo';
		$apiSettings[self::API_SANDBOX_API_KEY] 	= 'decafbaddecafbaddecafbaddecafbad';

		$config->import($apiSettings);

		// default settings for price and currency
		$currencySettings = array(
				self::CURRENCY_DEFAULT 			=> 'EUR',
				self::CURRENCY_DEFAULT_PRICE 	=> 0.29,
		);
		$config->import($currencySettings);

		$config->set(
			self::REG_LATERPAY_CURRENCY,
			$registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_CURRENCY, $config->get(self::CURRENCY_DEFAULT))
		);

		$enabledPostTypes = $registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_ENABLED_POST_TYPES);

		// content preview settings
		$contentSettings = array(
				self::CONTENT_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT => $registry->get(self::PLUGIN_NAME_SPACE,
					self::REG_LATERPAY_TEASER_CONTENT_WORD_COUNT),
				self::CONTENT_PREVIEW_PERCENTAGE_OF_CONTENT => $registry->get(self::PLUGIN_NAME_SPACE,
					self::REG_LATERPAY_PREVIEW_EXCERPT_PERCENTAGE_OF_CONTENT),
				self::CONTENT_PREVIEW_WORD_COUNT_MIN => $registry->get(self::PLUGIN_NAME_SPACE,
					self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MIN),
				self::CONTENT_PREVIEW_WORD_COUNT_MAX => $registry->get(self::PLUGIN_NAME_SPACE,
					self::REG_LATERPAY_PREVIEW_EXCERPT_WORD_COUNT_MAX),
				self::CONTENT_SHOW_PURCHASE_BUTTON => $registry->get(self::PLUGIN_NAME_SPACE,
					self::REG_LATERPAY_SHOW_PURCHASE_BUTTON),
				self::CONTENT_ENABLED_POST_TYPES => $enabledPostTypes ? $enabledPostTypes : array(),
		);

		$config->import($contentSettings);

		/**
		 * Access logging for generating sales statistics within the plugin;
		 * Sets a cookie and logs all requests from visitors to your blog, if enabled
		 *
		 * @var bool $access_logging_enabled
		 *
		 * @return bool
		*/
		$config->set(
			self::LOGGING_ACCESS_LOGGING_ENABLED,
			$registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_ACCESS_LOGGING_ENABLED)
		);

		// Browscap browser detection library
		$browscapSettings = array(
			// Auto-update browscap library
			// The plugin requires browscap to ensure search engine bots, social media sites, etc. don't crash when visiting a paid post
			// When set to true, the plugin will automatically fetch updates of this library from browscap.org
			self::BROWSCAP_AUTOUPDATE => FALSE,
			self::BROWSCAP_SILENT => TRUE,
			// If you can't or don't want to enable automatic updates, you can provide the full path to a browscap.ini file
			// on your server that you update manually from http://browscap.org/stream?q=PHP_BrowsCapINI
			self::BROWSCAP_MANUALLY_UPDATED_COPY => NULL,
		);

		/**
		* Browcap settings.
		*
		* @var array $browscap_settings
		*
		* @return array $browscap_settings
		*/
		$config->import($browscapSettings);

		// admin preview settings

		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE) === NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_IS_IN_VISIBLE_TEST_MODE,
				0
			);
		}

		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_PREVIEW_AS_VISITOR) === NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_PREVIEW_AS_VISITOR,
				0
			);
		}

		if ($registry->get(self::PLUGIN_NAME_SPACE, self::REG_LATERPAY_STATISTICS_TAB_IS_HIDDEN) === NULL) {
			$registry->set(
				self::PLUGIN_NAME_SPACE,
				self::REG_LATERPAY_STATISTICS_TAB_IS_HIDDEN,
				0
			);
		}

		return $config;
	}

	/**
	 * Set new value.
	 *
	 * @param string $name  Name of parameter
	 * @param mixed  $value Value of parameter
	 *
	 * @return void|LaterPay_Model_Config
	 */
	public function set ($name, $value) {
		if ($this->frozen) {
			return $this->stop(
					'This object has been frozen. You cannot change the parent anymore.');
		}

		$this->properties[$name] = $value;
		unset($this->deleted[$name]);

		return $this;
	}

	/**
	 * Import an array or an object as properties.
	 *
	 * @param array|object $var Variable
	 *
	 * @return void|LaterPay_Model_Config
	 */
	public function import ($var) {
		if ($this->frozen) {
			return $this->stop(
					'This object has been frozen. You cannot change the parent anymore.');
		}

		if (! is_array($var) and ! is_object($var)) {
			return $this->stop(
				'Cannot import this variable. Use arrays and objects only, not a "' .
				gettype($var) . '".');
		}

		foreach ($var as $name => $value) {
			$this->properties[$name] = $value;
		}

		return $this;
	}

	/**
	 * Get a value.
	 *
	 * Might be taken from parent object.
	 *
	 * @param string $name Name of parameter
	 *
	 * @return mixed
	 */
	public function get ($name) {
		if (isset($this->properties[$name])) {
			return $this->properties[$name];
		}

		if (isset($this->deleted[$name])) {
			return NULL;
		}

		if (NULL === $this->parent) {
			return NULL;
		}

		return $this->parent->get($name);
	}

	/**
	 * Get all properties.
	 *
	 * @param bool $useParent Get parent object's properties too
	 *
	 * @return array
	 */
	public function getAll($useParent = FALSE) {
		if (! $useParent) {
			return $this->properties;
		}

		$parentProperties = $this->parent->getAll(TRUE);
		$all = array_merge($parentProperties, $this->properties);

		// strip out properties existing in the parent but deleted in this instance
		return array_diff($all, $this->deleted);
	}

	/**
	 * Check if property exists.
	 *
	 * Due to syntax restrictions in PHP we cannot name this "isset()".
	 *
	 * @param string $name Parameter name
	 *
	 * @return bool
	 */
	public function has($name) {
		if (isset($this->properties[$name])) {
			return TRUE;
		}

		if (isset($this->deleted[$name])) {
			return FALSE;
		}

		if (NULL === $this->parent) {
			return FALSE;
		}

		return $this->parent->has($name);
	}

	/**
	 * Delete a key and set its name to the $deleted list.
	 *
	 * Further calls to has() and get() will not take this property into
	 * account.
	 *
	 * @param string $name Parameter name
	 *
	 * @return void|LaterPay_Model_Config
	 */
	public function delete($name) {
		if ($this->frozen) {
			return $this->stop(
					'This object has been frozen. You cannot change the parent anymore.');
		}

		$this->deleted[$name] = TRUE;
		unset($this->properties[$name]);

		return $this;
	}

	/**
	 * Set parent object.
	 * Properties of this object will be inherited.
	 *
	 * @param tx_laterpay_config $object Parent object
	 *
	 * @return tx_laterpay_config
	 */
	public function setParent(tx_laterpay_config $object) {
		if ($this->frozen) {
			return $this->stop(
					'This object has been frozen. You cannot change the parent anymore.');
		}

		$this->parent = $object;

		return $this;
	}

	/**
	 * Test if the current instance has a parent.
	 *
	 * @return bool
	 */
	public function hasParent() {
		return NULL === $this->parent;
	}

	/**
	 * Lock write access for this object.
	 * Forever.
	 *
	 * @return LaterPay_Model_Config
	 */
	public function freeze() {
		$this->frozen = TRUE;

		return $this;
	}

	/**
	 * Test from outside if an object has been frozen.
	 *
	 * @return bool
	 */
	public function isFrozen() {
		return $this->frozen;
	}

	/**
	 * Used for attempts to write to a frozen instance.
	 *
	 * Might be replaced by a child class.
	 *
	 * @param string $msg  Error message. Always be specific.
	 * @param string $code Re-use the same code to group error messages.
	 *
	 * @throws Exception
	 *
	 * @return void|WP_Error
	 */
	protected function stop($msg, $code = '') {
		if ('' === $code) {
			$code = __CLASS__;
		}

		if (class_exists('WP_Error')) {
			return new WP_Error($code, $msg);
		}

		throw new Exception($msg, $code);
	}

	/**
	 * Wrapper for set().
	 *
	 * @param string $name  Name of parameter
	 * @param mixed  $value Value of parameter
	 *
	 * @see set()
	 *
	 * @return LaterPay_Model_Config
	 */
	public function __set($name, $value) {
		return $this->set($name, $value);
	}

	/**
	 * Wrapper for get().
	 *
	 * @param string $name Name of parameter
	 *
	 * @see get()
	 *
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * Wrapper for has().
	 *
	 * @param string $name Name of parameter
	 *
	 * @see has()
	 *
	 * @return bool
	 */
	public function __isset($name) {
		return $this->has($name);
	}

	/**
	 * Get data from typo: registry.
	 *
	 * @param string $name NAme of parameter
	 *
	 * @return mixed
	 */
	public static function getOption($name) {
		$registry = t3lib_div::makeInstance('t3lib_Registry');

		return $registry->get(self::PLUGIN_NAME_SPACE, $name);
	}

	/**
	 * Set option in typo: registry.
	 *
	 * @param string $name Name of parameter
	 * @param mixed $value Stored data
	 *
	 * @return bool
	 */
	public static function updateOption($name, $value) {
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$registry->set(self::PLUGIN_NAME_SPACE, $name, $value);

		return TRUE;
	}
}
