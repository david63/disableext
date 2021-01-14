<?php
/**
 *
 * @package Disable Extensions
 * @copyright (c) 2017 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\disableext\acp;

class disableext_info
{
	public function module()
	{
		return [
			'filename'	=> '\david63\disableext\acp\disableext_module',
			'title' 	=> 'DISABLE_EXTENSIONS',
			'modes' 	=> [
				'main' => ['title' => 'DISABLE_EXTENSIONS', 'auth' => 'ext_david63/disableext && acl_a_extensions', 'cat' => ['ACP_EXTENSION_MANAGEMENT']],
			],
		];
	}
}
