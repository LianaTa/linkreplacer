
$(document).ready(function(){
  $("#link_replacer_form").submit(function(){
       $.ajax({
               type:"POST",
               url: "http://linkreplacer.com/replace.php",
               data:{
                     
                     "url_name":     $('#url_name').val()
                    },  
                success: function(html){
                    $("#link_replace_result").html(html);
                    
                   }
              });
            return false;
          });

});
