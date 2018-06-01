<?php 
add_action("wp_ajax_nw_save_chapter1", "nw_save_chapter1", 3, 5);
add_action("wp_ajax_nopriv_nw_save_chapter1", "must_login_chapter1", 3, 5);
add_action( 'init', 'nw_chapter_enqueuer1' );

function nw_chapter_enqueuer1() {
   wp_enqueue_script(  'nw_chapter_script1', CPTURL1 .'/js/nw_chapter1.js', '1', true);
   wp_localize_script( 'nw_chapter_script1', 'nw_save_chapter1', 
                  array( 'ajaxurl' => admin_url( 'admin-ajax.php') ,
                        'action' => 'nw_save_chapter1',
                        'user_id' => 1,
                        'chapter_id' => 1,
                        'nw_profile_nonce' =>  wp_create_nonce( 'nw_profile_nonce1' )
                        ));
}

function must_login_chapter1() {
   echo "You must log in to save a chapter";
   wp_die();
}

/*
 * create a chapter from the screen information and save in table using the organization of the chapter
 */

   // testing the new approach -- one screen to create the chapter and update the content

//function nw_chapter1() {

function nw_save_chapter1() {
   global $wpdb;

   if ( !wp_verify_nonce( $_REQUEST['nonce'], "nw_profile_nonce1")) {
      exit("No naughty business please - nw_chapter1");
   }   

   // process the list that came as a json and transform into an array
   $list = $_REQUEST['post_list'];
   $list = stripslashes($list);

   $list_array = json_decode( $list, true);

   $chapter_id = $_REQUEST['chapter_id'];
   $chapter_title = $_REQUEST['chapter_title'];
   $chapter_description = $_REQUEST['chapter_description'];
   $chapter_category = $_REQUEST['chapter_category'];
   $version = 1;

   // Save the custom chapter pages in the database in a linear format

   // first, clear everything in the database
   // mysqli_query($connection2, 'TRUNCATE TABLE chapter_navigator_mptt');
   // use the table after eliminate the chapter if it exist before

   if ( $chapter_id == -1){
      // create a new chapter and then use the id to store then
      // insert the list of items contained in the chapter
      $sql = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "book
      (  title,
         description,
         book_type,
         category ) VALUES ( %s, %s, %s, %d);",
            $chapter_title,
            $chapter_description,
            "chapter",
            $chapter_category  );
      $status = $wpdb->query($sql);
      $chapter_id = $wpdb->insert_id;
   } else {
      // we need to update the name and related info. ??? is needed?
      $err = $wpdb->query( "UPDATE " . $wpdb->prefix . "book SET title='". $chapter_title ."',
       description = '".$chapter_description . "' WHERE id = " . $chapter_id);


      // the chapter exist, need to be removed the old one and insert the new one
      $sql = $wpdb->prepare( "DELETE FROM ". $wpdb->prefix . "book_content WHERE book_id = %d", $chapter_id );
      $error_sql = $wpdb->query( $sql );
   }

   for ( $i = 0 ; $i < sizeof($list_array) ; $i++){
      $error_sql = $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "book_content ( book_id, version, page_id, order_id, category_id )
         VALUES ( %d, %d, %d, %d, %d )" ,
            $chapter_id, 
            $version, 
            $list_array[$i]['id'],
            $list_array[$i]['drag_menu_order'], 
            $list_array[$i]['drag_category']
          ));
   }

   $result['status'] = "success";
   // it is used as a way to avoid multiple savings of the new book
   $result['chapter_id'] = $chapter_id;

   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   } else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }
   wp_die();
}
?>