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
 * LaterPay core logger.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_core_logger implements t3lib_Singleton{

	/**
	 * Logger levels
	 */
	const DEBUG     = 100;
	const INFO      = 200;
	const NOTICE    = 250;
	const WARNING   = 300;
	const ERROR     = 400;
	const CRITICAL  = 500;
	const ALERT     = 550;
	const EMERGENCY = 600;

	/**
	 * Contains all debugging levels.
	 *
	 * @var array
	 */
	protected $levels = array(
		100 => 'DEBUG',
		200 => 'INFO',
		250 => 'NOTICE',
		300 => 'WARNING',
		400 => 'ERROR',
		500 => 'CRITICAL',
		550 => 'ALERT',
		600 => 'EMERGENCY',
	);

	/**
	 * Time zone.
	 * @var \DateTimeZone
	 */
	protected $timezone;

	/**
	 * Name of logger.
	 * @var string
	 */
	protected $name;

	/**
	 * The handler stack.
	 *
	 * @var tx_laterpay_core_logger_handler_interface[]
	 */
	protected $handlers;

	/**
	 * Processors that will process all log records.
	 *
	 * To process records of a single handler instead, add the processor on that specific handler
	 *
	 * @var tx_laterpay_core_logger_processor_interface[]
	 */
	protected $processors;

	/**
	 * Constructor of object.
	 *
	 * @param string $name The logging channel
	 * @param array $handlers Optional stack of handlers, the first one in the array is called first, etc.
	 * @param array $processors Optional array of processors
	 */
	public function __construct($name = 'default', array $handlers = array(), array $processors = array()) {
		$this->name       = $name;
		$this->handlers   = $handlers;
		$this->processors = $processors;
		$this->timezone   = new DateTimeZone(date_default_timezone_get() ? date_default_timezone_get() : 'UTC');

		$this->initLogger();
	}

	/**
	 * Init logger.
	 *
	 * @return void
	 */
	public function initLogger() {
		$handlers   = array();
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['debug']) {
			// LaterPay WordPress handler to render the debugger pane
			$wpHandler = new tx_laterpay_core_logger_handler_typo3();
			$wpHandler->setFormatter( new tx_laterpay_core_logger_formatter_html() );

			$handlers[] = $wpHandler;
		} else {
			$handlers[] = new tx_laterpay_core_logger_handler_null();
		}

		// add additional processors for more detailed log entries
		$processors = array(
				new tx_laterpay_core_logger_processor_web(),
				new tx_laterpay_core_logger_processor_memoryusage(),
				new tx_laterpay_core_logger_processor_memorypeakusage(),
		);

		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;

		$this->name       = $name;
		$this->handlers   = $handlers;
		$this->processors = $processors;
	}

	/**
	 * Create instance of class.
	 *
	 * @return Ambigous <object, mixed, array<t3lib_Singleton>, t3lib_Singleton>
	 */
	public static function getInstance () {
		return t3lib_div::makeInstance('tx_laterpay_core_logger');
	}

	/**
	 * Add a log record at the DEBUG level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function debug($message, array $context = array()) {
		return $this->addRecord(self::DEBUG, $message, $context);
	}

	/**
	 * Add a log record at the ERROR level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function error($message, array $context = array()) {
		return $this->addRecord(self::ERROR, $message, $context);
	}

	/**
	 * Add a log record at the INFO level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function info($message, array $context = array()) {
		return $this->addRecord(self::INFO, $message, $context);
	}

	/**
	 * Add a log record at the NOTICE level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function notice($message, array $context = array()) {
		return $this->addRecord(self::NOTICE, $message, $context);
	}

	/**
	 * Add a log record at the WARNING level.
	 *
	 * @param mixed $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function warning($message, array $context = array()) {
		return $this->addRecord(self::WARNING, $message, $context);
	}

	/**
	 * Add a log record at the CRITICAL level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function critical($message, array $context = array()) {
		return $this->addRecord(self::CRITICAL, $message, $context);
	}

	/**
	 * Add a log record at the ALERT level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function alert($message, array $context = array()) {
		return $this->addRecord(self::ALERT, $message, $context);
	}

	/**
	 * Add a log record at the EMERGENCY level.
	 *
	 * @param string $message The log message
	 * @param array $context The log context
	 *
	 * @return bool
	 */
	public function emergency($message, array $context = array()) {
		return $this->addRecord(self::EMERGENCY, $message, $context);
	}

	/**
	 * Add a record to the log.
	 *
	 * @param int $level Level of message
	 * @param string $message Log message
	 * @param array $context Running contect
	 *
	 * @return bool
	 */
	public function addRecord($level, $message, array $context = array()) {
		if (! $this->handlers) {
			$this->pushHandler(new tx_laterpay_core_logger_handler_null());
		}

		$dateTime = new DateTime('now', $this->timezone);

		$record = array(
			'message'    => (string) $message,
			'context'    => $context,
			'level'      => $level,
			'level_name' => self::getLevelName($level),
			'channel'    => $this->name,
			'datetime'   => $dateTime,
			'extra'      => array(),
		);

		// check, if any handler will handle this message
		$isHandling = FALSE;
		foreach ($this->handlers as $handler) {
			if ($handler->isHandling($record)) {
				$isHandling = TRUE;
				break;
			}
		}

		if ($isHandling) {
			// found at least one, process message and dispatch it
			foreach ($this->processors as $processor) {
				$record = $processor->process($record);
			}
			foreach ($this->handlers as $handler) {
				$handler->handle($record);
			}
		}
		return $isHandling;
	}

	/**
	 * Get name of logger.
	 *
	 * @return str
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Push a handler onto the stack.
	 *
	 * @param tx_laterpay_core_logger_handler_interface $handler Handler instance
	 *
	 * @return void
	 */
	public function pushHandler(tx_laterpay_core_logger_handler_interface $handler) {
		array_unshift($this->handlers, $handler);
	}

	/**
	 * Pop a handler from the stack.
	 *
	 * @return tx_laterpay_core_logger_handler_interface
	 */
	public function popHandler() {
		if (! $this->handlers) {
			throw new \LogicException('You tried to pop from an empty handler stack.');
		}

		return array_shift($this->handlers);
	}

	/**
	 * Get list of handlers.
	 *
	 * @return tx_laterpay_core_logger_handler_interface[]
	 */
	public function getHandlers() {
		return $this->handlers;
	}

	/**
	 * Add a processor onto the stack.
	 *
	 * @param tx_laterpay_core_logger_processor_interface $callback Call back
	 *
	 * @return void
	 */
	public function pushProcessor(tx_laterpay_core_logger_processor_interface $callback) {
		array_unshift($this->processors, $callback);
	}

	/**
	 * Remove the processor on top of the stack and return it.
	 *
	 * @return callable
	 */
	public function popProcessor() {
		if (! $this->processors) {
			throw new \LogicException('You tried to pop from an empty processor stack.');
		}

		return array_shift($this->processors);
	}

	/**
	 * Get processors.
	 *
	 * @return callable[]
	 */
	public function getProcessors() {
		return $this->processors;
	}

	/**
	 * Check, if the Logger has a handler that listens on the given level.
	 *
	 * @param int $level Level
	 *
	 * @return bool
	 */
	public function isHandling($level) {
		$record = array(
			'level' => $level
		);

		foreach ($this->handlers as $handler) {
			if ($handler->isHandling($record)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Get the name of the logging level.
	 *
	 * @param int $level Get name for specified level
	 *
	 * @return string $levelName
	 */
	public function getLevelName($level) {
		return $this->levels[$level];
	}

	/**
	 * Hook handler for ['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']
	 * Flush all buffered by handlers content
	 *
	 * @param mixed $parameters Array of input parameters
				'jsLibs'               => &$jsLibs,
				'jsFiles'              => &$jsFiles,
				'jsFooterFiles'        => &$jsFooterFiles,
				'cssFiles'             => &$cssFiles,
				'headerData'           => &$this->headerData,
				'footerData'           => &$this->footerData,
				'jsInline'             => &$jsInline,
				'cssInline'            => &$cssInline,
				'xmlPrologAndDocType'  => &$this->xmlPrologAndDocType,
				'htmlTag'              => &$this->htmlTag,
				'headTag'              => &$this->headTag,
				'charSet'              => &$this->charSet,
				'metaCharsetTag'       => &$this->metaCharsetTag,
				'shortcutTag'          => &$this->shortcutTag,
				'inlineComments'       => &$this->inlineComments,
				'baseUrl'              => &$this->baseUrl,
				'baseUrlTag'           => &$this->baseUrlTag,
				'favIcon'              => &$this->favIcon,
				'iconMimeType'         => &$this->iconMimeType,
				'titleTag'             => &$this->titleTag,
				'title'                => &$this->title,
				'metaTags'             => &$metaTags,
				'jsFooterInline'       => &$jsFooterInline,
				'jsFooterLibs'         => &$jsFooterLibs,
				'bodyContent'          => &$this->bodyContent
	 * @param object $pObj Instance t3lib_PageRenderer
	 *
	 * @return void
	 */
	public function handlersLoadAssets(&$parameters, &$pObj) {
		if (((TYPO3_MODE == 'BE') && (defined('LP_RENDER'))) || (TYPO3_MODE != 'BE')) {
			foreach ($this->handlers as $handler) {
				$handler->loadAssets($pObj);
			}
		}
	}

	/**
	 * Hook handler for ['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']
	 * Flush all buffered by handlers content
	 *
	 * @param mixed $parameters Array of input parameters
				'jsLibs'               => &$jsLibs,
				'jsFiles'              => &$jsFiles,
				'jsFooterFiles'        => &$jsFooterFiles,
				'cssFiles'             => &$cssFiles,
				'headerData'           => &$this->headerData,
				'footerData'           => &$this->footerData,
				'jsInline'             => &$jsInline,
				'cssInline'            => &$cssInline,
				'xmlPrologAndDocType'  => &$this->xmlPrologAndDocType,
				'htmlTag'              => &$this->htmlTag,
				'headTag'              => &$this->headTag,
				'charSet'              => &$this->charSet,
				'metaCharsetTag'       => &$this->metaCharsetTag,
				'shortcutTag'          => &$this->shortcutTag,
				'inlineComments'       => &$this->inlineComments,
				'baseUrl'              => &$this->baseUrl,
				'baseUrlTag'           => &$this->baseUrlTag,
				'favIcon'              => &$this->favIcon,
				'iconMimeType'         => &$this->iconMimeType,
				'titleTag'             => &$this->titleTag,
				'title'                => &$this->title,
				'metaTags'             => &$metaTags,
				'jsFooterInline'       => &$jsFooterInline,
				'jsFooterLibs'         => &$jsFooterLibs,
				'bodyContent'          => &$this->bodyContent
	 * @param object $pObj Instance t3lib_PageRenderer
	 *
	 * @return void
	 */
	public function handlersFlush(&$parameters, &$pObj) {
		if (((TYPO3_MODE == 'BE') && (defined('LP_RENDER'))) || (TYPO3_MODE != 'BE')) {
			foreach ($this->handlers as $handler) {
				$content = $handler->flushRecords();
				if (!empty($content)) {
					$parameters['footerData'][] = $content . LF;
				}
			}
		}
	}
}
