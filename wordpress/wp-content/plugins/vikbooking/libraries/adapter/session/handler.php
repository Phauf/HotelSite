<?php
/** 
 * @package   	VikWP - Libraries
 * @subpackage 	adapter.session
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to handle the PHP session.
 * Provides the methods to begin a new session and to destroy it.
 *
 * @since 10.0
 */
class JSessionHandler
{
	/**
	 * Starts a new session, only if it doesn't already exist.
	 *
	 * @return 	void
	 */
	public static function start()
	{
		if (!self::isStarted())
		{
			session_start();
		}

		/**
		 * Filters whether to preempt an HTTP request's return value.
		 * It is needed to support concurrent cURL requests (see Site Health).
		 *
		 * Returning a non-false value from the filter will short-circuit the HTTP request and return
		 * early with that value. A filter should return either:
		 *
		 *  - An array containing 'headers', 'body', 'response', 'cookies', and 'filename' elements
		 *  - A WP_Error instance
		 *  - boolean false (to avoid short-circuiting the response)
		 *
		 * Returning any other value may result in unexpected behaviour.
		 *
		 * @since 2.9.0
		 *
		 * @param false|array|WP_Error $preempt  Whether to preempt an HTTP request's return value. Default false.
		 * @param array                $r        HTTP request arguments.
		 * @param string               $url      The request URL.
		 */
		add_filter('pre_http_request', function($preempt, $r, $url)
		{
			$input = JFactory::getApplication()->input;

			// check if we are running a "vik" plugin
			if (!preg_match("/^com_vik/", $input->get('option')))
			{
				// always write session and terminate it before starting a request
				// when a VIK plugin is not running
				session_write_close();
			}

			return $preempt;
		}, 10, 3);

		/**
		 * Suppress "session active" critical error when Site Health performs its tests.
		 *
		 * Even if the session is active on Site Health, the plugin always call the
		 * `session_write_close` method before making any HTTP requests.
		 *
		 * @since 5.5.0
		 */
		add_action('init', function() {
			global $pagenow;

			// check if the current page is Site Health
			if (preg_match("/^site-health/i", $pagenow))
			{
				// always write session to avoid receiving a critical issue
				session_write_close();
			}
		});
	}

	/**
	 * Destroys the current active session, only if it already exists.
	 *
	 * @param 	boolean  $restart 	True to immediately restart a new session.
	 *
	 * @return 	void
	 */
	public static function destroy($restart = true)
	{
		if (!self::isStarted())
		{
			return;
		}

		session_destroy();

		if ($restart)
		{
			self::start();
		}
	}

	/**
	 * Checks if a session has been started.
	 *
	 * @return 	boolean  True if active, otherwise false.
	 */
	public static function isStarted()
	{
		return (bool) self::getId();
	}

	/**
	 * Returns the session ID, if any.
	 *
	 * @return 	string 	The unique session ID if active, otherwise an empty string.
	 */
	public static function getId()
	{
		return session_id();
	}

	/**
	 * Returns the session name, if any.
	 *
	 * @return 	string 	The session name.
	 */
	public static function getName()
	{
		return session_name();
	}
}
