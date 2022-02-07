<?php
/** 
 * @package   	VikWP - Libraries
 * @subpackage 	adapter.loader
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Plugin smart loader class.
 *
 * @since 10.0
 */
abstract class JLoader
{
	/**
	 * The list containing all the resources loaded.
	 *
	 * @var array
	 */
	protected static $includes = array();

	/**
	 * The list containing all the filename aliases.
	 *
	 * @var array
	 */
	protected static $aliases = array();

	/**
	 * Base path to load resources.
	 *
	 * @var string
	 */
	public static $base = '';

	/**
	 * Loads the specified file.
	 *
	 * @param   string  $key   The class name to look for (dot notation).
	 * @param   string  $base  Search this directory for the class.
	 *
	 * @return  boolean  True on success, otherwise false.
	 */
	public static function import($key, $base = null)
	{
		// if no base provided, use the default one
		if (empty($base))
		{
			$base = static::$base;
		}

		$sign = serialize(array($key, $base));

		// if the resource is not loaded, try to do it
		if (!isset(static::$includes[$sign]))
		{
			$success = false;

			// remove trailing slash (if any)
			$base = rtrim($base, DIRECTORY_SEPARATOR);

			$parts = explode('.', $key);
			$class = array_pop($parts);

			// if the file has been registered with an alias, replace it with the original one
			if (isset(static::$aliases[$class]))
			{
				$class = static::$aliases[$class];
			}

			// re-insert class to build the relative path
			$parts[] = $class;

			// build the path
			$path = implode(DIRECTORY_SEPARATOR, $parts);

			// if the file exists, load it
			if (is_file($base . DIRECTORY_SEPARATOR . $path . '.php'))
			{
				$success = (bool) include_once $base . DIRECTORY_SEPARATOR . $path . '.php';
			}

			// cache the loading status
			static::$includes[$sign] = $success;
		}

		return static::$includes[$sign];
	}

	/**
	 * Register an alias of a given class filename.
	 * This is useful for those files that contain a dot in their name.
	 *
	 * @param 	string 	$name 	The filename to register.
	 * @param 	string 	$alias 	The alias to use.
	 */
	public static function registerAlias($name, $alias)
	{	
		if (!isset(static::$aliases[$alias]))
		{
			static::$aliases[$alias] = $name;
		}
	}
}
