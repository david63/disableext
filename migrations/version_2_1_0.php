<?php
/**
*
* @package Disable Extensions
* @copyright (c) 2017 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\disableext\migrations;

use phpbb\db\migration\migration;

class version_2_1_0 extends migration
{
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp', 'ACP_EXTENSION_MANAGEMENT', array(
					'module_basename'	=> '\david63\disableext\acp\disableext_module',
					'modes'				=> array('main'),
				),
			)),
		);
	}
}
