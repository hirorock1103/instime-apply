$(function(){
    $('#viewer div img').each(function(i){
        $(this).css({opacity:'0'}).attr('id','view' + (i + 1).toString());
        $('#viewer div img:first').css({opacity:'1',zIndex:'99'});
    });
 
    $('#viewer ul li').click(function(){
        var connectCont = $('#viewer ul li').index(this);
        var showCont = connectCont+1;
 
        $('#viewer div img#view' + (showCont)).siblings().stop().animate({opacity:'0'},1000);
        $('#viewer div img#view' + (showCont)).stop().animate({opacity:'1'},1000);
 
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
    });
 
    $('#viewer ul li:not(.active)').hover(function(){
        $(this).stop().animate({opacity:'1'},200);
    },function(){
        $(this).stop().animate({opacity:'0.5'},200);
    });
 
    $('#viewer ul li').css({opacity:'0.5'});
    $('#viewer ul li:first').addClass('active');
});