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
		global $user, $auth, $template, $cache, $request, $config;

		$this->tpl_name = 'acp_minical_body';
		$this->page_title = $user->lang('ACP_MINICAL');
		$action		= request_var('action', '');

		add_form_key('sheer/minical');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('sheer/minical'))
			{
				trigger_error('FORM_INVALID');
			}
			$config->set('minical_enable', $request->variable('minical_enable', 0));
			$config->set('minical_forums', $request->variable('minical_forums', ''));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'MINICAL_FORUMS'	=> $config['minical_forums'],
			'S_CHECKED_DISABLE'	=> (!$config['minical_enable']) ? ' checked="checked" ' : '',
			'S_CHECKED_ENABLE'	=> ($config['minical_enable']) ? ' checked="checked" ' : '',
		));
	}
}
