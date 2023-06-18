<?php

/***************************************************************************
 *
 *   OUGC Thread Contributors plugin (/inc/plugins/ougc_threadcontributors.php)
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

declare(strict_types=1);

// Die if IN_MYBB is not defined, for security reasons.
if (!defined('IN_MYBB')) {
    die('This file cannot be accessed directly.');
}

const OUGC_THREAD_CONTRIBUTORS_ROOT = MYBB_ROOT . 'inc/plugins/ougc/ThreadContributors';

// PLUGINLIBRARY
if (!defined('PLUGINLIBRARY')) {
    define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');
}

// Necessary plugin information for the ACP plugin manager.
function ougc_threadcontributors_info(): array
{
    global $lang;

    ougc_threadcontributors_lang_load();

    return [
        'name' => 'OUGC Thread Contributors',
        'description' => $lang->setting_group_ougc_threadcontributors,
        'website' => 'https://community.mybb.com/mods.php?action=view&pid=1375',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.33',
        'versioncode' => 1833,
        'compatibility' => '183*',
        'codename' => 'ougc_threadcontributors',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

// _activate() routine
function ougc_threadcontributors_activate(): void
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

    $PL->settings(
        'ougc_threadcontributors',
        $lang->setting_group_ougc_threadcontributors,
        $lang->setting_group_ougc_threadcontributors_desc,
        $settingsData
    );

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
    $plugins = (array)$cache->read('ougc_plugins');

    $versionCode = ougc_threadcontributors_info()['versioncode'];

    if (!isset($plugins['threadcontributors'])) {
        $plugins['threadcontributors'] = $versionCode;
    }

    $ougc_threadcontributors->_db_verify_columns();

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['threadcontributors'] = $versionCode;

    $cache->update('ougc_plugins', $plugins);
}

// _is_installed() routine
function ougc_threadcontributors_is_installed(): bool
{
    global $ougc_threadcontributors;

    return $ougc_threadcontributors->_is_installed();
}

// _uninstall() routine
function ougc_threadcontributors_uninstall(): void
{
    global $PL, $cache, $ougc_threadcontributors, $db;

    if (!($PL instanceof \PluginLibrary)) {
        require_once \PLUGINLIBRARY;
    }

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
        $PL->cache_delete('ougc_plugins');
    }
}

// PluginLibrary dependency check & load
function ougc_threadcontributors_pl_check(): void
{
    global $PL, $lang;

    ougc_threadcontributors_lang_load();

    $info = ougc_threadcontributors_info();

    $fileExists = file_exists(\PLUGINLIBRARY);

    if ($fileExists && !($PL instanceof \PluginLibrary)) {
        require_once \PLUGINLIBRARY;
    }

    if (!$fileExists || $PL->version < $info['pl']['version']) {
        \flash_message(
            $lang->sprintf(
                $lang->ougc_threadcontributors_pl_required,
                $info['pl']['ulr'],
                $info['pl']['version']
            ),
            'error'
        );

        \admin_redirect('index.php?module=config-plugins');
    }
}

// Load language file
function ougc_threadcontributors_lang_load(): void
{
    global $lang;

    if (!isset($lang->setting_group_ougc_threadcontributors)) {
        $lang->load('ougc_threadcontributors');
    }
}

// Dark magic
function ougc_threadcontributors_showthread(): void
{
    global $db, $tid, $mybb, $templates, $lang, $thread;
    global $ougc_threadcontributors_list, $ougc_threadcontributors;

    ougc_threadcontributors_lang_load();

    $comma = $ougc_threadcontributors_list = $users = '';

    $orderdir = 'DESC';

    if ($mybb->settings['ougc_threadcontributors_orderdir']) {
        $orderdir = 'ASC';
    }

    $tid = (int)$tid;

    if (empty($thread['ougc_threadcontributors'])) {
        $ougc_threadcontributors->set_update_thread($tid);

        $uids = $ougc_threadcontributors->update_thread();
    } else {
        $uids = array_map('intval', explode(',', $thread['ougc_threadcontributors']));
    }

    $uids = implode("','", array_values($uids));

    $author = (int)$thread['uid'];

    $where = ["u.uid IN ('{$uids}')"];

    if ($mybb->settings['ougc_threadcontributors_ignoreauthor']) {
        $where[] = "u.uid!='{$author}'";
    }

    $post_count_cache = [];

    if ($mybb->settings['ougc_threadcontributors_count_posts']) {
        $query = $db->simple_select(
            "posts p LEFT JOIN {$db->table_prefix}users u ON (u.uid=p.uid)",
            'p.uid',
            implode(' AND ', array_merge($where, ["p.tid='{$tid}' AND p.visible='1'"]))
        );

        while ($uid = (int)$db->fetch_field($query, 'uid')) {
            if (!isset($post_count_cache[$uid])) {
                $post_count_cache[$uid] = 0;
            }

            ++$post_count_cache[$uid];
        }
    }

    $queryTable = 'users u';

    $orderBy = 'u.username';

    if ($mybb->settings['ougc_threadcontributors_orderby'] == 'posttime') {
        $where[] = "p.tid='{$tid}'";

        $queryTable = "posts p LEFT JOIN {$db->table_prefix}users u ON (u.uid=p.uid)";

        $orderBy = 'p.dateline';
    }

    $query = $db->simple_select(
        $queryTable,
        'u.uid, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup',
        implode(' AND ', $where),
        [
            'order_by' => $orderBy,
            'order_dir' => $orderdir
        ]
    );

    $showavatars = true;

    if (!$mybb->settings['ougc_threadcontributors_showavatars'] || (
            $mybb->user['uid'] && !$mybb->user['showavatars'] ||
            !$mybb->user['uid'] && !$mybb->settings['ougc_threadcontributors_showavatars_guests']
        )) {
        $showavatars = false;
    }

    $max_dimension = (int)$mybb->settings['ougc_threadcontributors_maxsize'];

    $max_dimensions = $max_dimension . 'x' . $max_dimension;

    global $PL;

    if (!($PL instanceof \PluginLibrary)) {
        require_once \PLUGINLIBRARY;
    }

    $done_users = [];

    while ($user = $db->fetch_array($query, 'pid')) {
        $uid = (int)$user['uid'];

        if (isset($done_users[$uid])) {
            continue;
        }

        $done_users[$uid] = true;

        $user['username'] = htmlspecialchars_uni($user['username']);

        //$date = my_date('relative', $user['dateline']);

        if ($mybb->settings['ougc_threadcontributors_allowPostFiltering']) {
            if ($mybb->seo_support === true) {
                $userLink = get_thread_link($thread['tid']);
            } else {
                $userLink = get_thread_link($thread['tid'], 0, 'thread');
            }
        } else {
            $userLink = get_profile_link($uid);
        }

        $user['profilelink'] = $PL->url_append($userLink, ['otc_filter' => $uid]);

        $user['username_formatted'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

        if ($showavatars) {
            $avatar = format_avatar($user['avatar'], $user['avatardimensions'], $max_dimensions);

            $dyn = eval($templates->render('ougcthreadcontributors_user_avatar', true, false));
        } else {
            $dyn = eval($templates->render('ougcthreadcontributors_user_plain', true, false));
        }

        $post_count = '';

        if ($mybb->settings['ougc_threadcontributors_count_posts']) {
            $posts_count = 0;

            if (isset($post_count_cache[$uid])) {
                $posts_count = \my_number_format($post_count_cache[$user['uid']]);
            }

            $post_count = eval($templates->render('ougcthreadcontributors_user_postcount'));
        }

        $users .= eval($templates->render('ougcthreadcontributors_user'));

        if (!$showavatars) {
            $comma = $lang->ougc_threadcontributors_comma;
        }
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
        global $plugins;
        global $templatelist;

        // Tell MyBB when to run the hook
        if (defined('IN_ADMINCP')) {
            $plugins->add_hook('admin_config_settings_start', 'ougc_threadcontributors_lang_load');
            $plugins->add_hook('admin_style_templates_set', 'ougc_threadcontributors_lang_load');
            $plugins->add_hook('admin_config_settings_change', 'ougc_threadcontributors_lang_load');
        } else {
            $plugins->add_hook('class_moderation_delete_post', [$this, 'hook_class_moderation_delete_post_start']);
            $plugins->add_hook('class_moderation_merge_posts', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_merge_threads', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_split_posts', [$this, 'hook_class_moderation_merge_posts']);
            $plugins->add_hook('class_moderation_approve_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_unapprove_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_soft_delete_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('class_moderation_restore_posts', [$this, 'hook_class_moderation_approve_posts']);
            $plugins->add_hook('showthread_end', 'ougc_threadcontributors_showthread');
            $plugins->add_hook('showthread_start', [$this, 'hook_showthread_start']);
            $plugins->add_hook('datahandler_post_insert_post_end', [$this, 'hook_datahandler_post_insert_post_end']);
            $plugins->add_hook('datahandler_post_update_end', [$this, 'hook_datahandler_post_insert_post_end']); // perhaps the author changed because of external plugins

            if (isset($templatelist)) {
                $templatelist .= ',';
            } else {
                $templatelist = '';
            }

            $templatelist .= 'ougcthreadcontributors, ougcthreadcontributors_user, ougcthreadcontributors_user_avatar, ougcthreadcontributors_user_plain, ougcthreadcontributors_user_postcount';

        }
    }

    // Plugin API:_is_installed() routine
    function _is_installed(): bool
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
    function _db_columns(): array
    {
        return [
            'threads' => [
                'ougc_threadcontributors' => "text NULL"
            ],
        ];
    }

    // Verify DB columns
    function _db_verify_columns(): void
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

    function update_thread(): array
    {
        global $db;

        $uids = [];

        if (!($tid = $this->get_update_thread())) {
            return $uids;
        }

        $query = $db->simple_select('posts', 'uid', "tid='{$tid}' AND visible='1'");

        while ($uid = $db->fetch_field($query, 'uid')) {
            $uid = (int)$uid;

            $uids[$uid] = $uid;
        }

        if (!empty($uids)) {
            $db->update_query('threads', ['ougc_threadcontributors' => implode(',', $uids)], "tid='{$tid}'");
        }

        return $uids;
    }

    function set_update_thread(int $tid): void
    {
        $this->update_thread = (int)$tid;
    }

    function get_update_thread(): int
    {
        return $this->update_thread;
    }

    function hook_class_moderation_delete_post_start(&$pid): void
    {
        global $plugins;

        $post = get_post($pid);

        $tid = (int)$post['tid'];

        $this->set_update_thread($tid);

        $plugins->add_hook('class_moderation_delete_post', [$this, 'hook_class_moderation_delete_post']);
    }

    function hook_class_moderation_delete_post(&$pid): void
    {
        $this->update_thread();
    }

    function hook_class_moderation_merge_posts(&$args): void
    {
        $tid = (int)$args['tid'];

        $this->set_update_thread($tid);

        $this->update_thread();
    }

    function hook_class_moderation_approve_posts(&$pids): void
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

    function hook_showthread_start(): void
    {
        global $mybb;

        $userID = $mybb->get_input('otc_filter', \MyBB::INPUT_INT);

        if (!$userID || !$mybb->settings['ougc_threadcontributors_allowPostFiltering']) {
            return;
        }

        global $plugins, $visibleonly, $visibleonly_p, $visibleonly_p_t;

        $visibleonly .= " AND uid='{$userID}'";

        $visibleonly_p .= " AND p.uid='{$userID}'";

        $visibleonly_p_t .= " AND (p.uid='{$userID}' OR t.uid!='{$userID}')";

        $plugins->add_hook('multipage', [$this, 'hook_multipage']);
    }

    function hook_multipage(array &$arguments): array
    {
        global $mybb;
        global $PL;

        if (!($PL instanceof \PluginLibrary)) {
            require_once \PLUGINLIBRARY;
        }

        $userID = $mybb->get_input('otc_filter', \MyBB::INPUT_INT);

        $urlParams = ['otc_filter' => $userID];

        if ($mybb->settings['ougc_threadcontributors_allowPostFiltering'] && $mybb->seo_support === false) {
            $urlParams['action'] = 'thread';
        }

        $arguments['url'] = $PL->url_append($arguments['url'], $urlParams);


        return $arguments;
    }

    function hook_datahandler_post_insert_post_end(\PostDataHandler &$dataHandler): void
    {
        if (!empty($dataHandler->data['tid'])) {
            $tid = (int)$dataHandler->data['tid'];

            $this->set_update_thread($tid);

            $this->update_thread();
        }
    }
}

global $ougc_threadcontributors;

$ougc_threadcontributors = new OUGC_ThreadContributors;