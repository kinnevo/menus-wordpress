jQuery(document).ready( function() {

   jQuery("#user_dontcare").on( 'click', function() {
      post_id = jQuery(this).attr("data-post_id");
      nonce = jQuery(this).attr("data-nonce");
      current_user = jQuery(this).attr("current_user");
      book = jQuery(this).attr("book");
      version = jQuery(this).attr("version");

      jQuery.ajax(
         {
            type : "post",
            dataType : "json",
            url : my_dontcare.ajaxurl,
            data : {action: "my_dontcare", nonce: nonce, post_id : post_id, current_user : current_user, book : book, version : version},
            success: function(response) {
               if(response.type == "success") {
                  jQuery("#dontcare_status").html(response.dontcare1)
               } else {
                  alert("Your dont care could not be added")
               }
            }
         }
      )   

   })

});
