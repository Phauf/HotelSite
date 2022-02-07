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

JLoader::import('adapter.mvc.view');
JLoader::import('adapter.mvc.model');

/**
 * The main controller used by the MVC framework.
 * This controller is used to dispatch the requested actions
 * to the apposite views.
 *
 * @since 10.0
 */
abstract class JController
{
	/**
	 * A list of controller instance.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * A list of excluded methods.
	 *
	 * @var array
	 */
	protected $excludedMethods = array();

	/**
	 * The controller prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * The base path of the plugin.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  10.1.30
	 */
	protected $redirect;

	/**
	 * Redirect message.
	 *
	 * @var    string
	 * @since  10.1.30
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 * @since  10.1.30
	 */
	protected $messageType;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var    array
	 * @since  10.1.30
	 */
	protected $taskMap;

	/**
	 * Class constructor.
	 *
	 * @param   string  $prefix  The prefix for the controller.
	 * @param   string  $base  	 The base path from which loading the controller.
	 */
	public function __construct($prefix, $base)
	{
		$this->prefix 	= $prefix;
		$this->basePath = $base;

		$reflect = new ReflectionClass('JController');

		foreach ($reflect->getMethods() as $method)
		{
			$this->excludedMethods[] = $method->getName();
		}

		$this->registerTask('unpublish', 'publish');
	}

	/**
	 * Method to get a singleton controller instance.
	 *
	 * @param   string  $prefix  The prefix for the controller.
	 * @param   string  $base  	The base path from which loading the controller.
	 *
	 * @return  self 	A new controller instance.
	 */
	public static function getInstance($prefix, $base)
	{
		if (!isset(static::$instances[$prefix]))
		{
			$app   = JFactory::getApplication();
			$input = $app->input;

			$task = $input->get('task');
			$cmd  = $input->get('controller');

			if (strpos($task, '.') !== false)
			{
				$split = explode('.', $task);

				$cmd  = array_shift($split);
				$task = array_pop($split);
			}

			$folder = $app->isAdmin() ? 'admin' : 'site';

			// load the main controller
			if (!JLoader::import($folder . '.controller', $base))
			{
				wp_die(
					'<h1>' . JText::translate('FATAL_ERROR') . '</h1>' .
					'<p>' . JText::translate('CONTROLLER_FILE_NOT_FOUND_ERR') . '</p>',
					404
				);
			}

			$className = $prefix . 'Controller';

			// check if the controller class exists
			if (!class_exists($className))
			{
				wp_die(
					'<h1>' . JText::translate('FATAL_ERROR') . '</h1>' .
					'<p>' . JText::sprintf('CONTROLLER_CLASS_NOT_FOUND_ERR', $className) . '</p>',
					404
				);
			}

			// try to check if we should load a dedicated controller
			if ($cmd && JLoader::import($folder . '.controllers.' . $cmd, $base))
			{
				$childClass = $className . ucwords($cmd);

				if (class_exists($childClass))
				{
					$className = $childClass;
				}
			}

			// instantiate the controller
			$controller = new $className($prefix, $base);

			// make sure the controller is a valid instance
			if (!$controller instanceof JController)
			{
				wp_die(
					'<h1>' . JText::translate('FATAL_ERROR') . '</h1>' .
					'<p>' . JText::translate('CONTROLLER_INVALID_INSTANCE_ERR') . '</p>',
					500
				);
			}

			// cache the instance
			static::$instances[$prefix] = $controller;
		}

		return static::$instances[$prefix];
	}

	/**
	 * Typical view method for MVC based architecture.
	 *
	 * This function is provided as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	getView()
	 * @uses 	getModel()
	 */
	public function display()
	{
		$input = JFactory::getApplication()->input;
		
		// get view name
		$action = $input->get('view', null);
		$layout = $input->get('layout', null);

		if ($action)
		{
			// try to obtain the view related to the specified action
			$view = $this->getView($action);

			if ($view)
			{
				// try to obtain the model related to the specified view
				$model = $this->getModel($action);

				if ($model)
				{
					// attach the model if exists
					$view->setModel($model);
				}

				/**
				 * Fires before the controller displays the view.
				 *
				 * @param 	JView  $view  The view instance.
				 *
				 * @since 	10.1.16
				 */
				do_action_ref_array(strtolower($this->prefix) . '_before_display_' . strtolower($action), array(&$view));

				// display the view before to terminate
				$view->display($layout);

				/**
				 * Fires after the controller displayed the view.
				 *
				 * @param 	JView  $view  The view instance.
				 *
				 * @since 	10.1.16
				 */
				do_action(strtolower($this->prefix) . '_after_display_' . strtolower($action), array($view));
			}

		}

		return $this;
	}

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task 	The task to perform. If no matching task is found, 
	 * 							the default 'display' method is executed.
	 *
	 * @return  mixed   The value returned by the called method.
	 */
	public function execute($task)
	{
		// get only the string after the dot, if any
		if (strpos($task, '.') !== false)
		{
			$split = explode('.', $task);
			$task  = array_pop($split);

			/**
			 * Reset the task without the controller context.
			 *
			 * @since 10.1.30
			 */
			JFactory::getApplication()->input->set('task', $task);
		}

		// raise an error if we are trying to call reserved methods
		if (in_array($task, $this->excludedMethods) && $task != 'display')
		{
			wp_die(
				'<h1>' . JText::translate('FATAL_ERROR') . '</h1>' .
				'<p>' . JText::translate('CONTROLLER_PROTECTED_METHOD_ERR') . '</p>',
				403
			);
		}

		$reflect = new ReflectionClass(get_class($this));

		// check if we should use a different task linked to the specified one
		if (isset($this->taskMap[$task]))
		{
			$task = $this->taskMap[$task];
		}

		// check if the $task method is callable
		if (!$reflect->hasMethod($task) || !$reflect->getMethod($task)->isPublic())
		{
			// otherwise use default 'display' method
			$task = 'display';
		}

		try
		{
			// dispatch callback
			$result = call_user_func(array($this, $task));
		}
		catch (Exception $e)
		{
			// We need to terminate the buffer here to avoid displaying 
			// the output printed by the views into the error screen.

			while (ob_get_status())
			{
				// repeat until the buffer is empty
				ob_end_clean();
			}

			if (!wp_doing_ajax())
			{
				// raise an error in case an exception has been thrown
				wp_die(
					'<h1>' . JText::translate('FATAL_ERROR') . '</h1>' .
					'<p>' . $e->getMessage() . '</p>',
					($code = $e->getCode()) ? $code : 500
				);
			}
			else
			{
				/**
				 * Raise a minified error for AJAX requests.
				 *
				 * @since 10.1.21
				 */
				wp_die(
					$e->getMessage(),
					($code = $e->getCode()) ? $code : 500
				);
			}
		}

		return $result;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect.
	 * @param   string  $type  Message type.
	 *
	 * @return  self    This object to support chaining.
	 *
	 * @since   10.1.30
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		// register redirection URL
		$this->redirect = $url;

		if ($msg !== null)
		{
			// controller may have set this directly
			$this->message = $msg;
		}

		// ensure the type is not overwritten by a previous call to setMessage
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// if the type is explicitly set, set it
		else
		{
			$this->messageType = $type;
		}

		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   10.1.30
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$app = JFactory::getApplication();

			if ($this->message)
			{
				// enqueue the redirect message
				$app->enqueueMessage($this->message, $this->messageType);
			}

			// execute the redirect
			$app->redirect($this->redirect);
		}

		return false;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task.
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since   10.1.30
	 */
	public function registerTask($task, $method)
	{
		$this->taskMap[strtolower($task)] = $method;

		return $this;
	}

	/**
	 * Returns the view object related to the specified name.
	 *
	 * @param 	string 	$view 	The view name.
	 *
	 * @return 	mixed 	The view object if exists, otherwise false.
	 */
	protected function getView($view)
	{
		$app = JFactory::getApplication();

		$folder = $app->isAdmin() ? 'admin' : 'site';

		// get view path
		$path = implode(DIRECTORY_SEPARATOR, array($this->basePath, $folder, 'views', $view, 'view.html.php'));

		// make sure the file path exists
		if (!is_file($path))
		{
			return false;
		}

		// include the view file
		require_once $path;

		$className = $this->prefix . 'View' . ucwords($view);

		// make sure the view class exists
		if (!class_exists($className))
		{
			return false;
		}

		$obj = new $className(dirname($path));

		// make sure the view is a valid instance
		if (!$obj instanceof JView)
		{
			return false;
		}

		return $obj;
	}

	/**
	 * Returns the model object related to the specified name.
	 *
	 * @param 	string 	$model 	The model name.
	 *
	 * @return 	mixed 	The model object if exists, otherwise false.
	 */
	protected function getModel($model = null)
	{
		$app = JFactory::getApplication();

		if (is_null($model))
		{
			// obtain the model name from the classname of this controller
			$model = strtolower(str_replace($this->prefix . 'Controller', '', get_class($this)));
		}

		$folder = $app->isAdmin() ? 'admin' : 'site';

		// get model path
		$path = implode(DIRECTORY_SEPARATOR, array($this->basePath, $folder, 'models', $model . '.php'));

		// make sure the file path exists
		if (!is_file($path))
		{
			return false;
		}

		// include the model file
		require_once $path;

		$className = $this->prefix . 'Model' . ucwords($model);

		// make sure the model class exists
		if (!class_exists($className))
		{
			return false;
		}

		$obj = new $className();

		// make sure the model is a valid instance
		if (!$obj instanceof JModel)
		{
			return false;
		}

		return $obj;
	}
}
