<?php
/**
*
* info_minical [English]
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
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

$lang = array_merge($lang, array(
	'ACP_MINICAL'					=> 'Events Calendar',
	'ACP_MINICAL_EXPLAIN'			=> 'Basic settings.',
	'ACP_MINICAL_ANOUNCE_EXPLAIN'	=> 'Here you can get information about the unexpired events and remove them if necessary. Detainees announcements are marked in color.',

	'MINICAL_ENABLE'				=> 'Enable Events Calendar',
	'MINICAL_ENABLE_EXPLAIN'		=> 'If enabled, information about upcoming events and not expired will be displayed above header on each page. Users with the appropriate permission can create announcements of events.',
	'ACP_MINICAL_MANAGE'			=> 'Manage',
	'MINICAL_FORUMS'				=> 'Events forums ',
	'MINICAL_FORUMS_EXPLAIN'		=> 'Forums in which it will be possible to create events announcements. To select multiple forums, use appropriate for your computer and browser combination of mouse and keyboard. Selected forums are displayed on a blue background.',

	'DELETE_MARKED_EVENTS_SUCESS'	=> 'Selected announcements have been successfully removed',
	'DELETE_EVENTS_SUCESS'			=> 'All announcements have been successfully removed',
	'START'							=> 'Start',
	'END'							=> 'End',
	'EVENT_SHIFT'					=> 'Delay',
	'NO_EVENTS'						=> 'No unexpired events.',
));
