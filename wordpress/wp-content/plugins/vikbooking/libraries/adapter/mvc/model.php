<?php
/** 
 * @package   	VikWP - Libraries
 * @subpackage 	adapter.mvc
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.application.object');

/**
 * The model class used by the MVC framework.
 * A model can be used by a controller or a view to handle 
 * a certain entity of the plugin.
 *
 * The model can be invoked when the value contained 
 * in $_REQUEST['task'] is equals to 'ComponentModel' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups.save' -> ComponentModelGroups
 *
 * @since 10.0
 */
abstract class JModel extends JObject
{
	/**
	 * A list of model instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * A list of included paths.
	 *
	 * @var   array
	 * @since 10.1.24
	 */
	protected static $paths = array();

	/**
	 * The database table name.
	 *
	 * @var string
	 */
	protected $_table;

	/**
	 * The database table primary key name.
	 *
	 * @var string
	 */
	protected $_pk;

	/**
	 * The model name.
	 * e.g. ComponentModel[NAME]
	 *
	 * @var string
	 */
	protected $_name = null;

	/**
	 * The component name.
	 * e.g. [COMPONENT]ModelName
	 *
	 * @var string
	 */
	protected $_component = null;

	/**
	 * The application client.
	 *
	 * @var string
	 */
	protected $_client = null;

	/**
	 * Tries to load the specified model, only creating it if 
	 * it doesn't exist yet.
	 *
	 * @param 	string 	 $prefix 	The model prefix.
	 * @param 	string 	 $name 		The model name.
	 * @param 	string   $client   	The application client (null to auto-detect).
	 * @param 	string 	 $table 	The db table.
	 * @param 	string 	 $pk 		The primary key.
	 *
	 * @return 	mixed 	 The model instance if found, otherwise null.
	 */
	public static function getInstance($prefix, $name, $client = null, $table = null, $pk = 'id')
	{
		if (is_null($client))
		{
			$client = JFactory::getApplication()->isAdmin() ? 'admin' : 'site';
		}

		/**
		 * The current version of JModel is not using the same arguments as 
		 * specified by Joomla, since the model name is always passed as
		 * first argument.
		 *
		 * For this reason, we need to swap the prefix and the name when
		 * passed using Joomla standards. This can be done when at least one
		 * of the following conditions is verified:
		 *
		 * - the name contains 'model' word
		 * - the name is actually the folder name and the prefix isn't
		 *
		 * @since 10.1.24
		 */
		if (preg_match("/model/i", $name)
			|| (is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $name) && !is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $prefix)))
		{
			// swap name with prefix
			$tmp    = $name;
			$name   = $prefix;
			$prefix = $tmp;
		}

		// remove 'model' from prefix (if any) and make it lowercase
		$prefix = strtolower(preg_replace("/model/i", '', $prefix));

		$sign = serialize(array($prefix, $name, $client));

		if (!isset(static::$instances[$sign]))
		{
			static::$instances[$sign] = false;

			// create classname
			$classname = ucfirst($prefix) . 'Model' . ucfirst($name);

			/**
			 * Merge default include path (the fallback) with specified directories.
			 * Then iterate the list to find the first available model.
			 *
			 * @since 10.1.24
			 */
			$paths = array_merge(
				// specified include paths
				self::addIncludePath(),
				// default include path
				array(
					implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $prefix, $client, 'models'))
				)
			);

			// iterate paths until we find an existing file (or till the list is empty)
			for ($i = 0, $path = null; $i < count($paths) && !$path; $i++)
			{
				// create path
				$path = $paths[$i] . DIRECTORY_SEPARATOR . $name . '.php';

				// make sure the file exists
				if (!is_file($path))
				{
					// unset path to keep iterating
					$path = null;
				}
			}

			// make sure we have a path
			if ($path)
			{
				// include model
				require_once $path;

				// make sure the class exists
				if (class_exists($classname))
				{
					// cache model instance
					static::$instances[$sign] = new $classname($table, $pk);
				}
			}
		}

		return static::$instances[$sign];
	}

	/**
	 * Add a directory where the class should search for models.
	 * You may either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array of paths to search.
	 *
	 * @return  array 	An array with directory elements.
	 *
	 * @since   10.1.24
	 */
	public static function addIncludePath($path = null)
	{
		if (!empty($path))
		{
			JLoader::import('adapter.filesystem.path');

			foreach ((array) $path as $includePath)
			{
				$includePath = JPath::clean($includePath);

				// check if the path is a dir
				if (!is_dir($includePath))
				{
					// extract name between component and models
					if (preg_match("/components[\/\\\\]com_([a-z0-9_]+)[\/\\\\]models/", $includePath, $match))
					{
						// access adapters of current plugin
						$option = JFactory::getApplication()->input->get('option');
						// strip initial com_ from option name
						$option = preg_replace("/^com_/", '', $option);

						// rewrite include path
						$includePath = JPath::clean(WP_PLUGIN_DIR . '/' . $option . '/libraries/adapter/mvc/models/' . end($match));
					}
				}

				// make sure the folder is not already in the list
				if (!in_array($includePath, static::$paths))
				{
					// push directory as first
					array_unshift(static::$paths, $includePath);
				}
			}
		}

		return static::$paths;
	}

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$table 	The db table.
	 * @param 	string 	$pk 	The primary key.
	 */
	public function __construct($table = null, $pk = 'id')
	{
		$this->_table 	= $table;
		$this->_pk 		= $pk;
	}

	/**
	 * Returns the DB table name.
	 *
	 * @return 	string 	The db table.
	 */
	public function getTableName()
	{
		return $this->_table;
	}

	/**
	 * Returns the DB table primary key column name.
	 *
	 * @return 	string 	The primary key.
	 */
	public function getPrimaryKey()
	{
		return $this->_pk;
	}

	/**
	 * Returns the model name.
	 *
	 * @return 	string 	Model name.
	 */
	public function getModelName()
	{
		if (is_null($this->_name))
		{
			$class = get_class($this);

			if (preg_match("/Model(.*?)$/", $class, $match))
			{
				$this->_name = strtolower($match[1]);
			}
			else
			{
				$this->_name = $class;
			}	
		}

		return $this->_name;
	}

	/**
	 * Returns the component name.
	 *
	 * @return 	string 	Component name.
	 */
	public function getComponentName()
	{
		if (is_null($this->_component))
		{
			$class = get_class($this);

			if (preg_match("/(.*?)Model/", $class, $match))
			{
				$this->_component = strtolower($match[1]);
			}
			else
			{
				$this->_component = $class;
			}	
		}

		return $this->_component;
	}

	/**
	 * Returns the application client folder (admin or site).
	 *
	 * @return 	string 	The client.
	 */
	public function getClientFolder()
	{
		if (is_null($this->_client))
		{
			$this->_client = JFactory::getApplication()->isAdmin() ? 'admin' : 'site';
		}

		return $this->_client;
	}
}

/**
 * Alias for JModel, which is still used by the components.
 *
 * @since 10.1.24
 */
class JModelLegacy extends JModel
{

}
