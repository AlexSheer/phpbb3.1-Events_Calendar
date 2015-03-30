<?php
/**
*
* info_minical [Russian]
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
	'ACP_MINICAL'				=> 'Календарь событий',
	'ACP_MINICAL_EXPLAIN'		=> 'Основные настройки.',
	'MINICAL_ENABLE'			=> 'Включить календарь событий',
	'MINICAL_ENABLE_EXPLAIN'	=> 'Если включено, то информация о предстоящих и не истекших событиях будет отображаться над шапкой каждой страницы. Пользователи, имеющие соответствующее право, смогут создавать анонсы событий.',
	'ACP_MINICAL_MANAGE'		=> 'Управление',
	'MINICAL_FORUMS'			=> 'Форумы событий',
	'MINICAL_FORUMS_EXPLAIN'	=> 'Форуы, в которых будет возможно создание анонсов событий. Для выбора нескольких форумов используйте соответствующую для вашего компьютера и браузера комбинацию мыши и клавиатуры. Выбранные форумы отображаются на синем фоне.',
));
