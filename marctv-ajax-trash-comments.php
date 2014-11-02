<?php

/*
  Plugin Name: MarcTV ajax trash comments
  Plugin URI: http://marctv.de/blog/marctv-wordpress-plugins/
  Description: Trash your comments in the frontend with one click.
  Version: 1.0.2
  Author: MarcDK
  Author URI: http://www.marctv.de
  License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  your option) any later version.

 */

function get_trash_comment_link( $comment_text ) {
	if ( current_user_can( 'moderate_comments' ) && is_single() ) {
		$comment_id = get_comment_ID();
		$nonce      = wp_create_nonce("delete-comment_$comment_id");
		$del_nonce  = esc_html( '_wpnonce=' . $nonce );
		$trash_url  = esc_url( "/wp-admin/comment.php?action=trashcomment&c=$comment_id&$del_nonce" );
		$trash_link = "<small><a data-nonce='$nonce' data-cid='$comment_id' class='marctv-trash-btn marctv-trash' href='$trash_url' title='" . esc_attr__( __( 'Move this comment to the trash', 'marctv-ajax-trash-comments' ) ) . "'>" . __( 'trash', 'marctv-ajax-trash-comments' ) . '</a></small>';

		return $comment_text . $trash_link;
	}
}

function add_marctv_ajax_comment_scripts() {
	wp_enqueue_style(
		"jquery.marctv_edc", WP_PLUGIN_URL . "/" . dirname( plugin_basename( __FILE__ ) ) . "/marctv-ajax-trash-comments.css", false, "1.0" );

	wp_enqueue_script(
		"jquery.marctv_edc", WP_PLUGIN_URL . "/" . dirname( plugin_basename( __FILE__ ) ) . "/jquery.marctv-ajax-trash-comments.js", array( "jquery" ), "1.0", 0 );

	$params = array(
		'adminurl' => admin_url( 'admin-ajax.php' ),
		'trash_string' => __( 'trash', 'marctv-ajax-trash-comments' ),
		'untrash_string' => __( 'untrash', 'marctv-ajax-trash-comments' ),
		'trashing_string' => __( 'trashing', 'marctv-ajax-trash-comments' ),
		'untrashing_string' => __( 'untrashing', 'marctv-ajax-trash-comments' ),
		'error_string' => __( 'error', 'marctv-ajax-trash-comments' )
	);

	wp_localize_script( 'jquery.marctv_edc', 'marctvedc', $params );
}

function marctv_trash_comment() {

	if ( current_user_can( 'moderate_comments' ) ) {
		/* retrieve comment id from post ajax request and sanitize to number */
		$comment_id = filter_input( INPUT_POST, 'cid', FILTER_SANITIZE_NUMBER_FLOAT );
		$comment_status = wp_get_comment_status( $comment_id );

		/* check if the ajax referrer is the trash link */
		check_ajax_referer("delete-comment_$comment_id");

		switch ( $comment_status ) {
			case 'approved':
				if( wp_trash_comment( $comment_id ) ) {
					wp_die( 'trashed' );
				}
				break;
			case 'trash':
				if( wp_untrash_comment( $comment_id ) ) {
					wp_die( 'untrashed' );
				}
				break;
			default:
				wp_die( 'error' );
		}
	}
}

function marctv_ajax_trash_comments_load_textdomain() {
	load_plugin_textdomain( 'marctv-ajax-trash-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_filter( 'comment_text', 'get_trash_comment_link', 99 );

add_action( 'wp_print_styles', 'add_marctv_ajax_comment_scripts' );

add_action( "wp_ajax_marctv_trash_comment", "marctv_trash_comment" );

add_action( 'plugins_loaded', 'marctv_ajax_trash_comments_load_textdomain' );
