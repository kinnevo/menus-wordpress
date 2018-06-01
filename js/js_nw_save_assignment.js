jQuery(document).ready( function() {

	jQuery( "#js_nw_save_assignment").click( function() {
		/*
		*	Information in the form:
		*		user_id
		*		user_meta roles -- more than one
		*		selected_book
		*		agent
		*		welcome message to the user
		*/
        user_id = document.getElementById("nw_current_user_id").value; 
        is_reader = document.getElementById("nw_reader").checked;
        is_agent = document.getElementById("nw_agent").checked;
        book_id = document.getElementById("nw_selected_book_id").value;
        assigned_agent = document.getElementById("nw_selected_agent").value;
        welcome_message = document.getElementById("nw_user_welcome").value;
        
        nonce = nw_save_assigned_book.nw_save_assigned_book_nonce;
        jQuery.ajax({
          type : "post",
          datatype : "json",
              url : nw_save_assigned_book.ajaxurl,
              data : { action: "nw_save_assigned_book",

						user_id : user_id, 
				        is_reader : is_reader,
				        is_agent : is_agent,
				        book_id : book_id,
				        assigned_agent : assigned_agent,
				        welcome_message : welcome_message,

              			nonce : nonce },
                success: function(response) {
                  var obj = JSON.parse(response);
                    if(obj.status == "success") {

                      jQuery("#saved_book_status").fadeIn(500);
                      document.getElementById("saved_book_status").innerHTML = " Your book has been assigned";
                      setTimeout(function() {jQuery("#saved_book_status").fadeOut(1500); },3000);

                    } else {
                      alert("wrong list of topics");
                    }
                }
        });
        
	});
});	