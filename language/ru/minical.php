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
	'EVENT_TITLE'		=> 'Заголовок события',
	'START_DATE'		=> 'Дата наступления события',
	'END_DATE'			=> 'Дата окончания события',
	'ADD_EVENT_EXPLAIN' => 'Если вы не хотите добавлять событие, оставьте это поле пустым.',
	'SELECT_DATE'		=> 'Выбрать дату',
	'END_DATE_ERROR'	=> 'Дата окончания события не может быть меньше даты наступления события.',
	'START_DATE_ERROR'	=> 'Дата события не может быть меньше текущей.',
	'TITLE_TOO_SHORT'	=> 'Заголовок события слишком короткий.',
	'DELETE_EVENT'		=> 'Удалить событие',
	'ADVANCED_FORM'		=> 'расширенный режим',
));
