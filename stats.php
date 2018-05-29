<?php

add_action('admin_menu', 'stats_menu');


function stats_menu() {

	$hook = "";

	if ( current_user_can('administrator') )
	$hook = add_submenu_page(
		'book_management',
		'Statistics',
		'Statistics',
		'administrator',
		'statistics',
		'stats_settings_page');

	elseif ( is_agent( get_current_user_id()) == 'true' ){
	$hook = add_submenu_page(
		'book_management',
		'Statistics',
		'Statistics',
		'editor',
		'statistics',
		'stats_settings_page');
	}

	add_action( "load-$hook", 'screen_option1'  );

	/*
	 * Screen options
	 */
	function screen_option1() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Sheets',
			'default' => 20,
			'option'  => 'statistics_sheets_per_page'
		];

		add_screen_option( $option, $args );
	}

	add_filter('set-screen-option', 'stats_screen_set_option', 10, 3);
 
	function stat_screen_set_option($status, $option, $value) {
	    if ( 'statistics_sheets_per_page' == $option ) return $value;
	    return $status;
	}

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'statistics',
	'title'		=> __('Display Statistics and Interactions'),
	'content'	=>
		'<p>' . __('Review the statistics for the users assigned to you as an agent.') . '</p>'
	) );

}

function stats_settings_page(){
	?>
	<H1>Reloia</H1>
	<h2>Book Statistics</h2>
	<?php
}
