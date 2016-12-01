initialOffset = [5,12];
textLineHeight = 16;
// here we define what should be displayed per list entry.
// every printed text line is one array
// every entry in the line is one object
displayElements =   [
                        [   
                        	{'type':'param', 'val':"ARTICLE_COLOR__STYLE__LABEL"}
                        ],
                        [
                            {'type':'param', 'val':"ARTICLE_COLOR__COLOR__LABEL"}
                        ]
                    ];


function fillListsWithArticles(firstrun){
    var articles = retrieveArticleList();

    var svg = getSVGForChange();
    var listofElements = "";
    $.each(articles, function(i,area){
        var svg_area = svg['element'].find("#poly_"+i);
        if (svg_area.attr("data-min")){
            var min = svg_area.attr("data-min"),
                max = svg_area.attr("data-max"),
                width = parseInt(svg_area.attr("data-width"));
            area['offset'] = min.split(",");
            area['offset'][1] = parseInt(area['offset'][1]);
            var x = parseInt(area['offset'][0]);
        }
        else {
            area['visible'] = false;
        }
        $.each(area['elements'], function(eindex,element){
            if(element && area['visible']){
                var text = element['ARTICLE_COLOR__STYLE__LABEL']+" "+element["ARTICLE_COLOR__COLOR__LABEL"];
                var oid = element['OID'];
                var lineAt = 0;
                var y = area['offset'][1];
                var altlength = (text.length*textLineHeight*0.4);
                //wrapper element g
                listofElements +='<g class="dragElement" transform="translate('+x+','+y+')" data-x="'+x+'"  data-y="'+y+'" data-origcoord="'+x+','+y+'" data-shelf-oid="'+i+'" data-oid="'+oid+'" mask="url(#poly_'+i+'_mask_helper)">';
                //helper for background is calculated by line size
                var helperheight = textLineHeight*displayElements.length;
                listofElements +='<rect class="helperRect" x="0" y="0" height="'+helperheight+'" width="'+(altlength > width ? altlength : width)+'"/>';
                //go through list of elements
                $.each(displayElements, function(entryi, line){
                    listofElements +='<text data-textfor="'+oid+'" x="'+initialOffset[0]+'" y="'+(parseInt(lineAt*textLineHeight)+parseInt(initialOffset[1]))+'"';
                    var text = '';
                    $.each(line, function(elindex, lineelement){
                       if (lineelement.type == 'param'){
                           text += element[lineelement.val];
                       }
                       else {
                           text += lineelement.val;
                       }
                    });
                    listofElements += ' fulltext="'+text+'" mask="url(#poly_'+i+'_mask)">'+text+'</text>';
                    area['offset'][1] = parseInt(area['offset'][1])+textLineHeight;
                    lineAt += 1;
                });


                //Close group
                listofElements +='</g>';


            }
        });
    });
    $("#VisualPlaceholder").html(svg['wrapper']+svg['content']+'<g class="dragElementList">'+listofElements+'</g>'+"</svg>");
    if (firstrun){initializeDragFunctionality()};

}

function getSVGForChange(){

    //delete old elements for drag
    var dragElements = document.getElementsByClassName("dragElementList");
    $(document).add(".dragElement").off();
    if (dragElements.length > 0){
        var parent = dragElements[0].parentNode;
        parent.removeChild(dragElements[0]);
    }
    var svg = $("#Planogram").clone(false);
    var wrapperelement = '<svg ';
    var content = svg.html();

    $(svg).each(function() {
        $.each(this.attributes, function() {
            if(this.specified) {
                wrapperelement += this.name+'="'+this.value+'" ';
            }
        });
    });
    wrapperelement += '>';
    return {'element': svg, 'wrapper': wrapperelement, 'content':content}
}

function retrieveArticleList(){
    var articles = getArticles();
    var listOfArticles = {};
    $.each(articles['rows'], function(i,article){
        var listIndex = article['VM_SHELF_MODEL_POSITION__OID'];
        var elementKey = article['OID']; //could also be index i for numeric counting
        if (!listOfArticles.hasOwnProperty(listIndex)){
            listOfArticles[listIndex] = {'offset': [0,0], 'visible':true,'elements':[]};
        }
        listOfArticles[listIndex]['elements'][elementKey] = article;
    });
    return listOfArticles;
}

//-----------------------------------------------------------------------------
// HELPER FUNCTIONS FOR DATA RETRIEVAL - FILLED WITH DEMO DATA
//-----------------------------------------------------------------------------
/* MOD aka: moved to ExFace
function getArticles(){
    return {
        "rows": [
            {
                "VM_SHELF_MODEL_POSITION__OID": "21",
                "OID": "31",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "HO1, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4062356",
                "ARTICLE_COLOR__STYLE__LABEL": "231 Anorak",
                "ARTICLE_COLOR__COLOR__LABEL": "944 rot",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "1",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Pampolina"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "21",
                "OID": "21",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "HO1, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4062358",
                "ARTICLE_COLOR__STYLE__LABEL": "231 Anorak",
                "ARTICLE_COLOR__COLOR__LABEL": "946 marine blau",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "1",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Pampolina"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "23",
                "OID": "23",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "B1-1, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4067354",
                "ARTICLE_COLOR__STYLE__LABEL": "241 Baby Jeans aus Baumwolle",
                "ARTICLE_COLOR__COLOR__LABEL": "955 blue stone",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "3",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Vertbaudet"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "24",
                "OID": "24",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "B1-2, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4067356",
                "ARTICLE_COLOR__STYLE__LABEL": "242 Jungenhose mit verstellbarer Beinlänge",
                "ARTICLE_COLOR__COLOR__LABEL": "955 blue stone",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "4",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Vertbaudet"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "25",
                "OID": "25",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "B1-3, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4067355",
                "ARTICLE_COLOR__STYLE__LABEL": "242 Jungenhose mit verstellbarer Beinlänge",
                "ARTICLE_COLOR__COLOR__LABEL": "956 camel",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "10",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Vertbaudet"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "31",
                "OID": "223",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "HO2, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4062355",
                "ARTICLE_COLOR__STYLE__LABEL": "230 Anorak gestreift",
                "ARTICLE_COLOR__COLOR__LABEL": "943 dunkelblau",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "17",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Pampolina"
            },
            {
                "VM_SHELF_MODEL_POSITION__OID": "31",
                "OID": "212",
                "VM_SHELF__LABEL": "Baby HW 2016",
                "VM_SHELF_MODEL_POSITION__LABEL": "HO2, Fashion-Wand 15",
                "ARTICLE_COLOR__OID": "4062357",
                "ARTICLE_COLOR__STYLE__LABEL": "231 Anorak",
                "ARTICLE_COLOR__COLOR__LABEL": "945 helles rosa",
                "VM_SHELF_MODEL_POSITION__SEQUENCE": "17",
                "ARTICLE_COLOR__STYLE__BRAND__LABEL": "Pampolina"
            }
        ],
        "total": "7",
        "footer": []
    };

}*/