<?php

/*
 * This file is part of Pug
 */
namespace Pug;

/*
 * The MIT License (MIT)
 * Copyright (c) 2010 Justin Hileman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Pug autoloader
 *
 * Usage: Pug\Autoloader::register();
 */
class Autoloader
{
	private $baseDir;

	/**
	 * Autoloader constructor
	 *
	 * @param	string	$baseDir	Pug library base directory (default: dirname(__FILE__).'/..')
	 */
	public function __construct($baseDir = null)
	{
		if ($baseDir === null) {
			$baseDir = dirname(__FILE__).'/..';
		}

		// realpath doesn't always work, for example, with stream URIs
		$realDir = realpath($baseDir);
		if (is_dir($realDir))
		{
			$this->baseDir = $realDir;
		}
		else
		{
			$this->baseDir = $baseDir;
		}
	}

	/**
	 * Register a new instance as an SPL autoloader.
	 *
	 * @param	string	$baseDir	Pug library base directory (default: dirname(__FILE__).'/..')
	 * @return	Autoloader			Registered Autoloader instance
	 */
	public static function register($baseDir = null)
	{
		$loader = new self($baseDir);
		spl_autoload_register(array($loader, 'autoload'));

		return $loader;
	}

	/**
	 * Autoload Pug classes
	 *
	 * @param	string	$class
	 */
	public function autoload($class)
	{
		if($class[0] === '\\')
		{
			$class = substr($class, 1);
		}

		if(strpos($class, __NAMESPACE__) !== 0)
		{
			return;
		}

		$file = sprintf('%s/%s.php', $this->baseDir, str_replace('\\', '/', $class));
		if(is_file($file))
		{
			require $file;
		}
	}
}

?>
