jQuery(document).ready( function() {

	function refresh_stats( ){
		if (!document.getElementById("nw_select_user_for_stats") )
			alert ( "There is not active readers available in the system");
		else {
			user_id = document.getElementById("nw_select_user_for_stats").value
			nonce = nw_read_stats.nw_read_stats_nonce;
			jQuery.ajax({
				type : "post",
				datatype : "json",
				url : nw_read_stats.ajaxurl,
				data : { action: 'nw_read_stats', user_id : document.getElementById("nw_select_user_for_stats").value, 
						nonce : nonce
						},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == "success") {
						jQuery("#poststuff").html(obj.text);
						// alert("success");
					} else {
					  alert("wrong user");
					}
				}	
			});		
		}
	}


	jQuery("#nw_select_user_for_stats").change(function(){
		refresh_stats( );
	});

	jQuery("#nw_refresh_stats").click(function(){
		refresh_stats( );
	});

});