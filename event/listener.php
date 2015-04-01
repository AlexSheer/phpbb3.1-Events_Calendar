<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
/**
* Assign functions defined in this class to event listeners in the core
*
* @return array
* @static
* @access public
*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'show_event',
			'core.posting_modify_submission_errors'	=> 'check_event',
			'core.submit_post_end'					=> 'add_event',
			'core.posting_modify_template_vars'		=> 'edit_event',
			'core.modify_posting_parameters'		=> 'delete_event',
		);
	}

	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\auth\auth */

	protected $auth;

	/** @var \phpbb\db\driver\driver_interface $db */
	protected $db;

	/** @var \phpbb\config\config $config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* Constructor
	*/
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\request\request_interface $request,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		$table_prefix,
		$phpbb_root_path,
		$php_ext,
		$minical_table
	)
	{
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->table_prefix = $table_prefix;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->minical_table = $minical_table;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'sheer/minical',
			'lang_set' => 'minical',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function show_event($event)
	{
		if ($this->config['minical_enable'])
		{
/*
			static $utc;
			$time_zone = ($this->user->data['user_id'] != ANONYMOUS) ? $this->user->data['user_timezone'] : $this->config['board_timezone'];

			if (!isset($utc))
			{
				$utc = new \DateTimeZone($time_zone);
			}

			$dt = $this->user->create_datetime('now', $utc);
*/
			$offset = $this->get_time_offset();
			$sql = 'SELECT *
				FROM '. $this->minical_table . '
				WHERE event_end > '. (time() - 86400) . '
				ORDER BY event_start ';
			$result = $this->db->sql_query($sql);
			$count = 0;
			while($row = $this->db->sql_fetchrow($result))
			{
				$rest_time = floor(($row['event_start'] - time()) / 86400);
				if($rest_time <= $row['shift_end'] || !$row['shift_end'])
				{
					$count++;
				}

				$ff = $row['event_start'];
				//print "$ff<br />";

				$this->template->assign_block_vars('events', array(
					'START'		=> $this->user->format_date(($row['event_start']), 'l, j F Y'),
					'END'		=> ($row['event_start'] != $row['event_end']) ? $this->user->format_date(($row['event_end']), 'l, j F Y') : '',
					'TITLE'		=> ($rest_time <= $row['shift_end'] || !$row['shift_end']) ? $row['event_title'] : '',
					'U_EVENT'	=> append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '')
					)
				);
			}
			$this->db->sql_freeresult($result);

			$this->template->assign_vars(array(
				'JAN'	=> $this->user->lang(array('datetime', 'January')),
				'FEB'	=> $this->user->lang(array('datetime', 'February')),
				'MAR'	=> $this->user->lang(array('datetime', 'March')),
				'APR'	=> $this->user->lang(array('datetime', 'April')),
				'MAY'	=> $this->user->lang(array('datetime', 'May')),
				'JUN'	=> $this->user->lang(array('datetime', 'June')),
				'JUL'	=> $this->user->lang(array('datetime', 'July')),
				'AUG'	=> $this->user->lang(array('datetime', 'August')),
				'SEP'	=> $this->user->lang(array('datetime', 'September')),
				'OCT'	=> $this->user->lang(array('datetime', 'October')),
				'NOV'	=> $this->user->lang(array('datetime', 'November')),
				'DEC'	=> $this->user->lang(array('datetime', 'December')),

				'SUN'	=> $this->user->lang(array('datetime', 'Sun')),
				'MON'	=> $this->user->lang(array('datetime', 'Mon')),
				'TUE'	=> $this->user->lang(array('datetime', 'Tue')),
				'WED'	=> $this->user->lang(array('datetime', 'Wed')),
				'THU'	=> $this->user->lang(array('datetime', 'Thu')),
				'FRI'	=> $this->user->lang(array('datetime', 'Fri')),
				'SAT'	=> $this->user->lang(array('datetime', 'Sat')),

				'S_SHOW_EVENT'	=> $count,
			));
		}
	}

	public function check_event($event)
	{
		$forums = explode(',', $this->config['minical_forums']);
		if (!in_array($event['forum_id'], $forums) || $this->request->variable('delete_event', false))
		{
			return;
		}

		$event_start = strtotime($this->request->variable('event_start', ''));
		$event_end = strtotime($this->request->variable('event_end', ''));
		$title = $this->request->variable('event_title', '', true);

		if($title)
		{
			if ($event_end && $event_end < $event_start)
			{
				$event['error'] = array($this->user->lang['END_DATE_ERROR']);
			}
			else if (($event_end && $event_end < time()) || $event_start < (time() - (time() - mktime(0, 0, 0))))
			{
				$event['error'] = array($this->user->lang['START_DATE_ERROR']);
			}
		}

		if (($event_end || $event_start) && empty($title))
		{
			$event['error'] = array($this->user->lang['TITLE_TOO_SHORT']);
		}
	}

	public function add_event($event)
	{
		$title = $this->request->variable('event_title', '', true);

		if (!$this->auth->acl_gets('u_add_event') || $this->config['minical_enable'] == false || empty($title))
		{
			return;
		}

		$mode = $event['mode'];
		$data = $event['data'];

		$forums = explode(',', $this->config['minical_forums']);
		if (!in_array($data['forum_id'], $forums))
		{
			return;
		}

		if($mode =='edit')
		{
			$sql = 'SELECT event_id
				FROM ' . $this->minical_table . '
				WHERE topic_id = ' . $data['topic_id'] . '';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}

		if ($this->request->variable('delete_event', false))
		{
			$sql = 'DELETE
				FROM ' . $this->minical_table . '
				WHERE post_id = ' . $data['post_id'] . '';
			$this->db->sql_query($sql);
		}
		else if ($mode == 'post' || $mode =='edit')
		{
			$this->request->variable('event_start', '');
			$event_start = strtotime($this->request->variable('event_start', ''));
			$event_end = strtotime($this->request->variable('event_end', ''));
			$shift_end = $this->request->variable('shift_end', '');

			if(!$event_end || !$this->request->variable('cal_interval_date', false))
			{
				$event_end = $event_start;
			}

			$offset = $this->get_time_offset();

			$sql_ary = array(
				'event_title'	=> $title,
				'event_start'	=> $event_start + $offset,
				'event_end'		=> $event_end + $offset,
				'forum_id'		=> $data['forum_id'],
				'topic_id'		=> $data['topic_id'],
				'post_id'		=> $data['post_id'],
				'shift_end'		=> $shift_end,
				'author_id'		=> $this->user->data['user_id'],
			);

			if ($mode == 'edit' && $row['event_id'] && $title)
			{
				$sql = 'UPDATE ' . $this->minical_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) .' WHERE topic_id = '. $data['topic_id'] .'';
			}
			else if (($mode == 'post' || $mode == 'edit'))
			{
				$sql = 'INSERT INTO ' . $this->minical_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			}
			$this->db->sql_query($sql);
		}
	}

	public function edit_event($event)
	{
		if (!$this->auth->acl_gets('u_add_event') || $this->config['minical_enable'] == false)
		{
			return;
		}

		$forums = explode(',', $this->config['minical_forums']);
		if (!in_array($event['forum_id'], $forums))
		{
			return;
		}

		$sql = 'SELECT *
			FROM ' . $this->minical_table . '
			WHERE topic_id = ' . $event['topic_id'] . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$start		= (isset($row['event_start'])) ? $this->request->variable('event_start', date('d-m-Y', $row['event_start'])) : $this->request->variable('event_start', '');
		$end		= (isset($row['event_end'])) ? $this->request->variable('event_end', date('d-m-Y', $row['event_end'])) : $this->request->variable('event_end', '');
		$shift_end	= (isset($row['shift_end'])) ? $this->request->variable('shift_end', $row['shift_end']) : $this->request->variable('shift_end', 0);

		$cal_interval_date = ($end == $start) ? 0 : 1;

		if ($event['preview'])
		{
			$cal_interval_date = $this->request->variable('cal_interval_date', 0);
		}

		$cal_interval_date = $this->request->variable('cal_interval_date', $cal_interval_date);

		$this->template->assign_vars(array(
			'EVENT_TITLE'		=> (isset($row['event_title'])) ? $this->request->variable('event_title', $row['event_title'], true) : $this->request->variable('event_title', '', true),
			'EVENT_START'		=> $start,
			'EVENT_END'			=> ($end != $start) ? $end : '',
			'S_EVENT_ENABLE'	=> true,
			'S_DELETE_ENABLE'	=> ($event['mode'] == 'edit' && isset($row['event_start'])) ? true : false,
			'S_DELETE_CHECKED'	=> ($this->request->variable('delete_event', false)) ? 'checked="checked"' : '',
			'S_POST_MODE'		=> ($event['mode'] == 'post') ? true : false,
			'ADVANCED_FORM_ON'	=> ($cal_interval_date) ? 'checked="checked"' : '',
			'SHIFT_END'			=> $shift_end,
		));
	}

	public function delete_event($event)
	{
		$forums = explode(',', $this->config['minical_forums']);
		if (in_array($event['forum_id'], $forums))
		{
			$confirm = $this->request->variable('confirm', false);
			if ($confirm)
			{
				$sql = 'DELETE
					FROM ' . $this->minical_table . '
					WHERE post_id = ' . $event['post_id'] . '';
				$this->db->sql_query($sql);
			}
		}
	}

	public function get_time_offset()
	{
		static $utc;
		$time_zone = ($this->user->data['user_id'] != ANONYMOUS) ? $this->user->data['user_timezone'] : $this->config['board_timezone'];

		if (!isset($utc))
		{
			$utc = new \DateTimeZone($time_zone);
		}

		$dt = $this->user->create_datetime('now', $utc);
		$offset = $dt->getOffset() + date('I') * 3600;
		return($offset);
	}
}
