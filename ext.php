<?php
/**
*
* @package Disable Extensions
* @copyright (c) 2017 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\disableext;

use phpbb\extension\base;

class ext extends base
{
	/**
	* Enable extension if phpBB version requirement is met
	*
	* @var string Require 3.2.0 due to updated 3.2 syntax
	*
	* @return bool
	* @access public
	*/
	public function is_enableable()
	{
		// Requires phpBB 3.3.0 or newer.
		$is_enableable = phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=');

		// Display a custom warning message if requirement fails.
		if (!$is_enableable)
		{
			// Need to cater for 3.1 and 3.2/3.3
			if (phpbb_version_compare(PHPBB_VERSION, '3.2.0', '>='))
			{
				$this->container->get('language')->add_lang('ext_enable_error', 'david63/disableext');
			}
			else
			{
				$this->container->get('user')->add_lang_ext('david63/disableext', 'ext_enable_error');
			}
		}

		return $is_enableable;
	}
}
