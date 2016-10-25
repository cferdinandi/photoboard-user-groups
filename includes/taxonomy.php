<?php

	/**
	 * Create user group taxonomy
	 */
	function photoboard_user_groups_create_taxonomy() {

		// Add new taxonomy, NOT hierarchical (like tags)
		$labels = array(
			'name'                       => _x( 'User Groups', 'taxonomy general name', 'photoboard_user_groups' ),
			'singular_name'              => _x( 'User Group', 'taxonomy singular name', 'photoboard_user_groups' ),
			'search_items'               => __( 'Search Groups', 'photoboard_user_groups' ),
			'popular_items'              => __( 'Popular Groups', 'photoboard_user_groups' ),
			'all_items'                  => __( 'All Groups', 'photoboard_user_groups' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Group', 'photoboard_user_groups' ),
			'update_item'                => __( 'Update Group', 'photoboard_user_groups' ),
			'add_new_item'               => __( 'Add New Group', 'photoboard_user_groups' ),
			'new_item_name'              => __( 'New Group Name', 'photoboard_user_groups' ),
			'separate_items_with_commas' => __( 'Separate groups with commas', 'photoboard_user_groups' ),
			'add_or_remove_items'        => __( 'Add or remove groups', 'photoboard_user_groups' ),
			'choose_from_most_used'      => __( 'Choose from the most used groups', 'photoboard_user_groups' ),
			'not_found'                  => __( 'No groups found.', 'photoboard_user_groups' ),
			'menu_name'                  => __( 'User Groups', 'photoboard_user_groups' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'public'                => false,
			// 'rewrite'               => array( 'slug' => 'group' ),
			'rewrite'               => false,
		);

		register_taxonomy( 'photoboard_user_groups', 'post', $args );
	}
	add_action( 'init', 'photoboard_user_groups_create_taxonomy', 0 );



	function mfields_set_default_object_terms( $post_id, $post ) {
		if ( 'publish' === $post->post_status ) {
			$defaults = array(
				'poc' => array( 'email-form' ),
			);
			$taxonomies = get_object_taxonomies( $post->post_type );
			foreach ( (array) $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $post_id, $taxonomy );
				if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
					wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
				}
			}
		}
	}
	add_action( 'save_post', 'mfields_set_default_object_terms', 100, 2 );