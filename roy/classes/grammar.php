<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

// What follows is a stripped down version of the fGrammar class of the
// Flourish project (http://flourishlib.com), containing only the
// pluralize() method and supporting methods.

/**
 * Copyright (c) 2007-2010 Will Bond <will@flourishlib.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Grammar
{
	/**
	 * Rules for singular to plural inflection of nouns
	 * 
	 * @var array
	 */
	static private $singular_to_plural_rules = array(
		'([ml])ouse$'                  => '\1ice',
		'(media|info(rmation)?|news)$' => '\1',
		'(phot|log)o$'                 => '\1os',
		'^(q)uiz$'                     => '\1uizzes',
		'(c)hild$'                     => '\1hildren',
		'(p)erson$'                    => '\1eople',
		'(m)an$'                       => '\1en',
		'([ieu]s|[ieuo]x)$'            => '\1es',
		'([cs]h)$'                     => '\1es',
		'(ss)$'                        => '\1es',
		'([aeo]l)f$'                   => '\1ves',
		'([^d]ea)f$'                   => '\1ves',
		'(ar)f$'                       => '\1ves',
		'([nlw]i)fe$'                  => '\1ves',
		'([aeiou]y)$'                  => '\1s',
		'([^aeiou])y$'                 => '\1ies',
		'([^o])o$'                     => '\1oes',
		's$'                           => 'ses',
		'(.)$'                         => '\1s'
	);
	
	/**
	 * Returns the plural version of a singular noun
	 * 
	 * @param  string  $singular_noun  The singular noun to pluralize
	 * @param  boolean $return_error   If this is `TRUE` and the noun can't be pluralized, `FALSE` will be returned instead
	 * @return string  The pluralized noun
	 */
	static public function pluralize($singular_noun)
	{
		$original    = $singular_noun;
		$plural_noun = NULL;
		
		list ($beginning, $singular_noun) = self::splitLastWord($singular_noun);
		foreach (self::$singular_to_plural_rules as $from => $to) {
			if (preg_match('#' . $from . '#iD', $singular_noun)) {
				$plural_noun = $beginning . preg_replace('#' . $from . '#iD', $to, $singular_noun);
				break;
			}
		}
		
		if (!$plural_noun) {
			throw new RoyProgrammerException('The noun specified could not be pluralized');
		}
		
		return $plural_noun;
	}
	
	/**
	 * Splits the last word off of a `camelCase` or `underscore_notation` string
	 * 
	 * @param  string $string  The string to split the word from
	 * @return array  The first element is the beginning part of the string, the second element is the last word
	 */
	static private function splitLastWord($string)
	{
		// Handle strings with spaces in them
		if (strpos($string, ' ') !== FALSE) {
			return array(substr($string, 0, strrpos($string, ' ')+1), substr($string, strrpos($string, ' ')+1));
		}
		
		// Handle underscore notation
		if ($string == self::underscorize($string)) {
			if (strpos($string, '_') === FALSE) { return array('', $string); }
			return array(substr($string, 0, strrpos($string, '_')+1), substr($string, strrpos($string, '_')+1));
		}
		
		// Handle camel case
		if (preg_match('#(.*)((?<=[a-zA-Z]|^)(?:[0-9]+|[A-Z][a-z]*)|(?<=[0-9A-Z]|^)(?:[A-Z][a-z]*))$#D', $string, $match)) {
			return array($match[1], $match[2]);
		}
		
		return array('', $string);
	}
	
	/**
	 * Converts a `camelCase`, human-friendly or `underscore_notation` string to `underscore_notation`
	 * 
	 * @param  string $string  The string to convert
	 * @return string  The converted string
	 */
	static public function underscorize($string)
	{
		$original = $string;
		$string = strtolower($string[0]) . substr($string, 1);
		
		// If the string is already underscore notation then leave it
		} elseif (strpos($string, '_') !== FALSE) {
		
		// Allow humanized string to be passed in
		} elseif (strpos($string, ' ') !== FALSE) {
			$string = strtolower(preg_replace('#\s+#', '_', $string));
		
		} else {
			do {
				$old_string = $string;
				$string = preg_replace('/([a-zA-Z])([0-9])/', '\1_\2', $string);
				$string = preg_replace('/([a-z0-9A-Z])([A-Z])/', '\1_\2', $string);
			} while ($old_string != $string);
			
			$string = strtolower($string);
		}
		
		return $string;
	}
	
	/**
	 * Forces use as a static class
	 * 
	 * @return Grammar
	 */
	private function __construct() { }
}
