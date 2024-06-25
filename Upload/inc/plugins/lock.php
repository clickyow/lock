<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/plugins/lock.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Lock is a MyBB plugin for hiding content and selling it for your Newpoints currency.
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

if (THIS_SCRIPT == 'showthread.php') {
    global $templatelist;

    if (!isset($templatelist)) {
        $templatelist = '';
    }

    $templatelist .= ',lock_wrapper,lock_form,';
}

defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

function lock_info(): array
{
    global $mybb, $lang;

    isset($lang->lock) || $lang->load('lock');

    $lock_desc = '';

    if ($mybb->get_input('module') === 'config-plugins') {
        $lock_desc = ' This work is forked off the <a href="https://github.com/neko">Lock</a> plugin by <a href="https://community.mybb.com/user-99749.html">Nekomimi</a>.';
    }

    return [
        'name' => 'Lock',
        'description' => $lang->lock_desc . $lock_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.36',
        'versioncode' => 1836,
        'compatibility' => '18*',
        'codename' => 'ougc_lock',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

global $plugins;

if (!defined('IN_ADMINCP')) {
    // keep people from using the highlight feature to bypass the tags
    $plugins->add_hook('parse_message_start', 'lock_highlight_start');
    $plugins->add_hook('parse_message', 'lock_highlight_end');

    // adds a new action method to MyBB.
    $plugins->add_hook('global_end', 'lock_purchase');

    // remove hide tags from quotes
    $plugins->add_hook('parse_quoted_message', 'lock_quoted');

    // validate maximum cost
    $plugins->add_hook('datahandler_post_validate_post', ['Shortcodes', 'validate_post']);
    $plugins->add_hook('datahandler_post_validate_thread', ['Shortcodes', 'validate_post']);
} else {
    $plugins->add_hook('admin_formcontainer_end', 'lock_admin_formcontainer_end');
    $plugins->add_hook('admin_user_groups_edit_commit', 'lock_admin_user_groups_edit_commit');
}

if (!empty($mybb->input['highlight'])) {
    $highlight_replacement = null;
}

if (!class_exists('Shortcodes')) {
    require __DIR__ . '/lock/shortcodes.class.php';
}

function lock_activate(): bool
{
    global $db, $PL, $lang;
    lock_deactivate();

    require_once __DIR__ . '/lock/core/install.php';

    return true;
}

function lock_deactivate(): bool
{
    global $PL, $lang;

    isset($lang->lock) || $lang->load('lock');

    $info = lock_info();

    if ($file_exists = file_exists(PLUGINLIBRARY)) {
        $PL or require_once PLUGINLIBRARY;
    }

    if (!$file_exists || $PL->version < $info['pl']['version']) {
        flash_message($lang->sprintf($lang->lock_pluginlibrary, $info['pl']['url'], $info['pl']['version']), 'error');
        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function lock_uninstall(): bool
{
    global $db, $PL;

    require_once __DIR__ . '/lock/core/uninstall.php';

    return true;
}

function lock_is_installed(): bool
{
    global $db;

    return (bool)$db->field_exists('unlocked', 'posts');
}

function lock_highlight_start(string $message): string
{
    global $mybb, $replacement;

    if (!empty($mybb->input['highlight'])) {
        $replacement = substr(
            str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYXZ', 20)),
            0,
            20
        );

        switch ((string)$mybb->settings['lock_type']) {
            case 'lock':
                $message = str_replace('lock', $replacement, $message);
                break;
            case 'cap':
                $message = str_replace('cap', $replacement, $message);
                break;
            default:
                $message = str_replace('hide', $replacement, $message);
                break;
        }
    }

    return $message;
}

function lock_highlight_end(string $message): string
{
    global $mybb, $replacement;

    if (!empty($mybb->input['highlight'])) {
        switch ((string)$mybb->settings['lock_type']) {
            case 'lock':
                $message = str_replace($replacement, 'lock', $message);
                break;
            case 'cap':
                $message = str_replace($replacement, 'cap', $message);
                break;
            default:
                $message = str_replace($replacement, 'hide', $message);
                break;
        }
    }

    return Shortcodes::parse($message);
}

function lock_purchase(): bool
{
    global $_POST, $mybb, $db;

    require_once __DIR__ . '/lock/core/purchase.php';

    return true;
}

require_once __DIR__ . '/lock/core/shortcode.php';

function lock_quoted(array &$quoted_post): array
{
    Shortcodes::set_tag();
    $quoted_post['message'] = preg_replace(
        '#\[' . Shortcodes::$tag . '(.*)\[/' . Shortcodes::$tag . '\]#is',
        '',
        $quoted_post['message']
    );

    return $quoted_post;
}

// Hook: admin_formcontainer_end
function lock_admin_formcontainer_end(): bool
{
    global $run_module, $form_container, $lang;

    if ($run_module == 'user' && isset($form_container->_title) && $form_container->_title == $lang->users_permissions) {
        global $form, $mybb;

        isset($lang->lock) || $lang->load('lock');

        $perms = [];

        $db_fields = lock_get_db_fields();

        foreach ($db_fields['usergroups'] as $name => $definition) {
            $perms[] = "<br />{$lang->lock_permission_maxcost}<br /><small>{$lang->lock_permission_maxcost_desc}</small><br />{$form->generate_text_box($name, $mybb->get_input($name, MyBB::INPUT_STRING), ['id' => $name, 'class' => 'field50'])}";
        }

        $form_container->output_row(
            $lang->setting_group_lock,
            '',
            '<div class="group_settings_bit">' . implode('</div><div class="group_settings_bit">', $perms) . '</div>'
        );
    }

    return true;
}

// Hook: admin_user_groups_edit_commit
function lock_admin_user_groups_edit_commit(): bool
{
    global $updated_group, $mybb;

    $db_fields = lock_get_db_fields();

    foreach ($db_fields['usergroups'] as $name => $definition) {
        $updated_group[$name] = $mybb->get_input($name, MyBB::INPUT_STRING);
    }

    return true;
}

function lock_get_db_fields(): array
{
    global $db;

    // Create DB table
    switch ($db->type) {
        case 'pgsql':
        default:
            $fields = [
                'usergroups' => [
                    'lock_maxcost' => "VARCHAR(5) NOT NULL DEFAULT ''",
                ]
            ];
            break;
    }

    return $fields;
}