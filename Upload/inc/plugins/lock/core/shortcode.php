<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/plugins/lock/core/shortcode.php)
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

function lock_hide(array $params, string $content): string
{
    global $mybb, $post, $templates, $lang, $db;

    isset($lang->lock) || $lang->load('lock');

    // if the tag has no content, do nothing.
    if (!$content) {
        return '';
    }

    // return nothing if the print thread page is viewed
    if (empty($post['pid'])) {
        return $lang->lock_title;
    }


    if (!is_array($params)) {
        $params = (array)$params;
    }

    if (my_strpos($params[0], '=') === 0) {
        $params['cost'] = (int)str_replace('=', '', $params[0]);
    }

    // does the user have to pay for the content?
    if (function_exists(
            'newpoints_format_points'
        ) && ($mybb->settings['lock_purchases_enabled'] == true || (int)$mybb->settings['lock_default_price'] > 0)) {
        // is the pay to view feature allowed in this forum?
        $disabled = explode(',', $mybb->settings['lock_disabled_forums']);
        if (!in_array($post['fid'], $disabled) || $mybb->settings['lock_disabled_forums'] == -1) {
            // does the content have a price? can the user set the price?
            if (!isset($params['cost'])) {
                if (!empty($params['cost'])) {
                    // if not, do we have a default price?
                    if ($mybb->settings['lock_default_price'] > 0) {
                        $params['cost'] = $mybb->settings['lock_default_price'];
                    } else {
                        $params['cost'] = null;
                    }
                }
            } elseif (!(bool)$mybb->settings['lock_allow_user_prices'] && $mybb->settings['lock_default_price'] > 0) {
                $params['cost'] = $mybb->settings['lock_default_price'];
            }

            // is the cost an actual number?
            if (is_numeric($params['cost'])) {
                // cost must be valid, because numbers aren't evil.
                $cost = $params['cost'];

                // check to see whether the user hasn't already unlocked the content.
                $allowed = explode(',', $post['unlocked'] ?? '');

                if (in_array($mybb->user['uid'], $allowed)) {
                    $paid = true;
                }
            }
        }
    }

    static $posted = null;

    if (!isset($cost) && $posted === null) {
        // if there's no cost, this must be a "post to view" hide tag

        // check to see whether the user has posted in this thread.
        $query = $db->simple_select(
            'posts',
            '*',
            "tid = '{$post['tid']}' AND uid = '{$mybb->user['uid']}'"
        );//  AND visible='1' ?

        $posted = (bool)$db->num_rows($query);
    }

    // if no title has been set, set a default title.
    if (!isset($params['title'])) {
        $params['title'] = $lang->lock_title;
    }

    // if the user is not the OP, and has not been exempt from having hidden content
    if (
        $mybb->user['uid'] != $post['uid'] &&
        !is_member($mybb->settings['lock_exempt'])
    ) {
        // if the user isn't logged in, tell them to login or register.
        if (!$mybb->user['uid']) {
            $return = $lang->sprintf($lang->lock_nopermission_guest, $mybb->settings['bburl']);
            // if they are logged in, but the item has a price that they haven't paid yet, tell them how they can pay for it.
        } elseif (isset($cost) && !$paid && function_exists('newpoints_format_points')) {
            // include the pcrypt class, so we can encrypt our data; to keep it safe from spookys.
            require_once __DIR__ . '/../pcrypt.php';

            $key = $mybb->settings['lock_key'];

            $pcrypt = new \pcrypt();

            $pcrypt = $pcrypt->pcrypt(MODE_ECB, 'BLOWFISH', $key);

            // place the info we need, into an array
            $info = [
                'pid' => $post['pid'],
                'cost' => $cost
            ];

            // encode the information as json, for safe transit
            $info = json_encode($info);

            // encrypt the json, and encode it as base64; so it can be submitted in a form.
            $info = base64_encode($pcrypt->encrypt($info));

            static $posts_prices = [];

            if (!isset($posts_prices[$post['pid']])) {
                $posts_prices[$post['pid']] = (int)$cost;
            }

            $posts_prices[$post['pid']] = max($posts_prices[$post['pid']], (int)$cost);

            $points = strip_tags(newpoints_format_points($posts_prices[$post['pid']]));

            $user_points = $lang->sprintf(
                $lang->lock_purchase_yougot,
                strip_tags(newpoints_format_points($mybb->user['newpoints']))
            );

            $lang_confirm = $lang->sprintf($lang->lock_purchase_confirm, $points);
            $lock_purchase = $lang->sprintf($lang->lock_purchase, $points);

            // build the return button.
            $return = eval($templates->render('lock_form', true, false));
            // if the user doesn't need to pay, but hasn't posted

        } elseif (!$paid && !$posted) {
            // tell them to reply to the thread.

            $return = $lang->lock_nopermission_reply;
            // all is good.
        } else {
            // give them the content.
            $return = $content;
        }
        // bypass the hide tags.
    } else {
        // give them the content
        $return = $content;
    }

    $cost_desc = '';

    if (isset($cost) && function_exists('newpoints_format_points') && !isset($points)) {
        $points = newpoints_format_points($cost);

        $cost_desc = $lang->sprintf($lang->lock_purchase_cost, $points);
    }

    $return = eval($templates->render('lock_wrapper', true, false));

    return $return;
}

// add the hide tag if the shortcodes plugin has been installed.

global $mybb;

switch ((string)$mybb->settings['lock_type']) {
    case 'lock':
        Shortcodes::add('lock', 'lock_hide');
        break;
    case 'cap':
        Shortcodes::add('cap', 'lock_hide');
        break;
    default:
        Shortcodes::add('hide', 'lock_hide');
        break;
}