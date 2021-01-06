<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/plugins/ougc_threadcontributors.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2014-2020 Omar Gonzalez
 *   
 *   Website: https://ougc.network
 *
 *   Shows a list of users who contributed to a thread discussion.
 *
 ***************************************************************************

****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Add our hook
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', 'ougc_threadcontributors_lang_load');
	$plugins->add_hook('admin_style_templates_set', 'ougc_threadcontributors_lang_load');
	$plugins->add_hook('admin_config_settings_change', 'ougc_threadcontributors_settings_change');
}
else
{
	global $templatelist;

	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	else
	{
		$templatelist = '';
	}

	$templatelist .= 'ougcthreadcontributors, ougcthreadcontributors_user, ougcthreadcontributors_user_avatar, ougcthreadcontributors_user_plain, ougcthreadcontributors_user_postcount';

	$plugins->add_hook('showthread_end', 'ougc_threadcontributors_showthread');
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Necessary plugin information for the ACP plugin manager.
function ougc_threadcontributors_info()
{
	global $lang;
	ougc_threadcontributors_lang_load();

	return array(
		'name'			=> 'OUGC Thread Contributors',
		'description'	=> $lang->setting_group_ougc_threadcontributors_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.22',
		'versioncode'	=> 1822,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_threadcontributors',
		'pl'			=> array(
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		)
	);
}

// _activate() routine
function ougc_threadcontributors_activate()
{
	global $PL, $cache, $lang, $ougc_threadcontributors;

	ougc_threadcontributors_pl_check();

	// Add settings group
	$PL->settings('ougc_threadcontributors', $lang->setting_group_ougc_threadcontributors, $lang->setting_group_ougc_threadcontributors_desc, array(
		'showavatars'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_showavatars,
			'description'	=> $lang->setting_ougc_threadcontributors_showavatars_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'showavatars_guests'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_showavatars_guests,
			'description'	=> $lang->setting_ougc_threadcontributors_showavatars_guests_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	1,
		),
		'count_posts'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_count_posts,
			'description'	=> $lang->setting_ougc_threadcontributors_count_posts_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'orderby'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_orderby,
			'description'	=> $lang->setting_ougc_threadcontributors_orderby_desc,
			'optionscode'	=> "radio
username={$lang->setting_ougc_threadcontributors_orderby_username}
posttime={$lang->setting_ougc_threadcontributors_orderby_posttime}
postcount={$lang->setting_ougc_threadcontributors_orderby_postcount}",
			'value'			=>	'username',
		),
		'orderdir'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_orderdir,
			'description'	=> $lang->setting_ougc_threadcontributors_orderdir_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'ignoreauthor'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_ignoreauthor,
			'description'	=> $lang->setting_ougc_threadcontributors_ignoreauthor_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'maxsize'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_maxsize,
			'description'	=> $lang->setting_ougc_threadcontributors_maxsize_desc,
			'optionscode'	=> 'numeric',
			'value'			=>	30,
		)
	));

	// Add template group
	$PL->templates('ougcthreadcontributors', 'OUGC Thread Contributors', array(
		''	=> '<br />
<span class="smalltext">{$lang->ougc_threadcontributors_contributors}: {$users}</span>
<br />
<style>
	.ougcthreadcontributors_user img {
		border-radius: 50%;
		max-width: {$max_dimension}px;
		max-height: {$max_dimension}px;
	}
</style>',
		'user'	=> '{$comma}<a href="{$user[\'profilelink\']}" title="{$user[\'username\']}" class="ougcthreadcontributors_user">{$dyn}</a>',
		'user_avatar'	=> '<img src="{$avatar[\'image\']}" alt="{$user[\'username\']}" {$avatar[\'width_height\']} />',
		'user_plain'	=> '{$user[\'username_formatted\']}',
	));

	// Modify templates
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';

	find_replace_templatesets('showthread', '#'.preg_quote('{$usersbrowsing}').'#', '{$usersbrowsing}{$ougc_threadcontributors_list}');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');

	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_threadcontributors_info();

	if(!isset($plugins['threadcontributors']))
	{
		$plugins['threadcontributors'] = $info['versioncode'];
	}

	$ougc_threadcontributors->_db_verify_columns();

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['threadcontributors'] = $info['versioncode'];

	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_threadcontributors_deactivate()
{
	global $cache;

	ougc_threadcontributors_pl_check();

	// Revert template edits
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';

	find_replace_templatesets('showthread', '#'.preg_quote('{$ougc_threadcontributors_list}').'#', '', 0);

	find_replace_templatesets('showthread', '#'.preg_quote('{$ougc_threadcontributors}').'#', '', 0);
}

// _uninstall() routine
function ougc_threadcontributors_install()
{
	global $ougc_threadcontributors;

	$ougc_threadcontributors->_db_verify_columns();
}

// _is_installed() routine
function ougc_threadcontributors_is_installed()
{
	global $ougc_threadcontributors;

	return $ougc_threadcontributors->_is_installed();
}

// _uninstall() routine
function ougc_threadcontributors_uninstall()
{
	global $PL, $cache, $ougc_threadcontributors, $db;

	ougc_threadcontributors_pl_check();

	foreach($ougc_threadcontributors->_db_columns() as $table => $columns)
	{
		foreach($columns as $name => $definition)
		{
			!$db->field_exists($name, $table) || $db->drop_column($table, $name);
		}
	}

	$PL->settings_delete('ougc_threadcontributors');

	$PL->templates_delete('ougcthreadcontributors');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['threadcontributors']))
	{
		unset($plugins['threadcontributors']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$cache->delete('ougc_plugins');
	}
}

// PluginLibrary dependency check & load
function ougc_threadcontributors_pl_check()
{
	global $lang;

	ougc_threadcontributors_lang_load();

	$info = ougc_threadcontributors_info();

	if($file_exists = file_exists(PLUGINLIBRARY))
	{
		global $PL;
	
		$PL or require_once PLUGINLIBRARY;
	}

	if(!$file_exists || $PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_threadcontributors_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
	}
}

// Pretty settings
function ougc_threadcontributors_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');

	$groupname = (string)$db->fetch_field($query, 'name');

	if($groupname == 'ougc_threadcontributors')
	{
		ougc_threadcontributors_lang_load();
	}
}

// Load language file
function ougc_threadcontributors_lang_load()
{
	global $lang;

	isset($lang->setting_group_ougc_threadcontributors) or $lang->load('ougc_threadcontributors');
}

// Dark magic
function ougc_threadcontributors_showthread()
{
	global $db, $tid, $visible, $mybb, $templates, $ougc_threadcontributors_list, $ougc_threadcontributors, $lang, $thread;

	ougc_threadcontributors_lang_load();

	$comma = $ougc_threadcontributors_list = $users = '';

	$orderdir = 'DESC';

	if($mybb->settings['ougc_threadcontributors_orderdir'])
	{
		$orderdir = 'ASC';
	}

	if(empty($thread['ougc_threadcontributors']))
	{
		$ougc_threadcontributors->set_update_thread($tid);

		$uids = $ougc_threadcontributors->update_thread();
	}

	if(empty($uids))
	{
		$uids = array_map('intval', explode(',', $thread['ougc_threadcontributors']));
	}

	$uids = implode("','", array_values($uids));

	$author = (int)$thread['uid'];

	$where = ["u.uid IN ('{$uids}')"];

	if($mybb->settings['ougc_threadcontributors_ignoreauthor'])
	{
		$where[] = "u.uid!='{$author}'";
	}

	$post_count_cache = [];

	if($mybb->settings['ougc_threadcontributors_count_posts'])
	{
		$query = $db->simple_select(
			"posts p LEFT JOIN {$db->table_prefix}users u ON (u.uid=p.uid)",
			'p.uid',
			implode(' AND ', array_merge($where, ["p.tid='{$tid}' AND p.visible='1'"]))
		);

		while($uid = (int)$db->fetch_field($query, 'uid'))
		{
			++$post_count_cache[$uid];
		}
	}

	if($mybb->settings['ougc_threadcontributors_orderby'] == 'postcount')
	{
		$where[] = "p.tid='{$tid}' AND p.visible='1'";

		$where = implode(' AND ', $where);

		$query = $db->simple_select(
			"users u INNER JOIN {$db->table_prefix}posts p ON (p.uid=u.uid)",
			'u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup, COUNT(p.pid) AS total_posts',
			$where,
			array(
				'order_by' => 'total_posts',
				'order_dir' => $orderdir,
				'group_by' => 'u.uid',
			)
		);
	}
	elseif($mybb->settings['ougc_threadcontributors_orderby'] == 'posttime')
	{
		$where[] = "p.tid='{$tid}' AND p.visible='1'";

		$where = implode(' AND ', $where);

		$query = $db->simple_select(
			"users u LEFT JOIN {$db->table_prefix}posts p ON (p.uid=u.uid)",
			'u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup',
			$where,
			array(
				'order_by' => 'p.dateline',
				'order_dir' => $orderdir
			)
		);
	}
	else
	{
		$where = implode(' AND ', $where);

		$query = $db->simple_select(
			'users u',
			'u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup',
			$where,
			array(
				'order_by' => 'u.username',
				'order_dir' => $orderdir
			)
		);
	}

	$showavatars = $mybb->settings['ougc_threadcontributors_showavatars'] && ((!$mybb->user['uid'] && $mybb->settings['ougc_threadcontributors_showavatars_guests']) || $mybb->user['showavatars']);

	$max_dimension = (int)$mybb->settings['ougc_threadcontributors_maxsize'];

	$max_dimensions = $max_dimension.'x'.$max_dimension;

	$done_users = array();

	while($user = $db->fetch_array($query))
	{
		$user['uid'] = (int)$user['uid'];

		if(isset($done_users[$user['uid']]))
		{
			continue;
		}

		$post_count = '';

		$done_users[$user['uid']] = true;

		$user['username'] = htmlspecialchars_uni($user['username']);

		$date = my_date('relative', $user['dateline']);

		$user['profilelink'] = get_profile_link($user['uid']);
		$user['username_formatted'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

		if($showavatars)
		{
			$avatar = format_avatar($user['avatar'], $user['avatardimensions'], $max_dimensions);

			$dyn = eval($templates->render('ougcthreadcontributors_user_avatar', true, false));
		}
		else
		{
			$dyn = eval($templates->render('ougcthreadcontributors_user_plain', true, false));
		}

		if($mybb->settings['ougc_threadcontributors_count_posts'])
		{
			$posts_count = 0;

			if(isset($post_count_cache[$user['uid']]))
			{
				$posts_count = my_number_format($post_count_cache[$user['uid']]);
			}

			$post_count = eval($templates->render('ougcthreadcontributors_user_postcount'));
		}

		$users .= eval($templates->render('ougcthreadcontributors_user'));

		$comma = $showavatars ? ' ' : $lang->ougc_threadcontributors_comma.' ';
	}

	if($users)
	{
		$ougc_threadcontributors_list = eval($templates->render('ougcthreadcontributors'));
	}
}

// Plugin class
class OUGC_ThreadContributors
{
	private $update_thread = 0;

	function __construct()
	{
		global $plugins, $settings, $templatelist;

		// Tell MyBB when to run the hook
		if(!defined('IN_ADMINCP'))
		{
			$plugins->add_hook('class_moderation_delete_post', array($this, 'hook_class_moderation_delete_post_start'));
			$plugins->add_hook('class_moderation_merge_posts', array($this, 'hook_class_moderation_merge_posts'));
			$plugins->add_hook('class_moderation_merge_threads', array($this, 'hook_class_moderation_merge_posts'));
			$plugins->add_hook('class_moderation_split_posts', array($this, 'hook_class_moderation_merge_posts'));
			$plugins->add_hook('class_moderation_approve_posts', array($this, 'hook_class_moderation_approve_posts'));
			$plugins->add_hook('class_moderation_unapprove_posts', array($this, 'hook_class_moderation_approve_posts'));
			$plugins->add_hook('class_moderation_soft_delete_posts', array($this, 'hook_class_moderation_approve_posts'));
			$plugins->add_hook('class_moderation_restore_posts', array($this, 'hook_class_moderation_approve_posts'));

			$plugins->add_hook('datahandler_post_insert_post_end', array($this, 'hook_datahandler_post_insert_post_end'));
			$plugins->add_hook('datahandler_post_update_end', array($this, 'hook_datahandler_post_insert_post_end'));
		}
	}

	// Plugin API:_is_installed() routine
	function _is_installed()
	{
		global $db;

		foreach($this->_db_columns() as $table => $columns)
		{
			foreach($columns as $name => $definition)
			{
				$installed = $db->field_exists($name, $table);
	
				break;
			}
		}

		return $installed;
	}

	// List of columns
	function _db_columns()
	{
		$tables = array(
			'threads'	=> array(
				'ougc_threadcontributors' => "text NULL"
			),
		);

		return $tables;
	}

	// Verify DB columns
	function _db_verify_columns()
	{
		global $db;

		foreach($this->_db_columns() as $table => $columns)
		{
			foreach($columns as $field => $definition)
			{
				if($db->field_exists($field, $table))
				{
					$db->modify_column($table, "`{$field}`", $definition);
				}
				else
				{
					$db->add_column($table, $field, $definition);
				}
			}
		}
	}

	function update_thread()
	{
		global $db;

		if(!($tid = $this->get_update_thread()))
		{
			return false;
		}

		$query = $db->simple_select('posts', 'uid', "tid='{$tid}' AND visible='1'");

		$uids = array();

		while($uid = (int)$db->fetch_field($query, 'uid'))
		{
			$uids[$uid] = $uid;
		}

		if(!empty($uids))
		{
			$db->update_query('threads', array('ougc_threadcontributors' => implode(',', $uids)), "tid='{$tid}'");
		}

		return $uids;
	}

	function set_update_thread($tid)
	{
		$this->update_thread = (int)$tid;
	}

	function get_update_thread()
	{
		return $this->update_thread;
	}

	function hook_class_moderation_delete_post_start(&$pid)
	{
		global $plugins;

		$post = get_post($pid);

		$this->set_update_thread($post['tid']);

		$plugins->add_hook('class_moderation_delete_post', array($this, 'hook_class_moderation_delete_post'));
	}

	function hook_class_moderation_delete_post(&$pid)
	{
		$this->update_thread();
	}

	function hook_class_moderation_merge_posts(&$args)
	{
		$this->set_update_thread($args['tid']);

		$this->update_thread();
	}

	function hook_class_moderation_approve_posts(&$pids)
	{
		if(!empty($pids))
		{
			global $db;

			$done = array();

			$query = $db->simple_select('posts', 'tid', "pid IN (".implode(',', $pids).")");

			while($tid = (int)$db->fetch_field($query, 'tid'))
			{
				if(isset($done[$tid]))
				{
					continue;
				}
		
				$done[$tid] = $tid;

				$this->set_update_thread($tid);
		
				$this->update_thread();
			}
		}
	}

	function hook_datahandler_post_insert_post_end(&$dh)
	{
		$this->set_update_thread($dh->data['tid']);

		$this->update_thread();
	}
}

global $ougc_threadcontributors;

$ougc_threadcontributors = new OUGC_ThreadContributors;