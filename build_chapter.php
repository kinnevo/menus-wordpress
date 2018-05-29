<?php

// Build Chapter Management

add_action('admin_menu', 'build_chapter_list_menu');

function build_chapter_list_menu(){

	$hook = add_submenu_page(
		'book_management',
		'Chapters Management', 
		'All Chapters', 
		'administrator',
		'chapter_mgmt_list',
		'chapter_mgmt_list');

	add_action( "load-$hook", 'chapter_screen_option' );

	/**
	 * Screen options
	 */
	function chapter_screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Chapters',
			'default' => 10,
			'option'  => 'chapters_per_page'
		];
		add_screen_option( $option, $args );
	}

	add_filter('set-screen-option', 'chapter_screen_set_option', 10, 3);
 
	function chapter_screen_set_option($status, $option, $value) {
	    if ( 'chapters_per_page' == $option ) return $value;
	    return $status;
	}

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'list_chapters',
	'title'		=> __('List Chapters available'),
	'content'	=>
		'<p>' . __('List all the chapters available.') . '</p>'
	) );

}


class NW_Chapter_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => 'Book', 'sp', //singular name of the listed records
			'plural'   => 'Books', 'sp', //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
	}

	// customer WP_List_Table object
//	public $book_obj;

	public static function get_chapters($per_page = 10, $page_number = 1, $search = "" ) {
		global $wpdb;

		if ( $search == "")
			$sql = "SELECT * FROM {$wpdb->prefix}book WHERE book_type='chapter'";
		else
			$sql = "SELECT * FROM {$wpdb->prefix}book WHERE title LIKE '%{$search}%' AND book_type='chapter'";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
//		var_dump($sql);

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
//		var_dump($result);
		return $result;
	}

	/**
	 * Delete a book record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_chapter( $chapter_id ) {
		global $wpdb;

        // the book exist, need to be removed the old one and insert the new one
		$error_sql = $wpdb->query("DELETE FROM " . $wpdb->prefix . "book_content WHERE book_id = '$chapter_id'");
    	$error_sql = $wpdb->query( "DELETE FROM " . $wpdb->prefix . "book WHERE id = '$chapter_id'");
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}book  WHERE book_type='chapter'";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no books data is available */
	public function no_items() {
		echo 'No Chapters available.';
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		global $wpdb;

		switch ( $column_name ) {
			case 'id':
			case 'description':
				return $item[ $column_name ];

			case 'category':
					$category=$item[ $column_name ];
					$category_name = $wpdb->get_row(  "SELECT name from " . $wpdb->prefix . "terms WHERE term_id=$category", ARRAY_A );
				return $category_name['name'];

			case 'title':
				return '<a href ="/wp-admin/admin.php?page=chapter_mgmt1&id='. $item['id'] . "&category=". $item['category'] . ' ">' . $item[ $column_name ] . '</a>';
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
/*
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}
*/
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_id( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_chapter' );
		$title = '<strong>' . $item['id'] . '</strong>';
		$actions = [
		/*	'edit'   => sprintf( '<a href="/wp-admin/admin.php?page=new_chapter&action=edit&id=%d&_wpnonce=%s">Edit</a>',    absint( $item['id'] ), $delete_nonce ),*/
			'delete' => sprintf( '<a href="/wp-admin/admin.php?page=chapter_mgmt_list&action=delete&chapter_id=%d&_wpnonce=%s">Delete</a>',absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
//			'cb'      => '<input type="checkbox" />',
			'id' => 'id',
			'title' => 'Title',
			'description' => 'Description',
			'category' => 'Category'
		);

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', false ),
			'book_type' => array ( 'book_type', false)
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
/*	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}
*/

/*
	public function process_bulk_action() {
		die("to be programmed this function");
	}
*/
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items( $search = "") {

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable);

		$per_page     = $this->get_items_per_page( 'chapters_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_chapters( $per_page, $current_page, $search );
	}

	public function chapter_management_ext( $obj ) {
		global $wpdb;
		if ( isset($_REQUEST['action'])){
			switch ($_REQUEST['action']) {
				case 'delete':
						$this->delete_chapter( $_REQUEST['chapter_id']);
					break;
				
				default:
					# code...
					break;
			}
		}

//		if( false == isset( $_POST[ 's' ])  ?  $search = "" : $search = $_POST['s'])
		if( false == isset( $_POST[ 's' ]))
			$search = "";
		else
			$search = $_POST['s'];

		echo '<div class="wrap">';

		echo '<h1 class="wp-heading-inline">Manage Chapters</h1>';
		echo '<a href="/wp-admin/admin.php?page=chapter_mgmt" class="page-title-action">Add New</a>';
		echo '<div style="margin-right:300px;">';
		echo '<p>You can build as many prepackaged chapters as you like. Use this tool to create groups of pages you\'ll use regularly, like "Westside Shoreline Communities" or "Montessori Schools." You can then drag these prepackaged chapters into as many custom books as you like. Note that changes made to these prepackaged chapters will not be reflected in books you\'ve already built. The list below displays the prepackaged chapters currently available to you.</p>';
		echo '</div>';
		echo	'<div id="poststuff">';
		echo		'<div id="post-body" class="metabox-holder columns-2">';
		echo			'<div id="post-body-content">';
		echo				'<div class="meta-box-sortables ui-sortable">';
		echo					'<form method="post">';
		echo						'<input type="hidden" />';
								$obj->prepare_items( $search );
								$obj->search_box('Search chapters by title', 'title');
								$obj->display();
		echo					'</form>';
		echo				'</div>';
		echo			'</div>';
		echo		'</div>';
		echo		'<br class="clear">';
		echo	'</div>';
//		echo '</div>';
	}
}

function chapter_mgmt_list(){
	$chapter_obj = new NW_Chapter_List();
	$chapter_obj->chapter_management_ext( $chapter_obj );
}
