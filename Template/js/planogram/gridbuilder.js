var svg = '';
var dimensions = [200,200];
var overlay ='';
// MOD aka
var toff = [-2,-12]; //negative text offset for shelf name

function setUpAreas(data){

    $.each(data, function(id,element){
        if (element.hasOwnProperty("TYPE")){
            console.log(element['TYPE']);
        } else {
        	// MOD aka
        	var coordinates = $.parseJSON(element['COORDINATES']);
            var positions = getWidthAndHeight(coordinates);
            svg += createPolygon(element['OID'],coordinates);
            console.log(positions);
            svg += printText(positions[0]-toff[0],positions[1]-toff[1], element['CODE']);
            overlay += '<div style="left:'+positions[0]+'px;top:'+positions[1]+'px;width:'+positions[2]+'px;height:'+positions[3]+'px;" id="list_'+element['OID']+'" class="elementList"></div>';
        }
    });
}


function createPolygon(id,params){

    var points = "";
    $.each(params, function(cID,coord){
        points += coord['x']+","+coord['y']+" ";
    });
    return '<polygon id="'+id+'" points="'+points+'" class="shelfElement"/>';
}


function printText(x,y,text){
    return '<text x="'+x+'" y="'+y+'" fill="white">'+text+'</text>';
}

function setUpDisplay(background, data){
    //Get the Background Image and stuff it into SVG
    setBackgroundImage(background);
    //Get your area data amd stuff it into SVG
    setUpAreas(data);
    //Wrap it in the svg tag
    completeSVG();
    //Place it in the DOM
    $("#VisualPlaceholder").html(svg+overlay).width(dimensions[0]).height(dimensions[1]);
}

//On document load, setup
/* MOVED to ExFace
 * $(document).ready(function(){
    //Retrieve all data from external sources - demo in this case
    var background = getBackgroundImage();
    var data = getGridInfo();
    //this is where the magic happens
    setUpDisplay(background, data['rows']);
});*/
//-----------------------------------------------------------------------------
//HELPER FUNCTIONS TO BUILD SVG AREA - THIS COULD BE PRETTIER
//-----------------------------------------------------------------------------
function resetSVG(){
    svg = '';
}
function completeSVG(){
    //Dimensions is biggest know x and y value from all the values including background image
    svg = '<svg width="'+dimensions[0]+'" height="'+dimensions[1]+'">'+svg+'</svg>';
}

function setBackgroundImage(background){
    if (background['width'] > dimensions[0]){dimensions[0]=background['width'];}
    if (background['height'] > dimensions[1]){dimensions[1]=background['height'];}
    svg += '<image width="'+background['width']+'" height="'+background['height']+'" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+background['src']+'"></image>';
}
//-----------------------------------------------------------------------------
// HELPER FUNCTIONS FOR GEOMETRY
//-----------------------------------------------------------------------------
function getWidthAndHeight(params){
    var maxx = 0, maxy = 0;
    var minx = 9999999, miny = 9999999;
    $.each(params, function(cID,coord){
        if (coord['x'] < minx){minx =coord['x'];}
        if (coord['y'] < miny){miny =coord['y'];}
        if (coord['x'] > maxx){maxx =coord['x'];}
        if (coord['y'] > maxy){maxy =coord['y'];}
    });
    var width=maxx-minx;
    var height=maxy-miny;
    return [minx, miny,width,height];
}
//-----------------------------------------------------------------------------
// HELPER FUNCTIONS FOR DATA RETRIEVAL - FILLED WITH DEMO DATA
//-----------------------------------------------------------------------------
/*function getGridInfo(){
    var data = {
        "rows": [
            {
                "CODE": "HO1",
                "DESCRIPTION": "Stange oben links",
                "COORDINATES": [{'x':35,'y':40},{'x':135,'y':40},{'x':135,'y':220},{'x':35,'y':220}],
                "OID": "21",
                "SEQUENCE": "1",
                "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
                "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            },
            {
                "CODE": "HU1",
                "DESCRIPTION": "Stange unten links",
                "COORDINATES": [{'x':35,'y':280},{'x':135,'y':280},{'x':135,'y':430},{'x':35,'y':430}],
                "OID": "31",
                "SEQUENCE": "2",
                "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
                "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            },
            //{
            //    "CODE": "B1-1",
            //    "DESCRIPTION": "Boden oben",
            //    "COORDINATES": [],
            //    "OID": "23",
            //    "SEQUENCE": "3",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B1-2",
            //    "DESCRIPTION": "Boden oben",
            //    "COORDINATES": [],
            //    "OID": "24",
            //    "SEQUENCE": "4",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B2-1",
            //    "DESCRIPTION": "Boden mitte",
            //    "COORDINATES": [],
            //    "OID": "27",
            //    "SEQUENCE": "5",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B2-2",
            //    "DESCRIPTION": "Boden mitte",
            //    "COORDINATES": [],
            //    "OID": "28",
            //    "SEQUENCE": "6",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU2",
            //    "DESCRIPTION": "Stange unten mitte",
            //    "COORDINATES": [],
            //    "OID": "32",
            //    "SEQUENCE": "7",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU3",
            //    "DESCRIPTION": "Stange unten mitte",
            //    "COORDINATES": [],
            //    "OID": "33",
            //    "SEQUENCE": "8",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU4",
            //    "DESCRIPTION": "Stange unten mitte",
            //    "COORDINATES": [],
            //    "OID": "34",
            //    "SEQUENCE": "9",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B1-3",
            //    "DESCRIPTION": "Boden oben",
            //    "COORDINATES": [],
            //    "OID": "25",
            //    "SEQUENCE": "10",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B1-4",
            //    "DESCRIPTION": "Boden oben",
            //    "COORDINATES": [],
            //    "OID": "26",
            //    "SEQUENCE": "11",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B2-3",
            //    "DESCRIPTION": "Boden mitte",
            //    "COORDINATES": [],
            //    "OID": "29",
            //    "SEQUENCE": "12",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B2-4",
            //    "DESCRIPTION": "Boden mitte",
            //    "COORDINATES": [],
            //    "OID": "30",
            //    "SEQUENCE": "13",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU5",
            //    "DESCRIPTION": "Stange unten mitte",
            //    "COORDINATES": [],
            //    "OID": "35",
            //    "SEQUENCE": "14",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU6",
            //    "DESCRIPTION": "Stange unten mitte",
            //     "COORDINATES": [],
            //    "OID": "36",
            //    "SEQUENCE": "15",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU7",
            //    "DESCRIPTION": "Stange unten mitte",
            //    "COORDINATES": [],
            //    "OID": "37",
            //    "SEQUENCE": "16",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HO2",
            //    "DESCRIPTION": "Stange oben rechts",
            //    "COORDINATES": [],
            //    "OID": "22",
            //    "SEQUENCE": "17",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "HU8",
            //    "DESCRIPTION": "Stange unten rechts",
            //    "COORDINATES": [],
            //    "OID": "38",
            //    "SEQUENCE": "18",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //},
            //{
            //    "CODE": "B3",
            //    "DESCRIPTION": "Boden unten",
            //    "COORDINATES": [],
            //    "OID": "39",
            //    "SEQUENCE": "19",
            //    "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
            //    "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            //}
        ],
        "total": "19",
        "footer": []
    };

    return data;
}
function getBackgroundImage(){
    return {'src':'./src/img/FashionRack.png', 'width': 587, 'height': 528};
}*/