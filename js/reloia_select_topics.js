// global variables
// copy of the original full list of elements in a category

var listing = [];
var outer = [];
var last_select = 0;

jQuery(document).ready( function() {
	left_elements = [];

	function copy_list(){
		listing = [];
		outer = [];
		if ( document.getElementById("left-events") != null ){
			var listing_ptr = document.getElementById("left-events").children;
			for (var i =  0 ; i < listing_ptr.length ; i++) {
				listing[i] = document.getElementById("left-events").children[i];
				outer[i] = document.getElementById("left-events").children[i].outerHTML;
			}
		}
	}

	function clear_left() {
		var x = document.getElementById("left-events").children;
		max = x.length;
		for (var i = 0; i < max; i++) {
		    x[0].parentNode.removeChild(x[0]);
		}
	}

	function backup_to_left( topic ){
	    clear_left( );

		event = "left-events" + topic;
		elements = document.getElementById(event)
		num_elements = elements.childElementCount;
		for ( var i = 0 ; i < num_elements ; i++) {
			document.getElementById('left-events').appendChild(document.getElementById(event).children[i].cloneNode(true));
		}
	}


	function clear_backup( topic ){
		event = "left-events" + topic;
		var x = document.getElementById(event).children;
		max = x.length;
		for (var i = 0; i < max; i++) {
		    x[0].parentNode.removeChild(x[0]);
		}
	}
		

	function left_to_backup( topic ){
		clear_backup( topic );

		event = "left-events" + topic;
		elements = document.getElementById('left-events')
		num_elements = elements.childElementCount;
		for ( var i = 0 ; i < num_elements ; i++) {
			document.getElementById(event).appendChild(document.getElementById('left-events').children[i].cloneNode(true));
		}
	}


	function element_in_list( element, list ){
		// console.log (list); 

		for ( var i = 0 ; i < list.childElementCount ; i++ )
			if ( element == list.children[i].id ){
				// console.log ( "match: " + list.children[i].id); 
				return true;
			}
		return false;
	}
	/*
	*	review all chapters to validate and remove the used information in the book
	*/
	function update_chapters(){
		/*
		 *	for ( all left categories)
		 		if ( item in rigth exist in left )
		 			remove item
		 */
		 left_elements = document.getElementById('left-elements');
		 for ( var i = 0 ; i < left_elements.childElementCount ; i++ ){ // categories
		 	// elements in categories
		 	for ( var j = 0 ; j <  left_elements.children[i].childElementCount ; j++ ){
			 	if ( element_in_list( left_elements.children[i].children[j].id, document.getElementsByClassName("right-content")[i] )){
			 		left_elements.children[i].children[j].remove()
			 	}
		 	}
		 }

	}

	if ( window.location.search.search("build_new_book" ) != -1 ){

		topic = document.getElementById("left-elements").children[0].id.replace( "left-events", "");
		update_chapters();
		backup_to_left(topic);

		copy_list();

		autocomplete_1(document.getElementById("reloia_seek_topics"));
		nw_chapters_list();
	}

	function select_book_topics(topic, posts_ids){
		backup_to_left(topic);
	}

			// fill the list of post in the left hand-side 

	jQuery("#reloia_select_topics_book").change(function(){

		// fill according to the post/pre-populated radio selection
		$radio_value = document.getElementById("list_posts").checked;
		selected = document.getElementById("reloia_select_topics_book").selectedIndex;
		var chapter = "#nw_chapters" + ( selected + 1 );
		// Find the elements stored in the right hand side using the chapter ID
/*
		if ( $radio_value == true )
			select_book_topics( document.getElementById("reloia_select_topics_book").value, chapter );
		else
			select_pre_populated_topics( document.getElementById("reloia_select_topics_book").value, chapter );
*/
		jQuery(chapter).click();
	});	

	function autocomplete_1(inp) {
	  /*the autocomplete function takes two arguments,
	  the text field element and an array of possible autocompleted values:*/
	  /*execute a function when someone writes in the text field:*/

//	  dummy = 0 ;

	  inp.addEventListener("input", function(e) {
	      var a, b, i, val = this.value;
	      /*close any already open lists of autocompleted values*/

//	      dummy += 1;
	    closeAllLists_1( );

          if (!val) { 
            for (i = 0; i < outer.length; i++) {
            //for (i = 0; i < listing.length; i++) {
                /* make a copy of all the elements in the category */
                jQuery(listing[i]).clone().appendTo(document.getElementById( "left-events"))
            }
          } else {
              /*for each item in the array...*/
            for (i = 0; i < listing.length; i++) {
                /*check if the item starts with the same letters as the text field value:
                    if positive add to the list */
                if (listing[i].innerHTML.substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                  /*create a DIV element for each matching element from the original list*/

                  jQuery(listing[i]).clone().appendTo(document.getElementById( "left-events"))
                }
            }
          }
		}
	  ); 


	  function closeAllLists_1() {
	    /*close all autocomplete lists in the document,
	    except the one passed as an argument:*/
	    var x = document.getElementById("left-events").children;
	    max = x.length;
	    for (var i = 0; i < max; i++) {
	        x[0].parentNode.removeChild(x[0]);
	        // console.dir( x );
	    }
	  }
   }


	jQuery("#list_posts").on( 'change', function(){
		posts_ids = [];
		current_panel = "right-events" + document.getElementById("reloia_select_topics_book").value;
		content_panel = document.getElementById(current_panel);

		for (var xx =  0 ; xx < content_panel.childElementCount ; xx++) {
			posts_ids[xx] = content_panel.children[xx].id;
        }

		select_book_topics( document.getElementById("reloia_select_topics_book").value, posts_ids );

	});


	jQuery("#show_all_chapters").on( 'click', function(){
		var acc1 = document.getElementsByClassName("nw_chapters");
		var i1;

    	for (i1 = 0; i1 < acc1.length; i1++) {
			acc1[i1].style.display = "display";
    	}

	});


	// CREATE THE RIGHT HAND LISTING
	//
	// Identify what is open to take the information from the temporal space there and save in th chapter area
	// intialize the left hand side
	// copy the current content in the temporal space
	// Open the new window
	// wrong way to initialize: 	jQuery(".nw_chapters").click( function(){ .. and close ) at the end
	// 
	function nw_chapters_list(){
		var acc = document.getElementsByClassName("nw_chapters");
		var i;

		for (i = 0; i < acc.length; i++) {
		  acc[i].addEventListener("click", function() {


		// when there is one active panel -- we need to save the active data available in the 
		// temp area into the active area before switching 

		// identify:    which one is the active chapter at the time of a click
		//				and the clicked item. (chapter)
			if ( document.getElementsByClassName("nw_active").length == 1 ){
				// alert ( document.getElementsByClassName("nw_active")[0].id );  

				// currently active
				// copy the draggable info in right-events into the active panel

				// get the source 
				temp_content = document.getElementById( "right-events");
				// sequence = document.getElementsByClassName("nw_active")[0].id.replace( "nw_chapters", "");
				// dest_name = "right-events" + sequence;

				dest_name = temp_content.parentElement.children[0].id
				dest_content = document.getElementById( dest_name );


				// =====
				// backup the left hand side
				left_side = temp_content.parentElement.children[0].id.replace("right-events", "");
				left_to_backup( left_side );

				// =====

				// clear the destination area

				while ( dest_content.childElementCount > 0)
					dest_content.removeChild(dest_content.children[0]);

				// copy from temp to active data

				for (var xx =  0 ; xx < temp_content.childElementCount ; xx++) {
		            jQuery( temp_content.children[xx]).clone().appendTo(document.getElementById( dest_name ));
				}

			} else{
				// alert ( "not active panel");
			}


			active_board = this.id;

			// alert ( "Selected item: " + active_board );

			// copy the source information to the temp area
			chapter = this.id;
			cnum1 = chapter.replace( "nw_chapters", "");
			var e = document.getElementById("reloia_select_topics_book")
			chapter_num = e.options[(cnum1)-1].value;

/*			 
			current_panel = "right-events" + chapter_num;
			content_panel = document.getElementById(current_panel);
			listingx = [];

			for (var xx =  0 ; xx < content_panel.childElementCount ; xx++) {
				listingx[xx] = content_panel.children[xx];
	            jQuery(listingx[xx]).clone().appendTo(document.getElementById( "right-events"));
			}
*/
			// clean right_e
			right_e= document.getElementById('right-events');
			right_e_cnt = right_e.childElementCount;
			for( xx = 0 ; xx < right_e_cnt ; xx++){
				right_e.children[0].parentNode.removeChild(right_e.children[0]);
			}

/*
			// copy the source information to the temp area
			chapter = this.id;
			chapter_num = chapter.replace( "nw_chapters", "");

XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

			check the selected assignment /// already checked

XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

*/
			document.getElementById('reloia_select_topics_book').options[(cnum1-1)].selected = 'selected';


			current_panel = "right-events" + chapter_num;
			content_panel = document.getElementById(current_panel);
			listingx = [];
			posts_ids = [];

			for (var xx =  0 ; xx < content_panel.childElementCount ; xx++) {
				listingx[xx] = content_panel.children[xx];
				posts_ids[xx] = content_panel.children[xx].id;
	            jQuery(listingx[xx]).clone().appendTo(document.getElementById( "right-events"));
	        }

			if ( document.getElementById( "right-events").childElementCount == 0){
				jQuery( "<div class='empty' style='width:400px; height:28px'></div>").appendTo(document.getElementById( "right-events"));
			}


			// copy the pointer from the active panel to right_e to keep as temp area
			right_e= document.getElementById('right-events');

			// set the temp area in the current location
			panel = "nw_panel" + this.id.replace( "nw_chapters", "");

			//panel = "nw_panel" + chapter;

			panel_content = document.getElementById( panel ).appendChild(right_e);

			// Handle the switching of panels

			var acc1 = document.getElementsByClassName("nw_chapters");
			var i1;

	    	for (i1 = 0; i1 < acc1.length; i1++) {
				acc1[i1].nextElementSibling.style.maxHeight = null;
			    acc1[i1].classList.toggle("nw_active", false);
	    	}

		    this.classList.toggle("nw_active");
		    var panel = this.nextElementSibling;
		    if (panel.style.maxHeight){
	// alert ("SET TO NULL");
		      panel.style.maxHeight = null;
		    } else {
	// alert ("SET TO MAX");

		      panel.style.maxHeight = panel.scrollHeight + "px";
		    } 

			// fill the list of post in the left hand-side 
			// we pass the list of posts already in the right handside to avoid duplications

			// fill according to the post/pre-populated radio selection
			$radio_value = document.getElementById("list_posts").checked;
			if ( $radio_value == true )
				select_book_topics( chapter_num, posts_ids );
			else
				select_pre_populated_topics( chapter_num, posts_ids );

		  });  // end of the click event management
		}
	}
});
