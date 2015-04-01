<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\acp;

class minical_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $template, $request, $config;

		switch ($mode)
		{
			case 'events':
				$this->tpl_name = 'acp_minical_events_body';
				$this->page_title = $user->lang('ACP_MINICAL_EVENTS');
				$this->manage_events();
			break;
			case 'settings':
			// No break here
			default:
				$this->tpl_name = 'acp_minical_body';
				$this->page_title = $user->lang('ACP_MINICAL');
				$this->settings();
			break;
		}
	}

	function settings()
	{
		global $user, $template, $request, $config;

		$forums = explode(',', $config['minical_forums']);

		$action			= $request->variable('action', '');
		$minical_forums	= $request->variable('forum_id', $forums);

		$forum_list = make_forum_select(false, false, true, true, true, false, true);
		$s_forum_options = '';
		foreach($forum_list as $key => $value)
		{
			$selected = (in_array($value['forum_id'], $forums)) ? true : false;
			$s_forum_options .='<option value="' . $value['forum_id'] . '"' . (($selected) ? ' selected="selected"' : '') . (($value['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $value['padding'] . $value['forum_name'] . '</option value>';
		}

		add_form_key('sheer/minical');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('sheer/minical'))
			{
				trigger_error('FORM_INVALID');
			}
			$config->set('minical_forums', implode(',', $minical_forums));
			$config->set('minical_enable', $request->variable('minical_enable', 0));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_CHECKED_DISABLE'	=> (!$config['minical_enable']) ? ' checked="checked" ' : '',
			'S_CHECKED_ENABLE'	=> ($config['minical_enable']) ? ' checked="checked" ' : '',
			'S_FORUM_OPTIONS'	=> $s_forum_options,
		));
	}

	function manage_events()
	{
		global $user, $template, $request, $db, $phpbb_container, $phpbb_root_path, $phpEx;

		$this->minical_table = $phpbb_container->getParameter('tables.minical');

		$ids		= $request->variable('ids', array(0));
		$deletemark	= $request->variable('delmarked', false, false, \phpbb\request\request_interface::POST);
		$deleteall	= $request->variable('delall', false, false, \phpbb\request\request_interface::POST);

		if (($deletemark || $deleteall))
		{
			if (confirm_box(true))
			{
				if ($deletemark && sizeof($ids))
				{
					$msg = $user->lang['DELETE_MARKED_EVENTS_SUCESS'];
					$sql = 'DELETE FROM ' . $this->minical_table. '
								WHERE ' . $db->sql_in_set('event_id', $ids);
					$db->sql_query($sql);
					meta_refresh(3, append_sid($this->u_action));
				}
				if($deleteall)
				{
					$sql = 'TRUNCATE ' . $this->minical_table;
					$msg = $user->lang['DELETE_EVENTS_SUCESS'];
					$db->sql_query($sql);
				}
				trigger_error($msg . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
					'delmarked'	=> $deletemark,
					'delall'	=> $deleteall,
					'ids'		=> $ids,
					'action'	=> $this->u_action))
				);
			}
		}

		static $utc;
		$time_zone = $user->data['user_timezone'];

		if (!isset($utc))
		{
			$utc = new \DateTimeZone($time_zone);
		}

		$dt = $user->create_datetime('now', $utc);
		$offset = $dt->getOffset();

		$sql = 'SELECT e.*, u.username, u.user_id, t.topic_id, t.topic_title, t.forum_id
			FROM ' . $this->minical_table . ' e, '. USERS_TABLE .' u, ' . TOPICS_TABLE . ' t
			WHERE u.user_id = e.author_id
			AND t.topic_id = e.topic_id
			ORDER BY event_start ASC';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('events', array(
				'ID'		=> $row['event_id'],
				'START'		=> $user->format_date(($row['event_start'] - $offset), 'd M Y'),
				'END'		=> ($row['event_start'] != $row['event_end']) ? $user->format_date(($row['event_end'] - $offset), 'd M Y') : '',
				'TITLE'		=> $row['event_title'],
				'SHIFT'		=> $row['shift_end'],
				'USER'		=> $row['username'],
				'TOPIC'		=> $row['topic_title'],
				'U_TOPIC'	=> append_sid("{$phpbb_root_path}/viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '')
				)
			);
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
		));
	}
}
