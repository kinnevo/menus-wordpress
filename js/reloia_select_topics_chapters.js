var listing_chapter = [];
var outer_chapter = [];


jQuery(document).ready( function() {

	function copy_list_chapter(){
		listing_chapter = [];
		outer_chapter = [];
		if ( document.getElementById("left-events") != null ){
			var listing_ptr = document.getElementById("left-events").children;
			for (var i =  0 ; i < listing_ptr.length ; i++) {
				listing_chapter[i] = document.getElementById("left-events").children[i];
				outer_chapter[i] = document.getElementById("left-events").children[i].outerHTML;
			}
		}
	}


	// select the topics for the chapter select element
	jQuery("#reloia_select_topics").change(function(){

		if (confirm('Are you sure you want to start a new chapter with a new subject?')) {
		    // Create a new chapter by initializing the form

			document.getElementById("nw_chapter_id").value = -1; 
   			document.getElementById("nw_chapter_title").placeholder = "Enter Chapter Name here";
      		document.getElementById("nw_chapter_title").value = "";

			document.getElementById("nw_chapter_description").placeholder = "Provide a Description about this chapter";
			document.getElementById("nw_chapter_description").value = "";
			document.getElementById("nw_chapter_category").value = document.getElementById("reloia_select_topics").selectedIndex;
			last_select = document.getElementById("reloia_select_topics").selectedIndex;

			// clean the right area by removing all the elements in there
			$list = document.getElementById('right-events').children;   // get all input controls
			max = $list.length;
			for ( i = 0 ; i < max ; i++){
				$list[0].parentNode.removeChild($list[0]);
			}

			topic = document.getElementById("reloia_select_topics").value;
		    nonce = reloia_list_posts.reloia_list_posts_nonce;
			jQuery.ajax({
				type : "post",
				datatype : "json",
		        url : reloia_list_posts.ajaxurl,
				data : { action: "reloia_list_posts", topic : topic, nonce : nonce },
	            success: function(response) {
	            	var obj = JSON.parse(response);
	                if(obj.status == "success") {
	                  	jQuery("#left-events").html(obj.topics);
	                } else {
	                	alert("wrong list of topics");
	                }
	            }
			});
		} else {
			document.getElementById("reloia_select_topics").selectedIndex =last_select;
		}
	});




});

