var listing_book = [];
var outer_book = [];

jQuery(document).ready( function() {

  function copy_list_book(){
        listing_book = [];
        outer_book = [];
        var listing_ptr = document.getElementById("left-events").children;
        for (var i =  0 ; i < listing_ptr.length ; i++) {
          listing_book[i] = document.getElementById("left-events").children[i];
          outer_book[i] = document.getElementById("left-events").children[i].outerHTML;
        }
  }

  function clean_chapter(chapter){
    // clear the destination area
    chapter_content = document.getElementById(chapter);
    while ( chapter_content.childElementCount > 0) {
      chapter_content.removeChild(chapter_content.children[0]);
    }
  };

  function clean_temp_area(){
    chapter_content = document.getElementById("right-events");
    while ( chapter_content.childElementCount > 0) {
      chapter_content.removeChild(chapter_content.children[0]);
    }
  };

  function clean_book(){
        var list = document.getElementsByClassName("right-content");
        for ( var i = 0 ; i < list.length ; i++) {
          // document.getElementsByClassName("right-content")[0].id
          clean_chapter( list[i].id );
        }
        clean_temp_area();
  };

   jQuery("#js_nw_new_book1").click( function() {
    if (confirm('Are you sure you want to start a new book?')) {

          // Create a new book by initializing the form

        document.getElementById("nw_book_id").value = -1; 

        document.getElementById("nw_book_title").placeholder = "Enter Book Name here";
        document.getElementById("nw_book_title").value = "";

        document.getElementById("nw_book_description").placeholder = "Provide a Description about this book";
        document.getElementById("nw_book_description").value = "";

        //topic = document.getElementById("reloia_select_topics_book").value;

        var e = document.getElementById("reloia_select_topics_book");
        document.getElementById("reloia_select_topics_book").selectedIndex = 0;

        topic = e.options[e.selectedIndex].value;
        last_select = document.getElementById("reloia_select_topics_book").selectedIndex;

        clean_book();

        nonce = reloia_list_posts.reloia_list_posts_nonce;
        mode = 1;
        jQuery.ajax({
          type : "post",
          datatype : "json",
              url : reloia_list_posts.ajaxurl,
              data : { action: "reloia_list_posts", topic : topic, nonce : nonce, mode : mode },
                success: function(response) {
                  var obj = JSON.parse(response);
                    if(obj.status == "success") {
                        jQuery("#left-events").html(obj.topics);
                        copy_list_book();

                        // clear the input field
                        document.getElementById("reloia_seek_topics").value = "";
                    } else {
                      alert("wrong list of topics");
                    }
                }
        });
      } else {
        document.getElementById("reloia_select_topics_book").selectedIndex =last_select;
      }
   });

// new version to use the accordion by chapter
//
// WARNING -- check that the last additions are saved into the permanent area

  function copy_draggable_to_permanent_area( chapter ){

      // currently active
      // copy the draggable info in right-events into the active panel

      // get the source 
      temp_content = document.getElementById( "right-events");

      dest_name = "right-events" + chapter;
      dest_content = document.getElementById( dest_name );

      // clear the destination area

      while ( dest_content.childElementCount > 0)
        dest_content.removeChild(dest_content.children[0]);

      // copy from temp to active data

      for (var xx =  0 ; xx < temp_content.childElementCount ; xx++) {
              if ( temp_content.children[0].className.indexOf("empty") != 0 ){
                jQuery( temp_content.children[xx]).clone().appendTo(document.getElementById( dest_name ));
              }
      } 
  }

// Save a Book
   jQuery("#js_nw_save_book1").click( function() {

  if ( (book_title = document.getElementById("nw_book_title").value ) == ""){
    alert( "Empty Book Name");
    return;
  }

   if ( (book_description = document.getElementById("nw_book_description").value ) == ""){
    alert("Empty Book Description");
    return;
  }

   var list = document.getElementsByClassName("right-content");

   var book = [];
   var book_item = [];
   var book_draft = {};

   // once you have $list you can do whatever you want
   var ControlCnt = list.length;
   // Now loop through list of controls

        // find if there is any open panel to save the potential last modifications
        if ( jQuery(".nw_active").find("id").prevObject.length == 1 ) {
          // chapter = jQuery(".nw_active").find("id").prevObject[0].id.replace("nw_chapters","");
          temp_content = document.getElementById( "right-events");
          chapter = temp_content.parentElement.children[0].id.replace("right-events","");
          copy_draggable_to_permanent_area( chapter );
        }

   jQuery(list).each( function(index) {
 
    //var chapter_list = list[index].children;
    //chapter_list.each( function( index2 ){

      jQuery(list[index].children).each( function( index1, elements1) {

//        console.log(this);
        if ( this.className != "empty"){
           var id = this.attributes[0].value;
           var drag_parent = this.attributes[2].value;
           var drag_menu_order = this.attributes[3].value;
           var category = this.attributes[4].value;


//           var category = document.getElementById("reloia_select_topics_book").value;


           book_item['id'] = id;
           book_item['drag_parent'] = drag_parent;
           book_item['drag_menu_order'] = index;  //drag_menu_order;
           book_item['drag_category'] = category + 1;

           var bb = { id:id, drag_parent:drag_parent,  drag_menu_order: index, drag_category:category} ;
           book.push( bb );
         }
     });
       // debugging support ---         <p id='status' class='hidden'>Status Text</p>
       // console.log( "ID: " + id + " " + drag_parent + " " + drag_menu_order + " " + index);
    //});
   });


   book = JSON.stringify(book)
   // console.log(book);
   // document.getElementById("status").innerHTML = book;

   action = nw_book.action;
   post_list = book;
   book_id = document.getElementById("nw_book_id").value; 
   nonce = nw_book.nw_profile_nonce;
   dataType = "json";
   jQuery.ajax({
         type : "post",
         dataType : "json",
         url : nw_book.ajaxurl,
         data : {action: "nw_book", post_list : post_list, book_id : book_id,  nonce: nonce, 
          book_title : book_title, book_description : book_description },
         success: function(response) {
            // var obj = JSON.parse(response);
            if(response.status == "success") {
                jQuery("#status1").fadeIn(500);
                document.getElementById("status1").innerHTML = "Your book has been saved";

   // it is used as a way to avoid multiple savings of the new book

                document.getElementById("nw_book_id").value = response.book_id; 
                setTimeout(function() {jQuery("#status1").fadeOut(1500); },3000);
            } else {
               alert("Your book can not be set NOW");
            }
         }
      })
   })

 

});
