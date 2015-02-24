<?php
/*
Plugin Name: Instructions Menu Debug Lists
Description:This plugin lists wiki pages that are not in the sidebar Instructions menu, or which are duplicates or which are trashed.
Author: Hamish Willee
Version: 0.1
*/


/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function add_menu_dashboard_widget_function() {

	wp_add_dashboard_widget(
                 'menu_debug_widget',         // Widget slug.
                 'Instructions Menu - Debug',         // Title.
                 'menu_dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'add_menu_dashboard_widget_function' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function menu_dashboard_widget_function() {

        $wiki_menu_items_all = array();
        $wiki_items_all = array();
        $wiki_menu_items_duplicates = array();

	// Get all wiki items in menu 'Instructions'
	$menu = wp_get_nav_menu_object( 'Instructions' );
	$menu_items = wp_get_nav_menu_items($menu->term_id);

	foreach ( (array) $menu_items as $key => $menu_item ) {
	    $title = $menu_item->title;
	    $url = $menu_item->url;
            $object= $menu_item->object;
            $urlArray=explode("/",$url);
            $theSlug= $urlArray[sizeof($urlArray) - 2];

	    if ('wiki' === $object ) {

                if (array_key_exists($theSlug, $wiki_menu_items_all)) {
                    $combined_duplicate_title=$wiki_menu_items_all[$theSlug]['title'].' XDUP:'.$title;
                    $wiki_menu_items_duplicates[$theSlug] = array('url' => $url,'title' => $combined_duplicate_title);
                }
	        $wiki_menu_items_all[ $theSlug] = array('url' => $url,'title' => $title);
            }

	}
        

        // Get all published wiki items
        // http://codex.wordpress.org/Class_Reference/WP_Post
        // http://codex.wordpress.org/Function_Reference/get_posts

        $args = array(
	'posts_per_page'   => -1,
	'post_type'        => 'wiki',
	'suppress_filters' => true 
        );
        $posts_array = get_posts( $args ); 
	foreach ( (array) $posts_array as $key => $post ) {
	    $title = $post ->post_title;
            $theSlug = $post ->post_name;
	    $wiki_items_all[ $theSlug] = array('title' => $title);
	}


        // Get all trashed wiki items
        $args = array(
	'posts_per_page'   => -1,
        'post_status'      => 'trash',
	'post_type'        => 'wiki',
	'suppress_filters' => true 
        );
        $trashed_posts_array = get_posts( $args ); 
	foreach ( (array) $trashed_posts_array as $key => $post ) {
	    $title = $post ->post_title;
            $theSlug = $post ->post_name;
	    $wiki_items_in_trash[ $theSlug] = array('title' => $title);
	}

       echo '<strong>Index</strong>
             <ul><li><a href="#menuomissions">Menu Omissions </a></li>
                 <li><a href="#duplicatemenuitems">Menu Duplicates </a></li>
                 <li><a href="#menutrash">Menu Items in Trash</a></li>
             </ul>';

        //List published items in the wiki that are not in the Instructions menu
        $items_not_in_menu = array_diff_key($wiki_items_all,$wiki_menu_items_all );


        echo '<div id="menuomissions"></div>';
        echo '<strong>Instructions Menu Omissions ('.count($items_not_in_menu).') </strong>';

        
	$menu_list = '<ul>';

	foreach ( (array) $items_not_in_menu as $key => $menu_item ) {
	    $menu_list .= '<li><a href="'.site_url($key) . '">' . $menu_item['title'] . '</a></li>';
	}
	$menu_list .= '</ul>';
        echo $menu_list;


        echo '<div id="duplicatemenuitems"></div>';
        echo '<strong>Instructions Menu Duplicates ('.count($wiki_menu_items_duplicates).') </strong>';
	$menu_list = '<ul>';
	foreach ( (array) $wiki_menu_items_duplicates as $key => $menu_item ) {
	    $menu_list .= '<li><a href="' .site_url($key) . '">' . $menu_item['title'] . '</a></li>';
	}
	$menu_list .= '</ul>';
        echo $menu_list;


	
        echo '<div id="menutrash"></div>';
        $menu_items_in_trash = array_intersect($wiki_items_in_trash, $wiki_menu_items_all );
        //$menu_items_in_trash = array_intersect($wiki_menu_items_all , $wiki_items_in_trash);
        echo '<strong>Instructions Menu in Trash ('.count($menu_items_in_trash).') </strong>';

	$menu_list = '<ul>';
	foreach ( (array) $menu_items_in_trash as $key => $menu_item ) {
	    $menu_list .= '<li>' . $menu_item['title'] . '</li>';
	}
	$menu_list .= '</ul>';
        echo $menu_list;


}



?>