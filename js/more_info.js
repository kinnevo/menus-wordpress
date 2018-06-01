jQuery(document).ready( function() {

   jQuery("#user_more_info").on('click', function() {
      
      post_id = jQuery(this).attr("data-post_id");
      nonce = jQuery(this).attr("data-nonce");
      current_user = jQuery(this).attr("current_user");
      book = jQuery(this).attr("book");
      version = jQuery(this).attr("version");

      jQuery.ajax(
         {
            type : "post",
            dataType : "json",
            url : my_more_info.ajaxurl,
            data : {action: "my_more_info", nonce: nonce, post_id : post_id, current_user : current_user, book : book, version : version},
            success: function(response) {
               if(response.type == "success") {
                  jQuery("#user_more_info").html(response.more_info1)
               } else {
                  alert("Your more info request could not be processed")
               }
            }
         }
      )   

   })

})
