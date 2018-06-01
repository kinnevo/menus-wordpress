<?php
// Build a chapter

add_action('admin_menu', 'build_chapter_menu');

function build_chapter_menu(){

	$hook = add_submenu_page(
		'book_management',
		'New Chapter', // tab title
		'New Chapter', // sidebar nav
		'administrator', // user level required
		'chapter_mgmt1', //
		'chapter_mgmt1');

	set_current_screen($hook );
	get_current_screen()->add_help_tab( array (
	'id'		=> 'new_chapter',
	'title'		=> __('New Chapter'),
	'content'	=>
		'<p>' . __('Create a new chapter.') . '</p>'
	) );
}


/*
 *  Read content for a user
 */

function reloia_list_posts(){
	global $wpdb;

   	if ( !wp_verify_nonce( $_REQUEST['nonce'], "reloia_list_posts_nonce")) {
    	exit("No naughty business please - reloia_list_posts_nonce ");
   	}

	$topic_listing = "";
    $source = $_REQUEST['topic'];
	$a = $source;
	$b = $source;
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
			"WHERE " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id=%s  AND " . $wpdb->prefix . "posts.post_type = 'post' AND " . $wpdb->prefix . "posts.post_status = 'publish' ORDER BY " . $wpdb->prefix . "posts.post_title;", 
	 	$a, $b );

    $my_posts = $wpdb->get_results ($sql_query);

    if ( sizeof($my_posts) == 0){
    	$topic_listing .= "<P>There are not posts available</P>";
	} else {
		for ( $i = 0 ; $i < sizeof($my_posts) ; $i++){
//			$topic_listing	.= '<p>' . $my_posts[$i]->post_title . '</p>';
			$parent = 0; // not used for now

	    	$topic_listing	.=  "<div id='" . $my_posts[$i]->ID . 
	    					  "' class=nw_post" . 
	    			          " drag-parent='" . $parent . 
	    			          "' drag-menu-order='" . ($i+1) . 
	    			          "' drag-category='" . ($a) .
	    			          "' drag-type=post>" 
	    			           . substr( $my_posts[$i]->post_title,0 , 80) . "</div>";
      	}
	}
	$result['topics'] = $topic_listing;
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

function my_ajax_reloia_list_posts(){
   echo "You must log in to list posts";
   die();
}


function reloia_scripts() {
  wp_enqueue_script(  'reloia_list_posts', CPTURL1 . '/js/reloia_select_topics_chapters.js', array( 'jquery' ), '1', true );
  wp_localize_script( 'reloia_list_posts', 'reloia_list_posts', array(
                      'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                      'action' => "reloia_list_posts",
                      'reloia_list_posts_nonce' => wp_create_nonce( 'reloia_list_posts_nonce' )
    )
  );
}
add_action( "wp_ajax_reloia_list_posts", "reloia_list_posts", 3, 5 );
add_action( "wp_ajax_nopriv_reloia_list_posts", "my_ajax_reloia_list_posts", 3, 5 );

add_action( 'init', 'reloia_scripts' );


/**
 * Book organizer Draggable class
 *
 * @package WordPress 
 * @subpackage Book
 * @since 0.0.1
 */

/**
 * Class to manage the creation of custom lists
 *
 * This class handles the creation of books, chapters and any kind of knowledge units
 *
 * @since 0.0.1
 *
 *	Params 
 *		new - id = -1
 *		edit - id = current chapter / book
 *		delete - id = chapter to be deleted
 */
class Book_Manager_Draggable {
	private $cuerrent_id;
	private $current_book;

	// class constructor
	public function __construct() {
	}

	function in_post_list ( $id, $list){
		if ( empty($list) )
			return false;
		for ( $i = 0 ; $i < sizeof($list) ; $i++)
			if ($list[$i]['page_id'] == $id )
				return true;
		return false;
	}

	public function get_left_post( $category){
		global $wpdb;

		$a = $category; // term_relationships.term_taxonomy_id
		$b = $category; // term_relationships.term_taxonomy_id

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
				"WHERE " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id=%s  AND " . $wpdb->prefix . "posts.post_type = 'post' ORDER BY " . $wpdb->prefix . "posts.post_title;", 
		 	$a, $b );

        $pages_list = $wpdb->get_results ($sql_query);

		return $pages_list;
	}

	public function get_right_post( $chapter){
		global $wpdb;

      	$current_book_content = $wpdb->get_results(  "SELECT * from " . $wpdb->prefix . "book_content WHERE book_id=$chapter ", ARRAY_A );
		return $current_book_content;
	}

	//
	// Build the two sides by slipting the posts in the category from the ones already added to the prepacked chapter
	//
	function fill_draggable( $category, $chapter ) // if chapter == -1 will be a new chapter - it is not available
	{
		$parent = 1;
		$left_rows = $this->get_left_post( $category ); // if new -- read post of a given category
														// if exist -- read posts not contained in the chapter
		if ($chapter == -1 ){
			$right_rows = "";
		} else {
			$right_rows = $this->get_right_post( $chapter );
		}

		$left = "";
		$right = "";

		for ( $i = 0 ; $i < sizeof( $left_rows) ; $i++ ){

			if ( $chapter == -1 ){ // is a new chapter 
    			$left .=  "<div id='" . $left_rows[$i]->ID . 
    					  "' class=nw_post" . 		    			
    			          " drag-parent='" . $parent . 
    			          "' drag-menu-order='" . ($i+1) . 
    			          "' drag-category='" . ($category) . 
    			          "' drag-type=post>" 
    			           . substr( $left_rows[$i]->post_title,0 , 80)  . "</div>";

			} else {
				if ( $this->in_post_list( $left_rows[$i]->ID, $right_rows ) ){
					$right .= "<div id='" . $left_rows[$i]->ID . 
	    					  "' class=nw_post" . 		    			
	    			          " drag-parent='" . $parent . 
	    			          "' drag-menu-order='" . ($i+1) . 
	    			          "' drag-category='" . ($category) . 
	    			          "' drag-type=post>" 
	    			           . substr( $left_rows[$i]->post_title,0 , 80)  . "</div>";

				} else {
	    			$left .=  "<div id='" . $left_rows[$i]->ID . 
	    					  "' class=nw_post" . 		    			
	    			          " drag-parent='" . $parent . 
	    			          "' drag-menu-order='" . ($i+1) . 
	    			          "' drag-category='" . ($category) . 
	    			          "' drag-type=post>" 
	    			           . substr( $left_rows[$i]->post_title,0 , 80)  . "</div>";
				}
			}
		}

		return array( $left, $right);
	}

	public function BM_header(){
	?>
		<div class="wrap">
		<h1>Create a Prepackaged Chapter</h1>
		<h2>With this tool, you can create prepackaged chapters for a specific category. This can speed the creation of books considerably if you have sets of pages that you use regularly.</h2>
		<p>Start the process by selecting a category from the dropdown menu below. This will populate the list of available content in the drag-and-drop editor. Next, enter a chapter name e.g. "Westside Lakeshore Suburbs." Enter a brief description for your reference, such as usage notes; this will show up in the Manage Chapters screen and helps others understand the purpose of the chapter.</p>
		<p>With that done, you can drag the content you need from left to right in the section below. To complete the process, click <b>Save this Chapter</b>. Note that if you change the selection you made in the category dropdown menu, the tool will reset your selections and start building a new chapter.</p>
	<?php 
	}



	/**
	 * Create a customized booklet by draging information from the library to a customized document
	 */
	public function book_management() {
		global $wpdb;

        $myrows = $wpdb->get_results ("SELECT * FROM " . $wpdb->prefix . "terms " .
                "INNER JOIN " . 
                    $wpdb->prefix . "term_taxonomy " .
                "ON " .
                     $wpdb->prefix  . "terms.term_id =" . $wpdb->prefix . "term_taxonomy.term_taxonomy_id " .
                "WHERE " . $wpdb->prefix . "term_taxonomy.taxonomy  = 'category' ORDER BY " . $wpdb->prefix . "terms.term_order;"  );

		if ( isset($_REQUEST['id'] )){
			$this->current_id = $_REQUEST['id'];
			$this->current_book = $wpdb->get_row(  "SELECT * from " . $wpdb->prefix . "book WHERE id=$this->current_id", ARRAY_A );
			$this->category = $_REQUEST['category'];
		} else {
			$this->current_id = -1;
			$this->current_book['title'] = "";
			$this->current_book['description'] = "";
			$this->category = $myrows[0]->term_id; // First Category in the list

		}
		$this->BM_header();

        if ( $myrows == null){
        ?>
        	<P>There are no tags available</P>
        <?php
    	} else {
        ?> 

	        <form method="post" action="" autocomplete="off">
 				<b>Start building a new Chapter</b> by selecting your subject: 
	           	<select id="reloia_select_topics" style="width:200px">
	           	<?php
	           	// read all categories from post
	           	// what to do from page or custom type?
	              for ( $i = 0 ; $i < sizeof($myrows) ; $i++){
	                $term = $myrows[$i]->name;
	                if ( $myrows[$i]->term_id == ($this->category) )
						echo  '<option value="' . $myrows[$i]->term_id .'" selected>'. $term . '</option>';
	                else
						echo  '<option value="' . $myrows[$i]->term_id .'">'. $term . '</option>';
	              }
	            ?>
	            </select>
	        </form>

<?php	    }
?>

		<form name="chapter_name" action="" method="post" id="nw_chapter_name">
			<div id="chapterwrap">
				<input type="text" name="chapter_title" size="50" 
				<?php if ($this->current_id != -1 ) echo 'value="'.$this->current_book['title'].'"'?>
				 id="nw_chapter_title" autocomplete="off" placeholder="Enter Chapter Name here.">
				<br>
				<textarea name="chapter_description" cols="120" rows="3" id="nw_chapter_description" autocomplete="off" placeholder="Provide a Description about this Chapter."><?php  if (($this->current_book['description'])!="") echo $this->current_book['description'];?></textarea>
				<input type="hidden" id="nw_chapter_id" value="<?php echo $this->current_id  ?>" >
				<input type="hidden" id="nw_chapter_category" value="<?php echo $this->category  ?>">
			</div>
		</form>

		<button id="js_nw_save_chapter1" class="xbk5-button">Save this Chapter</button><span id='status1'></span><br>
		
	<!-- search by post title
        <form method="post" action="" autocomplete="off">
            <label for="seek">Search by post title:</label>
            <input id="reloia_seek_topics_chapters" type="text" name="seek">
				<input type="hidden" id="nw_book_id" value= <?php echo '"' . $this->current_id . '"' ; ?> />
        </form> 
    -->

<!-- 	    <a href=<?php echo home_url();?> target="_blank">Preview Chapter Content</a> -->

<?php  
	$fill = $this->fill_draggable( $this->category, $this->current_id); ?>
	<div class="examples">
<!-- 		<div class="parent"> -->
		<div class="drag-parent">
		    <div class='drag-wrapper'>
		    	<div id='left-events' class='xbk5_container-dd'>
		    		<?php echo $fill[0]; ?>
		     	</div>
		     	<div id="right-events" class='xbk5_container-dd'>
					<?php echo $fill[1]; ?>
		     	</div>
			</div>
		</div>
	</div>
    <?php
	}
}


function chapter_mgmt1(){
?>
<?php 
	$chapter_drag = new Book_Manager_Draggable;
	$chapter_drag->book_management();
}