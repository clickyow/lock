<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/plugins/lock/core/install.php)
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

global $lang, $PL, $db;

isset($lang->lock) || $lang->load('lock');

// add a new setting group for Lock
$PL->settings('lock', 'Lock Settings', $lang->lock_desc, array(
    'key' => array(
        'title' => $lang->setting_lock_key,
        'description' => $lang->setting_lock_key_desc,
        'optionscode' => 'text',
        'value' => substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYXZ", 20)), 0, 20),
    ),
    'purchases_enabled' => array(
        'title' => $lang->setting_lock_purchases_enabled,
        'description' => $lang->setting_lock_purchases_enabled_desc,
        'optionscode' => 'yesno',
        'value' => 1,
    ),
    'allow_user_prices' => array(
        'title' => $lang->setting_lock_allow_user_prices,
        'description' => $lang->setting_lock_allow_user_prices_desc,
        'optionscode' => 'yesno',
        'value' => 1,
    ),
    'default_price' => array(
        'title' => $lang->setting_lock_default_price,
        'description' => $lang->setting_lock_default_price_desc,
        'optionscode' => 'numeric',
        'value' => 0,
    ),
    'default_price' => array(
        'title' => $lang->setting_lock_default_price,
        'description' => $lang->setting_lock_default_price_desc,
        'optionscode' => 'numeric',
        'value' => 0,
    ),
    'tax' => array(
        'title' => $lang->setting_lock_tax,
        'description' => $lang->setting_lock_tax_desc,
        'optionscode' => 'numeric',
        'value' => 10,
    ),
    'exempt' => array(
        'title' => $lang->setting_lock_exempt,
        'description' => $lang->setting_lock_exempt_desc,
        'optionscode' => 'groupselect',
        'value' => '3,4',
    ),
    'disabled_forums' => array(
        'title' => $lang->setting_lock_disabled_forums,
        'description' => $lang->setting_lock_disabled_forums_desc,
        'optionscode' => 'forumselect',
        'value' => '',
    ),
    'type' => array(
        'title' => $lang->setting_lock_type,
        'description' => $lang->setting_lock_type_desc,
        'optionscode' => 'radio
hide=Hide
lock=Lock
cap=Cap',
        'value' => 'hide',
    ),
));

// Lets delete unwanted setting groups
$delete = null;

$query = $db->simple_select("settinggroups", "*", "name='lock'");
while ($group = $db->fetch_array($query)) {
    if (!is_array($delete)) {
        $delete = array();
        continue;
    }

    $delete[] = $group['gid'];
}

$delete = implode("','", $delete);

$db->delete_query("settinggroups", "gid IN ('{$delete}')");

// add a new colum to the posts table.
if ($db->field_exists('unlocked', 'posts')) {
    $db->modify_column('posts', 'unlocked', 'TEXT');
} else {
    $db->add_column('posts', 'unlocked', 'TEXT');
}

// Insert a template group
$PL->templates('lock', 'Lock', array(
    'wrapper' => '<div class="hidden-content">
	<div class="hidden-content-title">
		<strong>{$params[\'title\']}</strong>
	</div>
	<div class="hidden-content-body">
		{$return}
	</div>
</div>',
    'form' => '<form method="post">
	{$lang->lock_purchase_desc}
	<input type="submit" class="button" value="{$lock_purchase}" onclick="javascript: return confirm(\'{$lang_confirm}\');" />
	<input type="hidden" name="info" value="{$info}" />
	<input type="hidden" name="action" value="purchase" />
</form>',
));

// Add DB fields
foreach (lock_get_db_fields() as $table => $fields) {
    foreach ($fields as $name => $definition) {
        if (!$db->field_exists($name, $table)) {
            $db->add_column($table, $name, $definition);
        } else {
            $db->modify_column($table, $name, $definition);
        }
    }
}