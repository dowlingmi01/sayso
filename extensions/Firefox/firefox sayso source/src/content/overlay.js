var com = com || {};
com.kynetx = com.kynetx || {};
var domains_loaded = false;
var kynetx_runtime_url = "http://init.kobj.net/js/shared/kobj-static.js";

            /*
                a239x21 Say.So Star Bar (Production)
                a239x22 Say.So QA
                a239x18 Say.So DEV
                a239x20 Say.So LOCAL
             * 
             * testing: a8x15
             */

com.kynetx.a239x18 = {
    //edit following two lines only, not one above!
    label:"Say.So Dev Hamza Test",
    appid:'a239x18',
    full_runtime: "",
    enabled:true,
    domains:[],
    onLoad: function() {
        try {
//             alert("loading")
            // initialization code
            this.initialized = true;
            if (document.getElementById("kynetx-a239x18-toggle").getAttribute("checked") == "false") {
                com.kynetx.a239x18.enabled = false;
            }
            //this.strings = document.getElementById("kynetx-strings");
            var appcontent = document.getElementById("appcontent");   // browser
            //alert('got doc');
            appcontent.addEventListener("DOMContentLoaded", com.kynetx.a239x18.onPageReady, true);
            //alert('added dom listener');
            //lookup domains
            com.kynetx.a239x18.refreshDomainList();
            com.kynetx.a239x18.getRuntime();
            //alert('domain request sent!');
        } catch(e) {
            errorStack(e);
        }

    },
    getRuntime: function(cb) {
//      alert("Getting Domain List");

        var req = new XMLHttpRequest();
        req.open('GET', kynetx_runtime_url, true);
        //alert("Calling " + "http://init.kobj.net/js/dispatch/"+com.kynetx.a239x18.appid+"?cachebust="+(new Date().getTime()));

        req.onreadystatechange = function (aEvt) {
            if (req.readyState == 4) {
                if (req.status == 200) {
                    com.kynetx.a239x18.full_runtime = req.responseText;
                } else {
//		  	alert("Error loading page\n");
                }
            }

        };
        req.send(null);
    },
    refreshDomainList: function(cb) {
//      alert("Getting Domain List");

        var req = new XMLHttpRequest();
        req.open('GET', "http://init.kobj.net/js/dispatch/" + com.kynetx.a239x18.appid + "?cachebust=" + (new Date().getTime()), true);
        //alert("Calling " + "http://init.kobj.net/js/dispatch/"+com.kynetx.a239x18.appid+"?cachebust="+(new Date().getTime()));

        req.onreadystatechange = function (aEvt) {
            if (req.readyState == 4) {
                if (req.status == 200) {
                    var nativeJSON = Components.classes["@mozilla.org/dom/json;1"].createInstance(Components.interfaces.nsIJSON);
                    var dispatchData = nativeJSON.decode(req.responseText);
                    //clear existing list
                    com.kynetx.a239x18.domains = [];
                    for (i in dispatchData[com.kynetx.a239x18.appid]) {
                        domain = dispatchData[com.kynetx.a239x18.appid][i];
                        //            alert("RETURNED DOMAIN " + domain);
                        //alert(domain);
                        com.kynetx.a239x18.domains.push(domain);
                    }
                    domains_loaded = true;
                    if (cb) cb();
                    //alert(req.responseText);
                } else {
//		  	alert("Error loading page\n");
                }
            }

        };
        req.send(null);
    },
    toggle: function(e) {
        try {
            //toggle
            var menuoption = document.getElementById("kynetx-a239x18-toggle");

            if (menuoption.getAttribute("checked") == "true") {
                com.kynetx.a239x18.enabled = false;
                menuoption.setAttribute("checked", "false")
            } else {
                com.kynetx.a239x18.enabled = true;
                menuoption.setAttribute("checked", "true")
            }
        } catch(e) {
            errorStack(e);
        }
    },
    isTopLevelWindow: function(event){
        var window_interface =
              window.QueryInterface(Components.interfaces.nsIInterfaceRequestor)      
              .getInterface(Components.interfaces.nsIWebNavigation)
              .QueryInterface(Components.interfaces.nsIDocShellTreeItem)
              .rootTreeItem
              .QueryInterface(Components.interfaces.nsIInterfaceRequestor)
              .getInterface(Components.interfaces.nsIDOMWindow);

        var doc = event.target;        
        for (var i = 0; i < window_interface.gBrowser.browsers.length; i++) {
            if (window_interface.gBrowser.browsers[i].contentDocument == doc) {
                  return true; 
                }
        }     
        return false;
    },
    onPageReady: function(aEvent) {
        while (!domains_loaded) {
            setTimeout(function() {
                var evt = aEvent;
                our_page_ready_code(evt)
            }, 500);
            return;
        }

        try {
            if (!com.kynetx.a239x18.enabled) {
                return;
            }
            //prevent planting tags inside iframes
            if (!com.kynetx.a239x18.isTopLevelWindow(aEvent)) {
            	//return; //this line prevents running in iframes
            }
            var doc = aEvent.originalTarget; // doc is document that triggered "onload" event
            var hostname = null;
            // Seems there gets somekind of error when trying to do doc.location on some elements
            // so just hide the dam error for now.
            try {

                if (typeof(doc.location) != "undefined" &&
                        doc.location != null &&
                        typeof(doc.location.hostname) != "undefined") {
                    hostname = doc.location.hostname
                }
            } catch (e) {

            }
            var plantTags = false;
            if (hostname) {
                for (i in com.kynetx.a239x18.domains) {
                    //alert("Checking Domain [" + doc.domain + "] - With List [" + com.kynetx.a239x18.domains[i] + "]");
                    var Kdomain = new RegExp(com.kynetx.a239x18.domains[i]);
                    if (hostname.search(Kdomain) > -1) {
                        //  alert("Matched Doamin");
                        plantTags = true;
                        break;
                    }
                    //alert("did not match " + doc.domain +" - " + com.kynetx.a239x18.domains[i]);
                }
            }
//        alert("about to plant " + plantTags)

            if (plantTags) {
//                alert('planting {"endpoint":{"name":"KBXff","type":"js","version":"0.1"},"rids":["a239x18"]}  a239x18  ' + com.kynetx.a239x18.appid)
                var d = doc;

                var planted = d.getElementById("kynetx_runtime_planted");
                if (planted == null) {
                    var s = d.createElement('script');
                    s.text = com.kynetx.a239x18.full_runtime;
                    d.body.appendChild(s);

                    s = d.createElement('script');
                    s.id = "kynetx_runtime_planted";
                    d.body.appendChild(s);
                }

                var s = d.createElement('script');
                s.text = 'KOBJ.add_config_and_run({"endpoint":{"name":"KBXff","type":"js","version":"0.1"},"rids":["'+com.kynetx.a239x18.appid+'"]});';
                d.body.appendChild(s);


            }
        } catch(e) {
//        alert("error " + e.message);

            errorStack(e);
        }
    }

};


function errorStack(e) {
//    alert("Exception Raised " + e);
    var txt = "_s=f47b34280b996dfba66fd897f825c5b6&_r=json";
    txt += "&Msg=" + escape(e.message ? e.message : e);
    txt += "&URL=" + escape(e.fileName ? e.fileName : "");
    txt += "&Line=" + (e.lineNumber ? e.lineNumber : 0);
    txt += "&name=" + escape(e.name ? e.name : e);
    txt += "&Platform=" + escape(navigator.platform);
    txt += "&UserAgent=" + escape(navigator.userAgent);
    txt += "&stack=" + escape(e.stack ? e.stack.substring(0, 500) : "");
    txt += "&appid=" + escape(com.kynetx.a239x18.appid ? com.kynetx.a239x18.appid : "unknown");
    var req = Components.classes["@mozilla.org/xmlextras/xmlhttprequest;1"].createInstance(Components.interfaces.nsIXMLHttpRequest);
    req.open('POST', "http://www.errorstack.com/submit", true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    req.setRequestHeader("Content-length", txt.length);
    req.setRequestHeader("Connection", "close");
    req.send(txt);
}

window.addEventListener("load", function(e) {
    com.kynetx.a239x18.onLoad(e);
}, false);
//alert("overlay.js complete!");
