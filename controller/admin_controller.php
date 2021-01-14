<?php
/**
 *
 * @package Disable Extensions
 * @copyright (c) 2017 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\disableext\controller;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\extension\manager;
use david63\disableext\core\functions;

/**
 * Admin controller
 */
class admin_controller
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var manager */
	protected $phpbb_extension_manager;

	/** @var functions */
	protected $functions;

	/** @var array phpBB tables */
	protected $tables;

	/** @var string */
	protected $ext_images_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor for admin controller
	 *
	 * @param config             $config                     Config object
	 * @param driver_interface   $db                         Database object
	 * @param request            $request                    Request object
	 * @param template           $template                   Template object
	 * @param user               $user                       User object
	 * @param language           $language                   Language object
	 * @param log                $log                        Log object
	 * @param manager            $phpbb_extension_manager    Extension manager
	 * @param functions          $functions                  Functions for the extension
	 * @param array              $tables                     phpBB db tables
	 * @param string             $ext_images_path            Path to this extension's images
	 *
	 * @return \david63\disableext\controller\admin_controller
	 * @access public
	 */
	public function __construct(config $config, driver_interface $db, request $request, template $template, user $user, language $language, log $log, manager $phpbb_extension_manager, functions $functions, array $tables, string $ext_images_path)
	{
		$this->config                  = $config;
		$this->db                      = $db;
		$this->request                 = $request;
		$this->template                = $template;
		$this->user                    = $user;
		$this->language                = $language;
		$this->log                     = $log;
		$this->phpbb_extension_manager = $phpbb_extension_manager;
		$this->functions               = $functions;
		$this->tables                  = $tables;
		$this->ext_images_path         = $ext_images_path;
	}

	/**
	 * Display the options a user can configure for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_options()
	{
		// Add the language files
		$this->language->add_lang(['acp_disableext', 'acp_common'], $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'disableext_manage';
		add_form_key($form_key);

		// Set the intial variables
		$orig_ext_count = $this->request->variable('orig_ext_count', 0);
		$confirm        = false;
		$continue       = true;
		$back           = false;

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
				$confirm  = true;
				$continue = false;
			}
			else if ($this->request->is_set_post('confirm'))
			{
				// Get the enabled extensions, excluding this one
				$sql = 'SELECT ext_name
                    FROM ' . $this->tables['ext'] . "
                    WHERE ext_active = 1
                    AND ext_name <> '" . $this->db->sql_escape($this->functions->get_ext_namespace()) . "'";

				$result = $this->db->sql_query($sql);

				// Now we can try to disable the extensions
				if (!empty($result))
				{
					while ($ext_name = $this->db->sql_fetchrow($result))
					{
						while ($this->phpbb_extension_manager->disable_step($ext_name['ext_name']))
						{
							continue;
						}
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
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DISABLE_EXTENSIONS_LOG', time(), [$disabled_ext, $orig_ext_count]);

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

		$hidden_fields = [
			'orig_ext_count' => $orig_ext_count,
		];

		// Template vars for header panel
		$version_data = $this->functions->version_check();

		// Are the PHP and phpBB versions valid for this extension?
		$valid = $this->functions->ext_requirements();

		$this->template->assign_vars([
			'DOWNLOAD' 			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

 			'EXT_IMAGE_PATH'	=> $this->ext_images_path,

			'HEAD_TITLE' 		=> $this->language->lang('DISABLE_EXTENSIONS'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('DISABLE_EXTENSIONS_EXPLAIN'),

			'NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_BACK' 			=> $back,
			'S_VERSION_CHECK' 	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER' 	=> $this->functions->get_meta('version'),
		]);

		// Set output vars for display in the template
		$this->template->assign_vars([
			'FORM_CONFIRM' 		=> $confirm,
			'FORM_CONTINUE' 	=> $continue,
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($hidden_fields),
			'U_ACTION' 			=> $this->u_action,
		]);

		if ($continue)
		{
			$this->template->assign_var('MESSAGE', $this->language->lang('DISABLE_COUNT', (int) $orig_ext_count));
		}
		else if ($confirm)
		{
			$this->template->assign_var('MESSAGE', $this->language->lang('ARE_YOU_SURE', (int) $orig_ext_count));
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
            FROM ' . $this->tables['ext'] . "
                WHERE ext_active = 1
                AND ext_name <> '" . $this->db->sql_escape($this->functions->get_ext_namespace()) . "'";

		$result    = $this->db->sql_query($sql);
		$ext_count = (int) $this->db->sql_fetchfield('active_ext');

		$this->db->sql_freeresult($result);

		return $ext_count;
	}
}
