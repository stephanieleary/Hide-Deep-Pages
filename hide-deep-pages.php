<?php
/*
Plugin Name: Hide Deep Pages
Plugin URI: http://stephanieleary.com
Description: Plugin to hide child pages on Edit screens unless specifically requested.
Author: Stephanie Leary
Version: 0.1.1
Author URI: http://stephanieleary.com
*/

/*
TODO:
-- Ajaxify

/**/

add_filter( 'posts_where' , 'hide_deep_pages_where' );

function hide_deep_pages_where( $where ) {

    if ( is_admin() ) {
        if ( isset( $_REQUEST['child_of'] ) && !empty( $_REQUEST['child_of'] ) && intval( $_REQUEST['child_of'] ) != 0 )
            $where .= sprintf(" AND post_parent = %d", intval( $_REQUEST['child_of'] ) );
        else
            $where .= sprintf(" AND post_parent = %d", 0 );
    }
    return $where;
}

add_filter( 'page_row_actions', 'hide_deep_pages_action_links', 10, 2 );

function hide_deep_pages_action_links( $actions, $page ) {

    $child_pages = get_posts( array( 'post_status' => 'any', 'post_type' => 'page', 'post_parent' => $page->ID ) );
    if ( count( $child_pages ) > 0 ) {
        $action_url = add_query_arg( array( 'child_of' => $page->ID ) );
        $actions['show_child_pages'] = sprintf( '<a href="%s">%s</a>' , $action_url, __( 'Show Child Pages' ) );
    }
    
    return $actions;
}
