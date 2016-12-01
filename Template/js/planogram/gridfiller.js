initialOffset = [5,12];
textLineHeight = 16;

function fillListsWithArticles(){
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
            area['offset'][1] = parseInt(area['offset'][1])+initialOffset[1];
            var x = parseInt(area['offset'][0])+initialOffset[0];
        }
        else {
            area['visible'] = false;
        }
        $.each(area['elements'], function(eindex,element){
            if(element && area['visible']){
                var text = element['ARTICLE_COLOR__STYLE__LABEL']+" "+element["ARTICLE_COLOR__COLOR__LABEL"];
                var oid = element['OID'];

                var y = area['offset'][1];
                var helpercoord = getHelperCoords(x,y);
                var altlength = (text.length*textLineHeight*0.4);
                listofElements +='<g>';
                listofElements +='<rect class="helperRect" data-helperfor="'+oid+'" x="'+helpercoord[0]+'" y="'+helpercoord[1]+'" height="'+textLineHeight+'" width="'+(altlength > width ? altlength : width)+'" mask="url(#poly_'+i+'_mask_helper)"/>';
                listofElements +='<text class="dragElement" data-shelf-oid="'+i+'" data-oid="'+oid+'" data-origcoord="'+x+','+y+'" x="'+x+'" y="'+y+'" fulltext="'+text+'" mask="url(#poly_'+i+'_mask)">';
                listofElements +=text+'</text>';
                listofElements +='</g>';
                area['offset'][1] = parseInt(area['offset'][1])+textLineHeight;
            }
        });
    });
    $("#VisualPlaceholder").html(svg['wrapper']+svg['content']+listofElements+"</svg>");
    initializeDragFunctionality();

}
function getHelperCoords(x,y){
    return [x-initialOffset[0], y-initialOffset[1]];
}
function getSVGForChange(){
    var svg = $("#Planogram");
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

}