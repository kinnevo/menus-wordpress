
	// jQuery(".nw_select_conversation").click(function(){
	jQuery("#agent_table1").on('click', ".nw_select_conversation", function(){

		user_id = this.getAttribute("user");
		book_id = this.getAttribute("book");

	    nonce = nw_select_conversations.nw_select_conversations_nonce;
		jQuery.ajax({
			type : "post",
			datatype : "json",
	        url : nw_select_conversations.ajaxurl,
			data : { action: 'nw_select_conversations', user_id : user_id, book_id : book_id, 
					nonce : nonce
					},
            success: function(response) {
            	var obj = JSON.parse(response);
                if(obj.status == "success") {
                  	jQuery("#conversation_table2").html(obj.text);
					document.getElementById("conversation_table2").style.display="block";
					document.getElementById("conversation_text3").style.display="none";
                } else {
                  alert("wrong user");
                }
            }
		});		
	});
