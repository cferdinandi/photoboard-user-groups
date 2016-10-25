<?php

	/**
	 * Redirect users who don't have access to a post
	 */
	function photoboard_user_groups_redirect() {

		global $post;

		// Only run for logged-in users on individual pages
		if ( !is_single() || !is_user_logged_in() ) return;

		// Ignore for admins
		if ( current_user_can( 'edit_themes' ) ) return;

		// Get user groups for this album
		$terms = get_the_terms( $post, 'photoboard_user_groups' );
		if ( empty( $terms ) ) return;

		// Get the current user's group
		$current_user = wp_get_current_user();
		$user_group = get_user_meta( $current_user->ID, 'photoboard_user_group', true );

		// If user is in a group, see if they have access
		if ( !empty( $user_group ) && $user_group !== 'none' ) {
			$the_group = intval( $user_group );
			foreach( $terms as $key => $term ) {
				if ( intval( $term->term_id ) === $the_group ) return;
			}
		}

		// Redirect
		wp_safe_redirect( site_url(), 302 );
		exit;

	}
	add_action( 'wp', 'photoboard_user_groups_redirect' );



	/**
	 * Hide posts from users who don't have access
	 * @param  object $query The WordPress query
	 */
	function photoboard_filter_query_by_user_group( $query ) {

		// Don't run on admin, for admins, or for logged out users
		if ( is_admin() || !$query->is_main_query() || !is_user_logged_in() || current_user_can( 'edit_themes' ) ) return;

		// Get current user group
		$current_user = wp_get_current_user();
		$user_group = get_user_meta( $current_user->ID, 'photoboard_user_group', true );
		$user_groups = get_terms( 'photoboard_user_groups', array( 'hide_empty' => false) );
		$groups = array();

		// Create an array of other user groups
		foreach ($user_groups as $key => $group) {
			if ( $group->term_id === $user_group ) continue;
			$groups[] = $group->term_id;
		}

		// Get original taxonomy query
		$tax_query = $query->get('tax_query');

		// Modify the taxonomy query
		$tax_query[] = array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'photoboard_user_groups',
				'field' => 'id',
				'terms' => $user_group,
				'operator'=> 'IN'
			),
			array(
				'taxonomy' => 'photoboard_user_groups',
				'field' => 'id',
				'terms' => $groups,
				'operator'=> 'NOT IN'
			)
		);

		// Update query with new filters
		$query->set( 'tax_query', $tax_query );

	}
	add_action( 'pre_get_posts', 'photoboard_filter_query_by_user_group' );