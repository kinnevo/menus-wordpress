var chapter_items;


function copy_list(){
    listing = [];
    outer = [];
    var listing_ptr = document.getElementById("left-events").children;
    for (var i =  0 ; i < listing_ptr.length ; i++) {
        listing[i] = document.getElementById("left-events").children[i];
        outer[i] = document.getElementById("left-events").children[i].outerHTML;
    }
  }

function select_pre_populated_topics( topic ){
//    topic = document.getElementById("reloia_select_topics_book").value;
    nonce = reloia_list_pre_chapters.reloia_list_pre_chapters_nonce;
    jQuery.ajax({
        type : "post",
        datatype : "json",
        url : reloia_list_pre_chapters.ajaxurl,
        data : { action: "reloia_list_pre_chapters", nonce : nonce , topic : topic},
        success: function(response) {
            var obj = JSON.parse(response);
            if(obj.status == "success") {
                jQuery("#left-events").html(obj.topics);
                jQuery("#chapter_listings").html(obj.chapter_content);
//                chapter_items = obj.chapter_content;
                // jQuery("#chapter_info").html(obj.chapter_content_1);
                //copy_list();

                // clear the input field
                document.getElementById("reloia_seek_topics").value = "";
            } else {
                alert("AJAX: wrong list of topics");
            }
        }
    });    
}



jQuery("#list_pre_chapters").on( 'change', function(){
    select_pre_populated_topics( document.getElementById("reloia_select_topics_book").value );
});