<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/plugins/lock/core/purchase.php)
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

global $mybb, $db;

// if the action is not purchase, we don't need to continue.
if ($mybb->input['action'] !== 'purchase') {
    return;
}

// if the purchases functionality has not been enabled, we do not need to continue.
if ($mybb->settings['lock_purchases_enabled'] != true || !function_exists('newpoints_format_points')) {
    return;
}

$key = $mybb->settings['lock_key'];

// include the pcrypt class, so we can decrypt the sent info.
require_once __DIR__ . '/../pcrypt.php';
$pcrypt = new \pcrypt();

$pcrypt = $pcrypt->pcrypt(MODE_ECB, 'BLOWFISH', $key);

// convert the sent info back into json data
$json = $pcrypt->decrypt(base64_decode($_POST['info']));

// if the data is indeed json data
if ($info = json_decode($json)) {
    // if the data has been successfully turned back into an object.
    if (is_object($info)) {
        // if the cost and post id are not numbers, return an error.
        if (!is_numeric($info->cost) || !is_numeric($info->pid)) {
            error('Something went wrong: NaN');
        }

        $post = get_post($info->pid);

        $higher_price = 0;

        Shortcodes::get_higher_price_from_message($post['message'], $higher_price);

        if ((bool)$mybb->settings['lock_allow_user_prices']) {
            $info->cost = max($higher_price, $info->cost); // too much?
        } else {
            $params['cost'] = (int)$mybb->settings['lock_default_price'];
        }

        // check whether the current user has already unlocked the content
        $query = $db->simple_select('posts', 'uid,unlocked', "pid='{$info->pid}'");
        $post = $db->fetch_array($query);

        $allowed = explode(',', $post['unlocked'] ?? '');

        if (!is_array($allowed)) {
            $allowed = array();
        }

        if (!in_array($mybb->user['uid'], $allowed)) {
            // user doesn't have it unlocked
            if ($mybb->user['newpoints'] < $info->cost) {
                // user does not have enough funds to pay for the item
                error('You do not have enough points to purchase this item.');
            } else {
                // take the points from the user
                newpoints_addpoints($mybb->user['uid'], -$info->cost);

                $mybb->settings['lock_tax'] = $mybb->settings['lock_tax'];

                if (is_numeric($mybb->settings['lock_tax']) && $mybb->settings['lock_tax'] > 0) {
                    $tax = $mybb->settings['lock_tax'];
                }

                if (isset($tax) && $tax > 100) {
                    $tax = 100;
                }

                if (is_numeric($tax)) {
                    $info->cost = $info->cost - ($info->cost / 100 * $tax);
                }

                // give them to the creator of the post
                newpoints_addpoints($post['uid'], $info->cost);

                // add the user to the list of people with access to the content
                $allowed[] = $mybb->user['uid'];
                $allowed = implode(',', $allowed);

                $unlocked = array(
                    'unlocked' => $allowed,
                );

                $db->update_query('posts', $unlocked, "pid='{$info->pid}'");
            }
        }

        // now, check that the post actually exists
        $query = $db->simple_select('posts', '*', "pid = '{$info->pid}'");
        if ($db->num_rows($query)) {
            // if it does, redirect the user to the post.
            $post = $db->fetch_array($query);
            $url = $mybb->settings['bburl'] . '/' . get_post_link($info->pid) . '#pid' . $info->pid;

            header('Location: ' . $url);
            exit();
        }
    }
}