
jQuery("#nw_save_conversation").click(function(){


//jQuery("#conversation_table2").on('click', ".nw_save_conversation", function(){

	description = document.getElementById("nw_conversation_area").value;
	ask_info = document.getElementById("nw_conversation_area").attributes["ask_info"];
    nonce = nw_save_conversation.nw_save_conversation_nonce;

	jQuery.ajax({
		type : "post",
		datatype : "json",
        url : nw_save_conversation.ajaxurl,
		data : { action: 'nw_save_conversation', description : description,  ask_info : ask_info,
				nonce : nonce
				},
        success: function(response) {
        	var obj = JSON.parse(response);
            if(obj.status == "success") {

                jQuery("#nw_status_conversation").fadeIn(500);
                document.getElementById("nw_status_conversation").innerHTML = "Your conversation has been saved";
                setTimeout(function() {jQuery("#nw_status_conversation").fadeOut(1500); },3000);

            } else {
				alert("wrong user");
            }
        }
	});		


});