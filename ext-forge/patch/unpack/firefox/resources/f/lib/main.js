var config = {
    uuid: "4093d42274ec11e1a41a12313d1adcbe"
};
var pageWorker = require("page-worker");
var pageMod = require("page-mod");
var data = require("self").data;
var request = require("request");
var notif = require("notifications");
var ss = require("simple-storage");
if (!ss.storage || !ss.storage.prefs) {
    ss.storage.prefs = {}
}
var button;
var workers = [];
var background;
var addWorker = function(a) {
    workers.push(a)
};
var removeWorker = function(b) {
    var a = workers.indexOf(b);
    if (a != -1) {
        workers.splice(a, 1)
    }
};
var attachWorker = function(a) {
    addWorker(a);
    a.on("detach", function() {
        removeWorker(this)
    });
    a.on("message", handleNonPrivCall)
};
var handleNonPrivCall = function(c) {
    var a = this;
    var b = function(d) {
        return function(g) {
            var f = {
                callid: c.callid,
                status: d,
                content: g
            };
            try {
                a.postMessage(f)
            } catch (h) {}
        }
    };
    call(c.method, c.params, a, b("success"), b("error"))
};
var nullFn = function() {};
var call = function(j, c, b, k, h) {
    var l = "parameters could not be stringified";
    try {
        l = JSON.stringify(c)
    } catch (g) {}
    apiImpl.logging.log({
        message: "Received call to " + j + " with parameters: " + l,
        level: 10
    }, nullFn, nullFn);
    if (!k) {
        k = nullFn
    }
    if (!h) {
        h = nullFn
    }
    try {
        var f = j.split("."),
            a = apiImpl;
        for (var d = 0; d < f.length; d++) {
            a = a[f[d]]
        }
        if (typeof(a) !== "function") {
            h({
                message: j + " does not exist",
                type: "UNAVAILABLE"
            })
        }
        a.call(b, c, function() {
            var m = "arguments could not be stringified";
            try {
                m = JSON.stringify(arguments)
            } catch (i) {}
            apiImpl.logging.log({
                message: "Call to " + j + "(" + l + ") succeeded: " + m,
                level: 10
            }, nullFn, nullFn);
            k.apply(this, arguments)
        }, function() {
            var m = "arguments could not be stringified";
            try {
                m = JSON.stringify(arguments)
            } catch (i) {}
            apiImpl.logging.log({
                message: "Call to " + j + "(" + l + ") failed: " + m,
                level: 30
            }, nullFn, nullFn);
            h.apply(this, arguments)
        })
    } catch (g) {
        h({
            message: "Unknown error: " + g,
            type: "UNEXPECTED_FAILURE"
        })
    }
};
var apiImpl = {
    message: function(b) {
        var a = {
            event: b.event,
            data: b.data,
            type: "message"
        };
        if (b.event == "toFocussed") {
            a.event = "broadcast"
        }
        background.postMessage(a);
        workers.forEach(function(c) {
            if (b.event !== "toFocussed" || c.tab === require("tabs").activeTab) {
                c.postMessage(a)
            }
        })
    },
    button: {
        setIcon: function(b, c, a) {
            if (button) {
                button.update({
                    icon: b
                });
                c()
            } else {
                a({
                    message: "Button does not exist",
                    type: "UNAVAILABLE"
                })
            }
        },
        setURL: function(b, c, a) {
            if (button) {
                if (b && b.indexOf("http://") !== 0 && b.indexOf("https://") !== 0) {
                    b = require("self").data.url("src" + (b.substring(0, 1) == "/" ? "" : "/") + b)
                }
                button.update({
                    url: b
                });
                c()
            } else {
                a({
                    message: "Button does not exist",
                    type: "UNAVAILABLE"
                })
            }
        },
        setTitle: function(c, b, a) {
            if (button) {
                button.update({
                    title: c
                });
                b()
            } else {
                a({
                    message: "Button does not exist",
                    type: "UNAVAILABLE"
                })
            }
        },
        setBadge: function(b, c, a) {
            if (button) {
                button.update({
                    badgeText: b
                });
                c()
            } else {
                a({
                    message: "Button does not exist",
                    type: "UNAVAILABLE"
                })
            }
        },
        setBadgeBackgroundColor: function(a, c, b) {
            if (button) {
                button.update({
                    badgeBGColor: a
                });
                c()
            } else {
                b({
                    message: "Button does not exist",
                    type: "UNAVAILABLE"
                })
            }
        },
        onClicked: {
            addListener: function(b, c, a) {
                if (button) {
                    button.addListener(c);
                    apiImpl.button.setURL("")
                } else {
                    a({
                        message: "Button does not exist",
                        type: "UNAVAILABLE"
                    })
                }
            }
        }
    },
    logging: {
        log: function(c, b, a) {
            if (typeof console !== "undefined") {
                switch (c.level) {
                    case 10:
                        if (console.debug !== undefined && !(console.debug.toString && console.debug.toString().match("alert"))) {
                            console.debug(c.message)
                        }
                        break;
                    case 30:
                        if (console.warn !== undefined && !(console.warn.toString && console.warn.toString().match("alert"))) {
                            console.warn(c.message)
                        }
                        break;
                    case 40:
                    case 50:
                        if (console.error !== undefined && !(console.error.toString && console.error.toString().match("alert"))) {
                            console.error(c.message)
                        }
                        break;
                    default:
                    case 20:
                        if (console.info !== undefined && !(console.info.toString && console.info.toString().match("alert"))) {
                            console.info(c.message)
                        }
                        break
                }
                b()
            }
        }
    },
    tools: {
        getURL: function(c, b, a) {
            name = c.name.toString();
            if (name.indexOf("http://") === 0 || name.indexOf("https://") === 0) {
                b(name)
            } else {
                b(data.url("src" + (name.substring(0, 1) == "/" ? "" : "/") + name))
            }
        }
    },
    notification: {
        create: function(c, b, a) {
            require("notifications").notify({
                title: c.title,
                text: c.text
            });
            b()
        }
    },
    tabs: {
        open: function(c, b, a) {
            require("tabs").open({
                url: c.url,
                inBackground: c.keepFocus,
                onOpen: function() {
                    b()
                }
            })
        },
        closeCurrent: function() {
            this.tab.close()
        }
    },
    request: {
        ajax: function(e, d, b) {
            var a = false;
            var f = require("timers").setTimeout(function() {
                if (a) {
                    return
                }
                a = true;
                b && b({
                    message: "Request timed out",
                    type: "EXPECTED_FAILURE"
                })
            }, e.timeout ? e.timeout : 60000);
            var c = request.Request({
                url: e.url,
                onComplete: function(g) {
                    require("timers").clearTimeout(f);
                    if (a) {
                        return
                    }
                    a = true;
                    if (g.status >= 200 && g.status < 400) {
                        d(g.text)
                    } else {
                        b({
                            message: "HTTP error code received from server: " + g.status,
                            statusCode: g.status,
                            type: "EXPECTED_FAILURE"
                        })
                    }
                },
                content: e.data,
                headers: e.headers
            });
            if (e.type == "POST") {
                c.post()
            } else {
                c.get()
            }
        }
    },
    prefs: {
        get: function(c, b, a) {
            b(ss.storage.prefs[c.key] === undefined ? "undefined" : ss.storage.prefs[c.key])
        },
        set: function(c, b, a) {
            ss.storage.prefs[c.key] = c.value;
            b()
        },
        keys: function(c, b, a) {
            b(Object.keys(ss.storage.prefs))
        },
        all: function(c, b, a) {
            b(ss.storage.prefs)
        },
        clear: function(c, b, a) {
            delete ss.storage.prefs[c.key];
            b()
        },
        clearAll: function(c, b, a) {
            b(ss.storage.prefs = {})
        }
    },
    file: {
        string: function(b, c, a) {
            c(data.load(b.uri.substring(data.url("").length)))
        }
    }
};
exports.main = function(b, c) {
    background = pageWorker.Page({
        contentURL: data.url("forge.html"),
        contentScriptFile: data.url("forge/api-firefox-proxy.js"),
        contentScriptWhen: "start",
        onMessage: handleNonPrivCall
    });
    var a = function(j) {
        if (j == "<all_urls>") {
            j = "*://*"
        }
        j = j.split("://");
        var f = j[0];
        var h, i;
        if (j[1].indexOf("/") === -1) {
            h = j[1];
            i = ""
        } else {
            h = j[1].substring(0, j[1].indexOf("/"));
            i = j[1].substring(j[1].indexOf("/"))
        }
        var g = "";
        if (f == "*") {
            g += "(http|https|file|ftp)://"
        } else {
            if (["http", "https", "file", "ftp"].indexOf(f) !== -1) {
                g += f + "://"
            } else {
                return new RegExp("^$")
            }
        } if (h == "*") {
            g += ".*"
        } else {
            if (h.indexOf("*.") === 0) {
                g += "(.+.)?" + h.substring(2)
            } else {
                g += h
            }
        }
        g += i.replace(/\*/g, ".*");
        return new RegExp(g)
    };
    var d = function(f) {
        if (f.map) {
            return f.map(a)
        } else {
            return a(f)
        }
    };
    var e = function(f) {
        if (f.map) {
            return f.map(data.url)
        } else {
            return data.url(f)
        }
    };
    pageMod.PageMod({
        include: d(["http://*/*", "https://*/*"]),
        contentScriptFile: e(["forge/app_config.js", "forge/all.js", "src/js/config.js", "src/js/content.js"]),
        contentScriptWhen: "start",
        onAttach: function(h) {
            attachWorker(h);
            var g = [];
            for (var f in g) {
                g[f] = data.load(g[f])
            }
            h.postMessage({
                type: "css",
                files: g
            })
        }
    });
    pageMod.PageMod({
        include: data.url("") + "*",
        contentScriptFile: data.url("forge/api-firefox-proxy.js"),
        contentScriptWhen: "start",
        onAttach: attachWorker
    })
};
