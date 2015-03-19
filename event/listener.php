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
			'core.modify_posting_parameters'		=> 'delete_event',
			'core.posting_modify_template_vars'		=> 'edit_event',
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
		\phpbb\user $user, \phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		$table_prefix,
		$phpbb_root_path,
		$php_ext
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

		define ('EVENTS_TABLE', $this->table_prefix . 'events');
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
			static $utc;
			$time_zone = ($this->user->data['user_id'] != ANONYMOUS) ? $this->user->data['user_timezone'] : $this->config['board_timezone'];

			if (!isset($utc))
			{
				$utc = new \DateTimeZone($time_zone);
			}

			$dt = $this->user->create_datetime('now', $utc);
			$offset = $dt->getOffset();
			$sql = 'SELECT *
				FROM '. EVENTS_TABLE .'
				WHERE event_end > '. (time() + $offset) . '
				ORDER BY event_start ';
			$result = $this->db->sql_query($sql);
			while($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('events', array(
					'START'		=> $this->user->format_date(($row['event_start'] + $offset), 'l, j F Y'),
					'END'		=> ($row['event_start'] != $row['event_end']) ? $this->user->format_date(($row['event_end'] + $offset), 'l, j F Y') : '',
					'TITLE'		=> $row['event_title'],
					'U_EVENT'	=> append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 't=' . $row['topic_id'] . '&amp;f=' . $row['forum_id'] . '')
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
			));
		}
	}

	public function check_event($event)
	{
		$forums = explode(',', $this->config['minical_forums']);
		if (!in_array($event['forum_id'], $forums))
		{
			return;
		}

		$error = array();

		$event_start = strtotime($this->request->variable('event_start', ''));
		$event_end = strtotime($this->request->variable('event_end', ''));
		$title = $this->request->variable('event_title', '', true);

		if ($event_end && $event_end < $event_start)
		{
			$error[] = $this->user->lang['END_DATE_ERROR'];
		}
		if (($event_end && $event_end < time()) || $event_start < time())
		{
			$error[] = $this->user->lang['START_DATE_ERROR'];
		}
		if (($event_end || $event_start) && empty($title))
		{
			$error[] = $this->user->lang['TITLE_TOO_SHORT'];
		}

		if (sizeof($error))
		{
			$event['error'] = $error;
		}
	}

	public function add_event($event)
	{
		if (!$this->auth->acl_gets('u_add_event') || $this->config['minical_enable'] == false)
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

		$sql = 'SELECT event_id
			FROM ' . EVENTS_TABLE . '
			WHERE topic_id = ' . $data['topic_id'] . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$title = $this->request->variable('event_title', '', true);

		if ($mode == 'post' || $mode =='edit')
		{
			$event_start = strtotime($this->request->variable('event_start', ''));
			$event_end = strtotime($this->request->variable('event_end', ''));

			if(!$event_end || !$this->request->variable('advanced', false))
			{
				$event_end = $event_start;
			}

			$sql_ary = array(
				'event_title'	=> $title,
				'event_start'	=> $event_start,
				'event_end'		=> $event_end,
				'forum_id'		=> $data['forum_id'],
				'topic_id'		=> $data['topic_id'],
				'post_id'		=> $data['post_id'],
			);

			if ($mode == 'edit' && $row['event_id'] && $title)
			{
				$sql = 'UPDATE ' . EVENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) .' WHERE topic_id = '. $data['topic_id'] .'';
			}
			else if (($mode == 'post' || $mode == 'edit') && !$row['event_id'] && $title)
			{
				$sql = 'INSERT INTO ' . EVENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
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
			FROM ' . EVENTS_TABLE . '
			WHERE topic_id = ' . $event['topic_id'] . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$start	= (isset($row['event_start'])) ? $this->request->variable('event_start', date('d-m-Y', $row['event_start'])) : $this->request->variable('event_start', '');
		$end	= (isset($row['event_end']))   ? $this->request->variable('event_end',   date('d-m-Y', $row['event_end'])) :   $this->request->variable('event_end', '');

		$advanced = false;
		if ($end != $start)
		{
			$advanced = true;
		}
		if (empty($end))
		{
			$advanced = false;
		}

		$advanced = $this->request->variable('advanced', $advanced);

		$this->template->assign_vars(array(
			'EVENT_TITLE'		=> (isset($row['event_title'])) ? $this->request->variable('event_title', $row['event_title'], true) : $this->request->variable('event_title', '', true),
			'EVENT_START'		=> $start,
			'EVENT_END'			=> ($end != $start) ? $end : '',
			'S_EVENT_ENABLE'	=> true,
			'S_DELETE_ENABLE'	=> ($event['mode'] == 'edit' && isset($row['event_start'])) ? true : false,
			'S_DELETE_CHECKED'	=> ($this->request->variable('delete_event', false)) ? 'checked="checked"' : '',
			'S_ADVANCED_CHECKED'=> ($advanced) ? 'checked="checked"' : '',
			'S_POST_MODE'		=> ($event['mode'] == 'post') ? true : false,
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
					FROM ' . EVENTS_TABLE . '
					WHERE post_id = ' . $event['post_id'] . '';
				$this->db->sql_query($sql);
			}
		}
	}
}