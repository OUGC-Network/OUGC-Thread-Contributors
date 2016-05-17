<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/plugins/ougc_threadcontributors.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2014 Omar Gonzalez
 *   
 *   Website: http://omarg.me
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

	$templatelist .= 'ougcthreadcontributors, ougcthreadcontributors_user, ougcthreadcontributors_user_avatar, ougcthreadcontributors_user_plain';

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
		'description'	=> $lang->setting_group_ougc_threadcontributors,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.0.0',
		'versioncode'	=> 1000,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_threadcontributors',
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_threadcontributors_activate()
{
	global $PL, $cache, $lang;
	ougc_threadcontributors_pl_check();

	// Add settings group
	$PL->settings('ougc_threadcontributors', $lang->setting_group_ougc_threadcontributors, $lang->setting_group_ougc_threadcontributors_desc, array(
		'showavatars'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_showavatars,
			'description'	=> $lang->setting_ougc_threadcontributors_showavatars_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		),
		'orderby'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_orderby,
			'description'	=> $lang->setting_ougc_threadcontributors_orderby_desc,
			'optionscode'	=> "select
username={$lang->setting_ougc_threadcontributors_orderby_username}
posttime={$lang->setting_ougc_threadcontributors_orderby_posttime}",
			'value'			=>	'username',
		),
		'orderby'	=> array(
			'title'			=> $lang->setting_ougc_threadcontributors_orderdir,
			'description'	=> $lang->setting_ougc_threadcontributors_orderdir_desc,
			'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));

	// Add template group
	$PL->templates('ougcthreadcontributors', '<lang:setting_group_ougc_threadcontributors>', array(
		''	=> '<br />
<span class="smalltext">{$lang->ougc_threadcontributors_contributors}: {$users}</span>
<br />',
		'user'	=> '{$comma}<a href="{$user[\'profilelink\']}" title="{$user[\'username\']}">{$dyn}</a>',
		'user_avatar'	=> '<img src="{$avatar[\'image\']}" alt="{$user[\'username\']}" {$avatar[\'width_height\']} />',
		'user_plain'	=> '{$user[\'username_formatted\']}',
	));

	// Modify templates
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('showthread', '#'.preg_quote('{$usersbrowsing}').'#', '{$usersbrowsing}{$ougc_threadcontributors}');

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
	find_replace_templatesets('showthread', '#'.preg_quote('{$ougc_threadcontributors}').'#', '', 0);
}

// _is_installed() routine
function ougc_threadcontributors_is_installed()
{
	global $cache;

	$plugins = (array)$cache->read('ougc_plugins');

	return !empty($plugins['threadcontributors']);
}

// _uninstall() routine
function ougc_threadcontributors_uninstall()
{
	global $PL, $cache;
	ougc_threadcontributors_pl_check();

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

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_threadcontributors_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_threadcontributors_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Pretty settings
function ougc_threadcontributors_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');
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
	global $db, $tid, $visible, $mybb, $templates, $ougc_threadcontributors, $lang;
	ougc_threadcontributors_lang_load();

	$comma = $ougc_threadcontributors = '';

	$options = array('orderby' => 'p.dateline', 'orderdir' => 'DESC');
	if($mybb->settings['orderby'] == 'username')
	{
		$options['orderby'] = 'u.username';
	}
	if($mybb->settings['orderdir'])
	{
		$options['orderdir'] = 'ASC';
	}

	// Lets get the pids of the posts on this page.
	$query = $db->query("
		SELECT u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup, p.username AS postusername, p.dateline
		FROM ".TABLE_PREFIX."posts p
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
		WHERE p.tid='{$tid}'{$visible}
		ORDER BY {$options['orderby']} {$options['orderdir']}
	");

	$showavatars = $mybb->settings['ougc_threadcontributors_showavatars'] && $mybb->user['showavatars'];

	$done_users = array();
	while($user = $db->fetch_array($query, 'pid'))
	{
		if(isset($done_users[$user['uid']]))
		{
			continue;
		}

		$done_users[$user['uid']] = true;

		$user['username'] = $user['username'] ? $user['username'] : $user['postusername'];
		$user['username'] = htmlspecialchars_uni($user['username']);

		$date = my_date('relative', $user['dateline']);

		$user['profilelink'] = get_profile_link($user['uid']);
		$user['username_formatted'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

		if($showavatars)
		{
			$avatar = ougc_threadcontributors_format_avatar($user['avatar'], $user['avatardimensions'], '30x30');
			eval('$dyn = "'.$templates->get('ougcthreadcontributors_user_avatar').'";');
		}
		else
		{
			eval('$dyn = "'.$templates->get('ougcthreadcontributors_user_plain').'";');
		}

		eval('$users .= "'.$templates->get('ougcthreadcontributors_user').'";');

		$comma = $showavatars ? '' : $lang->ougc_threadcontributors_comma.' ';
	}

	$users or $users = 'None';

	eval('$ougc_threadcontributors = "'.$templates->get('ougcthreadcontributors').'";');
}

/**
 * Formats an avatar to a certain dimension
 *
 * @param string The avatar file name
 * @param string Dimensions of the avatar, width x height (e.g. 44|44)
 * @param string The maximum dimensions of the formatted avatar
 * @return array Information for the formatted avatar
 */
function ougc_threadcontributors_format_avatar($avatar, $dimensions = '', $max_dimensions = '')
{
	global $mybb;
	static $avatars;

	if(!isset($avatars))
	{
		$avatars = array();
	}

	if(!$avatar)
	{
		// Default avatar
		$avatar = $mybb->settings['useravatar'];
		$dimensions = $mybb->settings['useravatardims'];
	}

	if(isset($avatars[$avatar]))
	{
		return $avatars[$avatar];
	}

	if(!$max_dimensions)
	{
		$max_dimensions = $mybb->settings['maxavatardims'];
	}

	$avatar_width_height = '';

	if($dimensions)
	{
		$dimensions = explode("|", $dimensions);

		if($dimensions[0] && $dimensions[1])
		{
			list($max_width, $max_height) = explode('x', $max_dimensions);

			if($dimensions[0] > $max_width || $dimensions[1] > $max_height)
			{
				require_once MYBB_ROOT."inc/functions_image.php";
				$scaled_dimensions = scale_image($dimensions[0], $dimensions[1], $max_width, $max_height);
				$avatar_width_height = "width=\"{$scaled_dimensions['width']}\" height=\"{$scaled_dimensions['height']}\"";
			}
			else
			{
				$avatar_width_height = "width=\"{$dimensions[0]}\" height=\"{$dimensions[1]}\"";
			}
		}
	}

	$avatars[$avatar] = array(
		'image' => $mybb->get_asset_url($avatar),
		'width_height' => $avatar_width_height
	);

	return $avatars[$avatar];
}