<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\migrations;

class minical_0_0_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'events'	=> array(
					'COLUMNS'		=> array(
						'event_id'		=> array('UINT', null, 'auto_increment'),
						'event_title'	=> array('VCHAR:255', ''),
						'event_start'	=> array('TIMESTAMP', 0),
						'event_end'		=> array('TIMESTAMP', 0),
						'forum_id'		=> array('UINT', 0),
						'topic_id'		=> array('UINT', 0),
						'post_id'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'event_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'events',
			),
		);
	}

	public function update_data()
	{
		return array(
			// Current version
			array('config.add', array('minical_version', '0.0.1')),
			array('config.add', array('minical_enable', '0')),
			array('config.add', array('minical_forums', '')),
			array('config.add', array('events_prune_gc', '86400', '0')),
			array('config.add', array('events_prune_last_gc', '0', '1')),

			// Add permission
			array('permission.add', array('u_add_event', true)),
			// Add permissions sets
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_add_event', 'role', true)),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_add_event', 'role', true)),
			array('permission.permission_set', array('REGISTERED', 'u_add_event', 'group', true)),
			// ACP
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_MINICAL')),
			array('module.add', array('acp', 'ACP_MINICAL', array(
				'module_basename'	=> '\sheer\minical\acp\minical_module',
				'module_langname'	=> 'ACP_MINICAL_MANAGE',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'ext_sheer/minical && acl_a_board',
			))),
		);
	}
}
