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

/*
 *	Each Chapter have two elements -- its name and information and the list of elements included in the chapter
 *		the chapter names use the class "nw_chapter" and the elements are a set with class "nw_post" 
 *		the chapter is visible and the content of chapters are included 
 *		in a <div id="chapter_content">   .... each chapter has a <div id=chapter####><div ... al elements of the chapter >content</div>
 *		to hide the content it is used style="display: none;"
 *
 *	Chapters are formed in the AJAX function reloia_list_pre_chapters
 */



function reloia_list_pre_chapters(){
	global $wpdb;

   	if ( !wp_verify_nonce( $_REQUEST['nonce'], "reloia_list_pre_chapters_nonce")) {
    	exit("No naughty business please - reloia_list_pre_chapters_nonce ");
   	}

	$topic_listing = "";
	$chapter_listing = "";

    $topic = $_REQUEST['topic'];
	$list_of_chapters = $wpdb->get_results(
		"SELECT * FROM " . $wpdb->prefix . "book WHERE book_type='chapter' AND category = $topic");

    if ( sizeof($list_of_chapters) == 0){
    	$topic_listing .= "<h3 style='margin-left:30px;font-weight:bold;'>No prepackaged chapters for this subject have been created.</h3>";
	} else {
		// $chapter_listing .= '<div id="chapter_listings" style="display: none;">';
		for ( $i = 0 ; $i < sizeof($list_of_chapters) ; $i++){

	    // list the content of the chapter
	    	$b_id = $list_of_chapters[$i]->id;
	    	$content_chapter = $wpdb->get_results(
		"SELECT * FROM " . $wpdb->prefix . "book_content WHERE book_id = $b_id");

	    	$chapter_listing .= "<div id='chapter_content" . $list_of_chapters[$i]->id. "' class='chapter_content_listing'>"; 
	    	for ( $j = 0 ; $j < sizeof($content_chapter) ; $j++) {
	    		$chapter_listing .= "<div id='" . $content_chapter[$j]->page_id .
	    							"' class=nw_post" .
			    			        "  drag-parent='" . $i . 
			    			        "' drag-menu-order='" . ($j+1) .
			    			        "' drag-category='" . $content_chapter[$j]->category_id .
				    			    "' drag-type='post'>" .
			    			        substr( get_post($content_chapter[$j]->page_id)->post_title,0 , 80) . "</div>";
	    	}
	    	$chapter_listing .= "</div>";

	    	$topic_listing	.=  "<div id='" . $list_of_chapters[$i]->id . 
		    				  "' class=nw_chapter" . 
	    			          "  drag-parent='" . "1" . 
	    			          "' drag-menu-order='" . ($i+1) .
	    			          "' drag-category='" . $topic .
		    			      "' drag-type='" . $i .  // addslashes($chapter_listing) .
	    			          "'>" .
	    			          substr( $list_of_chapters[$i]->title,0 , 80) . "</div>";

      	}
      	//$chapter_listing .= '</div>';

	}
	$result['topics'] = $topic_listing;
	$result['chapter_content'] = $chapter_listing;
	$result['status'] = "success";

   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }

	wp_die();
}

function my_ajax_reloia_list_pre_chapters(){
   echo "You must log in to list chapters";
   die();
}

add_action( "wp_ajax_reloia_list_pre_chapters", "reloia_list_pre_chapters", 3, 5 );
add_action( "wp_ajax_nopriv_reloia_list_pre_chapters", "my_ajax_reloia_list_pre_chapters", 3, 5 );

add_action( 'init', 'reloia_scripts_2' );

function reloia_scripts_2() {
  wp_enqueue_script(  'reloia_list_pre_chapters', CPTURL1 . '/js/list_chapters.js', array( 'jquery' ), '1', true );
  wp_localize_script( 'reloia_list_pre_chapters', 'reloia_list_pre_chapters', 
  						array(
	                      'ajaxurl'   => admin_url( 'admin-ajax.php' ),
	                      'action' => "reloia_list_pre_chapters",
	                      'reloia_list_pre_chapters_nonce' => wp_create_nonce( 'reloia_list_pre_chapters_nonce' )
                      	) );
}


class NW_Book_List extends WP_List_Table {

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

	public static function get_books($per_page = 5, $page_number = 1, $search = "" ) {
		global $wpdb;

		if ( $search == "")
			$sql = "SELECT * FROM {$wpdb->prefix}book WHERE book_type='book'";
		else
			$sql = "SELECT * FROM {$wpdb->prefix}book WHERE book_type='book' AND title LIKE '%{$search}%'";

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
	public static function delete_book( $book_id ) {
		global $wpdb;

         // the book exist, need to be removed the old one and insert the new one
        $error_sql = $wpdb->query("DELETE FROM " . $wpdb->prefix . "book_content WHERE book_id = '$book_id'");
    	$error_sql = $wpdb->query( "DELETE FROM " . $wpdb->prefix . "book WHERE id = '$book_id'");
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}book ";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no books data is available */
	public function no_items() {
		echo 'No Books avaliable.';
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
		switch ( $column_name ) {
			case 'id':
			case 'description':
			case 'book_type':
				return $item[ $column_name ];

			case 'title':
				return '<a href ="/wp-admin/admin.php?page=build_new_book&book_id='. $item['id'] . ' ">' . $item[ $column_name ] . '</a>';
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

		$delete_nonce = wp_create_nonce( 'sp_delete_book' );
		$title = '<strong>' . $item['id'] . '</strong>';
		$actions = [
			//'edit'   => sprintf( '<a href="/wp-admin/admin.php?page=new_book&action=edit&id=%d&_wpnonce=%s">Edit</a>',    absint( $item['id'] ), $delete_nonce ),
			'delete' => sprintf( '<a href="/wp-admin/admin.php?page=book_management&action=delete&book_id=%d&_wpnonce=%s">Delete</a>',absint( $item['id'] ), $delete_nonce )
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
			//'book_type' => 'Book Type'
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

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items( $search = "") {

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable);

		$per_page     = $this->get_items_per_page( 'books_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_books( $per_page, $current_page, $search );
	}

	function column_book_type( $item){
		if ( isset($_REQUEST['paged'])) 
			$paged = "&paged=" . $_REQUEST['paged'];
		else
			$paged = "";

		$actions = array (
			'book' => sprintf('<a href="/wp-admin/admin.php?page=book_management&book_type=book&id=%d%s">book</a>',  $item['id'], $paged),
			//'global'     => sprintf('<a href="/wp-admin/admin.php?page=book_management&book_type=global&id=%d%s">global</a>',     $item['id'], $paged),
			//'draft'      => sprintf('<a href="/wp-admin/admin.php?page=book_management&book_type=draft&id=%d%s">draft</a>',        $item['id'], $paged)
			);
		return sprintf('%1$s %2$s', $item['book_type'], $this->row_actions($actions));
	}

	public function book_management_ext( $obj ) {
		if ( isset($_REQUEST['action'])){
			switch ($_REQUEST['action']) {
				case 'delete':
						$this->delete_book( $_REQUEST['book_id']);
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

		echo '<h1 class="wp-heading-inline">WP Bookbuilder</h1>';
		
		echo '<h3>WP Bookbuilder is a system that allows you to created curated lists of your content, specifically tailored for and assigned to your users.</h3>';
		echo '<p>In brief, here are the steps to follow in order to add a new client to the system. Each screen has more detailed instructions to follow.</p>';
		echo '<ol><li>First, set your client up with an login. This is done via the <a href="/wp-admin/user-new.php">Add New User</a> screen.</li>';
		echo '<li>You can use the New Chapter screen to prepackage chapters, saving them for use in books you build for future clients.</li>';
		echo '<li>You\'ll use the New Book screen to create a collection of content tailored for your client\'s needs. This can include individual pages mixed with prepackaged chapters.</li>';
		echo '<li>Once you\'ve created your book, you\'ll assign it to your client. Head over to the Manage Users screen to do this, and to assign them to their PM as well.</li>';
		echo '<li>You can track your client\'s usage of the system and their engagement with the content on the Statistics screen.</li>';
		echo '<li>Messages sent to PMs by their clients are accessible on the Conversations screen</li>';
		echo '</ol>';
		echo '<p><b>Shortcodes</b>:</p>';
		echo '<ul><li>[ns_navigator] – adds previous post, next post, and home buttons</li><li>[ns_interest_manager] – add thumbs up, thumbs down, and chat buttons</li><li>[ns_custom_navigation] – displays curated content for current user</li><li>[ns_toc] – allows display of multiple books for current user. Keyed by book ID. (feature in development)</li></ul>';
		
		echo '<h2>Manage your Books</h2>';
		echo '<a href="/wp-admin/admin.php?page=build_new_book"><button class="xbk5-button">Create a New Book</button></a>';

		echo	'<div id="poststuff">';
		echo		'<div id="post-body" class="metabox-holder">';
		echo			'<div id="post-body-content">';
		echo				'<div class="meta-box-sortables ui-sortable">';
		echo					'<form method="post">';
		echo						'<input type="hidden" />';
								$obj->prepare_items( $search );
								$obj->search_box('Search books by title', 'title');
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


function book_management(){
	$book_obj = new NW_Book_List();
	$book_obj->book_management_ext( $book_obj );
}

