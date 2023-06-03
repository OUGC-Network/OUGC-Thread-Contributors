<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/plugins/ougc_threadcontributors.php)
 *     Author: Omar Gonzalez
 *   Copyright: © 2014-2020 Omar Gonzalez
 *
 *   Website: https://ougc.network
 *
 *   Shows a list of users who contributed to a thread discussion.
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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Add our hook
if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_settings_start', 'ougc_threadcontributors_lang_load');
    $plugins->add_hook('admin_style_templates_set', 'ougc_threadcontributors_lang_load');
    $plugins->add_hook('admin_config_settings_change', 'ougc_threadcontributors_settings_change');
} else {
    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= 'ougcthreadcontributors, ougcthreadcontributors_user, ougcthreadcontributors_user_avatar, ougcthreadcontributors_user_plain';

    $plugins->add_hook('showthread_end', 'ougc_threadcontributors_showthread');
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Necessary plugin information for the ACP plugin manager.
function ougc_threadcontributors_info()
{
    global $lang;
    ougc_threadcontributors_lang_load();

    return [
        'name' => 'OUGC Thread Contributors',
        'description' => $lang->setting_group_ougc_threadcontributors,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.22',
        'versioncode' => 1822,
        'compatibility' => '18*',
        'codename' => 'ougc_threadcontributors',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

// _activate() routine
function ougc_threadcontributors_activate()
{
    global $PL, $cache, $lang, $ougc_threadcontributors;

    ougc_threadcontributors_pl_check();

    // Add settings group
    $settingsContents = \file_get_contents(OUGC_THREAD_CONTRIBUTORS_ROOT . '/settings.json');

    $settingsData = \json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougc_threadcontributors_{$settingKey}"})) {
            continue;
        }

        if ($settingData['optionscode'] == 'radio') {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougc_threadcontributors_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougc_threadcontributors_{$settingKey}"};
        $settingData['description'] = $lang->{"setting_ougc_threadcontributors_{$settingKey}_desc"};
    }

    // Modify templates
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
    $PL->settings(
        'ougc_threadcontributors',
        $lang->setting_group_ougc_threadcontributors,
        $lang->setting_group_ougc_threadcontributors_desc,
        $settingsData
    );

    find_replace_templatesets('showthread', '#' . preg_quote('{$usersbrowsing}') . '#', '{$usersbrowsing}{$ougc_threadcontributors_list}');
    // Add templates
    $templatesDirIterator = new \DirectoryIterator(OUGC_THREAD_CONTRIBUTORS_ROOT . '/templates');

    $templates = [];

    foreach ($templatesDirIterator as $template) {
        if (!$template->isFile()) {
            continue;
        }

        $pathName = $template->getPathname();

        $pathInfo = \pathinfo($pathName);

        if ($pathInfo['extension'] === 'html') {
            $templates[$pathInfo['filename']] = \file_get_contents($pathName);
        }
    }

    if ($templates) {
        $PL->templates('ougcthreadcontributors', 'OUGC Thread Contributors', $templates);
    }

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    $info = ougc_threadcontributors_info();

    if (!isset($plugins['threadcontributors'])) {
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
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

    find_replace_templatesets('showthread', '#' . preg_quote('{$ougc_threadcontributors_list}') . '#', '', 0);

    find_replace_templatesets('showthread', '#' . preg_quote('{$ougc_threadcontributors}') . '#', '', 0);
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

    foreach ($ougc_threadcontributors->_db_columns() as $table => $columns) {
        foreach ($columns as $name => $definition) {
            !$db->field_exists($name, $table) || $db->drop_column($table, $name);
        }
    }

    $PL->settings_delete('ougc_threadcontributors');

    $PL->templates_delete('ougcthreadcontributors');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['threadcontributors'])) {
        unset($plugins['threadcontributors']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }
}

// PluginLibrary dependency check & load
function ougc_threadcontributors_pl_check()
{
    global $lang;

    ougc_threadcontributors_lang_load();

    $info = ougc_threadcontributors_info();

    if ($file_exists = file_exists(PLUGINLIBRARY)) {
        global $PL;

        $PL or require_once PLUGINLIBRARY;
    }

    if (!$file_exists || $PL->version < $info['pl']['version']) {
        flash_message($lang->sprintf($lang->ougc_threadcontributors_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
        admin_redirect('index.php?module=config-plugins');
    }
}

// Pretty settings
function ougc_threadcontributors_settings_change()
{
    global $db, $mybb;

    $query = $db->simple_select('settinggroups', 'name', 'gid=\'' . (int)$mybb->input['gid'] . '\'');
    $groupname = $db->fetch_field($query, 'name');

    if ($groupname == 'ougc_threadcontributors') {
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

    $comma = $ougc_threadcontributors_list = $where = $users = '';

    $orderdir = 'DESC';

    if ($mybb->settings['ougc_threadcontributors_orderdir']) {
        $orderdir = 'ASC';
    }

    if (empty($thread['ougc_threadcontributors'])) {
        $ougc_threadcontributors->set_update_thread($tid);

        $uids = $ougc_threadcontributors->update_thread();
    }

    if (empty($uids)) {
        $uids = array_map('intval', explode(',', $thread['ougc_threadcontributors']));
    }

    $uids = implode("','", array_values($uids));

    $author = (int)$thread['uid'];

    if ($mybb->settings['ougc_threadcontributors_orderby'] == 'posttime') {
        if ($mybb->settings['ougc_threadcontributors_ignoreauthor']) {
            $where = " AND u.uid!='{$author}'";
        }

        $query = $db->query("
			SELECT u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup
			FROM " . TABLE_PREFIX . "posts p
			LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=p.uid)
			WHERE p.tid='{$tid}' AND u.uid IN ('{$uids}'){$where}
			ORDER BY p.dateline {$orderdir}
		");
    } else {
        if ($mybb->settings['ougc_threadcontributors_ignoreauthor']) {
            $where = " AND uid!='{$author}'";
        }

        $query = $db->simple_select(
            'users',
            'uid, username, avatar, avatardimensions, usergroup, displaygroup',
            "uid IN ('{$uids}'){$where}",
            [
                'order_by' => 'username',
                'order_dir' => $orderdir
            ]
        );
    }

    $showavatars = $mybb->settings['ougc_threadcontributors_showavatars'] && $mybb->user['showavatars'];

    $max_dimension = (int)$mybb->settings['ougc_threadcontributors_maxsize'];

    $max_dimensions = $max_dimension . 'x' . $max_dimension;

    $done_users = [];

    while ($user = $db->fetch_array($query, 'pid')) {
        if (isset($done_users[$user['uid']])) {
            continue;
        }

        $done_users[$user['uid']] = true;

        $user['username'] = htmlspecialchars_uni($user['username']);

        $date = my_date('relative', $user['dateline']);

        $user['profilelink'] = get_profile_link($user['uid']);
        $user['username_formatted'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

        if ($showavatars) {
            $avatar = format_avatar($user['avatar'], $user['avatardimensions'], $max_dimensions);

            $dyn = eval($templates->render('ougcthreadcontributors_user_avatar', true, false));
        } else {
            $dyn = eval($templates->render('ougcthreadcontributors_user_plain', true, false));
        }

        $users .= eval($templates->render('ougcthreadcontributors_user'));

        $comma = $showavatars ? ' ' : $lang->ougc_threadcontributors_comma . ' ';
    }

    if ($users) {
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
        if (!defined('IN_ADMINCP')) {
            $plugins->add_hook('class_moderation_delete_post', [$this, 'hook_class_moderation_delete_post_start']);
            $plugins->add_hook('class_moderation_merge_posts', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_merge_threads', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_split_posts', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_approve_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_unapprove_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_soft_delete_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_restore_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('datahandler_post_insert_post_end', [$this, 'hook_datahandler_post_insert_post_end']);
            $plugins->add_hook('datahandler_post_update_end', [$this, 'hook_datahandler_post_insert_post_end']); // perhaps the author changed because of external plugins
        }
    }

    // Plugin API:_is_installed() routine
    function _is_installed()
    {
        global $db;

        foreach ($this->_db_columns() as $table => $columns) {
            foreach ($columns as $name => $definition) {
                $installed = $db->field_exists($name, $table);

                break;
            }
        }

        return $installed;
    }

    // List of columns
    function _db_columns()
    {
        $tables = [
            'threads' => [
                'ougc_threadcontributors' => "text NULL"
            ],
        ];

        return $tables;
    }

    // Verify DB columns
    function _db_verify_columns()
    {
        global $db;

        foreach ($this->_db_columns() as $table => $columns) {
            foreach ($columns as $field => $definition) {
                if ($db->field_exists($field, $table)) {
                    $db->modify_column($table, "`{$field}`", $definition);
                } else {
                    $db->add_column($table, $field, $definition);
                }
            }
        }
    }

    function update_thread()
    {
        global $db;

        if (!($tid = $this->get_update_thread())) {
            return false;
        }

        $query = $db->simple_select('posts', 'uid', "tid='{$tid}' AND visible='1'");

        $uids = [];

        while ($uid = $db->fetch_field($query, 'uid')) {
            $uid = (int)$uid;

            $uids[$uid] = $uid;
        }

        if (!empty($uids)) {
            $db->update_query('threads', ['ougc_threadcontributors' => implode(',', $uids)], "tid='{$tid}'");
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

        $thread = get_thread($post['tid']);

        $this->set_update_thread($thread['tid']);

        $plugins->add_hook('class_moderation_delete_post', [$this, 'hook_class_moderation_delete_post']);
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
        if (!empty($pids)) {
            global $db;

            $done = [];

            $query = $db->simple_select('posts', 'tid', "pid IN (" . implode(',', $pids) . ")");

            while ($tid = (int)$db->fetch_field($query, 'tid')) {
                if (isset($done[$tid])) {
                    continue;
                }

                $done[$tid] = $tid;

                $this->set_update_thread($tid);

                $this->update_thread();
            }
        }
    }

    function hook_datahandler_post_insert_post_end(&$dataHandler): void
    {
        if (!empty($dataHandler->data['tid'])) {
            $this->set_update_thread($dataHandler->data['tid']);

            $this->update_thread();
        }
    }
}

global $ougc_threadcontributors;

$ougc_threadcontributors = new OUGC_ThreadContributors;