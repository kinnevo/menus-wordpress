jQuery(document).ready( function() {
    jQuery("#js_nw_select_book").click( function() {
   		alert ("click");
   		document.getElementById("nw_selected_book").innerText= "dos";
    });

	jQuery("#js_nw_select_book").change(function(){
	   	alert ("change");
	});

	jQuery(".js_class_select_book").click( function() {
		selected_book_id = this.value;
		selected_book_title = this.text;
		document.getElementById("nw_selected_book_id").value = selected_book_id;		
		document.getElementById("nw_selected_book").innerHTML = selected_book_title;
	});


	jQuery( "#nw_reader").click( function() {
		select_book_options = document.getElementById("nw_welcome_2");
		select_agent_options = document.getElementById("nw_welcome_3");
		if ( this.checked ) {
			select_book_options.style.visibility="visible";
			select_agent_options.style.visibility="visible";
		} else {
			select_book_options.style.visibility="hidden";			
			select_agent_options.style.visibility="hidden";
		}
	});


});