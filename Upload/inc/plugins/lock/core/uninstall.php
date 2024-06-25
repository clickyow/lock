<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/plugins/lock/core/uninstall.php)
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

global $PL, $db;

// Delete settings
$PL->settings_delete('lock');

// remove the unlocked column from the posts table.
!$db->field_exists('unlocked', 'posts') or $db->drop_column('posts', 'unlocked');

// Delete template group
$PL->templates_delete('lock');

// Remove DB fields
foreach (lock_get_db_fields() as $table => $fields) {
    foreach ($fields as $name => $definition) {
        if ($db->field_exists($name, $table)) {
            $db->drop_column($table, $name);
        }
    }
}