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
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
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
		$content = preg_replace('/\s+/', ' ', $content);
		$totalWords = count(explode(' ', $content));

		$config = tx_laterpay_config::getInstance();

		$percent = (int) $config->get('content.preview_percentage_of_content');
		$percent = max(min($percent, 100), 1);
		$min = (int) $config->get('content.preview_word_count_min');
		$max = (int) $config->get('content.preview_word_count_max');

		$numberOfWords = $totalWords * ($percent / 100);
		$numberOfWords = max(min($numberOfWords, $max), $min);

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
			'ellipsis' => ' ...',
			'exact' => TRUE,
			'html' => FALSE,
			'words' => FALSE
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
			$totalLength = mb_strlen(strip_tags($ellipsis));
			$openTags = array();
			$truncate = '';

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
}