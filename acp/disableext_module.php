<?php
/**
*
* @package Disable Extensions
* @copyright (c) 2017 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\disableext\acp;

class disableext_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'disableext_manage';
		$this->page_title	= $phpbb_container->get('language')->lang('DISABLE_EXTENSIONS');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.disableext.admin.controller');

		$admin_controller->display_options();
	}
}
