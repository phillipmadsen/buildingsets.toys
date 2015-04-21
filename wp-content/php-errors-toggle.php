<?php
/*
Plugin Name: PHP Errors Toggle
Plugin URI: http://www.wpbizplugins.com
Description: Toggle PHP errors on and off easily from the dashboard. WARNING: THIS IS SITEWIDE!
Version: 1.0.0
Author: Gabriel Nordeborn
Author URI: http://www.wpbizplugins.com
*/

/*  WPBizPlugins PHP Errors Toggle
    Copyright 2014  Gabriel Nordeborn  (email : gabriel@wpbizplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( isset( $_GET[ 'php-errors-toggle-set-new' ] ) ) {

    if( $_GET[ 'php-errors-toggle-set-new' ] == '1' ) {

        update_option( 'php_error_toggle', '0' );

    } else {

        update_option( 'php_error_toggle', '1' );

    }

}

if( php_error_toggle_get_option() == 1 ) {
    ini_set('display_startup_errors',1);
    ini_set('display_errors',1);
    error_reporting(-1);
}


function php_error_toggle_toolbar_entry( $wp_admin_bar ) {

    if( ! current_user_can( 'delete_plugins' ) ) return;

    $option = php_error_toggle_get_option();

    if( ( $option != 1 ) && ( $option != 0 ) ) $option = 1;

    $args = array(
        'id'    => 'php_errors_status_bar',
        'title' => '',
        'href'  => '?php-errors-toggle-set-new=' . $option,
        'meta'  => array( 
            'class' => 'php-error-toggle-container',
            'html'  => ''
            )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'php_error_toggle_toolbar_entry', 999 );

function php_error_toggle_print_css() {

    $php_error_toggle = php_error_toggle_get_option();

    if( $php_error_toggle == 1 ) {

        echo '<style type="text/css">';
        echo '
        .php-error-toggle-container {
            background-color: green !important;
        }
        ';
        echo '</style>';

        echo '<script type="text/javascript">';
        echo 'jQuery( document ).ready( function() {
            jQuery( "#wp-admin-bar-php_errors_status_bar a" ).text( "PHP errors is ON" );
        });';
        echo '</script>';

    } else {

        echo '<style type="text/css">';
        echo '
        .php-error-toggle-container {
            background-color: red !important;
        }
        ';
        echo '</style>';
        echo '<script type="text/javascript">';
        echo 'jQuery( document ).ready( function() {
            jQuery( "#wp-admin-bar-php_errors_status_bar a" ).text( "PHP errors is OFF" );
        });';
        echo '</script>';
    }

}
add_action( 'admin_bar_menu', 'php_error_toggle_print_css' );

function php_error_toggle_get_option() {

    $option = get_option( 'php_error_toggle' );

    return $option;

}
add_action( 'admin_head', 'php_error_toggle_print_css' );