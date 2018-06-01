jQuery(document).ready( function() {

   jQuery("#user_likes").on('click', function() {
      post_id = jQuery(this).attr("data-post_id");
      nonce = jQuery(this).attr("data-nonce");
      current_user = jQuery(this).attr("current_user");
      book = jQuery(this).attr("book");
      version = jQuery(this).attr("version");

      jQuery.ajax(
         {
            type : "post",
            dataType : "json",
            url : my_likes.ajaxurl,
            data : {action: "my_likes", nonce: nonce, post_id : post_id, current_user : current_user, book : book, version : version},
            success: function(response) {
               if(response.type == "success") {
                  jQuery("#likes_status").html(response.like1)
               } else {
                  alert("Your like can not be set")
               }
            }
         }
      )   

   })
})
