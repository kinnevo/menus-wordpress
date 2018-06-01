
	jQuery("#nw_select_agent").change(function(){
		user_id = document.getElementById("nw_select_agent").value
	    nonce = nw_select_agent.nw_select_agent_nonce;
		jQuery.ajax({
			type : "post",
			datatype : "json",
	        url : nw_select_agent.ajaxurl,
			data : { action: 'nw_select_agent', user_id : document.getElementById("nw_select_agent").value, 
					nonce : nonce
					},
            success: function(response) {
            	var obj = JSON.parse(response);
                if(obj.status == "success") {
                  	jQuery("#agent_table1").html(obj.text);
					document.getElementById("conversation_table2").style.display="none";
					document.getElementById("conversation_text3").style.display="none";
                } else {
                  alert("wrong user");
                }
            }
		});		
	});
