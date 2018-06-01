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


function my_ajax_reloia_list_posts_books(){
   echo "You must log in to list posts";
   die();
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


add_action("wp_ajax_nw_book", "nw_book", 3, 5);
add_action("wp_ajax_nopriv_nw_book", "must_login_book", 3, 5);

add_action( 'init', 'nw_book_enqueuer1' );
// add_action( 'admin_footer', 'nw_book_enqueuer' );

function nw_book_enqueuer1() {
   wp_enqueue_script(  'nw_book_script1', CPTURL1 .'/js/nw_book1.js', array(), '1', true);
   wp_localize_script( 'nw_book_script1', 'nw_book', 
                  array( 'ajaxurl' => admin_url( 'admin-ajax.php') ,
                        'action' => 'nw_book',
                        'user_id' => 1,
                        'book_id' => 1,
                        'nw_profile_nonce' =>  wp_create_nonce( 'nw_profile_nonce1' )
                        ));
}

//  @list -- is a json or an array?
//  @parent -- is the current id post of the parent of the child posts

function must_login_book() {
   echo "You must log in to save a book";
   wp_die();
}

/*
 * create a book from the screen information and save in mptt table using the organization of the book
 */


//
//  it is called from navigation-wheel.php -> function nw_book() -- in line 223
//
// Initialize -- the stack, database access and mptt
// Validate the ajax request -- nonce, get parameters
// conditionally eliminate the old book version if it exist o create a new version
// From the source that is the book_rigth window take the the list of records
// from the first page in the book, traverse the tree and store it in a mptt format in the table
// 
//
//


// nw_save_book()
function nw_book() {

	// testing the new approach


   global $wpdb;
// this is there difference between version 3 and verstion 4
// the sources:
//   Version 3: from the post table
//   Version 4: from the right window
//

   if ( !wp_verify_nonce( $_REQUEST['nonce'], "nw_profile_nonce1")) {
      exit("No naughty business please - nw_book");
   }   

   // process the list that came as a json and transform into an array
   $list = $_REQUEST['post_list'];
   $list = stripslashes($list);

   $list_array = json_decode( $list, true);

   $version = 1;
   $book_title = $_REQUEST['book_title'];
   $book_description = $_REQUEST['book_description'];
   $book_id = $_REQUEST['book_id'];
   
   // Save the custom book pages in the database in a linear format

   // first, clear everything in the database
   // mysqli_query($connection2, 'TRUNCATE TABLE book_navigator_mptt');
   // use the table after eliminate the book if it exist before
   if ( $book_id == -1){
      // create a new book and then use the id to store it, then
      // insert the list of items contained in the book

      $sql = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "book 
      (  title,
         description,
         book_type) VALUES ( %s, %s, %s);",
            $book_title,
            $book_description,
            "book"  );
      $status = $wpdb->query($sql);
      $book_id = $wpdb->insert_id;

   } else {

      // update the current book title and description for the created book

      if ( ($book_title != "") && ($book_description) != "" ){
         $error_sql = $wpdb->query( "UPDATE " . $wpdb->prefix . "book SET title = '" . $book_title . "', description = '" . $book_description . "' WHERE id = " . $book_id);


         // the book exist, need to be removed the old one and insert the new one
         $error_sql = $wpdb->query($wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "book_content WHERE book_id = %d", $book_id ));
      }
   }

   for ( $i = 0 ; $i < sizeof($list_array) ; $i++){
      $query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "book_content ( book_id, version, page_id, order_id, category_id )
         VALUES (%d, %d, %d, %d, %d )" ,
           $book_id,
           $version, 
           $list_array[$i]['id'],
           $list_array[$i]['drag_menu_order'],
           $list_array[$i]['drag_category'] );
         $error_sql = $wpdb->query( $query );
   }

   $result['status'] = "success";
   // it is used as a way to avoid multiple savings of the new book
   $result['book_id'] = $book_id;

   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }
   wp_die();
}


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
?>
		<div class="wrap">
	    <h1>Create a New Book</h1>
	    <h2>With this tool you'll put together a complete custom book for your users. Each book can be assigned to as many users as you like.</h2>
	    <p>Start by giving your book a name and entering a description. Note that this description will be visible to your users-it will be displayed just above the table of contents. Click <b>Save current book</b>.</p>
		<form name="book_name" action="" method="post" id="nw_book_name">
			<div id="bookwrap">
				<input type="hidden" name="book_id" id="nw_book_id" value=
				  <?php echo $this->current_id;?>>
				<input type="text" name="book_title" size="50" id="nw_book_title" autocomplete="off" placeholder="Enter Book Name here" value=
				  <?php echo $this->current_book['title']?>>
				<br>
				<textarea name="book_description" cols="120" rows="3" id="nw_book_description" autocomplete="off" placeholder="Provide a Description about this book"><?php if (($this->current_book['description'])!="") echo $this->current_book['description'];?></textarea>
			</div>
		</form>

	    <button id="js_nw_save_book1" class="xbk5-button">Save current book</button><span id='status1'></span>
<!-- 	    <a href=<?php echo home_url();?> target="_blank"><button  class="xbk5-button">Preview Book Content</button></a> -->
	    <button id="js_nw_new_book1" class="xbk5-button-alert">Clear my work and start a new book</button><span id='status_new_book'></span>
		<h3>Don't forget to click <b>Save current book</b> when you're done!</h3>

		</div>

		<?php
		// SELECT * FROM wp_terms INNER JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_taxonomy_id WHERE wp_term_taxonomy.taxonomy  = 'category' ORDER BY wp_terms.term_order;
		
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
				<p>To select your content, first choose a subject. You can then choose to list all available posts or a list of prepackaged chapters for the subject chosen. If your content list is long you may find it convenient to use the <b>Search by title</b> tool at right. Add the content you need for each chapter.</p>
				<p>You can revisit a chapter you've already built and add or remove content as needed. To remove content, simply drag it from the right column back to the left.</p>
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
		    	<div id='left-events' class='xbk5_container-dd'>

				<?php
				$parent = 1;
	    		// $pages_list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status = 'publish' limit 25");

				// = $category;
				$a = $myrows[0]->term_id; // term_relationships.term_taxonomy_id
				$b = $myrows[0]->term_id; // term_relationships.term_taxonomy_id
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
		        if ( sizeof($pages_list) != 0)
		    		for ( $i = 0 ; $i < sizeof($pages_list) ; $i++){
		    			echo "<div id='" . $pages_list[$i]->ID .
		    					  "' class=nw_post" . 		    			 
		    			          " drag-parent='" . $parent . 
		    			          "' drag-menu-order='" . ($i+1) . 
		    			          "' drag-category='" . ($a) . 
		    			          "' drag-type=post>" 
		    			           . substr( $pages_list[$i]->post_title,0 , 80)  . "</div>";
		    		}
		    	else
		    		echo "Empty List";
			    ?>
		     	 </div>

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