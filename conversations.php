<?php

add_action('admin_menu', 'conversations_menu');

function conversations_menu() {
	$hook = "";

	if ( current_user_can('administrator') ) {
		$hook = add_submenu_page(
			'book_management',
			'Conversations', 
			'Conversations', 
			'administrator', 
	 		'conversations', 
			'conversations');
	} elseif ( is_agent( get_current_user_id()) == 'true' ){
		$hook = add_submenu_page(
			'book_management',
			'Conversations', 
			'Conversations', 
			'editor', 
	 		'conversations', 
			'conversations');
	}
	add_action( "load-$hook", 'conversations_screen_option' );

	function conversations_screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Conversations',
			'default' => 20,
			'option'  => 'conversations_per_page'
		];

		add_screen_option( $option, $args );
	}

	add_filter('set-screen-option', 'conversation_screen_set_option', 10, 3);
 
	function conversation_screen_set_option($status, $option, $value) {
	    if ( 'conversations_per_page' == $option ) return $value;
	    return $status;
	}

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'conversations',
	'title'		=> __('Manage Conversations'),
	'content'	=>
		'<p>' . __('Read, Review and provide feedback to your assigned users.') . '</p>'
	) );


}

function conversations(){
	?>
	<H1>Conversations</H1>
	<?php
}