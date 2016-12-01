$(document).ready(function(){

    $("body").on('click', '#VisualPlaceholder svg .shelfElement', function(){
        alert("My name is "+$(this).attr("id"));
    });

    $("#Refresh").on("click", function(){
        var background = getBackgroundImage();
        var data = getGridInfo();
        //this is where the magic happens
        setUpDisplay(background, data['rows']);

    });
    $(".dragElement, .externalDrop").click(function(event){
       callClickEventForArticle(this);
    });

});

//------------------------------------------------------------
// Click functions
//------------------------------------------------------------
function callClickEventForArticle(target){
    var sourceshelf = $(target).attr("data-shelf-oid");
    var oid = $(target).attr("data-oid");
    var fulltext = $(target).attr("fulltext");
    alert('Element with ID ' + oid + ' - "'+fulltext+'" from Shelf ' + sourceshelf + " was clicked - Let's display pretty info");
}

//------------------------------------------------------------
// Drag & Drop Functions
//------------------------------------------------------------
function initializeDragFunctionality(){
    interact.on('dragend', dragEnd);
    interact.on('dragmove', dragMove);
    interact('.dragElement').draggables({max: 2});
    interact('.externalDrop').draggables({max: 2});
    interact('.shelfElement').dropzone({
        // only accept elements matching this CSS selector
        accept: '.dragElement, .externalDrop',
        overlap: 0.2,
        // listen for drop related events:
        ondropactivate: function (event) {
            // add active dropzone feedback
            event.target.classList.add('drop-active');
        },
        ondragenter: onDragEnter,
        ondragleave: onDragLeave,
        ondrop:onDropDraggable,
        ondropdeactivate: onDropDeactivate
    });
}

function dragMove(e) {
    var target = e.target;
    var oid = $(target).attr("data-oid");
    if(!$(target).attr("data-mask")){
        $(target).attr("data-mask", $(target).attr("mask")).attr("mask","");
        var helper = $(".helperRect[data-helperfor='" + oid + "']");
        helper.attr("data-mask", helper.attr("mask")).attr("mask","");
    }
    if (isSVGElement(target)) {
        var oid = $(target).attr("data-oid");
        var x = parseInt($(target).attr("x"))+e.dx;
        var y = parseInt($(target).attr("y"))+e.dy;
        var helperCoords = getHelperCoords(x,y);
        $(".helperRect[data-helperfor='"+oid+"']").attr("x",helperCoords[0] ).attr("y", helperCoords[1]);
        $(target).attr("x", x).attr("y", y);
    } else {
        if (!$(target).attr("data-origcoord")){
            $(target).attr("data-origcoord", "["+$(target).offset().left+","+$(target).offset().top+"]")
        }
        target.style.left = parseInt($(target).offset().left) + e.dx + 'px';
        target.style.top  = parseInt($(target).offset().top)  + e.dy + 'px';
    }
    return;
}
function dragEnd(e) {

    var target = e.target;

    //if element is not on any drop point return it to original location
    if (target.classList.value.indexOf("can-drop")===-1) {
        resetElement(target);
    }
    return false;
}
// In the future that should also reset the helper
function resetElement(target){
    var coords_original = $(target).attr("data-origcoord").split(",");
    var x = parseInt(coords_original[0]);
    var y = parseInt(coords_original[1]);
    var oid = $(target).attr("data-oid");
    if (isSVGElement(target)) {
        $(target).attr("mask", $(target).attr("data-mask")).removeAttr("data-mask");
        var helper = $(".helperRect[data-helperfor='" + oid + "']");
        helper.attr("mask", helper.attr("data-mask")).removeAttr("data-mask");
        var helperCoords = getHelperCoords(x, y);
        helper.attr("x", helperCoords[0]).attr("y", helperCoords[1]);
        $(target).attr("x", x).attr("y", y);
    }else {
        target.style.left = x + 'px';
        target.style.top  = y + 'px';
    }
}

function onDropDraggable(event) {
    var draggableItemShelf = $(event.relatedTarget).attr("data-shelf-oid");
    var draggableOID = $(event.relatedTarget).attr("data-oid");
    var enteredItemShelf = $(event.target).attr("data-oid");
    $(event.target).addClass(".dropped");
    if (draggableItemShelf == enteredItemShelf) {
        resetElement(event.relatedTarget);
        alert("Dropped in same shelf - nothing is accomplished");
        return;
    }
    else {
        successfulDragAction(draggableOID,draggableItemShelf,enteredItemShelf);
        $(event.target).removeClass(".dropped");
        return;
    }
}
function successfulDragAction(oid,sourceshelf,targetshelf){
    alert("Element with ID " + oid + " from Shelf " + sourceshelf + " was dropped in Shelf " + targetshelf);
    return;
}

function onDragEnter(event) {
        var draggableElement = event.relatedTarget,
            dropzoneElement = event.target;
        dropzoneElement.classList.add('drop-target');
        draggableElement.classList.add('can-drop');
}
function onDragLeave(event){
    event.target.classList.remove('drop-target');
    event.relatedTarget.classList.remove('can-drop');
}

function onDropDeactivate(event){
    // remove active dropzone feedback
    event.target.classList.remove('drop-active');
    event.target.classList.remove('drop-target');
}

function isSVGElement(element){
    return 'SVGElement' in window && element instanceof SVGElement;
    return false;
}