$(document).ready(function(){
  $('.grid').masonry({
    // options
    itemSelector: '.grid-item',
    columnWidth: '.grid-sizer',
    fitWidth:true,
    gutter:10,
    horizontalOrder:true,
    isAnimated:false,
    percentPosition:true
  });
  delayedMasonry();
});
function delayedMasonry(){
  setTimeout(function(){
    $('.grid').masonry();
  },1000);
}
$(window).bind('resize',function(){
  delayedMasonry();
});
