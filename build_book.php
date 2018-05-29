<?php
	/*
	 *	build a book
	*/

add_action('admin_menu', 'build_book_menu');
 
function build_book_menu(){
    $hook = add_menu_page( 
    	'Book Management', 
    	'WP BookBuilder', 
    	'manage_options', 
    	'book_management', 
    	'book_management', 
    	'dashicons-book-alt' );

    	add_submenu_page(
		'book_management',
		'All Books', 
		'All Books', 
		'administrator',
		'book_management',
		'book_management');

	add_action( "load-$hook", 'screen_option' );

	/**
	 * Screen options
	 */
	function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Books',
			'default' => 10,
			'option'  => 'books_per_page'
		];
		add_screen_option( $option, $args );
	}

	add_filter('set-screen-option', 'screen_set_option', 10, 3);
 
	function screen_set_option($status, $option, $value) {
	    if ( 'book_per_page' == $option ) return $value;
	    return $status;
	}

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'overview',
	'title'		=> __('Overview'),
	'content'	=>
		'<p>' . __('This screen provides access to all of your posts. You can customize the display of this screen to suit your workflow.') . '</p>'
	) );
}



function book_management(){
	?>
	<h1>Build Book</h1>
	<?php
}

