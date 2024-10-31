<?php
/*
Plugin Name: Navu Conversions
Plugin URI: https://navu.co/support/wordpress-plugin/
Version: 1.0.4
Author: Navu
Author URI: https://navu.co
Description: Use Navu's service to track conversions and target visitors with personalized content
Text Domain: navu-conversions
License: GPLv2 or later
 */

/*
"WordPress Plugin Template" Copyright (C) 2018 Michael Simpson  (email : michael.d.simpson@gmail.com)

This following part of this file is part of WordPress Plugin Template for WordPress.

WordPress Plugin Template is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

WordPress Plugin Template is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Contact Form to Database Extension.
If not, see http://www.gnu.org/licenses/gpl-3.0.html
 */

$NavuConversions_minimalRequiredPhpVersion = '5.6';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function NavuConversions_noticePhpVersionWrong()
{
    global $NavuConversions_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
    __('Error: plugin "Navu Conversions" requires a newer version of PHP to be running.', 'navu-conversions') .
    '<br/>' . __('Minimal version of PHP required: ', 'navu-conversions') . '<strong>' . esc_html($NavuConversions_minimalRequiredPhpVersion) . '</strong>' .
    '<br/>' . __('Your server\'s PHP version: ', 'navu-conversions') . '<strong>' . phpversion() . '</strong>' .
        '</div>';
}

function NavuConversions_PhpVersionCheck()
{
    global $NavuConversions_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $NavuConversions_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'NavuConversions_noticePhpVersionWrong');
        return false;
    }
    return true;
}

/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function NavuConversions_i18n_init()
{
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('navu-conversions', false, $pluginDir . '/languages/');
}

//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action('plugins_loadedi', 'NavuConversions_i18n_init');

// Run the version check.
// If it is successful, continue with initialization for this plugin
if (NavuConversions_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once 'navu-conversions_init.php';
    NavuConversions_init(__FILE__);
}
