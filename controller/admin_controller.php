<?php
/**
*
* @package Disable Extensions
* @copyright (c) 2017 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\disableext\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\cache\service;
use phpbb\extension\manager;
use david63\disableext\ext;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/** @var string Custom form action */
	protected $u_action;

	/** @var string for current extension name */
	protected $current_ext;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config				$config						Config object
	* @param \phpbb\db\driver\driver_interface	$db							Database object
	* @param \phpbb\request\request				$request					Request object
	* @param \phpbb\template\template			$template					Template object
	* @param \phpbb\user						$user						User object
	* @param \phpbb\language\language			$language					Language object
	* @param \phpbb\log\log						$log						Log object
	* @param \phpbb\cache\service				$cache						Cache object
	* @param \phpbb\extension\manager			$phpbb_extension_manager	Extension manager
	*
	* @return \david63\disableext\controller\admin_controller
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, request $request, template $template, user $user, language $language, log $log, service $cache, manager $phpbb_extension_manager)
	{
		$this->config 					= $config;
		$this->db  						= $db;
		$this->request					= $request;
		$this->template					= $template;
		$this->user						= $user;
		$this->language					= $language;
		$this->log						= $log;
		$this->cache 					= $cache;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;

		$this->current_ext 				= 'david63/disableext';
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Add the language file
		$this->language->add_lang('acp_disableext', $this->current_ext);

		// Create a form key for preventing CSRF attacks
		$form_key 	= 'disableext_manage';
		add_form_key($form_key);

		// Set the intial variables
		$orig_ext_count	= $this->request->variable('orig_ext_count', 0);
		$confirm 		= false;
		$continue		= true;

		// Is the form being submitted?
		if ($this->request->is_set_post('continue') || $this->request->is_set_post('confirm'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, continue processing
			if ($this->request->is_set_post('continue'))
			{
				$confirm 	= true;
				$continue 	= false;
			}
			else if ($this->request->is_set_post('confirm'))
			{
				// Get the enabled extensions, excluding this one
				$sql = 'SELECT ext_name
					FROM ' . EXT_TABLE . "
					WHERE ext_active = 1
					AND ext_name <> '" . $this->db->sql_escape($this->current_ext) . "'";

				$result = $this->db->sql_query($sql);

				// Now we can try to disable the extensions
				if (!empty($result))
				{
					while ($ext_name = $this->db->sql_fetchrow($result))
					{
						//while ($this->phpbb_extension_manager->disable_step($ext_name['ext_name']));
					}
					$this->db->sql_freeresult($result);
				}
				else
				{
					// No extensions were found to disable, this should not happen - but just in case!
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DISABLE_UNABLE_LOG');
					trigger_error($this->language->lang('NO_EXT_UNABLE'), E_USER_WARNING);
				}

				// Get count of extensions disabled
  				$disabled_ext = $orig_ext_count - $this->get_active_ext();

				// Add disable action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DISABLE_EXTENSIONS_LOG', time(), array($disabled_ext, $orig_ext_count));

				// Extensions have been disabled and logged
				// Confirm this to the user
				trigger_error($this->language->lang('EXTENSIONS_DISABLED', $disabled_ext, $orig_ext_count));
			}
		}

		// Let's see how many extension we can disable
		$orig_ext_count = $this->get_active_ext();

		// No point doing any processing if there is nothing to process
		if ($orig_ext_count == 0)
		{
			trigger_error($this->language->lang('NO_EXT_DATA'), E_USER_WARNING);
		}

		$hidden_fields = array(
			'orig_ext_count' => $orig_ext_count,
		);

		// Template vars for header panel
		$this->template->assign_vars(array(
			'HEAD_TITLE'		=> $this->language->lang('DISABLE_EXTENSIONS'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('DISABLE_EXTENSIONS_EXPLAIN'),

			'VERSION_NUMBER'	=> ext::DISABLE_EXT_VERSION,
		));

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'FORM_CONFIRM'			=> $confirm,
			'FORM_CONTINUE'			=> $continue,
			'S_HIDDEN_FIELDS'		=> build_hidden_fields($hidden_fields),
			'U_ACTION' 				=> $this->u_action,
		));

		if ($continue)
		{
			$this->template->assign_var('MESSAGE', $this->language->lang('DISABLE_COUNT', (int)$orig_ext_count));
		}
		else if ($confirm)
		{
			$this->template->assign_var('MESSAGE', $this->language->lang('ARE_YOU_SURE', (int)$orig_ext_count));
		}
	}

	/**
	* Get count of active extensions
	*
	* @return $ext_count
	* @access public
	*/
	public function get_active_ext()
	{
		$sql = 'SELECT COUNT(ext_active) AS active_ext
			FROM ' . EXT_TABLE . "
				WHERE ext_active = 1
				AND ext_name <> '" . $this->db->sql_escape($this->current_ext) . "'";

		$result		= $this->db->sql_query($sql);
		$ext_count	= (int)$this->db->sql_fetchfield('active_ext');

		$this->db->sql_freeresult($result);

		return $ext_count;
	}
}
