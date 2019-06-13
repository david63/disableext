<?php
/**
*
* @package Disable Extensions
* @copyright (c) 2017 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

/// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ARE_YOU_SURE'					=> array(
		1 => 'Are you sure that you want to disable %1$s extension?',
		2 => 'Are you sure that you want to disable %1$s extensions?',
	),

	'CONTINUE'						=> 'Continue',

	'DISABLE_COUNT'					=> array(
		1 => 'You are about to disable %1$s extension.',
		2 => 'You are about to disable %1$s extensions.',
	),
	'DISABLE_EXTENSIONS'			=> 'Disable extensions',
	'DISABLE_EXTENSIONS_EXPLAIN'	=> 'This extension will disable all of your extensions. It will not delete any of the data associated with an extension and you will be able to re-enable them again in <strong>“Manage extensions”</strong>.<br><br>If you want to remove an extension then after disabling the extensions you <strong>must</strong> “Delete data” for the extension <strong>before</strong> deleting the files.',

	'EXTENSIONS_DISABLED'			=> array(
		0 => 'No extensions were disabled on this run',
		1 => '%1$s of %2$s extension has been disabled',
		2 => '%1$s of %2$s extensions have been disabled',
	),

	'NEW_VERSION'					=> 'New Version',
	'NEW_VERSION_EXPLAIN'			=> 'There is a newer version of this extension available.',
	'NO_EXT_DATA'					=> 'There are no extensions to disable',
	'NO_EXT_UNABLE'					=> 'Unable to disable any extensions',

	'VERSION'						=> 'Version',
));

// Donate
$lang = array_merge($lang, array(
	'DONATE'					=> 'Donate',
	'DONATE_EXTENSIONS'			=> 'Donate to my extensions',
	'DONATE_EXTENSIONS_EXPLAIN'	=> 'This extension, as with all of my extensions, is totally free of charge. If you have benefited from using it then please consider making a donation by clicking the PayPal donation button opposite - I would appreciate it. I promise that there will be no spam nor requests for further donations, although they would always be welcome.',

	'PAYPAL_BUTTON'				=> 'Donate with PayPal button',
	'PAYPAL_TITLE'				=> 'PayPal - The safer, easier way to pay online!',
));
