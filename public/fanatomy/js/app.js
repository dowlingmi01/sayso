var currentBreak = 1;

function preloadCssImages() {

    var allImgs = [];//new array for all the image urls
    var k = 0; //iterator for adding images
    var sheets = document.styleSheets;//array of stylesheets

    for(var i = 0; i<sheets.length; i++){//loop through each stylesheet
        var cssPile = '';//create large string of all css rules in sheet
        var csshref = (sheets[i].href) ? sheets[i].href : 'window.location.href';
        var baseURLarr = csshref.split('/');//split href at / to make array
        baseURLarr.pop();//remove file path from baseURL array
        var baseURL = baseURLarr.join('/');//create base url for the images in this sheet (css file's dir)
        if(baseURL!="") baseURL+='/'; //tack on a / if needed
        if(document.styleSheets[i].title === "parallax" && document.styleSheets[i].cssRules){//w3 and firefox, we only want our style sheet
            var thisSheetRules = document.styleSheets[i].cssRules; //w3
            for(var j = 0; j<thisSheetRules.length; j++){
                cssPile+= thisSheetRules[j].cssText;
            }
        }
        else {
            cssPile+= document.styleSheets[i].cssText;
        }

        //parse cssPile for image urls and load them into the DOM
        var imgUrls = cssPile.match(/[^\(]+\.(gif|jpg|jpeg|png)/g);//reg ex to get a string of between a "(" and a ".filename"
        var loaded = 0; //number of images loaded
        if(imgUrls != null && imgUrls.length>0 && imgUrls != ''){//loop array\
            var arr = jQuery.makeArray(imgUrls);//create array from regex obj
            jQuery(arr).each(function(){
                allImgs[k] = new Image(); //new img obj
                allImgs[k].src = (this.charAt(0) == '/' || this.match('http://')) ? this : baseURL + this;	//set src either absolute or rel to css dir

                $(allImgs[k]).load(function(){
                    loaded++;
                    updateProgress(parseInt((loaded/allImgs.length*100).toFixed(0)));
                });
                k++;
            });
        }
    }//loop
    return allImgs;
}

function updateProgress(a) {
    return $(".progress").text(a + "%"), 100 === a ? $(".preloader").fadeOut("slow", function () {
        return skrollr.init();
    }) : void 0
}

$(document).ready(function(){
    preloadCssImages();
    $(".next_message").on('click', function() {
        var destinationBreak = $(".break_" + currentBreak++),
            duration = 1000;

        if(destinationBreak.data("duration")) {
            duration = destinationBreak.data("duration");
        }
        $("html, body").animate({ scrollTop: destinationBreak.data("top") }, duration);
    });
})