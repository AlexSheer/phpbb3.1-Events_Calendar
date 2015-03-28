<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
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

$lang = array_merge($lang, array(
	'EVENT_TITLE'		=> 'Event title',
	'START_DATE'		=> 'Date event start',
	'END_DATE'			=> 'Date event end',
	'ADD_EVENT_EXPLAIN' => 'If you do not want to add an event, leave this field blank.',
	'SELECT_DATE'		=> 'Select a date',
	'END_DATE_ERROR'	=> 'End date of the event can not be less than the start date of the event.',
	'START_DATE_ERROR'	=> 'Event date can not be less than or equal to the current.',
	'TITLE_TOO_SHORT'	=> 'Event title is too short.',
	'DELETE_EVENT'		=> 'Delete event',
	'ADVANCED_FORM'		=> 'advanced mode',
));
