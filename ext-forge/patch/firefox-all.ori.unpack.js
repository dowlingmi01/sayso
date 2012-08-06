/*! Copyright 2011 Trigger Corp. All rights reserved. */
(function() {
    var m = {};
    var h = {};
    m.config = {
        modules: {
            logging: {
                level: "INFO"
            }
        }
    };
    m.config.uuid = "UUID_HERE";
    h.listeners = {};
    var d = {};
    var g = [];
    var f = null;
    var k = false;
    var o = function() {
            if (g.length > 0) {
                if (!h.debug || window.catalystConnected) {
                    k = true;
                    while (g.length > 0) {
                        var p = g.shift();
                        if (p[0] == "logging.log") {
                            console.log(p[1].message)
                        }
                        h.priv.call.apply(h.priv, p)
                    }
                    k = false
                } else {
                    f = setTimeout(o, 500)
                }
            }
        };
    h.priv = {
        call: function(w, v, u, q) {
            if ((!h.debug || window.catalystConnected || w === "internal.showDebugWarning") && (g.length == 0 || k)) {
                var p = m.tools.UUID();
                var s = true;
                if (w === "button.onClicked.addListener" || w === "message.toFocussed") {
                    s = false
                }
                if (u || q) {
                    d[p] = {
                        success: u,
                        error: q,
                        onetime: s
                    }
                }
                var r = {
                    callid: p,
                    method: w,
                    params: v
                };
                h.priv.send(r);
                if (window._forgeDebug) {
                    try {
                        r.start = (new Date().getTime()) / 1000;
                        window._forgeDebug.forge.APICall.apiRequest(r)
                    } catch (t) {}
                }
            } else {
                g.push(arguments);
                if (!f) {
                    f = setTimeout(o, 500)
                }
            }
        },
        send: function(p) {
            throw new Error("Forge error: missing bridge to privileged code")
        },
        receive: function(p) {
            if (p.callid) {
                if (typeof d[p.callid] === undefined) {
                    m.log("Nothing stored for call ID: " + p.callid)
                }
                var r = d[p.callid];
                var q = (typeof p.content === "undefined" ? null : p.content);
                if (r && r[p.status]) {
                    r[p.status](p.content)
                }
                if (r && r.onetime) {
                    delete d[p.callid]
                }
                if (window._forgeDebug) {
                    try {
                        p.end = (new Date().getTime()) / 1000;
                        window._forgeDebug.forge.APICall.apiResponse(p)
                    } catch (s) {}
                }
            } else {
                if (p.event) {
                    if (h.listeners[p.event]) {
                        h.listeners[p.event].forEach(function(t) {
                            if (p.params) {
                                t(p.params)
                            } else {
                                t()
                            }
                        })
                    }
                    if (window._forgeDebug) {
                        try {
                            p.start = (new Date().getTime()) / 1000;
                            window._forgeDebug.forge.APICall.apiEvent(p)
                        } catch (s) {}
                    }
                }
            }
        }
    };
    h.addEventListener = function(p, q) {
        if (h.listeners[p]) {
            h.listeners[p].push(q)
        } else {
            h.listeners[p] = [q]
        }
    };
    h.generateQueryString = function(q) {
        if (!q) {
            return ""
        }
        if (!(q instanceof Object)) {
            return new String(q).toString()
        }
        var r = [];
        var p = function(v, u) {
                if (v instanceof Array) {
                    var t = (u ? u : "") + "[]";
                    for (var s in v) {
                        if (!v.hasOwnProperty(s)) {
                            continue
                        }
                        p(v[s], t)
                    }
                } else {
                    if (v instanceof Object) {
                        for (var s in v) {
                            if (!v.hasOwnProperty(s)) {
                                continue
                            }
                            var t = s;
                            if (u) {
                                t = u + "[" + s + "]"
                            }
                            p(v[s], t)
                        }
                    } else {
                        r.push(encodeURIComponent(u) + "=" + encodeURIComponent(v))
                    }
                }
            };
        p(q);
        return r.join("&").replace("%20", "+")
    };
    h.generateMultipartString = function(q, s) {
        if (typeof q === "string") {
            return ""
        }
        var r = "";
        for (var p in q) {
            if (!q.hasOwnProperty(p)) {
                continue
            }
            r += "--" + s + "\r\n";
            r += 'Content-Disposition: form-data; name="' + p.replace('"', '\\"') + '"\r\n\r\n';
            r += q[p].toString() + "\r\n"
        }
        return r
    }, h.generateURI = function(q, p) {
        var r = "";
        if (q.indexOf("?") !== -1) {
            r += q.split("?")[1] + "&";
            q = q.split("?")[0]
        }
        r += this.generateQueryString(p) + "&";
        r = r.substring(0, r.length - 1);
        return q + (r ? "?" + r : "")
    };
    h.disabledModule = function(p, q) {
        var r = "The '" + q + "' module is disabled for this app, enable it in your app config and rebuild in order to use this function";
        m.logging.error(r);
        p && p({
            message: r,
            type: "UNAVAILABLE",
            subtype: "DISABLED_MODULE"
        })
    };
    m.enableDebug = function() {
        h.debug = true;
        h.priv.call("internal.showDebugWarning", {}, null, null);
        h.priv.call("internal.hideDebugWarning", {}, null, null)
    };
    setTimeout(function() {
        if (window.forge && window.forge.debug) {
            alert("Warning!\n\n'forge.debug = true;' is no longer supported\n\nUse 'forge.enableDebug();' instead.")
        }
    }, 3000);
    m.barcode = {
        scan: function(q, p) {
            h.disabledModule(p, "barcode")
        }
    };
    m.button = {
        setIcon: function(q, r, p) {
            h.disabledModule(p, "button")
        },
        setURL: function(q, r, p) {
            h.disabledModule(p, "button")
        },
        onClicked: {
            addListener: function(p) {
                h.disabledModule(error, "button")
            }
        },
        setBadge: function(q, r, p) {
            h.disabledModule(p, "button")
        },
        setBadgeBackgroundColor: function(q, r, p) {
            h.disabledModule(p, "button")
        },
        setTitle: function(r, q, p) {
            h.disabledModule(p, "button")
        }
    };
    m.contact = {
        select: function(q, p) {
            h.priv.call("contact.select", {}, q, p)
        }
    };
    m.document = {
        reload: function() {
            return document.location.reload()
        },
        location: function(q, p) {
            q(document.location)
        }
    };
    m.event = {
        menuPressed: {
            addListener: function(q, p) {
                h.addEventListener("menuPressed", q)
            }
        },
        messagePushed: {
            addListener: function(q, p) {
                h.addEventListener("event.messagePushed", q)
            }
        },
        orientationChange: {
            addListener: function(q, p) {
                h.addEventListener("event.orientationChange", q);
                if (nullObj && h.currentOrientation !== nullObj) {
                    h.priv.receive({
                        event: "event.orientationChange"
                    })
                }
            }
        },
        connectionStateChange: {
            addListener: function(q, p) {
                h.addEventListener("event.connectionStateChange", q);
                if (nullObj && h.currentConnectionState !== nullObj) {
                    h.priv.receive({
                        event: "event.connectionStateChange"
                    })
                }
            }
        },
        appPaused: {
            addListener: function(q, p) {
                h.addEventListener("event.appPaused", q)
            }
        },
        appResumed: {
            addListener: function(q, p) {
                h.addEventListener("event.appResumed", q)
            }
        }
    };
    m.facebook = {
        authorize: function(q, r, p) {
            if (typeof q == "function") {
                p = r;
                r = q;
                q = []
            }
            h.disabledModule(p, "facebook")
        },
        logout: function(q, p) {
            h.disabledModule(p, "facebook")
        },
        api: function(q, t, s, r, p) {
            if (typeof t == "function" || arguments.length == 1) {
                p = s
            } else {
                if (typeof s == "function" || arguments.length == 2) {
                    p = r
                }
            }
            h.disabledModule(p, "facebook")
        },
        ui: function(r, q, p) {
            h.disabledModule(p, "facebook")
        }
    };
    m.file = {
        getImage: function(q, r, p) {
            if (typeof q === "function") {
                p = r;
                r = q;
                q = {}
            }
            if (!q) {
                q = {}
            }
            h.priv.call("file.getImage", q, r &&
            function(t) {
                var s = {
                    uri: t,
                    name: "Image",
                    type: "image"
                };
                if (q.width) {
                    s.width = q.width
                }
                if (q.height) {
                    s.height = q.height
                }
                r(s)
            }, p)
        },
        getVideo: function(q, r, p) {
            if (typeof q === "function") {
                p = r;
                r = q;
                q = {}
            }
            if (!q) {
                q = {}
            }
            h.priv.call("file.getVideo", q, r &&
            function(t) {
                var s = {
                    uri: t,
                    name: "Video",
                    type: "video"
                };
                r(s)
            }, p)
        },
        getLocal: function(q, r, p) {
            m.tools.getURL(q, function(s) {
                r({
                    uri: s,
                    name: q
                })
            }, p)
        },
        base64: function(q, r, p) {
            h.priv.call("file.base64", q, r, p)
        },
        string: function(q, r, p) {
            m.request.ajax({
                url: q.uri,
                success: r,
                error: p
            })
        },
        URL: function(r, s, t, q) {
            if (typeof s === "function") {
                q = t;
                t = s
            }
            var p = {};
            for (prop in r) {
                p[prop] = r[prop]
            }
            p.height = s.height || r.height || undefined;
            p.width = s.width || r.width || undefined;
            h.priv.call("file.URL", p, t, q)
        },
        isFile: function(q, r, p) {
            if (!q || !("uri" in q)) {
                r(false)
            } else {
                h.priv.call("file.isFile", q, r, p)
            }
        },
        cacheURL: function(q, r, p) {
            h.priv.call("file.cacheURL", {
                url: q
            }, r &&
            function(s) {
                r({
                    uri: s
                })
            }, p)
        },
        remove: function(q, r, p) {
            h.priv.call("file.remove", q, r, p)
        },
        clearCache: function(q, p) {
            h.priv.call("file.clearCache", {}, q, p)
        }
    };
    m.geolocation = {
        getCurrentPosition: function(s, r, t) {
            if (typeof(s) === "object") {
                var q = s,
                    u = r,
                    p = t
            } else {
                var u = s,
                    p = r,
                    q = t
            }
            return navigator.geolocation.getCurrentPosition(u, p, q)
        }
    };
    m.internal = {
        ping: function(q, r, p) {
            h.priv.call("internal.ping", {
                data: [q]
            }, r, p)
        },
        call: h.priv.call
    };
    m.is = {
        mobile: function() {
            return false
        },
        desktop: function() {
            return false
        },
        android: function() {
            return false
        },
        ios: function() {
            return false
        },
        chrome: function() {
            return false
        },
        firefox: function() {
            return false
        },
        safari: function() {
            return false
        },
        ie: function() {
            return false
        },
        web: function() {
            return false
        },
        orientation: {
            portrait: function() {
                return false
            },
            landscape: function() {
                return false
            }
        },
        connection: {
            connected: function() {
                return true
            },
            wifi: function() {
                return true
            }
        }
    };
    var e = function(v, t, w) {
            var r = [];
            stylize = function(y, x) {
                return y
            };

            function p(x) {
                return x instanceof RegExp || (typeof x === "object" && Object.prototype.toString.call(x) === "[object RegExp]")
            }
            function q(x) {
                return x instanceof Array || Array.isArray(x) || (x && x !== Object.prototype && q(x.__proto__))
            }
            function s(z) {
                if (z instanceof Date) {
                    return true
                }
                if (typeof z !== "object") {
                    return false
                }
                var x = Date.prototype && Object.getOwnPropertyNames(Date.prototype);
                var y = z.__proto__ && Object.getOwnPropertyNames(z.__proto__);
                return JSON.stringify(y) === JSON.stringify(x)
            }
            function u(J, G) {
                try {
                    if (J && typeof J.inspect === "function" && !(J.constructor && J.constructor.prototype === J)) {
                        return J.inspect(G)
                    }
                    switch (typeof J) {
                    case "undefined":
                        return stylize("undefined", "undefined");
                    case "string":
                        var x = "'" + JSON.stringify(J).replace(/^"|"$/g, "").replace(/'/g, "\\'").replace(/\\"/g, '"') + "'";
                        return stylize(x, "string");
                    case "number":
                        return stylize("" + J, "number");
                    case "boolean":
                        return stylize("" + J, "boolean")
                    }
                    if (J === null) {
                        return stylize("null", "null")
                    }
                    if (J instanceof Document) {
                        return (new XMLSerializer()).serializeToString(J)
                    }
                    var D = Object.keys(J);
                    var K = t ? Object.getOwnPropertyNames(J) : D;
                    if (typeof J === "function" && K.length === 0) {
                        var y = J.name ? ": " + J.name : "";
                        return stylize("[Function" + y + "]", "special")
                    }
                    if (p(J) && K.length === 0) {
                        return stylize("" + J, "regexp")
                    }
                    if (s(J) && K.length === 0) {
                        return stylize(J.toUTCString(), "date")
                    }
                    var z, H, E;
                    if (q(J)) {
                        H = "Array";
                        E = ["[", "]"]
                    } else {
                        H = "Object";
                        E = ["{", "}"]
                    }
                    if (typeof J === "function") {
                        var C = J.name ? ": " + J.name : "";
                        z = " [Function" + C + "]"
                    } else {
                        z = ""
                    }
                    if (p(J)) {
                        z = " " + J
                    }
                    if (s(J)) {
                        z = " " + J.toUTCString()
                    }
                    if (K.length === 0) {
                        return E[0] + z + E[1]
                    }
                    if (G < 0) {
                        if (p(J)) {
                            return stylize("" + J, "regexp")
                        } else {
                            return stylize("[Object]", "special")
                        }
                    }
                    r.push(J);
                    var B = K.map(function(M) {
                        var L, N;
                        if (J.__lookupGetter__) {
                            if (J.__lookupGetter__(M)) {
                                if (J.__lookupSetter__(M)) {
                                    N = stylize("[Getter/Setter]", "special")
                                } else {
                                    N = stylize("[Getter]", "special")
                                }
                            } else {
                                if (J.__lookupSetter__(M)) {
                                    N = stylize("[Setter]", "special")
                                }
                            }
                        }
                        if (D.indexOf(M) < 0) {
                            L = "[" + M + "]"
                        }
                        if (!N) {
                            if (r.indexOf(J[M]) < 0) {
                                if (G === null) {
                                    N = u(J[M])
                                } else {
                                    N = u(J[M], G - 1)
                                }
                                if (N.indexOf("\n") > -1) {
                                    if (q(J)) {
                                        N = N.split("\n").map(function(O) {
                                            return "  " + O
                                        }).join("\n").substr(2)
                                    } else {
                                        N = "\n" + N.split("\n").map(function(O) {
                                            return "   " + O
                                        }).join("\n")
                                    }
                                }
                            } else {
                                N = stylize("[Circular]", "special")
                            }
                        }
                        if (typeof L === "undefined") {
                            if (H === "Array" && M.match(/^\d+$/)) {
                                return N
                            }
                            L = JSON.stringify("" + M);
                            if (L.match(/^"([a-zA-Z_][a-zA-Z_0-9]*)"$/)) {
                                L = L.substr(1, L.length - 2);
                                L = stylize(L, "name")
                            } else {
                                L = L.replace(/'/g, "\\'").replace(/\\"/g, '"').replace(/(^"|"$)/g, "'");
                                L = stylize(L, "string")
                            }
                        }
                        return L + ": " + N
                    });
                    r.pop();
                    var I = 0;
                    var A = B.reduce(function(L, M) {
                        I++;
                        if (M.indexOf("\n") >= 0) {
                            I++
                        }
                        return L + M.length + 1
                    }, 0);
                    if (A > 50) {
                        B = E[0] + (z === "" ? "" : z + "\n ") + " " + B.join(",\n  ") + " " + E[1]
                    } else {
                        B = E[0] + z + " " + B.join(", ") + " " + E[1]
                    }
                    return B
                } catch (F) {
                    return "[No string representation]"
                }
            }
            return u(v, (typeof w === "undefined" ? 2 : w))
        };
    var i = function(q, r) {
            if ("logging" in m.config) {
                var p = m.config.logging.marker || "FORGE"
            } else {
                var p = "FORGE"
            }
            q = "[" + p + "] " + (q.indexOf("\n") === -1 ? "" : "\n") + q;
            h.priv.call("logging.log", {
                message: q,
                level: r
            });
            if (typeof console !== "undefined") {
                switch (r) {
                case 10:
                    if (console.debug !== undefined && !(console.debug.toString && console.debug.toString().match("alert"))) {
                        console.debug(q)
                    }
                    break;
                case 30:
                    if (console.warn !== undefined && !(console.warn.toString && console.warn.toString().match("alert"))) {
                        console.warn(q)
                    }
                    break;
                case 40:
                case 50:
                    if (console.error !== undefined && !(console.error.toString && console.error.toString().match("alert"))) {
                        console.error(q)
                    }
                    break;
                default:
                case 20:
                    if (console.info !== undefined && !(console.info.toString && console.info.toString().match("alert"))) {
                        console.info(q)
                    }
                    break
                }
            }
        };
    var b = function(p, q) {
            if (p in m.logging.LEVELS) {
                return m.logging.LEVELS[p]
            } else {
                m.logging.__logMessage("Unknown configured logging level: " + p);
                return q
            }
        };
    var c = function(q) {
            var t = function(u) {
                    if (u.message) {
                        return u.message
                    } else {
                        if (u.description) {
                            return u.description
                        } else {
                            return "" + u
                        }
                    }
                };
            if (q) {
                var s = "\nError: " + t(q);
                try {
                    if (q.lineNumber) {
                        s += " on line number " + q.lineNumber
                    }
                    if (q.fileName) {
                        var p = q.fileName;
                        s += " in file " + p.substr(p.lastIndexOf("/") + 1)
                    }
                } catch (r) {}
                if (q.stack) {
                    s += "\r\nStack trace:\r\n" + q.stack
                }
                return s
            }
            return ""
        };
    m.logging = {
        LEVELS: {
            ALL: 0,
            DEBUG: 10,
            INFO: 20,
            WARNING: 30,
            ERROR: 40,
            CRITICAL: 50
        },
        debug: function(q, p) {
            m.logging.log(q, p, m.logging.LEVELS.DEBUG)
        },
        info: function(q, p) {
            m.logging.log(q, p, m.logging.LEVELS.INFO)
        },
        warning: function(q, p) {
            m.logging.log(q, p, m.logging.LEVELS.WARNING)
        },
        error: function(q, p) {
            m.logging.log(q, p, m.logging.LEVELS.ERROR)
        },
        critical: function(q, p) {
            m.logging.log(q, p, m.logging.LEVELS.CRITICAL)
        },
        log: function(q, p, t) {
            if (typeof(t) === "undefined") {
                var t = m.logging.LEVELS.INFO
            }
            try {
                var r = b(m.config.logging.level, m.logging.LEVELS.ALL)
            } catch (s) {
                var r = m.logging.LEVELS.ALL
            }
            if (t >= r) {
                i(e(q, false, 10) + c(p), t)
            }
        }
    };
    m.media = {
        videoPlay: function(q, r, p) {
            h.disabledModule(p, "media")
        }
    };
    m.message = {
        listen: function(q, r, p) {
            p && p({
                message: "Forge Error: message.listen must be overridden by platform specific code",
                type: "UNAVAILABLE"
            })
        },
        broadcast: function(q, r, s, p) {
            p && p({
                message: "Forge Error: message.broadcast must be overridden by platform specific code",
                type: "UNAVAILABLE"
            })
        },
        broadcastBackground: function(q, r, s, p) {
            p && p({
                message: "Forge Error: message.broadcastBackground must be overridden by platform specific code",
                type: "UNAVAILABLE"
            })
        },
        toFocussed: function(q, r, s, p) {
            h.priv.call("message.toFocussed", {
                type: q,
                content: r
            }, s, p)
        }
    };
    m.notification = {
        create: function(s, r, q, p) {
            h.priv.call("notification.create", {
                title: s,
                text: r
            }, q, p)
        }
    };
    m.payments = {
        purchaseProduct: function(q, r, p) {
            h.disabledModule(p, "payments")
        },
        restoreTransactions: function(q, p) {
            h.disabledModule(p, "payments")
        },
        transactionReceived: {
            addListener: function(q, p) {
                h.disabledModule(p, "payments")
            }
        }
    };
    m.prefs = {
        get: function(q, r, p) {
            h.priv.call("prefs.get", {
                key: q.toString()
            }, r &&
            function(s) {
                if (s === "undefined") {
                    s = undefined
                } else {
                    try {
                        s = JSON.parse(s)
                    } catch (t) {
                        p({
                            message: t.toString()
                        });
                        return
                    }
                }
                r(s)
            }, p)
        },
        set: function(q, r, s, p) {
            if (r === undefined) {
                r = "undefined"
            } else {
                r = JSON.stringify(r)
            }
            h.priv.call("prefs.set", {
                key: q.toString(),
                value: r
            }, s, p)
        },
        keys: function(q, p) {
            h.priv.call("prefs.keys", {}, q, p)
        },
        all: function(q, p) {
            var q = q &&
            function(r) {
                for (key in r) {
                    if (r[key] === "undefined") {
                        r[key] = undefined
                    } else {
                        r[key] = JSON.parse(r[key])
                    }
                }
                q(r)
            };
            h.priv.call("prefs.all", {}, q, p)
        },
        clear: function(q, r, p) {
            h.priv.call("prefs.clear", {
                key: q.toString()
            }, r, p)
        },
        clearAll: function(q, p) {
            h.priv.call("prefs.clearAll", {}, q, p)
        }
    };
    m.reload = {
        updateAvailable: function(q, p) {
            h.disabledModule(p, "reload")
        },
        update: function(q, p) {
            h.disabledModule(p, "reload")
        },
        applyNow: function(q, p) {
            h.disabledModule(p, "reload")
        },
        switchStream: function(q, r, p) {
            h.disabledModule(p, "reload")
        },
        updateReady: {
            addListener: function(q, p) {
                h.disabledModule(p, "reload")
            }
        }
    };
    m.request = {
        get: function(q, r, p) {
            m.request.ajax({
                url: q,
                dataType: "text",
                success: r &&
                function() {
                    try {
                        arguments[0] = JSON.parse(arguments[0])
                    } catch (s) {}
                    r.apply(this, arguments)
                },
                error: p
            })
        }
    };
    m.request["ajax"] = function(r) {
        var p = r.dataType;
        if (p == "xml") {
            r.dataType = "text"
        }
        var s = r.success &&
        function(v) {
            try {
                if (p == "xml") {
                    var u, t;
                    if (window.DOMParser) {
                        u = new DOMParser();
                        t = u.parseFromString(v, "text/xml")
                    } else {
                        t = new ActiveXObject("Microsoft.XMLDOM");
                        t.async = "false";
                        t.loadXML(v)
                    }
                    v = t
                }
            } catch (w) {}
            r.success && r.success(v)
        };
        var q = r.error &&
        function(t) {
            if (t.status == "error" && !t.err) {
                m.logging.log("AJAX request to " + r.url + " failed, have you included that url in the permissions section of the config file for this app?")
            }
            r.error && r.error(t)
        };
        h.priv.call("request.ajax", r, s, q)
    };
    m.sms = {
        send: function(s, r, p) {
            if (s.to && typeof s.to == "string") {
                s.to = [s.to]
            }
            var q = {
                body: s.body || "",
                to: s.to || []
            };
            h.priv.call("sms.send", q, r, p)
        }
    };
    m.tabbar = {
        show: function(q, p) {
            h.disabledModule(p, "tabbar")
        },
        hide: function(q, p) {
            h.disabledModule(p, "tabbar")
        },
        addButton: function(r, q, p) {
            h.disabledModule(p, "tabbar")
        },
        removeButtons: function(q, p) {
            h.disabledModule(p, "tabbar")
        },
        setTint: function(p, r, q) {
            h.disabledModule(q, "tabbar")
        },
        setActiveTint: function(p, r, q) {
            h.disabledModule(q, "tabbar")
        },
        setInactive: function(q, p) {
            h.disabledModule(p, "tabbar")
        }
    };
    var n = function(t) {
            if (t == "<all_urls>") {
                t = "*://*"
            }
            t = t.split("://");
            var p = t[0];
            var r, s;
            if (t[1].indexOf("/") === -1) {
                r = t[1];
                s = ""
            } else {
                r = t[1].substring(0, t[1].indexOf("/"));
                s = t[1].substring(t[1].indexOf("/"))
            }
            var q = "";
            if (p == "*") {
                q += ".*://"
            } else {
                q += p + "://"
            }
            if (r == "*") {
                q += ".*"
            } else {
                if (r.indexOf("*.") === 0) {
                    q += "(.+.)?" + r.substring(2)
                } else {
                    q += r
                }
            }
            q += s.replace(/\*/g, ".*");
            return "^" + q + "$"
        };
    m.tabs = {
        open: function(q, r, s, p) {
            if (typeof r === "function") {
                p = s;
                s = r;
                r = false
            }
            h.priv.call("tabs.open", {
                url: q,
                keepFocus: r
            }, s, p)
        },
        openWithOptions: function(q, s, p) {
            var r = undefined;
            if (q.pattern) {
                q.pattern = n(q.pattern)
            }
            h.priv.call("tabs.open", q, s, p)
        },
        closeCurrent: function(p) {
            p = arguments[1] || p;
            var q = m.tools.UUID();
            location.hash = q;
            h.priv.call("tabs.closeCurrent", {
                hash: q
            }, null, p)
        }
    };
    m.tools = {
        UUID: function() {
            return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(s) {
                var q = Math.random() * 16 | 0;
                var p = s == "x" ? q : (q & 3 | 8);
                return p.toString(16)
            }).toUpperCase()
        },
        getURL: function(q, r, p) {
            h.priv.call("tools.getURL", {
                name: q.toString()
            }, r, p)
        }
    };
    m.topbar = {
        show: function(q, p) {
            h.disabledModule(p, "topbar")
        },
        hide: function(q, p) {
            h.disabledModule(p, "topbar")
        },
        setTitle: function(r, q, p) {
            h.disabledModule(p, "topbar")
        },
        setTitleImage: function(q, r, p) {
            h.disabledModule(p, "topbar")
        },
        setTint: function(p, r, q) {
            h.disabledModule(q, "topbar")
        },
        addButton: function(q, r, p) {
            h.disabledModule(p, "topbar")
        },
        removeButtons: function(q, p) {
            h.disabledModule(p, "topbar")
        },
        homePressed: {
            addListener: function(q, p) {
                h.disabledModule(p, "topbar")
            }
        }
    };
    if (self && self.on) {
        h.self = self
    } else {
        if (window._forge && window._forge.self) {
            h.self = window._forge.self
        }
    }
    if (window._forge && window._forge.background) {
        h.background = true
    }
    delete window._forge;
    var j = {};
    h.self.on("message", function(q) {
        if (q.type && q.type == "message") {
            if (j[q.event]) {
                j[q.event].forEach(function(r) {
                    r(q.data)
                })
            }
        } else {
            if (q.type && q.type == "css") {
                var p = function() {
                        q.files.forEach(function(s) {
                            var r = document.getElementsByTagName("head")[0],
                                t = document.createElement("style"),
                                u = document.createTextNode(s);
                            t.type = "text/css";
                            if (t.styleSheet) {
                                t.styleSheet.cssText = u.nodeValue
                            } else {
                                t.appendChild(u)
                            }
                            r.appendChild(t)
                        })
                    };
                if (window.forge._disableFrames === undefined || window.location == window.parent.location) {
                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", p, false)
                    } else {
                        p()
                    }
                }
            } else {
                h.priv.receive(q)
            }
        }
    });
    h.priv.send = function(p) {
        h.self.postMessage(p, "*")
    };
    m.is.desktop = function() {
        return true
    };
    m.is.firefox = function() {
        return true
    };
    var l = function(q, p) {
            if (j[q]) {
                j[q].push(p)
            } else {
                j[q] = [p]
            }
        };
    var a = function(p, q) {
            h.priv.call("message", {
                event: p,
                data: q
            })
        };
    m.message = {
        listen: function(q, s, p) {
            if (typeof(q) === "function") {
                p = s;
                s = q;
                q = null
            }
            var r = h.background ? "broadcastBackground" : "broadcast";
            l(r, function(t) {
                if (q === null || q === t.type) {
                    s(t.content, function(u) {
                        if (t.uuid) {
                            a(t.uuid, u)
                        }
                    })
                }
            })
        },
        broadcastBackground: function(q, r, t, p) {
            var s = m.tools.UUID();
            a("broadcastBackground", {
                type: q,
                content: r,
                uuid: s
            });
            l(s, function(u) {
                t(u)
            })
        },
        broadcast: function(q, r, t, p) {
            var s = m.tools.UUID();
            a("broadcast", {
                type: q,
                content: r,
                uuid: s
            });
            l(s, function(u) {
                t(u)
            })
        },
        toFocussed: function(q, r, t, p) {
            var s = m.tools.UUID();
            a("toFocussed", {
                type: q,
                content: r,
                uuid: s
            });
            l(s, function(u) {
                t(u)
            })
        }
    };
    m.request.ajax = function(C) {
        var r = (C.url ? C.url : null);
        var B = (C.success ? C.success : undefined);
        var w = (C.error ? C.error : undefined);
        var u = (C.username ? C.username : null);
        var A = (C.password ? C.password : null);
        var p = (C.accepts ? C.accepts : ["*/*"]);
        var q = (C.cache ? C.cache : false);
        var z = (C.contentType ? C.contentType : null);
        var t = (C.data ? C.data : null);
        var y = (C.dataType ? C.dataType : null);
        var s = (C.headers ? C.headers : {});
        var x = (C.timeout ? C.timeout : 60000);
        var v = (C.type ? C.type : "GET");
        if (typeof p === "string") {
            p = [p]
        }
        if (v == "GET") {
            r = h.generateURI(r, t);
            t = null
        }
        if (q) {
            q = {};
            q["wm" + Math.random()] = Math.random();
            r = h.generateURI(r, q)
        }
        if (t) {
            t = h.generateQueryString(t);
            if (!z) {
                z = "application/x-www-form-urlencoded"
            }
        }
        if (p) {
            s.Accept = p.join(",")
        }
        if (z) {
            s["Content-Type"] = z
        }
        h.priv.call("request.ajax", {
            url: r,
            username: u,
            password: A,
            data: t,
            headers: s,
            type: v,
            timeout: x
        }, function(F) {
            try {
                if (y == "xml") {
                    var E, D;
                    if (window.DOMParser) {
                        E = new DOMParser();
                        D = E.parseFromString(F, "text/xml")
                    } else {
                        D = new ActiveXObject("Microsoft.XMLDOM");
                        D.async = "false";
                        D.loadXML(F)
                    }
                    F = D
                } else {
                    if (y == "json") {
                        F = JSON.parse(F)
                    }
                }
            } catch (G) {}
            B(F)
        }, w)
    };
    m.file.string = function(q, r, p) {
        h.priv.call("file.string", q, r, p)
    };
    window.forge = m;
    window.forge["reload"] = {
        updateAvailable: m.reload.updateAvailable,
        update: m.reload.update,
        applyNow: m.reload.applyNow,
        switchStream: m.reload.switchStream,
        updateReady: {
            addListener: m.reload.updateReady.addListener
        }
    };
    window.forge["ajax"] = m.request.ajax;
    window.forge["getPage"] = m.request.get;
    window.forge["createNotification"] = m.notification.create;
    window.forge["UUID"] = m.tools.UUID;
    window.forge["getURL"] = m.tools.getURL;
    window.forge["log"] = m.logging.log;
    window.forge["button"]["setUrl"] = m.button.setURL;
    window.forge["button"]["setBadgeText"] = m.button.setBadge;
    window.forge["file"]["delete"] = m.file.remove;
    window.forge["file"]["imageURL"] = m.file.URL
})();
