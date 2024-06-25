<?php

/***************************************************************************
 *
 *    Lock plugin (/inc/languages/english/admin/lock.lang.php)
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

$l['lock'] = "Lock";
$l['lock_desc'] = "Lock is a MyBB plugin for hiding content and selling it for your Newpoints currency.";

$l['lock_pluginlibrary'] = "This plugin requires <a href=\"{1}\">PluginLibrary</a> version {2} or later to be uploaded to your forum. Please upload the necessary files before continuing.";

$l['setting_group_lock'] = "Lock";
$l['setting_group_lock_desc'] = "Settings for the hidding content plugin.";
$l['setting_lock_key'] = "Key";
$l['setting_lock_key_desc'] = "A password to keep spooky people from editing values they shouldn't be.";
$l['setting_lock_purchases_enabled'] = "Enable Newpoints purchases.";
$l['setting_lock_purchases_enabled_desc'] = "Allow users to sell locked content for newpoints credits.";
$l['setting_lock_allow_user_prices'] = "Allow user prices.";
$l['setting_lock_allow_user_prices_desc'] = "Do you want to let users set the price of their content?";
$l['setting_lock_default_price'] = "Default price.";
$l['setting_lock_default_price_desc'] = "The default price for hidden content. Set this to zero if you want hide tags to fall back to the \"Reply to view\" mode.";
$l['setting_lock_tax'] = "Tax";
$l['setting_lock_tax_desc'] = "Tax a percentage of the points every user spends on content (up to 100%).";
$l['setting_lock_exempt'] = "Excempt usergroups.";
$l['setting_lock_exempt_desc'] = "Select the usergroups that can bypass the hide tags.";
$l['setting_lock_disabled_forums'] = "Disabled forums.";
$l['setting_lock_disabled_forums_desc'] = "Select the forums that you do not want the \"pay to view\" functionality to work in.";
$l['setting_lock_type'] = "Use '[hide]' Instead of '[lock'].";
$l['setting_lock_type_desc'] = "You can either use a the wording 'hide' or 'lock'.";

$l['lock_permission_maxcost'] = "Maximum price per post.";
$l['lock_permission_maxcost_desc'] = "Insert the maximum Newpoints points users can charge to display hidden content. Leave empty for no limit. (Maximum: 99999)";