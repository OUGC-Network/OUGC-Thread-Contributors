<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/languages/english/admin/ougc_threadcontributors.lang.php)
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

// Plugin API
$l['setting_group_ougc_threadcontributors'] = 'OUGC Thread Contributors';
$l['setting_group_ougc_threadcontributors_desc'] = 'Shows a list of users who contributed to a thread discussion.';

// Settings
$l['setting_ougc_threadcontributors_showavatars'] = 'Show Users Avatars';
$l['setting_ougc_threadcontributors_showavatars_desc'] = 'If set to no a list of user names will be displayed.';
$l['setting_ougc_threadcontributors_showavatars_guests'] = 'Show Users Avatars For Guests';
$l['setting_ougc_threadcontributors_showavatars_guests_desc'] = 'If set to no a list of user names will be displayed.';
$l['setting_ougc_threadcontributors_orderby'] = 'Order By';
$l['setting_ougc_threadcontributors_orderby_desc'] = 'Please select the order by field to use.';
$l['setting_ougc_threadcontributors_orderby_username'] = 'Username';
$l['setting_ougc_threadcontributors_orderby_posttime'] = 'Post Time';
$l['setting_ougc_threadcontributors_orderdir'] = 'Order On Ascending Order';
$l['setting_ougc_threadcontributors_orderdir_desc'] = 'Select if you want to reverse the ordering of users.';
$l['setting_ougc_threadcontributors_ignoreauthor'] = 'Ignore Authors';
$l['setting_ougc_threadcontributors_ignoreauthor_desc'] = 'Should the contributor list ignore the thread author?';
$l['setting_ougc_threadcontributors_maxsize'] = 'Maximum Width and Height Size';
$l['setting_ougc_threadcontributors_maxsize_desc'] = 'Please input the maximum image size for both the width and height.';

// PluginLibrary
$l['ougc_threadcontributors_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';