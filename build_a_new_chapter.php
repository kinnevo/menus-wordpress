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
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	protected function BM_header(){
	?>
	    <h1>Build Book:
	    <?php 

	    echo substr($this->current_book['title'], 0, 50);
	    if  ( strlen ( $this->current_book['title']) > 50 )
	    	echo " ...";
	    ?></h1> 
	<?php 
	}

	function right_event_chapter( $parent, $id, $row ) {
		global $wpdb;
		// Testing
		$row = "1";  // not used now
//		      <div id=<?php echo "'right-events". $row . "' "

		?>
		      <div id="right-events" class='xbk5_container-dd'>
				<?php
/* 
 *	read the current content of the book
 *	if the list is empty put a id=999 as a placeholder
 *	otherwise read the content into the right-events xbk5_container-dd
 *
 *	I need user_id, book_id and version
 *	we need to keep the current value of the pages in the book
 */
		      	$current_book_content = $wpdb->get_results(  "SELECT * from " . $wpdb->prefix . "book_content WHERE book_id=$this->current_id ", ARRAY_A );


				if ( $current_book_content == null) {

		     	} else {
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
		      	}
			?>
		    </div>


		<?php 
	} // end of 	function right_event( $parent, $id, $row ) {


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
				 id="nw_chapter_title" autocomplete="off" placeholder="Enter Chapter Name here">
				<br>
				<textarea name="chapter_description" cols="120" rows="3" id="nw_chapter_description" autocomplete="off" placeholder="Provide a Description about this chapter"><?php  if (($this->current_book['description'])!="") echo $this->current_book['description'];?></textarea>
				<input type="hidden" id="nw_chapter_id" value="<?php echo $this->current_id  ?>" >
				<input type="hidden" id="nw_chapter_category" value="<?php echo $this->category  ?>">
			</div>
		</form>

		<button id="js_nw_save_chapter1" class="xbk5-button">Save this Chapter</button><span id='status1'></span><br>
		
					<!-- search by post title -->
	        <form method="post" action="" autocomplete="off">
	            <label for="seek">Search by post title:</label>
	            <input id="reloia_seek_topics" type="text" name="seek">
 				<input type="hidden" id="nw_book_id" value= <?php echo '"' . $this->current_id . '"' ; ?> />
	        </form>

<!-- 	    <a href=<?php echo home_url();?> target="_blank">Preview Chapter Content</a> -->

	<div class="examples">
<!-- 		<div class="parent"> -->
		<div class="drag-parent">
		    <div class='drag-wrapper'>
		    	<div id='left-events' class='xbk5_container-dd'>

				<?php
				$parent = 1;
	    		// $pages_list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status = 'publish' limit 25");

				$a = $this->category; // term_relationships.term_taxonomy_id
				$b = $this->category; // term_relationships.term_taxonomy_id

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
					<?php 
						$this->right_event_chapter( 1, $this->current_id, $i);  // $parent, $id, $row 
					?>
			</div>
		</div>
	</div>
    <?php
	}
}

class YOP_Book_Mgmt extends Book_Manager_Draggable{
	public function BM_header(){
	?>
		<div class="wrap">
		<h1>Create a Prepackaged Chapter</h1>
		<h2>With this tool, you can create prepackaged chapters for a specific category. This can speed the creation of books considerably if you have sets of pages that you use regularly.</h2>
		<p>Start the process by selecting a category from the dropdown menu below. This will populate the list of available content in the drag-and-drop editor. Next, enter a chapter name e.g. "Westside Lakeshore Suburbs." Enter a brief description for your reference, such as usage notes; this will show up in the Manage Chapters screen and helps others understand the purpose of the chapter.</p>
		<p>With that done, you can drag the content you need from left to right in the section below. Note that for longer lists, it may be easiest to use the <b>Search by post title</b> tool to quickly find the content you need. To complete the process, click <b>Save this Chapter</b>. Note that if you change the selection you made in the category dropdown menu, the tool will reset your selections and start building a new chapter.</p>
	<?php 
	}
}


function chapter_mgmt1(){
?>
<?php 
	$chapter_drag = new YOP_Book_Mgmt;
	$chapter_drag->book_management();

}