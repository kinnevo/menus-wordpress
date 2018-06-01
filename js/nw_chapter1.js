jQuery(document).ready( function() {

  jQuery("#js_nw_save_chapter1").click( function() {

   if (document.getElementById("nw_chapter_title").value == ""){
    alert( "Empty Chapter Name");
    return;
  }

   if (document.getElementById("nw_chapter_description").value == ""){
    alert("Empty Description");
    return;
  }

   var $list = jQuery('#right-events div');
   var chapter = [];
   var chapter_item = [];
   var chapter_draft = {};

   // once you have $list you can do whatever you want

   var ControlCnt = $list.length;
   // Now loop through list of controls
   $list.each( function(index) {

       var id = this.attributes[0].value;
       var drag_parent = this.attributes[2].value;
       var drag_menu_order = this.attributes[3].value;
       var category = this.attributes[4].value;

       chapter_item['id'] = id;
       chapter_item['drag_parent'] = drag_parent;
       chapter_item['drag_menu_order'] = index;  //drag_menu_order;
       chapter_item['drag_category'] = category ;

       var bb = { id:id, drag_parent:drag_parent,  drag_menu_order: index, drag_category:category} ;
       chapter.push( bb );
       // debugging support ---         <p id='status' class='hidden'>Status Text</p>
       // console.log( "ID: " + id + " " + drag_parent + " " + drag_menu_order + " " + index);
   });
   chapter = JSON.stringify(chapter)
   // console.log(chapter);
   // document.getElementById("status").innerHTML = chapter;

   post_list = chapter;
   chapter_id = document.getElementById("nw_chapter_id").value; 
   chapter_title = document.getElementById("nw_chapter_title").value;
   chapter_description = document.getElementById("nw_chapter_description").value;
//   chapter_category = Number(document.getElementById("nw_chapter_category").value) ;
  var e = document.getElementById("reloia_select_topics");
  chapter_category = e.options[e.selectedIndex].value;

   nonce = nw_save_chapter1.nw_profile_nonce;
   dataType = "json";
   jQuery.ajax({
         type : "post",
         dataType : "json",
         url : nw_save_chapter1.ajaxurl,
         data : {action: "nw_save_chapter1", post_list : post_list, chapter_id : chapter_id,  nonce: nonce, chapter_title: chapter_title,
          chapter_description:chapter_description, chapter_category: chapter_category},
         success: function(response) {
            // var obj = JSON.parse(response);
            if(response.status == "success") {
                jQuery("#status1").fadeIn(500);
                document.getElementById("status1").innerHTML = "Your chapter has been saved";

   // it is used as a way to avoid multiple savings of the new book

                document.getElementById("nw_chapter_id").value = response.chapter_id; 
                setTimeout(function() {jQuery("#status1").fadeOut(1500); },3000);
            } else {
               alert("Your chapter can not be saved NOW");
            }
         }
      })
   });

   jQuery("#js_nw_preview_chapter1").click( function() {
      alert("Preview Chapter");
   });


})
