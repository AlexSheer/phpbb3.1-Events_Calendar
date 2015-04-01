<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\migrations;

class minical_1_0_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['minical_version']) && version_compare($this->config['minical_version'], '1.0.1', '>=');
	}

	static public function depends_on()
	{
		return array('\sheer\minical\migrations\minical_1_0_0');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'events' => array(
					'author_id'		=> array('USINT', '1'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'events' => array('author_id'),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Current version
			array('config.update', array('minical_version', '1.0.1')),
			array('module.add', array('acp', 'ACP_MINICAL', array(
				'module_basename'	=> '\sheer\minical\acp\minical_module',
				'module_langname'	=> 'ACP_MINICAL_EVENTS',
				'module_mode'		=> 'events',
				'module_auth'		=> 'ext_sheer/minical && acl_a_board',
			))),
		);
	}
}
