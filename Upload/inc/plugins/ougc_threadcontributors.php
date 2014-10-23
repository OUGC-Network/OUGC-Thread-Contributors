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
if(!defined('IN_ADMINCP'))
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

	$templatelist .= 'ougc_threadcontributors, ougc_threadcontributors_user, ougc_threadcontributors_user_avatar, ougc_threadcontributors_user_plain';

	$plugins->add_hook('showthread_end', 'ougc_threadcontributors_showthread');
}

// Necessary plugin information for the ACP plugin manager.
function ougc_threadcontributors_info()
{
	return array(
		'name'			=> 'OUGC Thread Contributors',
		'description'	=> 'Shows a list of users who contributed to a thread discussion.',
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.0.0',
		'compatibility'	=> '18*'
	);
}

// _activate() routine
function ougc_threadcontributors_activate()
{
	global $cache;

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
	global $cache;

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

function ougc_threadcontributors_showthread()
{
	global $db, $tid, $visible, $mybb, $templates, $ougc_threadcontributors;

	// Lets get the pids of the posts on this page.
	$pids = $comma = $ougc_threadcontributors = '';
	$query = $db->query("
		SELECT u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup, p.dateline
		FROM ".TABLE_PREFIX."posts p
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
		WHERE p.tid='{$tid}'{$visible}
		ORDER BY p.dateline DESC
	");

	$templates->cache['ougc_threadcontributors'] = '<br />
<span class="smalltext">Users that contributed: {$users}</span>
<br />';
	$templates->cache['ougc_threadcontributors_user'] = '{$comma}<a href="{$user[\'profilelink\']}" title="{$date}">{$dyn}</a>';
	$templates->cache['ougc_threadcontributors_user_avatar'] = '<img src="{$avatar[\'image\']}" alt="" {$avatar[\'width_height\']} />';
	$templates->cache['ougc_threadcontributors_user_plain'] = '{$user[\'username\']}';

	$done_users = array();
	while($user = $db->fetch_array($query, 'pid'))
	{
		if(isset($done_users[$user['uid']]))
		{
			continue;
		}

		$done_users[$user['uid']] = true;

		$date = my_date('relative', $user['dateline']);

		$user['profilelink'] = get_profile_link($user['uid']);
		$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

		if($mybb->user['showavatars'])
		{
			$avatar = ougc_format_avatar($user['avatar'], $user['avatardimensions'], '30x30');
			eval('$dyn = "'.$templates->get('ougc_threadcontributors_user_avatar').'";');
		}
		else
		{
			eval('$dyn = "'.$templates->get('ougc_threadcontributors_user_plain').'";');
		}

		eval('$users .= "'.$templates->get('ougc_threadcontributors_user').'";');

		if(!$mybb->user['showavatars'])
		{
			$comma = ', ';
		}
	}

	$users or $users = 'None';

	eval('$ougc_threadcontributors .= "'.$templates->get('ougc_threadcontributors').'";');
}

/**
 * Formats an avatar to a certain dimension
 *
 * @param string The avatar file name
 * @param string Dimensions of the avatar, width x height (e.g. 44|44)
 * @param string The maximum dimensions of the formatted avatar
 * @return array Information for the formatted avatar
 */
function ougc_format_avatar($avatar, $dimensions = '', $max_dimensions = '')
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