<?php

add_action('admin_menu', 'build_new_book_menu');
 
function build_new_book_menu(){

	$hook = add_submenu_page(
		'book_management',
		'New Book', 
		'New Book', 
		'administrator',
		'build_new_book',
		'build_new_book');

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'build_new_book',
	'title'		=> __('Build New Book'),
	'content'	=>
		'<p>' . __('Create a new book.') . '</p>'
	) );

}


function build_new_book(){
	?>
	<H1>Reloia</H1>
	<h2>New Book</h2>
	<?php
}


?>