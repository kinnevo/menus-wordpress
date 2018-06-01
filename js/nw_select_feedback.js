
	jQuery("#conversation_table2").on('click', ".nw_select_feedback", function(){

		user_id = this.getAttribute("user");
		book_id = this.getAttribute("book");
		ask_info = this.getAttribute("ask_info");

	    nonce = nw_select_feedback.nw_select_feedback_nonce;
		jQuery.ajax({
			type : "post",
			datatype : "json",
	        url : nw_select_feedback.ajaxurl,
			data : { action: 'nw_select_feedback', user_id : user_id, book_id : book_id, ask_info : ask_info,
					nonce : nonce
					},
            success: function(response) {
            	var obj = JSON.parse(response);
                if(obj.status == "success") {
                  	jQuery("#nw_conversation_area").html(obj.text);
                  	document.getElementById("nw_conversation_area").attributes["ask_info"] = obj.ask_info;
					document.getElementById("conversation_text3").style.display="block";
//                  	jQuery("#nw_conversation_area_id").ask_info = obj.ask_info;
//                  	document.getElementById(nw_conversation_area).setAttribute("ask_info", obj.ask_info);
//                  	jQuery("#nw_conversation_area_id").value = obj.ask_info;
                } else {
                	alert("wrong user");
                }
            }
		});		
	});
