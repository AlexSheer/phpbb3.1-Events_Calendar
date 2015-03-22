<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\cron\task;

/**
* Tidy connection log cron task.
*
*/

class tidy_minical extends \phpbb\cron\task\base
{
	protected $config;
	protected $db;

	/**
	* Constructor.
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\log\log $phpbb_log,
		$minical_table
	)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_log = $phpbb_log;
		$this->minical_table = $minical_table;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->cron_tidy_minical();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last.
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['events_prune_last_gc'] < time() - $this->config['events_prune_gc'];
	}

	public function cron_tidy_minical()
	{
		$sql = 'DELETE
			FROM ' . $this->minical_table . '
			WHERE event_end < ' . time() . '';
		$this->db->sql_query($sql);
		$this->config->set('events_prune_last_gc', time(), true);
	}
}
