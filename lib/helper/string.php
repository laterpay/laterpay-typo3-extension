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
 * LaterPay string helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-typo3-extension
 * Author URI: https://laterpay.net/
 */
class tx_laterpay_helper_string {

	/**
	 * Get the first given number of words from a string.
	 *
	 * @param string $string Input string
	 * @param int $wordLimit Word limit
	 *
	 * @return string
	 */
	public static function limitWords($string, $wordLimit) {
		$words = explode(' ', $string);

		return implode(' ', array_slice($words, 0, $wordLimit));
	}

	/**
	 * Determine the number of words to be shown behind an overlay according to the settings supplied by the blog owner.
	 *
	 * @param string $content Input content
	 *
	 * @return int $numberOfWords
	 */
	public static function determineNumberOfWords($content) {
		$content 		= preg_replace('/\s+/', ' ', $content);
		$totalWords 	= count(explode(' ', $content));

		$config 		= tx_laterpay_config::getInstance();

		$percent 		= (int) $config->get('content.preview_percentage_of_content');
		$percent 		= max(min($percent, 100), 1);
		$min 			= (int) $config->get('content.preview_word_count_min');
		$max 			= (int) $config->get('content.preview_word_count_max');

		$numberOfWords 	= $totalWords * ($percent / 100);
		$numberOfWords 	= max(min($numberOfWords, $max), $min);

		return $numberOfWords;
	}

	/**
	 * Truncate text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with an ellipsis, if the text is longer than length.
	 *
	 * ### Options:
	 *
	 * - `ellipsis` Will be used as ending and appended to the trimmed string (`ending` is deprecated)
	 * - `exact` If false, $text will not be cut mid-word
	 * - `html` If true, HTML tags are handled correctly
	 *
	 * @param string $text String to truncate.
	 * @param int $length Length of returned string, including ellipsis.
	 * @param mixed $options An array of html attributes and options.
	 *
	 * @return string Trimmed string.
	 *
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::truncate
	 */
	public static function truncate($text, $length = 100, $options = array()) {
		$default = array(
			'ellipsis' 	=> ' ...',
			'exact' 	=> TRUE,
			'html' 		=> FALSE,
			'words' 	=> FALSE,
		);

		if (isset($options['ending'])) {
			$default['ellipsis'] = $options['ending'];
		} elseif (! empty($options['html'])) {
			$default['ellipsis'] = "\xe2\x80\xa6";
		}

		$options = array_merge($default, $options);
		extract($options);

		if (! function_exists('mb_strlen')) {
			class_exists('Multibyte');
		}

		if ($html) {
			$text = preg_replace('/<! --(.*?)-->/i', '', $text);

			if ($words) {
				$length = mb_strlen(self::limitWords(preg_replace('/<.*?>/', '', $text), $length));
			}

			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			$totalLength 	= mb_strlen(strip_tags($ellipsis));
			$openTags 		= array();
			$truncate 		= '';

			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag) {
				if (! preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
						array_unshift($openTags, $tag[2]);
					} elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
						$pos = array_search($closeTag[1], $openTags);
						if ($pos !== FALSE) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities,
						PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entitiesLength <= $left) {
								$left --;
								$entitiesLength += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}

					$truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
					break;
				} else {
					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}
				if ($totalLength >= $length) {
					break;
				}
			}
		} else {
			if ($words) {
				$length = mb_strlen(self::limitWords($text, $length));
			}
			if (mb_strlen($text) <= $length) {
				return $text;
			}
			$truncate = mb_substr($text, 0, $length - mb_strlen($ellipsis));
		}
		if (! $exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if ($html) {
				$truncateCheck = mb_substr($truncate, 0, $spacepos);
				$lastOpenTag = mb_strrpos($truncateCheck, '<');
				$lastCloseTag = mb_strrpos($truncateCheck, '>');
				if ($lastOpenTag > $lastCloseTag) {
					preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
					$lastTag = array_pop($lastTagMatches[0]);
					$spacepos = mb_strrpos($truncate, $lastTag) + mb_strlen($lastTag);
				}
				$bits = mb_substr($truncate, $spacepos);
				preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
				if (! empty($droppedTags)) {
					if (! empty($openTags)) {
						foreach ($droppedTags as $closingTag) {
							if (! in_array($closingTag[1], $openTags)) {
								array_unshift($openTags, $closingTag[1]);
							}
						}
					} else {
						foreach ($droppedTags as $closingTag) {
							$openTags[] = $closingTag[1];
						}
					}
				}
			}
			$truncate = mb_substr($truncate, 0, $spacepos);
		}
		$truncate .= $ellipsis;

		if ($html) {
			foreach ($openTags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	/**
	 * Translate and log untranslated string.
	 * Internal function used for debugging only.
	 *
	 * @param string $text Input text
	 *
	 * @return string
	 */
	public static function trAndLog($text) {
		static $allProcessed;
		if (!isset($allProcessed)) {
			$allProcessed = array();
		}

		if ($GLOBALS['LANG']) {
			$result = $GLOBALS['LANG']->getLL($text);

			if (empty($result)) {
				if (!in_array($text, $allProcessed)) {
					$allProcessed[] = $text;
					$encResult = htmlentities($text);

					t3lib_div::syslog(
						sprintf('No translation for: <label index="%s">%s</label>', $encResult, $encResult),
						tx_laterpay_config::PLUGIN_NAME
					);
				}
				$result = $text;
			}
		} else {
			$result = $text;
		}

		return $result;
	}

	/**
	 * Translate and echo string.
	 *
	 * @param string $text Input text
	 *
	 * @return void
	 */
	public static function trEcho($text) {
			echo self::trAndLog($text);
	}

	/**
	 * Translate text and context.
	 *
	 * @param string $text Input text
	 * @param string $context Context text
	 *
	 * @return string
	 */
	public static function trX($text, $context) {
		echo($text);

		$textTr 	= self::trAndLog($text);
		$contextTr 	= self::trAndLog($context);

		return $textTr . '|' . $contextTr;
	}

	/**
	 * Translate string.
	 *
	 * @param string $text Input text
	 *
	 * @return string
	 */
	public static function tr($text) {
		return self::trAndLog($text);
	}

	/**
	 * Navigate through an array and encode the values to be used in a URL.
	 *
	 * @param array|string $value The array or string to be encoded.
	 *
	 * @return array|string $value The encoded array (or string from the callback).
	 */
	public static function urlencodeDeep($value) {
		$value = is_array($value) ? array_map('tx_laterpay_helper_string::urlencodeDeep', $value) : urlencode($value);

		return $value;
	}

	/**
	 * Navigate through an array and remove slashes from the values.
	 * If an array is passed, the array_map() function causes a callback to pass the
	 * value back to the function. The slashes from this value will removed.
	 *
	 * @param mixed $value The value to be stripped.
	 *
	 * @return mixed Stripped value.
	 */
	public static function stripslashesDeep($value) {
		if ( is_array($value) ) {
			$value = array_map('tx_laterpay_helper_string::stripslashesDeep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key => $data) {
				$value->{$key} = self::stripslashesDeep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = stripslashes($value);
		}

		return $value;
	}

	/**
	 * Merge user defined arguments into defaults array.
	 * This function is used throughout WordPress to allow for both string or array
	 * to be merged into another array.
	 *
	 * @param string|array $string     Value to merge with $defaults
	 * @param mixed $array Array that serves as the defaults. Default empty.
	 *
	 * @return void
	 */
	public static function parseStr( $string, &$array ) {
		parse_str( $string, $array );
		if ( get_magic_quotes_gpc() ) {
			$array = self::stripslashesDeep( $array );
		}
	}

	/**
	 * From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
	 *
	 * @param array|object  $data       An array or object of data. Converted to array.
	 * @param string        $prefix     Optional. Numeric index. If set, start parameter numbering with it.
	 * @param string        $sep        Optional. Argument separator; defaults to 'arg_separator.output'.
	 * @param string        $key        Optional. Used to prefix key name. Default empty.
	 * @param bool          $urlencode  Optional. Whether to use urlencode() in the result. Default true.
	 *
	 * @return string The query string.
	 */
	public static function httpBuildQuery( $data, $prefix = NULL, $sep = NULL, $key = '', $urlencode = TRUE ) {
		$ret = array();

		foreach ( (array) $data as $k => $v ) {
			if ( $urlencode) {
				$k = urlencode($k);
			}
			if ( is_int($k) && $prefix != NULL ) {
				$k = $prefix . $k;
			}
			if ( !empty($key) ) {
				$k = $key . '%5B' . $k . '%5D';
			}
			if ( $v === NULL ) {
				continue;
			} elseif ( $v === FALSE ) {
				$v = '0';
			}

			if ( is_array($v) || is_object($v) ) {
				array_push($ret, self::httpBuildQuery($v, '', $sep, $k, $urlencode));
			} elseif ( $urlencode ) {
				array_push($ret, $k . '=' . urlencode($v));
			} else {
				array_push($ret, $k . '=' . $v);
			}
		}

		if ( NULL === $sep ) {
			$sep = ini_get('arg_separator.output');
		}
		return implode($sep, $ret);
	}

	/**
	 * Retrieve a modified URL query string.
	 * You can rebuild the URL and append a new query variable to the URL query by
	 * using this function. You can also retrieve the full URL with query data.
	 * Adding a single key & value or an associative array. Setting a key value to
	 * an empty string removes the key. Omitting oldquery_or_uri uses the $_SERVER
	 * value. Additional values provided are expected to be encoded appropriately
	 * with urlencode() or rawurlencode().
	 *
	 * @return string New URL query string.
	 */
	public static function addQueryArg() {
		$args = func_get_args();
		if ( is_array( $args[0] ) ) {
			if ( count( $args ) < 2 || FALSE === $args[1] ) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $args[1];
			}
		} else {
			if ( count( $args ) < 3 || FALSE === $args[2] ) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $args[2];
			}
		}

		$frag = strstr($uri, '#');
		if ($frag) {
			$uri = substr($uri, 0, - strlen($frag));
		} else {
			$frag = '';
		}

		if ( 0 === stripos( $uri, 'http://' ) ) {
			$protocol = 'http://';
			$uri = substr( $uri, 7 );
		} elseif ( 0 === stripos( $uri, 'https://' ) ) {
			$protocol = 'https://';
			$uri = substr( $uri, 8 );
		} else {
			$protocol = '';
		}

		if ( strpos( $uri, '?' ) !== FALSE ) {
			list( $base, $query ) = explode( '?', $uri, 2 );
			$base .= '?';
		} elseif ( $protocol || strpos( $uri, '=' ) === FALSE ) {
			$base = $uri . '?';
			$query = '';
		} else {
			$base = '';
			$query = $uri;
		}

		self::parseStr( $query, $qs );
		// this re-URL-encodes things that were already in the query string
		$qs = self::urlencodeDeep( $qs );
		if ( is_array( $args[0] ) ) {
			$kayvees = $args[0];
			$qs = array_merge( $qs, $kayvees );
		} else {
			$qs[$args[0]] = $args[1];
		}

		foreach ( $qs as $k => $v ) {
			if ( $v === FALSE ) {
				unset( $qs[$k] );
			}
		}

		$ret = self::httpBuildQuery( $qs, NULL, '&', '', FALSE);
		$ret = trim( $ret, '?' );
		$ret = preg_replace( '#=(&|$)#', '$1', $ret );
		$ret = $protocol . $base . $ret . $frag;
		$ret = rtrim( $ret, '?' );

		return $ret;
	}

	/**
	 * Check string for invalid UTF8 encoding.
	 *
	 * @param string $string Input string
	 * @param bool $strip Strip flag, use iconv function
	 *
	 * @return string
	 */
	public static function checkInvalidUtf8($string, $strip = FALSE) {
		$string = (string) $string;

		if (0 === strlen($string)) {
			return '';
		}

		// store the site charset as a static to avoid multiple calls to tx_laterpay_config::getOption()
		static $isUtf8;
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
			$isUtf8 = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == 'utf-8';
		} elseif (is_object($GLOBALS['LANG'])) {
			$isUtf8 = $GLOBALS['LANG']->charSet != 'utf-8';
		} else {
			// THIS is just a hopeful guess!
			$isUtf8 = FALSE;
		}

		if (!$isUtf8) {
			return $string;
		}

		// check for support for UTF8 in the installed PCRE library once and store the result in a static
		static $utf8Pcre;
		if (!isset($utf8Pcre)) {
			$utf8Pcre = @preg_match( '/^./u', 'a' );
		}
		// we can't require UTF8 in the PCRE installation, so just return the string in those cases
		if (!$utf8Pcre) {
			return $string;
		}

		// preg_match fails when it encounters invalid UTF8 in $string
		if (1 === @preg_match('/^./us', $string)) {
			return $string;
		}

		// attempt to strip the bad chars, if requested (not recommended)
		if ($strip && function_exists('iconv')) {
			return iconv('utf-8', 'utf-8', $string);
		}

		return '';
	}

	/**
	 * Sanitize text field.
	 *
	 * @param string $str Input text
	 *
	 * @return Ambigous <mixed, string, unknown>
	 */
	public static function sanitizeTextField($str) {
		$filtered = self::checkInvalidUtf8($str);

		if (strpos($filtered, '<') !== FALSE) {
			$filtered = strip_tags($filtered);
		} else {
			$filtered = trim(preg_replace('/[\r\n\t ]+/', ' ', $filtered));
		}

		$found = FALSE;
		while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
			$filtered = str_replace($match[0], '', $filtered);
			$found = TRUE;
		}

		if ($found) {
			// strip out the whitespace that may now exist after removing the octets
			$filtered = trim(preg_replace('/ +/', ' ', $filtered));
		}

		return $filtered;
	}

	/**
	 * Convert string to abs int.
	 *
	 * @param mixed $value Input value
	 *
	 * @return number
	 */
	public static function toAbsInt($value) {
		return abs(intval($value));
	}

	/**
	 * Unslash input string.
	 *
	 * @param string $value Inpit value
	 *
	 * @return multitype:
	 */
	public static function unslash($value) {
		if (is_array($value)) {
			$value = array_map('unslash', $value);
		} elseif (is_object($value)) {
			$vars = get_object_vars($value);
			foreach ($vars as $key => $data) {
				$value->{$key} = unslash($data);
			}
		} elseif (is_string($value)) {
			$value = stripslashes($value);
		}

		return $value;
	}
}
