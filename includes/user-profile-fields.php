<?php


	/**
	 * Add fields to the user profile
	 * @param  object $user The user
	 */
	function photoboard_user_groups_add_fields( $user ) {

		$user_groups = get_terms( 'photoboard_user_groups', array( 'hide_empty' => false) );
		$the_group = get_the_author_meta( 'photoboard_user_group', $user->ID );

		?>

		<h3><?php _e( 'User Groups', 'photoboard_user_groups' ); ?></h3>

		<?php if ( empty( $user_groups ) ) : ?>
			<p><?php _e( 'No user groups have been setup yet.', 'photoboard_user_groups' ); ?></p>
		<?php else : ?>
			<table class="form-table">

				<tr>
					<th>Group</th>

					<td>
						<label>
							<input type="radio" name="photoboard_user_group" id="photoboard-user-group-none" value="none" <?php checked( $the_group, 'none' ); ?> <?php checked( $the_group, '' ); ?>>
							<?php _e( 'None', 'photoboard_user_groups' ); ?>
						</label>
						<br><br>
						<?php foreach( $user_groups as $key => $group ) : ?>
							<label>
								<input type="radio" name="photoboard_user_group" id="photoboard-user-group-<?php echo esc_attr( $group->term_id ); ?>" value="<?php echo esc_attr( $group->term_id ); ?>" <?php checked( $the_group, $group->term_id ); ?>>
								<?php echo esc_html( $group->name ); ?>
							</label>
							<br><br>
						<?php endforeach; ?>
					</td>
				</tr>

			</table>
		<?php endif; ?>


		<?php
	}
	add_action( 'show_user_profile', 'photoboard_user_groups_add_fields' );
	add_action( 'edit_user_profile', 'photoboard_user_groups_add_fields' );



	/**
	 * Save custom fields on update
	 * @param  integer $user_id The user ID
	 */
	function photoboard_user_groups_save_fields( $user_id ) {

		// Security check
		if ( !current_user_can( 'edit_user', $user_id ) ) return false;

		// Update user group
		if ( !isset( $_POST['photoboard_user_group'] ) ) return;
		update_usermeta( $user_id, 'photoboard_user_group', $_POST['photoboard_user_group'] );

	}
	add_action( 'personal_options_update', 'photoboard_user_groups_save_fields' );
	add_action( 'edit_user_profile_update', 'photoboard_user_groups_save_fields' );