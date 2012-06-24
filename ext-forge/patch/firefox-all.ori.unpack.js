/*! Copyright 2011 Trigger Corp. All rights reserved. */
(function() {
    var m = {};
    m.config = {
        modules: {
            logging: {
                level: "INFO"
            }
        }
    };
    var h = {};
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
        var p = function(u, t) {
                if (u instanceof Array) {
                    var s = (t ? t : "") + "[]";
                    for (x in u) {
                        p(u[x], s)
                    }
                } else {
                    if (u instanceof Object) {
                        for (x in u) {
                            var s = x;
                            if (t) {
                                s = t + "[" + x + "]"
                            }
                            p(u[x], s)
                        }
                    } else {
                        r.push(encodeURIComponent(t) + "=" + encodeURIComponent(u))
                    }
                }
            };
        p(q);
        return r.join("&").replace("%20", "+")
    };
    h.generateMultipartString = function(p, r) {
        if (typeof p === "string") {
            return ""
        }
        var q = "";
        for (key in p) {
            q += "--" + r + "\r\n";
            q += 'Content-Disposition: form-data; name="' + key.replace('"', '\\"') + '"\r\n\r\n';
            q += p[key].toString() + "\r\n"
        }
        return q
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
    m.enableDebug = function() {
        h.debug = true;
        h.priv.call("internal.showDebugWarning", {}, null, null);
        h.priv.call("internal.hideDebugWarning", {}, null, null)
    };
    setTimeout(function() {
        if (window.forge.debug) {
            alert("Warning!\n\n'forge.debug = true;' is no longer supported\n\nUse 'forge.enableDebug();' instead.")
        }
    }, 3000);
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
    m.button = {
        setIcon: function(q, r, p) {
            h.priv.call("button.setIcon", q, r, p)
        },
        setURL: function(q, r, p) {
            h.priv.call("button.setURL", q, r, p)
        },
        onClicked: {
            addListener: function(p) {
                h.priv.call("button.onClicked.addListener", null, p)
            }
        },
        setBadge: function(q, r, p) {
            h.priv.call("button.setBadge", q, r, p)
        },
        setBadgeBackgroundColor: function(q, r, p) {
            h.priv.call("button.setBadgeBackgroundColor", q, r, p)
        },
        setTitle: function(r, q, p) {
            h.priv.call("button.setTitle", r, q, p)
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
        },
        ajax: function(r) {
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
    var e = function(v, t, w) {
            var r = [];
            stylize = function(z, y) {
                return z
            };

            function p(y) {
                return y instanceof RegExp || (typeof y === "object" && Object.prototype.toString.call(y) === "[object RegExp]")
            }
            function q(y) {
                return y instanceof Array || Array.isArray(y) || (y && y !== Object.prototype && q(y.__proto__))
            }
            function s(A) {
                if (A instanceof Date) {
                    return true
                }
                if (typeof A !== "object") {
                    return false
                }
                var y = Date.prototype && Object.getOwnPropertyNames(Date.prototype);
                var z = A.__proto__ && Object.getOwnPropertyNames(A.__proto__);
                return JSON.stringify(z) === JSON.stringify(y)
            }
            function u(K, H) {
                try {
                    if (K && typeof K.inspect === "function" && !(K.constructor && K.constructor.prototype === K)) {
                        return K.inspect(H)
                    }
                    switch (typeof K) {
                    case "undefined":
                        return stylize("undefined", "undefined");
                    case "string":
                        var y = "'" + JSON.stringify(K).replace(/^"|"$/g, "").replace(/'/g, "\\'").replace(/\\"/g, '"') + "'";
                        return stylize(y, "string");
                    case "number":
                        return stylize("" + K, "number");
                    case "boolean":
                        return stylize("" + K, "boolean")
                    }
                    if (K === null) {
                        return stylize("null", "null")
                    }
                    if (K instanceof Document) {
                        return (new XMLSerializer()).serializeToString(K)
                    }
                    var E = Object.keys(K);
                    var L = t ? Object.getOwnPropertyNames(K) : E;
                    if (typeof K === "function" && L.length === 0) {
                        var z = K.name ? ": " + K.name : "";
                        return stylize("[Function" + z + "]", "special")
                    }
                    if (p(K) && L.length === 0) {
                        return stylize("" + K, "regexp")
                    }
                    if (s(K) && L.length === 0) {
                        return stylize(K.toUTCString(), "date")
                    }
                    var A, I, F;
                    if (q(K)) {
                        I = "Array";
                        F = ["[", "]"]
                    } else {
                        I = "Object";
                        F = ["{", "}"]
                    }
                    if (typeof K === "function") {
                        var D = K.name ? ": " + K.name : "";
                        A = " [Function" + D + "]"
                    } else {
                        A = ""
                    }
                    if (p(K)) {
                        A = " " + K
                    }
                    if (s(K)) {
                        A = " " + K.toUTCString()
                    }
                    if (L.length === 0) {
                        return F[0] + A + F[1]
                    }
                    if (H < 0) {
                        if (p(K)) {
                            return stylize("" + K, "regexp")
                        } else {
                            return stylize("[Object]", "special")
                        }
                    }
                    r.push(K);
                    var C = L.map(function(N) {
                        var M, O;
                        if (K.__lookupGetter__) {
                            if (K.__lookupGetter__(N)) {
                                if (K.__lookupSetter__(N)) {
                                    O = stylize("[Getter/Setter]", "special")
                                } else {
                                    O = stylize("[Getter]", "special")
                                }
                            } else {
                                if (K.__lookupSetter__(N)) {
                                    O = stylize("[Setter]", "special")
                                }
                            }
                        }
                        if (E.indexOf(N) < 0) {
                            M = "[" + N + "]"
                        }
                        if (!O) {
                            if (r.indexOf(K[N]) < 0) {
                                if (H === null) {
                                    O = u(K[N])
                                } else {
                                    O = u(K[N], H - 1)
                                }
                                if (O.indexOf("\n") > -1) {
                                    if (q(K)) {
                                        O = O.split("\n").map(function(P) {
                                            return "  " + P
                                        }).join("\n").substr(2)
                                    } else {
                                        O = "\n" + O.split("\n").map(function(P) {
                                            return "   " + P
                                        }).join("\n")
                                    }
                                }
                            } else {
                                O = stylize("[Circular]", "special")
                            }
                        }
                        if (typeof M === "undefined") {
                            if (I === "Array" && N.match(/^\d+$/)) {
                                return O
                            }
                            M = JSON.stringify("" + N);
                            if (M.match(/^"([a-zA-Z_][a-zA-Z_0-9]*)"$/)) {
                                M = M.substr(1, M.length - 2);
                                M = stylize(M, "name")
                            } else {
                                M = M.replace(/'/g, "\\'").replace(/\\"/g, '"').replace(/(^"|"$)/g, "'");
                                M = stylize(M, "string")
                            }
                        }
                        return M + ": " + O
                    });
                    r.pop();
                    var J = 0;
                    var B = C.reduce(function(M, N) {
                        J++;
                        if (N.indexOf("\n") >= 0) {
                            J++
                        }
                        return M + N.length + 1
                    }, 0);
                    if (B > 50) {
                        C = F[0] + (A === "" ? "" : A + "\n ") + " " + C.join(",\n  ") + " " + F[1]
                    } else {
                        C = F[0] + A + " " + C.join(", ") + " " + F[1]
                    }
                    return C
                } catch (G) {
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
                h.addEventListener("event.orientationChange", q)
            }
        },
        connectionStateChange: {
            addListener: function(q, p) {
                h.addEventListener("event.connectionStateChange", q)
            }
        }
    };
    m.contact = {
        select: function(q, p) {
            h.priv.call("contact.select", {}, q, p)
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
        }
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
    m.topbar = {
        show: function(q, p) {
            h.priv.call("topbar.show", {}, q, p)
        },
        hide: function(q, p) {
            h.priv.call("topbar.hide", {}, q, p)
        },
        setTitle: function(r, q, p) {
            h.priv.call("topbar.setTitle", {
                title: r
            }, q, p)
        },
        setTitleImage: function(q, r, p) {
            if (q && q[0] === "/") {
                q = q.substr(1)
            }
            h.priv.call("topbar.setTitleImage", {
                icon: q
            }, r, p)
        },
        setTint: function(p, r, q) {
            h.priv.call("topbar.setTint", {
                color: p
            }, r, q)
        },
        addButton: function(q, r, p) {
            if (q.icon && q.icon[0] === "/") {
                q.icon = q.icon.substr(1)
            }
            h.priv.call("topbar.addButton", q, function(s) {
                r && h.addEventListener("topbar.buttonPressed." + s, r)
            }, p)
        },
        removeButtons: function(q, p) {
            h.priv.call("topbar.removeButtons", {}, q, p)
        },
        homePressed: {
            addListener: function(q, p) {
                h.addEventListener("topbar.homePressed", q)
            }
        }
    };
    m.tabbar = {
        show: function(q, p) {
            h.priv.call("tabbar.show", {}, q, p)
        },
        hide: function(q, p) {
            h.priv.call("tabbar.hide", {}, q, p)
        },
        addButton: function(r, q, p) {
            if (r.icon && r.icon[0] === "/") {
                r.icon = r.icon.substr(1)
            }
            h.priv.call("tabbar.addButton", r, function(s) {
                q && q({
                    remove: function(u, t) {
                        h.priv.call("tabbar.removeButton", {
                            id: s
                        }, u, t)
                    },
                    setActive: function(u, t) {
                        h.priv.call("tabbar.setActive", {
                            id: s
                        }, u, t)
                    },
                    onPressed: {
                        addListener: function(u, t) {
                            h.addEventListener("tabbar.buttonPressed." + s, u)
                        }
                    }
                })
            }, p)
        },
        removeButtons: function(q, p) {
            h.priv.call("tabbar.removeButtons", {}, q, p)
        },
        setTint: function(p, r, q) {
            h.priv.call("tabbar.setTint", {
                color: p
            }, r, q)
        },
        setActiveTint: function(p, r, q) {
            h.priv.call("tabbar.setActiveTint", {
                color: p
            }, r, q)
        },
        setInactive: function(q, p) {
            h.priv.call("tabbar.setInactive", {}, q, p)
        }
    };
    m.media = {
        videoPlay: function(q, r, p) {
            if (!q.uri) {
                q = {
                    uri: q
                }
            }
            h.priv.call("media.videoPlay", q, r, p)
        }
    };
    m.payments = {
        purchaseProduct: function(q, r, p) {
            h.priv.call("payments.purchaseProduct", {
                product: q
            }, r, p)
        },
        restoreTransactions: function(q, p) {
            h.priv.call("payments.restoreTransactions", {}, q, p)
        },
        transactionReceived: {
            addListener: function(q, p) {
                h.addEventListener("payments.transactionReceived", function(s) {
                    var r = function() {
                            if (s.notificationId) {
                                h.priv.call("payments.confirmNotification", {
                                    id: s.notificationId
                                })
                            }
                        };
                    q(s, r)
                })
            }
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
    m.request.ajax = function(D) {
        var r = (D.url ? D.url : null);
        var C = (D.success ? D.success : undefined);
        var w = (D.error ? D.error : undefined);
        var u = (D.username ? D.username : null);
        var B = (D.password ? D.password : null);
        var p = (D.accepts ? D.accepts : ["*/*"]);
        var q = (D.cache ? D.cache : false);
        var A = (D.contentType ? D.contentType : null);
        var t = (D.data ? D.data : null);
        var z = (D.dataType ? D.dataType : null);
        var s = (D.headers ? D.headers : {});
        var y = (D.timeout ? D.timeout : 60000);
        var v = (D.type ? D.type : "GET");
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
            if (!A) {
                A = "application/x-www-form-urlencoded"
            }
        }
        if (p) {
            s.Accept = p.join(",")
        }
        if (A) {
            s["Content-Type"] = A
        }
        h.priv.call("request.ajax", {
            url: r,
            username: u,
            password: B,
            data: t,
            headers: s,
            type: v,
            timeout: y
        }, function(G) {
            try {
                if (z == "xml") {
                    var F, E;
                    if (window.DOMParser) {
                        F = new DOMParser();
                        E = F.parseFromString(G, "text/xml")
                    } else {
                        E = new ActiveXObject("Microsoft.XMLDOM");
                        E.async = "false";
                        E.loadXML(G)
                    }
                    G = E
                } else {
                    if (z == "json") {
                        G = JSON.parse(G)
                    }
                }
            } catch (H) {}
            C(G)
        }, w)
    };
    m.file.string = function(q, r, p) {
        h.priv.call("file.string", q, r, p)
    };
    window.forge = {
        config: m.config,
        enableDebug: m.enableDebug,
        is: {
            mobile: m.is.mobile,
            desktop: m.is.desktop,
            android: m.is.android,
            ios: m.is.ios,
            chrome: m.is.chrome,
            firefox: m.is.firefox,
            safari: m.is.safari,
            ie: m.is.ie,
            web: m.is.web,
            orientation: {
                portrait: m.is.orientation.portrait,
                landscape: m.is.orientation.landscape
            },
            connection: {
                connected: m.is.connection.connected,
                wifi: m.is.connection.wifi
            }
        },
        message: {
            listen: m.message.listen,
            broadcast: m.message.broadcast,
            broadcastBackground: m.message.broadcastBackground,
            toFocussed: m.message.toFocussed
        },
        notification: {
            create: m.notification.create
        },
        request: {
            get: m.request.get,
            ajax: m.request.ajax
        },
        logging: {
            log: m.logging.log,
            debug: m.logging.debug,
            info: m.logging.info,
            warning: m.logging.warning,
            error: m.logging.error,
            critical: m.logging.critical
        },
        tabs: {
            open: m.tabs.open,
            openWithOptions: m.tabs.openWithOptions,
            closeCurrent: m.tabs.closeCurrent
        },
        tools: {
            UUID: m.tools.UUID,
            getURL: m.tools.getURL
        },
        prefs: {
            get: m.prefs.get,
            set: m.prefs.set,
            clear: m.prefs.clear,
            clearAll: m.prefs.clearAll,
            keys: m.prefs.keys
        },
        button: {
            setIcon: m.button.setIcon,
            setURL: m.button.setURL,
            onClicked: {
                addListener: m.button.onClicked.addListener
            },
            setBadge: m.button.setBadge,
            setBadgeBackgroundColor: m.button.setBadgeBackgroundColor,
            setTitle: m.button.setTitle
        },
        file: {
            getImage: m.file.getImage,
            getVideo: m.file.getVideo,
            getLocal: m.file.getLocal,
            isFile: m.file.isFile,
            URL: m.file.URL,
            base64: m.file.base64,
            string: m.file.string,
            cacheURL: m.file.cacheURL,
            remove: m.file.remove,
            clearCache: m.file.clearCache
        },
        media: {
            videoPlay: m.media.videoPlay
        },
        event: {
            menuPressed: {
                addListener: m.event.menuPressed.addListener
            },
            messagePushed: {
                addListener: m.event.messagePushed.addListener
            },
            orientationChange: {
                addListener: m.event.orientationChange.addListener
            },
            connectionStateChange: {
                addListener: m.event.connectionStateChange.addListener
            }
        },
        contact: {
            select: m.contact.select
        },
        geolocation: {
            getCurrentPosition: m.geolocation.getCurrentPosition
        },
        internal: {
            ping: m.internal.ping
        },
        sms: {
            send: m.sms.send
        },
        topbar: {
            show: m.topbar.show,
            hide: m.topbar.hide,
            setTitle: m.topbar.setTitle,
            setTitleImage: m.topbar.setTitleImage,
            setTint: m.topbar.setTint,
            addButton: m.topbar.addButton,
            removeButtons: m.topbar.removeButtons,
            homePressed: {
                addListener: m.topbar.homePressed.addListener
            }
        },
        tabbar: {
            show: m.tabbar.show,
            hide: m.tabbar.hide,
            addButton: m.tabbar.addButton,
            removeButtons: m.tabbar.removeButtons,
            setTint: m.tabbar.setTint,
            setActiveTint: m.tabbar.setActiveTint,
            setInactive: m.tabbar.setInactive
        },
        payments: {
            purchaseProduct: m.payments.purchaseProduct,
            restoreTransactions: m.payments.restoreTransactions,
            transactionReceived: {
                addListener: m.payments.transactionReceived.addListener
            }
        },
        media: {
            videoPlay: m.media.videoPlay
        },
        document: {
            reload: m.document.reload,
            location: m.document.location
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
    window.forge["file"]["imageURL"] = m.file.URL;
    window.forge["_get"] = h.priv.get;
    window.forge["_receive"] = function() {
        var p = arguments;
        setZeroTimeout(function() {
            h.priv.receive.apply(this, p)
        })
    };
    window.forge["_dispatchMessage"] = h.dispatchMessage
})();
