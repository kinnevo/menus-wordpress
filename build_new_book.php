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



function reloia_scripts_books() {
  wp_enqueue_script(  'reloia_list_posts_books', CPTURL1 . '/js/reloia_select_topics.js', array( 'jquery' ), '1', true );
  wp_localize_script( 'reloia_list_posts_books', 'reloia_list_posts_books', array(
                      'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                      'action' => "reloia_list_posts_books",
                      'reloia_list_posts_books_nonce' => wp_create_nonce( 'reloia_list_posts_books_nonce' )
    )
  );
}
add_action( "wp_ajax_reloia_list_posts_books", "reloia_list_posts_books", 3, 5 );
add_action( "wp_ajax_nopriv_reloia_list_posts_books", "my_ajax_reloia_list_posts_books", 3, 5 );

add_action( 'init', 'reloia_scripts_books' );


class NW_Dragable1 {

	// class instance
	static $instance;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * Create a customized booklet by draging information from the library to a customized document
	 */
	public function dragable() {
		global $wpdb;

		function right_event( $parent, $id, $row ) {
			global $wpdb;

/* 
 *	I need user_id, book_id and version
 *	we need to keep the current value of the pages in the book
 */

	      	$current_book_content = $wpdb->get_results(  "SELECT * from " . $wpdb->prefix . "book_content WHERE book_id=$id AND category_id=$row", ARRAY_A );

			// create a dummy empty slot for the temporally draggable area
			if ( $row == 1 ){
			    echo ( "<div id='right-events' class='xbk5_container-dd dktest'>" );
			      	echo ( "<div>Empty 1</div>");
			      	echo ( "<div>Empty 2</div>");
			    echo ( "</div>");
			}

			echo ( '<div id="right-events' . $row . '" style="display: none;" class="right-content">');
	      	for ( $i = 0 ; $i < sizeof($current_book_content) ; $i++){

	      		$post_id = $current_book_content[$i]['page_id'];
				$current_post_content = $wpdb->get_row(  "SELECT * from " . $wpdb->prefix . "posts WHERE ID=$post_id", ARRAY_A );

    			echo "<div id='" . $current_book_content[$i]['page_id'] . 
	    				  "' class=nw_post" . 
    			          " drag-parent='" . $parent . 
    			          "' drag-menu-order='" . $current_book_content[$i]['order_id'] . 
    			          "' drag-category='" . $current_book_content[$i]['category_id'] .
	    			      "' drag-type=post>" .
    			           substr( $current_post_content['post_title'] ,0 , 80)  . 
    			           "</div>";
			}
			echo ( '</div>');
		}  /// end of 	function right_event( $parent, $id, $row ) {


/*
 * STARTING POINT OF THE DRAGGABLE FUNCTION the main entry to display the elements of the book
 *
 */
	
		if ( isset($_REQUEST['book_id'] )){
			$this->current_id = $_REQUEST['book_id'];
			$this->current_book = $wpdb->get_row(  "SELECT * from " . $wpdb->prefix . "book WHERE id=$this->current_id", ARRAY_A );
		} else {
			$this->current_id = -1;
			$this->current_book['title'] = "";
			$this->current_book['description'] = "";
		}
/*
 *	the setup of the the headre of the page to define the book properties and control how to display the available
 *	information
 */		
        $myrows = $wpdb->get_results ("SELECT * FROM " . $wpdb->prefix . "terms " .
                "INNER JOIN " . 
                    $wpdb->prefix . "term_taxonomy " .
                "ON " .
                     $wpdb->prefix  . "terms.term_id =" . $wpdb->prefix . "term_taxonomy.term_taxonomy_id " .
                "WHERE " . $wpdb->prefix . "term_taxonomy.taxonomy  = 'category' ORDER BY wp_terms.term_order;"  );

        if ( $myrows == null){
        ?>
        	<P>There are no tags available</P>
        <?php
    	} else {
        ?> 
			<div class="form-wrapper">
		        <form method="post" action="" autocomplete="off">
				<section class="form-content">
	 				<div class="form-columns xbk5-subject">
		 				<label for="reloia_select_topics_book">Select Subject to add:</label>
			           	<select id="reloia_select_topics_book" style="width:200px;"> 
			           	<?php
			           	// read all categories from post
			           	// what to do from page or custom type?
			              for ( $i = 0 ; $i < sizeof($myrows) ; $i++){
			                $term = $myrows[$i]->name;
							echo  '<option value="' . $myrows[$i]->term_id .'">'. $term . '</option>';
			              }
			            ?>
			            </select>
	 				</div>
	 				<div class="form-columns xbk5-showme">
		 				<span class="xbk5-showme-label">Show me:</span>
		 				<input id="list_posts" type="radio" name="lists1" value="list_posts" checked>
		 				<label for="list_posts">All posts for this subject</label>

		 				<input id="list_pre_chapters" type="radio" name="lists1" value="list_pre_chapters">
		 				<label for="list_pre_chapters">Prepackaged chapters for this subject</label>
		 				
	 				</div>
	 				<div class="form-columns xbk5-searchtitle"> 				
			            <label>Search by title:</label>
			            <input id="reloia_seek_topics" type="text" name="seek">
		 				<input type="hidden" id="nw_book_id" value= <?php echo '"' . $this->current_id . '"' ; ?> />
	 				</div>

	 			<!--	<button id="show_all_chapters">Show All Chapters</button> -->
	
	 			</section>
		        </form>
			</div>
<?php	    }

/*
 *	Process the left hand side 
 *
 *	By selecting the available information by subject
 *
 */

?>

	<div class="examples">
<!-- 		<div class="parent"> -->
		<div class="drag-parent">
		    <div class='drag-wrapper'>
		    	<div id='left-events' class='xbk5_container-dd'></div>
				<div id="chapter_listings" style="display: none;"></div>
<div id='left-elements' style="display:none;"> 
				<?php
				$parent = 1;
for ( $i = 0 ; $i < sizeof($myrows) ; $i++){
				// = $category;
				$a = $myrows[$i]->term_id; // term_relationships.term_taxonomy_id
				$b = $myrows[$i]->term_id; // term_relationships.term_taxonomy_id
				$sql_query = $wpdb->prepare(
					"SELECT ID, post_title " .
						"FROM " . $wpdb->prefix . "term_relationships " .
						"INNER JOIN " . 
						    $wpdb->prefix . "term_taxonomy " .
						"ON " .
						    $wpdb->prefix . "term_relationships.term_taxonomy_id  = %s " .
						"INNER JOIN " .
							$wpdb->prefix . "posts " .
						"ON " . 
						    $wpdb->prefix . "term_relationships.object_id = wp_posts.ID " .
						"WHERE " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id=%s  AND " . $wpdb->prefix . "posts.post_type = 'post' AND " . $wpdb->prefix . "posts.post_status = 'publish' ORDER BY  " . $wpdb->prefix . "posts.post_title ;", 
				 	$a, $b );

		        $pages_list = $wpdb->get_results ($sql_query);

		        if ( sizeof($pages_list) != 0) {

	$left[$i] = "<div id='left-events" . $a . "'>";  // use the category (topic) as reference
	for ( $j = 0 ; $j < sizeof($pages_list) ; $j++ ) {

		$left[$i] .= "<div id='" . $pages_list[$j]->ID .
					  "' class=nw_post" . 		    			 
			          " drag-parent='" . $parent . 
			          "' drag-menu-order='" . ($j+1) . 
			          "' drag-category='" . ($a) . 
			          "' drag-type=post>" 
			           . substr( $pages_list[$j]->post_title,0 , 80)  . "</div>"; 
	}
	$left[$i] .= "</div>";
	echo $left[$i];


					}
		    	else
		    		echo "Empty List";
}
			    ?>
		     	 </div> <!-- end of hidden information -->

				<!-- a storage area to hold the content of the prepackaged chapters -->
				<div id="chapter_info" style="display:none" class="chapter-hidden"></div>

		     	 <?php

/*
 *	Read the book from the book_content data base and prepare to display in RIGHT HAND SIDE 
 *	with the current list of chapters and content for each chapter
 */

	              for ( $i = 0 ; $i < sizeof($myrows) ; $i++){
	                $term = $myrows[$i]->name;
	                $chapter = $i+1;
					echo '<button class="nw_chapters" id="nw_chapters' . $chapter . '"' . '>' . $term . ' </button>';
					?>
					<div class=nw_panel <?php echo " id='nw_panel" . $chapter . "'" ?>  >
						<?php 

							// right_event( 1, $this->current_id, $chapter);  // $parent, $id, $row (category)
							right_event( 1, $this->current_id, $myrows[$i]->term_id);  // $parent, $id, $row (category)
						?>
					</div class="panel">
					<?php 
	              }

				?>
			</div>
		</div>
	</div>
    <?php
	}
}

function build_new_book(){
	$draggable = new NW_Dragable1;
	$draggable->dragable();
}
?>