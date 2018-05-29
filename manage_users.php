<?php

/*
 *	Assign Book to user and manage the extended user information
 */

add_action('admin_menu', 'manage_users_menu');

function manage_users_menu() {
	$hook = add_submenu_page(
		'book_management',
		'Manage Users', 
		'Manage Users', 
		'administrator',
		'manage_users',
		'manage_users');

	add_action( "load-$hook", 'user_management_assign_screen' );

	/**
	 * Screen options
	 */
	function user_management_assign_screen() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Users',
			'default' => 10,
			'option'  => 'users_per_page'
		];
		add_screen_option( $option, $args );
	}

	add_filter('set-screen-option', 'user_management_screen_set_option', 10, 3);
 
	function user_management_screen_set_option($status, $option, $value) {
	    if ( 'users_per_page' == $option ) return $value;
	    return $status;
	}

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'manage_users',
	'title'		=> __('Assign Books to Users'),
	'content'	=>
		'<p>' . __('Assign Books to Users to be helped by the agents.') . '</p>'
	) );

}


function manage_users(){
	?>
	<H1>Reloia</H1>
	<h2>Manage User Access</h2>
	<?php

}