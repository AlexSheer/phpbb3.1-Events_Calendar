<?php
/**
*
* @package phpBB Extension - Mini Calendar
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\minical\acp;

class minical_module_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\minical\acp\minical_module',
			'version'	=> '1.0.0',
			'title' => 'ACP_MINICAL_MANAGE',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_MINICAL_MANAGE',
					'auth' => 'ext_sheer/minical && acl_a_board',
					'cat' => array('ACP_MINICAL')
				),
				'events'	=> array(
					'title' => 'ACP_MINICAL_EVENTS',
					'auth' => 'ext_sheer/minical && acl_a_board',
					'cat' => array('ACP_MINICAL')
				),
			),
		);
	}
}
