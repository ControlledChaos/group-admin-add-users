<?php

class Group_Admin_Add_Users_Extension extends BP_Group_Extension {

	public function __construct() {

		$this->enable_nav_item = false;

		$args = array(
			'screens'   => array(
				'create'  => array(
					'enabled'   => false
				),
				'edit'    => array(
					'slug'                  => 'add-user',
					'name'                  => __('Add User'),
					'position'              => 55,
					'screen_callback'       => array( $this, 'render_form' ),
					'screen_save_callback'  => array( $this, 'save_user' ),
					'submit_text'           => __( 'Add New User', 'group-admin-add-users' )
				),
				'admin'   => array(
					'enabled'   => false,
				),

			)
		);

		parent::init( $args );
	}

	public function render_form( $group_id = null ) {

		if ( ! $group_id || ! is_user_logged_in() || ! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) ) {
			return '';
		}

		?>

		<input name="group-admin-createuser" type="hidden" value="group-admin-createuser" />
		<?php wp_nonce_field( 'group-admin-createuser', '_wpnonce_group-admin-createuser' ); ?>

		<table class="form-table">

			<tr class="form-field">
				<th scope="row">
					<label for="user_login">
						<?php _e( 'Username', 'group-admin-add-users' ); ?><span class="description"><?php _e('(required)'); ?></span>
					</label>
				</th>
				<td>
					<input name="user_login" type="text" id="user_login" value="" aria-required="true" autocapitalize="none" autocomplete="off" autocorrect="off" maxlength="60" />
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row">
					<label for="email">
						<?php _e( 'Email', 'group-admin-add-users' ); ?><span class="description"><?php _e('(required)'); ?></span>
					</label>
				</th>

				<td>
					<input name="email" type="email" id="email" value="" autocomplete="off" />
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row">
					<label for="password">
						<?php _e( 'Password', 'group-admin-add-users' ); ?> <span class="description"><?php _e('(required)'); ?></span>
					</label>
				</th>

				<td>
					<input name="password" type="text" id="password" value="" autocomplete="off" />
				</td>
			</tr>

		</table>

		<?php
	}

	public function save_user( $group_id ) {

		if( ! $group_id || ! isset( $_POST['group-admin-createuser'] ) ) {
			return;
		}

		$user_id = bp_loggedin_user_id();

		if( ! $user_id || ! groups_is_user_admin( $user_id, $group_id ) ) {
			return;
		}

		if( ! wp_verify_nonce( $_POST['_wpnonce_group-admin-createuser'], 'group-admin-createuser' ) ) {

			bp_core_add_message( 'Invalid action', 'error' );

			return ;
		}

		$error = new WP_Error();

		$user_login = sanitize_user( $_POST['user_login'], true );
		$user_email = sanitize_email( $_POST['email'], true );
		$password   = sanitize_text_field( $_POST['password'] );

		if ( isset( $_POST['user_login'] ) && ! validate_username( $user_login ) )
			$error->add( 'user_login', __( 'This username is invalid because it uses illegal characters. Please enter a valid username.' ));

		if ( username_exists( $user_login ) )
			$error->add( 'user_login', __( 'This username is already registered. Please choose another one.' ));

		/** This filter is documented in wp-includes/user.php */
		$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

		if ( in_array( strtolower( $user_login ), array_map( 'strtolower', $illegal_logins ) ) ) {
			$error->add( 'invalid_username', __( '<strong>ERROR</strong>: Sorry, that username is not allowed.' ) );
		}

		/* checking email address */
		if ( empty( $user_email ) ) {
			$error->add( 'empty_email', __( 'Please enter an email address.' ), array( 'form-field' => 'email' ) );
		} elseif ( !is_email( $user_email ) ) {
			$error->add( 'invalid_email', __( 'The email address isn&#8217;t correct.' ), array( 'form-field' => 'email' ) );
		} elseif ( $owner_id = email_exists($user_email) ) {
			$error->add( 'email_exists', __('This email is already registered, please choose another one.'), array( 'form-field' => 'email' ) );
		}

		if ( empty( $password ) ) {
			$error->add( 'empty_pass', __( 'Please enter an password.' ), array( 'form-field' => 'password' ) );
		}

		if ( $error->get_error_codes() ) {

			bp_core_add_message( $error->get_error_message(), 'error' );

			return;
		}

		$user = wp_create_user( $user_login, $password, $user_email );

		if ( $user && ! is_wp_error( $user ) ) {

			groups_join_group( $group_id, $user );
			bp_core_add_message( __('User Created and added to the group'), 'success' );

			return;

		} else {
			return bp_core_add_message( __( 'Got some error' ), 'error' );
		}

	}

}

bp_register_group_extension( 'Group_Admin_Add_Users_Extension' );