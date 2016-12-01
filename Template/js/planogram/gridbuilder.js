var svg = '';
var dimensions = [200,200];
var toff = [0,5]; //negative text offset for shelf name

function setUpAreas(data){

    $.each(data, function(id,element){
        if (element.hasOwnProperty("TYPE")){
            //console.log(element['TYPE']);
        } else {
        	// MOD aka: parseJSON for coordinates
            var coords = $.parseJSON(element['COORDINATES']);
            if (coords.length == 1 && coords[0].hasOwnProperty('r')){

                var points = processCirclePoints(coords);
                svg += createCircle(element['OID'],points);
            }
            else {
                var points = processPolygonPoints(coords);
                svg += createPolygon(element['OID'], points );
                svg += printText(points['min'][0]-toff[0],points['min'][1]-toff[1], element['DESCRIPTION']);
            }
        }
    });
}

function createPolygon(id,points){

    var text = '<polygon data-oid="'+id+'" id="poly_'+id+'" points="'+points['points']+'" data-width="'+points['size'][0]+'" data-max="'+points['max'].join()+'" data-min="'+points['min'].join()+'" class="shelfElement"/>';
    //text +=  '<defs><linearGradient id="Gradient"><stop offset="0.9" stop-color="white" stop-opacity="1" /><stop offset="1" stop-color="white" stop-opacity="0" /></linearGradient><mask id="poly_'+id+'_mask"><rect x="'+points['min'][0]+'" y="'+points['min'][1]+'" width="'+(points['size'][0]-(2*initialOffset[0]))+'" height="100%" fill="url(#Gradient)"  /> </mask></defs>';
    text += '<defs><linearGradient id="Gradient'+id+'"><stop offset="0.8" stop-color="white" stop-opacity="1" /><stop offset="0.9" stop-color="white" stop-opacity="0" /></linearGradient><mask id="poly_'+id+'_mask"><polygon points="'+points['points']+'" fill="url(#Gradient'+id+')"/></mask><mask id="poly_'+id+'_mask_helper" fill="white"><polygon points="'+points['points']+'"/></mask></defs>'
    return text;
}

function createCircle(id, points){
    var text = '<circle data-oid="'+id+'" id="poly_'+id+'" cx="'+points['points'][1]+'" cy="'+points['points'][2]+'" r="'+points['points'][0]+'" data-width="'+points['size'][0]+'" data-max="'+points['max'].join()+'" data-min="'+points['min'].join()+'" class="shelfElement"/>';
    text += '<defs><linearGradient id="Gradient'+id+'"><stop offset="0.9" stop-color="white" stop-opacity="1" /><stop offset="1" stop-color="white" stop-opacity="0" /></linearGradient><mask id="poly_'+id+'_mask"><rect x="'+points['min'][0]+'" y="'+points['min'][1]+'" width="'+(points['size'][1]-initialOffset[0])+'" height="100%" fill="url(#Gradient'+id+')"/></mask><mask id="poly_'+id+'_mask_helper" fill="white"><rect x="'+points['min'][0]+'" y="'+points['min'][1]+'" width="'+(points['size'][1]-initialOffset[0])+'" height="100%"/></mask></defs>'
    return text;
}
function printText(x,y,text){
    return '<text x="'+x+'" y="'+y+'">'+text+'</text>';

}

function setUpDisplay(background, data){
    $("#VisualPlaceholder").empty();
    resetSVG();
    //Get the Background Image and stuff it into SVG
    setBackgroundImage(background);
    //Get your area data amd stuff it into SVG
    setUpAreas(data);
    //Wrap it in the svg tag
    completeSVG();
    //Place it in the DOM
    $("#VisualPlaceholder").html(svg);
    fillListsWithArticles();
}


//On document load, setup
/* MOD aka: moved to ExFace
$(document).ready(function(){
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
    svg = '<svg viewBox="0 0 '+dimensions[0]+' '+dimensions[1]+'" id="Planogram">'+svg+'</svg>';
}

function setBackgroundImage(background){
    if (background['width'] > dimensions[0]){dimensions[0]=background['width'];}
    if (background['height'] > dimensions[1]){dimensions[1]=background['height'];}
    svg += '<image width="'+background['width']+'" height="'+background['height']+'" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+background['src']+'"></image>';
}
//-----------------------------------------------------------------------------
// HELPER FUNCTIONS FOR GEOMETRY
//-----------------------------------------------------------------------------
function processPolygonPoints(coordinates){
    var points = "";
    var minx = 999999, miny = 999999;
    var maxx = 0,maxy = 0;
    $.each(coordinates, function(cID,coord){
        if (coord['x'] > maxx){maxx = coord['x'];}
        if (coord['y'] > maxy){maxy = coord['y'];}
        if (coord['x'] < minx){minx = coord['x'];}
        if (coord['y'] < miny){miny = coord['y'];}
        points += coord['x']+","+coord['y']+" ";
    });
    var width=maxx-minx;
    var height=maxy-miny;
    return {"points": points, "min":[minx,miny], "max":[maxx,maxy], "size":[width,height]}
}
function processCirclePoints(coordinates){
    var radius = coordinates[0]['r'];
    var cx = coordinates[0]['cx'];
    var cy = coordinates[0]['cy'];
    var isql = Math.round((Math.sqrt((radius*radius)/2))*100)/100;
    return {"points": [radius,cx,cy],"min":[cx-(isql), cy-(isql)], "max":[cx+(isql), cy+(isql)], "size":[isql*2,isql*2]};
}
//-----------------------------------------------------------------------------
// HELPER FUNCTIONS FOR DATA RETRIEVAL - FILLED WITH DEMO DATA
//-----------------------------------------------------------------------------
function getGridInfo(){
    var data = {
        "rows": [
            {
                "CODE": "HO1",
                "DESCRIPTION": "Stange oben links",
                "COORDINATES": [{'x':35,'y':40},{'x':180,'y':40},{'x':185,'y':220},{'x':35,'y':220}],
                "OID": "21",
                "SEQUENCE": "1",
                "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
                "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            },
            {
                "CODE": "HU1",
                "DESCRIPTION": "Stange unten links",
                "COORDINATES": [{'x':35,'y':280},{'x':335,'y':280},{'x':335,'y':830},{'x':35,'y':830}],
                "OID": "31",
                "SEQUENCE": "2",
                "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
                "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            },
            {
                "CODE": "B1-1",
                "DESCRIPTION": "Boden oben",
                "COORDINATES": [{'r':100,'cx':400,'cy':150}],
                "OID": "23",
                "SEQUENCE": "3",
                "VM_SHELF_MODEL__LABEL": "Fashion-Wand 15",
                "VM_SHELF_MODEL__VM_SHELF_TYPE__LABEL": "Wand"
            },
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
    return {'src':'./src/img/BackgroundImage.jpg', 'width': 542, 'height': 944};
}