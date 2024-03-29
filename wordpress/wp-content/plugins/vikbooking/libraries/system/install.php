<?php
/** 
 * @package   	VikBooking - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

VikBookingLoader::import('update.manager');
VikBookingLoader::import('update.license');

/**
 * Class used to handle the activation, deactivation and 
 * uninstallation of VikBooking plugin.
 *
 * @since 1.0
 */
class VikBookingInstaller
{
	/**
	 * Flag used to init the class only once.
	 *
	 * @var boolean
	 */
	protected static $init = false;

	/**
	 * Initialize the class attaching wp actions.
	 *
	 * @return 	void
	 */
	public static function onInit()
	{
		// init only if not done yet
		if (static::$init === false)
		{
			// handle installation message
			add_action('admin_notices', array('VikBookingInstaller', 'handleMessage'));

			/**
			 * Register hooks and actions here
			 */

			// mark flag as true to avoid init it again
			static::$init = true;
		}

		/**
		 * Check whether the Pro license has expired.
		 * 
		 * @since 	1.2.8
		 */
		add_action('admin_notices', array('VikBookingInstaller', 'handleUpdateWarning'));
	}

	/**
	 * Handles the activation of the plugin.
	 *
	 * @param 	boolean  $message 	True to display the activation message,
	 * 								false to ignore it.
	 *
	 * @return 	void
	 */
	public static function activate($message = true)
	{
		// get installed software version
		$version = get_option('vikbooking_software_version', null);

		// check if the plugin has been already installed
		if (is_null($version))
		{
			// dispatch UPDATER to launch installation queries
			VikBookingUpdateManager::install();

			// mark the plugin has installed to avoid duplicated installation queries
			update_option('vikbooking_software_version', VIKBOOKING_SOFTWARE_VERSION);
		}

		if ($message)
		{
			// set activation flag to display a message
			add_option('vikbooking_onactivate', 1);
		}
	}

	/**
	 * Handles the deactivation of the plugin.
	 *
	 * @return 	void
	 */
	public static function deactivate()
	{
		// do nothing for the moment
	}

	/**
	 * Handles the uninstallation of the plugin.
	 *
	 * @param 	boolean  $drop 	True to drop the tables of VikBooking from the database.
	 *
	 * @return 	void
	 */
	public static function uninstall($drop = true)
	{
		// dispatch UPDATER to drop database tables
		VikBookingUpdateManager::uninstall($drop);

		// delete installation flag
		delete_option('vikbooking_software_version');
	}

	/**
	 * Handles the uninstallation of the plugin.
	 * Proxy for uninstall method which always force database drop.
	 *
	 * @return 	void
	 *
	 * @uses 	uninstall()
	 *
	 * @since 	1.2.6
	 */
	public static function delete()
	{
		// complete uninstallation by dropping the database
		static::uninstall(true);
	}

	/**
	 * Checks if the current version should be updated
	 * and, eventually, processes it.
	 * 
	 * @return 	void
	 */
	public static function update()
	{
		// get installed software version
		$version = get_option('vikbooking_software_version', null);

		$app = JFactory::getApplication();

		// check if we are running an older version
		if (VikBookingUpdateManager::shouldUpdate($version))
		{
			/**
			 * Avoid useless redirections if doing ajax.
			 * 
			 * @since 1.1.6
			 */
			if (!wp_doing_ajax() && $app->isAdmin())
			{
				// process the update (we don't need to raise an error)
				VikBookingUpdateManager::update($version);

				// update cached plugin version
				update_option('vikbooking_software_version', VIKBOOKING_SOFTWARE_VERSION);

				// check if pro version
				if (VikBookingLicense::isPro())
				{
					// go to the pro-package download page
					$app->redirect('index.php?option=com_vikbooking&view=getpro&version=' . $version);
					exit;
				}
			}
		}
		// check if the current instance is a new blog of a network
		else if (is_null($version))
		{
			/**
			 * The version is NULL, vikbooking_software_version doesn't
			 * exist as an option of this blog.
			 * We need to launch the installation manually.
			 *
			 * @see 	activate()
			 *
			 * @since 	1.0.6
			 */

			// Use FALSE to ignore the activation message
			static::activate(false);
		}
	}

	/**
	 * Callback used to complete the update of the plugin
	 * made after a scheduled event.
	 *
	 * @param 	array  $results  The results of all attempted updates.
	 *
	 * @return 	void
	 *
	 * @since 	1.3.12
	 */
	public static function automaticUpdate($results)
	{
		// create log trace
		$trace = '### VikBooking Automatic Update | ' . JHtml::fetch('date', new JDate(), 'Y-m-d H:i:s') . "\n\n";
		$trace .= "```json\n" . json_encode($results, JSON_PRETTY_PRINT) . "\n```\n\n";

		// iterate all plugins
		foreach ($results['plugin'] as $plugin)
		{
			if (!empty($plugin->item->slug))
			{
				// register check trace
				$trace .= "Does `{$plugin->item->slug}` match `vikbooking`?\n\n";

				// make sure the plugin slug matches this one
				if ($plugin->item->slug == 'vikbooking')
				{
					// register status trace
					$trace .= "Did WP complete the update without errors? [" . ($plugin->result ? 'Y' : 'N') . "]\n\n";

					// plugin found, make sure the update was successful
					if ($plugin->result)
					{
						try
						{
							// register version trace
							$trace .= sprintf("Updating from [%s] to [%s]...\n\n", VIKBOOKING_SOFTWARE_VERSION, $plugin->item->new_version);

							// complete the update in background
							static::backgroundUpdate($plugin->item->new_version);

							// update completed without errors
							$trace .= "Background update completed\n\n";
						}
						catch (Exception $e)
						{
							// something went wrong, register error within the trace
							$trace .= sprintf(
								"An error occurred while trying to finalize the update (%d):\n> %s\n\n",
								$e->getCode(),
								$e->getMessage()
							);

							/**
							 * @todo An error occurred while trying to download the PRO version,
							 *       evaluate to send an e-mail to the administrator.
							 */
						}
					}
				}
			}
		}

		// register debug trace within a log file
		JLoader::import('adapter.filesystem.file');
		JFile::write(VIKBOOKING_BASE . DIRECTORY_SEPARATOR . 'au-log.md', $trace . "---\n\n");
	}

	/**
	 * Same as update task, but all made in background.
	 *
	 * @param 	string  $new_version  The new version of the plugin.
	 * 
	 * @return 	void
	 *
	 * @since 	1.3.12
	 */
	protected static function backgroundUpdate($new_version)
	{
		// get installed software version
		$version = get_option('vikbooking_software_version', null);

		// DO NOT use shouldUpdate method because, since we are always within
		// the same flow, the version constant is still referring to the previous
		// version. So, always assume to proceed with the update of the plugin.
		
		// process the update (we don't need to raise an error)
		VikBookingUpdateManager::update($version);

		// update cached plugin version
		update_option('vikbooking_software_version', $new_version);

		// check if pro version
		if (VikBookingLicense::isPro())
		{
			// load license model
			$model = JModel::getInstance('vikbooking', 'license', 'admin');

			// download PRO version hoping that all will go fine
			$result = $model->download(VikBookingLicense::getKey());

			if ($result === false)
			{
				// an error occurred, retrieve it as exception
				$error = $model->getError(null, $toString = false);

				// propagate exception
				throw $error;
			}
		}
	}

	/**
	 * Checks whether the automatic updates should be turned off.
	 * This is useful to prevent auto-updates for those customers
	 * that are running an expired PRO version. This will avoid
	 * losing the files after an unexpected update.
	 *
	 * @param 	boolean  $update  The current auto-update choice.
	 * @param 	object   $item    The plugin offer.
	 *
	 * @return 	mixed    Null to let WP decides, false to always deny it.
	 *
	 * @since 	1.3.12
	 */
	public static function useAutoUpdate($update, $item)
	{
		// make sure we are fetching VikBooking
		if (!empty($item->slug) && $item->slug == 'vikbooking')
		{
			// plugin found, lets check whether the user is
			// not running the PRO version
			if (!VikBookingLicense::isPro())
			{
				// not a PRO version, check whether a license
				// key was registered
				if (VikBookingLicense::getKey())
				{
					// The plugin registered a key; the customer
					// chose to let the license expires...
					// We need to prevent auto-updates.
					$update = false;
				}
			}
		}

		return $update;
	}

	/**
	 * In case of an expired PRO version, prompts a message informing
	 * the user that it is going to lose the PRO features.
	 *
	 * @param  array  $data      An array of plugin metadata.
 	 * @param  array  $response  An array of metadata about the available plugin update.
	 *
	 * @return 	void
	 *
	 * @since 	1.3.12
	 */
	public static function getUpdateMessage($data, $response)
	{
		// check whether the user is not running the PRO version
		if (!VikBookingLicense::isPro())
		{
			// not a PRO version, check whether a license
			// key was registered
			if (VikBookingLicense::getKey())
			{
				// The plugin registered a key; the customer
				// chose to let the license expires...
				// We need to display an alert.
				add_action('admin_footer', function() use ($data, $response)
				{
					// display layout
					echo JLayoutHelper::render(
						'html.license.update',
						array($data, $response),
						null,
						array('component' => 'com_vikbooking')
					);
				});
			}
		}
	}

	/**
	 * Method used to check for any installation message to show.
	 *
	 * @return 	void
	 */
	public static function handleMessage()
	{
		$app = JFactory::getApplication();

		// if we are in the admin section and the plugin has been activated
		if ($app->isAdmin() && get_option('vikbooking_onactivate') == 1)
		{
			// delete the activation flag to avoid displaying the message more than once
			delete_option('vikbooking_onactivate');

			?>
			<div class="notice is-dismissible notice-success">
				<p>
					<strong>Thanks for activating our plugin!</strong>
					<a href="https://vikwp.com" target="_blank">https://vikwp.com</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Check if the Pro version has expired to alert the user that an
	 * update would actually mean downgrading to the free version.
	 * 
	 * @since 	1.2.8
	 */
	public static function handleUpdateWarning()
	{
		global $pagenow;
	
		if ($pagenow == 'plugins.php' && VikBookingLicense::isExpired() && !VikBookingLicense::hasVcm())
		{
			if (!JFactory::getApplication()->input->cookie->getInt('vbo_update_warning_hide', 0))
			{
				?>
				<div class="notice is-dismissible notice-warning" id="vbo-update-warning">
					<p>
						<strong><?php echo JText::translate('VBOPROVEXPWARNUPD'); ?></strong>
					</p>
				</div>

				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#vbo-update-warning').on('click', '.notice-dismiss', function(event, el) {
						var numWeeks = 1;
						if (typeof localStorage !== 'undefined') {
							numWeeks = localStorage.getItem('vbo_update_warning_hide_count');
							numWeeks = !numWeeks ? 0 : parseInt(numWeeks);
							numWeeks++;
							localStorage.setItem('vbo_update_warning_hide_count', numWeeks);
						}
						var nd = new Date();
						nd.setDate(nd.getDate() + (7 * numWeeks));
						document.cookie = 'vbo_update_warning_hide=1; expires=' + nd.toUTCString() + '; path=/; SameSite=Lax';
					});
				});
				</script>
				<?php
			}
		}
	}
}
