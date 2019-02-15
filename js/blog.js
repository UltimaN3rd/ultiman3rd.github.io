var windowWidth = $(window).width();
var blogcontent;

function setBlogContent(blogPostToFetch){
 var xhttp = new XMLHttpRequest();
 xhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
    blogcontent.innerHTML = this.responseText;
   }
 };
 xhttp.open("GET",blogPostToFetch+".php", true);
 xhttp.send();
 window.location=window.location.pathname+'#'+blogPostToFetch;
}

$(document).ready(function() {
 windowWidth = $(window).width();
 blogcontent = document.getElementById('blogcontent');
 $(".hidden").removeClass("hidden").slideDown();
 $(".showhide").each(function(){
  $(this).addClass("showing");
 });
 $(".showhide").click(function(){
  var obj = $(this).next();
  if($(this).hasClass("showing")){
   $(this).removeClass("showing").addClass("hiding");
   if(!$(obj).hasClass("hidden")){
    $(obj).addClass("hidden").slideUp();
   }
  }
  else if($(this).hasClass("hiding")) {
   $(this).addClass("showing").removeClass("hiding");
   if($(obj).hasClass("hidden")){
    $(obj).removeClass("hidden").slideDown();
   }
  }
 });
 $(".blogpostlink").each(function(){
  this.onclick = function(){setBlogContent(this.id);};
 });
 if(window.location.hash){
  setBlogContent(window.location.hash.substr(1));
 } else {
  setBlogContent("newsletters/2018-10");
 }
});
