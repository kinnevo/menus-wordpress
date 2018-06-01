jQuery(document).ready( function() {


// Get the modal
if ( document.getElementById('myModal') != null ){
   var modal = document.getElementById('myModal');

   // Get the button that opens the modal
   var btn = document.getElementById("myBtn");

   // Get the <span> element that closes the modal
   var span = document.getElementsByClassName("close")[0];

   // When the user clicks the button, open the modal 
   btn.onclick = function() {
       modal.style.display = "block";
   }

   // When the user clicks on <span> (x), close the modal
   span.onclick = function() {
       modal.style.display = "none";
   }

   // When the user clicks anywhere outside of the modal, close it
   window.onclick = function(event) {
       if (event.target == modal) {
           modal.style.display = "none";
       }
   }
}

   jQuery("#ask_info").on('click', function() {
      post_id = jQuery(this).attr("data-post_id");
      nonce = jQuery(this).attr("data-nonce");
      ask_info_id = jQuery(this).attr("ask_info_id");
      desc_ask_info = document.getElementById("desc_ask_info").value;

      jQuery.ajax(
         {
            type : "post",
            dataType : "json",
            url : ask_info.ajaxurl,
            data : {action: "ask_info", nonce: nonce, post_id : post_id, ask_info_id : ask_info_id, desc_ask_info : desc_ask_info},
            success: function(response) {
               if(response.type == "success") {
                //  jQuery("#ask_info").html(response.more_info1)
                     modal.style.display = "none";

               } else {
                  alert("Your ask_info request could not be processed")
               }
            }
         }
      )   

   })

})
