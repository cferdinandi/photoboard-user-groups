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



	/**
	 * Add checkboxes to assign photo visibility by group
	 * @param array $form_fields Form fields settings
	 * @param object $post The attachment
	 * @return object The update form fields
	 */
	function photoboard_restrict_photo_access_add_fields($form_fields, $post) {

		// Get groups and visibility settings
		$access = get_post_meta( $post->ID, 'photoboard_media_user_groups', true );
		$groups = get_terms( 'photoboard_user_groups' );
		$html = '';

		// Create a checkbox for each group
		foreach ($groups as $group) {
			$checked = $access[$group->term_id] === 'on' ? 'checked' : '';
			$html .=
				'<label class="settingx">' .
					'<input type="checkbox" name="attachments[' . $post->ID . '][' . $group->term_id . ']" value="1" style="float: left;" ' . $checked . '>' .
					' <span class="name">' . $group->name . '</span>' .
				'</label><br><br>';
		}

		$form_fields['restrict_access'] = array(
			'label' => __('Restrict Access', 'photoboard'),
			'input' => 'html',
			'html'  => $html,
			// 'helps' => __('Control photo visibility', 'photoboard'),
		);

		return $form_fields;

	}
	add_filter('attachment_fields_to_edit', 'photoboard_restrict_photo_access_add_fields', null, 2);



	/**
	 * Save photo visibility by group
	 * @param object $post The attachment
	 * @param object $attachment The attachment ID
	 * @return object The post
	 */
	function photoboard_restrict_photo_access_save_fields($post, $attachment) {

		// Get groups and visibility settings
		$access = Array();
		$groups = get_terms( 'photoboard_user_groups' );

		// For each checkbox, set visibility
		foreach ($groups as $group) {
			$group_id = $group->term_id;
			$group_name = $group->name;
			if ( isset( $attachment[$group->term_id] ) ) {
				$access[$group->term_id] = 'on';
			}
			// if ( isset( $attachment[$group->term_id] ) ) {
			// 	$access[$group->term_id] = 'on';
			// } else {
			// 	$access[$group->term_id] = 'off';
			// }
		}

		// Save changes to database
		update_post_meta($post['ID'], 'photoboard_media_user_groups', $access);
		return $post;

	}
	add_filter('attachment_fields_to_save', 'photoboard_restrict_photo_access_save_fields', null , 2);