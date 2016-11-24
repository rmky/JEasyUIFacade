$(document).ready(function(){

    $("body").on('click', '#VisualPlaceholder svg .shelfElement', function(){
        alert("My name is "+$(this).attr("id"));
    });

});

function reactToClick(){

}