<?php
/*
Plugin Name: Hide Deep Pages
Plugin URI: http://stephanieleary.com
Description: Plugin to add Section mode to Edit Pages list, which hides child pages and lets you navigate one level at a time.
Author: Stephanie Leary
Version: 0.2
Author URI: http://stephanieleary.com
*/

/*
TODO:
-- Ajaxify

/**/

add_filter( 'posts_where' , 'hide_deep_pages_where' );

function hide_deep_pages_where( $where ) {

    if( is_admin() ) {

		if ( isset( $_REQUEST['child_of'] ) && !empty( $_REQUEST['child_of'] ) && intval( $_REQUEST['child_of'] ) != 0 )
            $where .= sprintf(" AND post_parent = %d", intval( $_REQUEST['child_of'] ) );
        elseif ( isset( $_REQUEST['mode'] ) && !empty( $_REQUEST['mode'] ) && 'section' == $_REQUEST['mode'] )
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

// inelegantly, add the view switcher icons to the Edit Pages footer markup
add_action( 'admin_footer-edit.php', 'hide_deep_pages_view_switch' );

function hide_deep_pages_view_switch() {
    if ( isset( $_REQUEST['post_type'] ) && !empty( $_REQUEST['post_type'] ) && 'page' == $_REQUEST['post_type'] ) {
        if ( isset( $_REQUEST['mode'] ) && !empty( $_REQUEST['mode'] ) && 'section' == $_REQUEST['mode'] ) {
            $sectionclass = 'current';
            $listclass = '';
        }
        else {
            $listclass = 'current';
            $sectionclass = '';
        }
        $normal_mode_url = add_query_arg( array( 'post_type' => 'page' ), admin_url( 'edit.php' ) );
        $section_mode_url = add_query_arg( array( 'mode' => 'section' ), $normal_mode_url );
        printf( '<div class="view-switch" style="display: none;">
        <a class="%4$s" href="%1$s"><img width="20" height="20" alt="List View" title="List View" src="%3$s"
         id="view-switch-list"></a>
        <a class="%5$s" href="%2$s"><img width="20" height="20" alt="Section View" title="Section View" src="%3$s"
         id="view-switch-excerpt"></a>
        </div>', $normal_mode_url, $section_mode_url, includes_url( '/images/blank.gif' ), $listclass, $sectionclass );
    }
}

// move the view switcher icons from the footer to the list table, and remove display: none
add_action( 'admin_head-edit.php', 'hide_deep_pages_switch_js' );

function hide_deep_pages_switch_js() {
    echo '<script>
        jQuery(document).ready(function(){
	        var vs = jQuery(".view-switch");
            vs.insertAfter( jQuery(".tablenav.top .tablenav-pages") );
            vs.show();
        });
        </script>';
}