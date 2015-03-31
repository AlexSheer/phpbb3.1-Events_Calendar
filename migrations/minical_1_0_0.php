<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\migrations;

class minical_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['minical_version']) && version_compare($this->config['minical_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\sheer\minical\migrations\minical_0_0_1');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'events' => array(
					'shift_end'		=> array('INT:8', '0'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'events' => array('shift_end'),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Current version
			array('config.update', array('minical_version', '1.0.0')),
		);
	}
}
