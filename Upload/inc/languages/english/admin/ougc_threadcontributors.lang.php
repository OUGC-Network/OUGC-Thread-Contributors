<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/languages/english/admin/ougc_threadcontributors.lang.php)
 *   Author: Omar Gonzalez
 *   Copyright: © 2014-2023 Omar Gonzalez
 *
 *   Website: https://ougc.network
 *
 *   Displays a list of users who contributed to a discussion.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

// Plugin API
$l['setting_group_ougc_threadcontributors'] = 'OUGC Thread Contributors';
$l['setting_group_ougc_threadcontributors_desc'] = 'Displays a list of users who contributed to a discussion.';

// Settings
$l['setting_ougc_threadcontributors_showavatars'] = 'Show Avatars';
$l['setting_ougc_threadcontributors_showavatars_desc'] = 'If disabled, a list of usernames will be displayed.';
$l['setting_ougc_threadcontributors_showavatars_guests'] = 'Show Avatars to Guests';
$l['setting_ougc_threadcontributors_showavatars_guests_desc'] = 'If disabled, a list of usernames will be displayed to guests.';
$l['setting_ougc_threadcontributors_count_posts'] = 'Display Post Count';
$l['setting_ougc_threadcontributors_count_posts_desc'] = 'If enabled, the total amount of (visible) posts will be displayed for each user.';
$l['setting_ougc_threadcontributors_orderby'] = 'Order By';
$l['setting_ougc_threadcontributors_orderby_desc'] = 'Order criteria to use: ordering by username could be faster.';
$l['setting_ougc_threadcontributors_orderby_username'] = 'Username';
$l['setting_ougc_threadcontributors_orderby_posttime'] = 'Post Time';
$l['setting_ougc_threadcontributors_orderby_postcount'] = 'Post Count';
$l['setting_ougc_threadcontributors_orderdir'] = 'Order On Ascending Order';
$l['setting_ougc_threadcontributors_orderdir_desc'] = 'If enabled, the order of users will be reversed.';
$l['setting_ougc_threadcontributors_ignoreauthor'] = 'Ignore Authors';
$l['setting_ougc_threadcontributors_ignoreauthor_desc'] = 'If enabled, the thread author will not be displayed in the contributors list.';
$l['setting_ougc_threadcontributors_maxsize'] = 'Avatar Maximum Dimensions';
$l['setting_ougc_threadcontributors_maxsize_desc'] = 'Set the maximum avatar width and height size.';
$l['setting_ougc_threadcontributors_allowPostFiltering'] = 'Allow Post Filtering (Experimental)';
$l['setting_ougc_threadcontributors_allowPostFiltering_desc'] = "If enabled, user list will link to the same thread filtered to display the user's posts. If disabled, the filtering wil not be accessible.";

// PluginLibrary
$l['ougc_threadcontributors_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';