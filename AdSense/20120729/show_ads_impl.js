(function () {
    var h = void 0,
        i = !0,
        j = null,
        m = !1,
        n = this,
        aa = function (a, b, c) {
            a = a.split(".");
            c = c || n;
            !(a[0] in c) && c.execScript && c.execScript("var " + a[0]);
            for (var d; a.length && (d = a.shift());)!a.length && b !== h ? c[d] = b : c = c[d] ? c[d] : c[d] = {}
        }, ba = function (a) {
            var b = typeof a;
            if ("object" == b) if (a) {
                if (a instanceof Array) return "array";
                if (a instanceof Object) return b;
                var c = Object.prototype.toString.call(a);
                if ("[object Window]" == c) return "object";
                if ("[object Array]" == c || "number" == typeof a.length && "undefined" != typeof a.splice && "undefined" != typeof a.propertyIsEnumerable && !a.propertyIsEnumerable("splice")) return "array";
                if ("[object Function]" == c || "undefined" != typeof a.call && "undefined" != typeof a.propertyIsEnumerable && !a.propertyIsEnumerable("call")) return "function"
            } else return "null";
            else if ("function" == b && "undefined" == typeof a.call) return "object";
            return b
        }, o = function (a) {
            return "array" == ba(a)
        }, ca = function (a) {
            var b = ba(a);
            return "array" == b || "object" == b && "number" == typeof a.length
        }, q = function (a) {
            return "string" == typeof a
        }, da = function (a) {
            var b = typeof a;
            return "object" == b && a != j || "function" == b
        }, ea = function (a, b, c) {
            return a.call.apply(a.bind, arguments)
        }, fa = function (a, b, c) {
            if (!a) throw Error();
            if (2 < arguments.length) {
                var d = Array.prototype.slice.call(arguments, 2);
                return function () {
                    var c = Array.prototype.slice.call(arguments);
                    Array.prototype.unshift.apply(c, d);
                    return a.apply(b, c)
                }
            }
            return function () {
                return a.apply(b, arguments)
            }
        }, r = function (a, b, c) {
            r = Function.prototype.bind && -1 != Function.prototype.bind.toString().indexOf("native code") ? ea : fa;
            return r.apply(j,
            arguments)
        }, ga = function (a, b) {
            var c = Array.prototype.slice.call(arguments, 1);
            return function () {
                var b = Array.prototype.slice.call(arguments);
                b.unshift.apply(b, c);
                return a.apply(this, b)
            }
        }, s = function (a, b, c) {
            aa(a, b, c)
        }, ha = function (a, b, c) {
            a[b] = c
        };
    var t = function (a, b) {
        var c = parseFloat(a);
        return isNaN(c) || 1 < c || 0 > c ? b : c
    }, ia = function (a, b) {
        return /^true$/.test(a) ? i : /^false$/.test(a) ? m : b
    }, ja = /^([\w-]+\.)*([\w-]{2,})(\:[0-9]+)?$/,
        ka = function (a, b) {
            if (!a) return b;
            var c = a.match(ja);
            return c ? c[0] : b
        };
    var la = t("0", 0),
        ma = t("0", 0),
        na = t("0", 0),
        oa = t("0", 0),
        pa = t("0", 0),
        qa = t("0", 0),
        ra = t("0.001", 0),
        sa = t("0.02", 0),
        ta = t("1", 0),
        ua = t("0.01", 0),
        va = t("0.01",
        0),
        wa = t("0", 0);
    var xa = function () {
        var a = "r20120718";
        return a
    }, ya = ia("false", m),
        za = ia("false", m),
        Aa = ia("false", m),
        Ba = ia("false", m);
    var Ca = function () {
        return ka("", "pagead2.googlesyndication.com")
    }, Da = function (a) {
        return a ? "pagead2.googlesyndication.com" : ka("", "pagead2.googlesyndication.com")
    };
    var Ja = function (a, b) {
        if (b) return a.replace(Ea, "&amp;").replace(Fa, "&lt;").replace(Ga, "&gt;").replace(Ha, "&quot;");
        if (!Ia.test(a)) return a; - 1 != a.indexOf("&") && (a = a.replace(Ea, "&amp;")); - 1 != a.indexOf("<") && (a = a.replace(Fa, "&lt;")); - 1 != a.indexOf(">") && (a = a.replace(Ga, "&gt;")); - 1 != a.indexOf('"') && (a = a.replace(Ha, "&quot;"));
        return a
    }, Ea = /&/g,
        Fa = /</g,
        Ga = />/g,
        Ha = /\"/g,
        Ia = /[&<>\"]/,
        La = function (a) {
            var b = {
                "&amp;": "&",
                "&lt;": "<",
                "&gt;": ">",
                "&quot;": '"'
            }, c = document.createElement("div");
            return a.replace(Ka, function (a,
            f) {
                var e = b[a];
                if (e) return e;
                if ("#" == f.charAt(0)) {
                    var g = Number("0" + f.substr(1));
                    isNaN(g) || (e = String.fromCharCode(g))
                }
                e || (c.innerHTML = a + " ", e = c.firstChild.nodeValue.slice(0, - 1));
                return b[a] = e
            })
        }, Ka = /&([^;\s<&]+);?/g,
        Ma = function (a, b) {
            for (var c = b.length, d = 0; d < c; d++) {
                var f = 1 == c ? b : b.charAt(d);
                if (a.charAt(0) == f && a.charAt(a.length - 1) == f) return a.substring(1, a.length - 1)
            }
            return a
        }, Na = {
            "\x00": "\\0",
            "\b": "\\b",
            "\f": "\\f",
            "\n": "\\n",
            "\r": "\\r",
            "\t": "\\t",
            "\x0B": "\\x0B",
            '"': '\\"',
            "\\": "\\\\"
        }, Oa = {
            "'": "\\'"
        }, Qa = function (a) {
            a = String(a);
            if (a.quote) return a.quote();
            for (var b = ['"'], c = 0; c < a.length; c++) {
                var d = a.charAt(c),
                    f = d.charCodeAt(0);
                b[c + 1] = Na[d] || (31 < f && 127 > f ? d : Pa(d))
            }
            b.push('"');
            return b.join("")
        }, Pa = function (a) {
            if (a in Oa) return Oa[a];
            if (a in Na) return Oa[a] = Na[a];
            var b = a,
                c = a.charCodeAt(0);
            if (31 < c && 127 > c) b = a;
            else {
                if (256 > c) {
                    if (b = "\\x", 16 > c || 256 < c) b += "0"
                } else b = "\\u", 4096 > c && (b += "0");
                b += c.toString(16).toUpperCase()
            }
            return Oa[a] = b
        }, Ra = function (a, b) {
            for (var c = 0, d = String(a).replace(/^[\s\xa0]+|[\s\xa0]+$/g, "").split("."), f = String(b).replace(/^[\s\xa0]+|[\s\xa0]+$/g, "").split("."), e = Math.max(d.length, f.length), g = 0; 0 == c && g < e; g++) {
                var l = d[g] || "",
                    p = f[g] || "",
                    k = RegExp("(\\d*)(\\D*)", "g"),
                    V = RegExp("(\\d*)(\\D*)", "g");
                do {
                    var D = k.exec(l) || ["", "", ""],
                        P = V.exec(p) || ["", "", ""];
                    if (0 == D[0].length && 0 == P[0].length) break;
                    var c = 0 == D[1].length ? 0 : parseInt(D[1], 10),
                        eb = 0 == P[1].length ? 0 : parseInt(P[1], 10),
                        c = (c < eb ? -1 : c > eb ? 1 : 0) || ((0 == D[2].length) < (0 == P[2].length) ? -1 : (0 == D[2].length) > (0 == P[2].length) ? 1 : 0) || (D[2] < P[2] ? -1 : D[2] > P[2] ? 1 : 0)
                } while (0 == c)
            }
            return c
        };
    var u = function (a, b) {
        this.width = a;
        this.height = b
    };
    u.prototype.ceil = function () {
        this.width = Math.ceil(this.width);
        this.height = Math.ceil(this.height);
        return this
    };
    u.prototype.floor = function () {
        this.width = Math.floor(this.width);
        this.height = Math.floor(this.height);
        return this
    };
    u.prototype.round = function () {
        this.width = Math.round(this.width);
        this.height = Math.round(this.height);
        return this
    };
    u.prototype.scale = function (a) {
        this.width *= a;
        this.height *= a;
        return this
    };
    var v = document,
        w = window,
        Sa = function (a) {
            var b = j;
            if ((a = a.getElementsByTagName("script")) && a.length) b = a[a.length - 1], b = b.parentNode;
            return b
        }, Ta = Sa(v);
    Da();
    var x = function (a, b) {
        for (var c in a) Object.prototype.hasOwnProperty.call(a, c) && b.call(j, a[c], c, a)
    }, Ua = function (a) {
        return !!a && "function" == typeof a && !! a.call
    }, Va = function (a) {
        return !!a && ("object" == typeof a || "function" == typeof a)
    }, Wa = function (a, b) {
        if (!a) return m;
        var c = i;
        x(b, function (b, f) {
            if (!c || !(f in a) || typeof b != typeof a[f]) c = m
        });
        return c
    }, Xa = function (a, b) {
        if (2 > arguments.length) return a.length;
        for (var c = 1, d = arguments.length; c < d; ++c) a.push(arguments[c]);
        return a.length
    };

    function y(a) {
        return "function" == typeof encodeURIComponent ? encodeURIComponent(a) : escape(a)
    }
    function Ya(a, b, c, d) {
        var d = d || document,
            f = d.createElement("script");
        f.type = "text/javascript";
        b && (f.onreadystatechange !== h ? f.onreadystatechange = function () {
            if ("complete" == f.readyState || "loaded" == f.readyState) try {
                b()
            } catch (a) {}
        } : f.onload = b);
        c && (f.id = c);
        f.src = a;
        var e = d.getElementsByTagName("head")[0];
        if (!e) return m;
        window.setTimeout(function () {
            e.appendChild(f)
        }, 0);
        return i
    }
    var Za = function (a, b, c) {
        return a.addEventListener ? (a.addEventListener(b, c, m), i) : a.attachEvent ? (a.attachEvent("on" + b, c), i) : m
    }, $a = function (a, b, c) {
        return a.removeEventListener ? (a.removeEventListener(b, c, m), i) : a.detachEvent ? (a.detachEvent("on" + b, c), i) : m
    }, ab = function (a) {
        "google_onload_fired" in a || (a.google_onload_fired = m, Za(a, "load", function () {
            a.google_onload_fired = i
        }))
    }, bb = function (a, b) {
        a.google_image_requests || (a.google_image_requests = []);
        var c = a.document.createElement("img");
        c.src = b;
        a.google_image_requests.push(c)
    };

    function cb(a) {
        return a in db ? db[a] : db[a] = -1 != navigator.userAgent.toLowerCase().indexOf(a)
    }
    var db = {};

    function fb() {
        if (navigator.plugins && navigator.mimeTypes.length) {
            var a = navigator.plugins["Shockwave Flash"];
            if (a && a.description) return a.description.replace(/([a-zA-Z]|\s)+/, "").replace(/(\s)+r/, ".")
        } else {
            if (navigator.userAgent && 0 <= navigator.userAgent.indexOf("Windows CE")) {
                for (var a = 3, b = 1; b;) try {
                    b = new ActiveXObject("ShockwaveFlash.ShockwaveFlash." + (a + 1)), a++
                } catch (c) {
                    b = j
                }
                return a.toString()
            }
            if (cb("msie") && !window.opera) {
                b = j;
                try {
                    b = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7")
                } catch (d) {
                    a = 0;
                    try {
                        b = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6"), a = 6, b.AllowScriptAccess = "always"
                    } catch (f) {
                        if (6 == a) return a.toString()
                    }
                    try {
                        b = new ActiveXObject("ShockwaveFlash.ShockwaveFlash")
                    } catch (e) {}
                }
                if (b) return a = b.GetVariable("$version").split(" ")[1], a.replace(/,/g, ".")
            }
        }
        return "0"
    }
    function gb(a) {
        var b = a.google_ad_format;
        return b ? 0 < b.indexOf("_0ads") : "html" != a.google_ad_output && 0 < a.google_num_radlinks
    }
    function z(a) {
        return !!a && -1 != a.indexOf("_sdo")
    }
    var hb = function (a, b) {
        if (!(1E-4 > Math.random())) {
            var c = Math.random();
            if (c < b) return c = Math.floor(c / b * a.length), a[c]
        }
        return j
    }, ib = function (a) {
        a.u_tz = -(new Date).getTimezoneOffset();
        a.u_his = window.history.length;
        a.u_java = navigator.javaEnabled();
        window.screen && (a.u_h = window.screen.height, a.u_w = window.screen.width, a.u_ah = window.screen.availHeight, a.u_aw = window.screen.availWidth, a.u_cd = window.screen.colorDepth);
        navigator.plugins && (a.u_nplug = navigator.plugins.length);
        navigator.mimeTypes && (a.u_nmime = navigator.mimeTypes.length)
    },
    jb = function (a, b) {
        var c = a.length;
        if (0 == c) return 0;
        for (var d = b || 305419896, f = 0; f < c; f++) var e = a.charCodeAt(f),
            d = d ^ (d << 5) + (d >> 2) + e & 4294967295;
        return 0 < d ? d : 4294967296 + d
    }, kb = function (a) {
        if (!a) return "";
        for (var b = [], c = 0; a && 25 > c; a = a.parentNode, ++c) b.push(a.id || "");
        return b.join()
    }, lb = function (a) {
        var b = [w.google_ad_slot, w.google_ad_format, w.google_ad_type, w.google_ad_width, w.google_ad_height];
        a && (a = kb(a)) && b.push(a);
        a = 0;
        b && (a = jb(b.join(":")));
        return a.toString()
    }, mb = function (a) {
        try {
            return !!a.location.href || "" === a.location.href
        } catch (b) {
            return m
        }
    };
    var nb = function (a, b, c, d) {
        b = "border:none;height:" + c + "px;margin:0;padding:0;position:relative;visibility:visible;width:" + b + "px";
        a = ['<ins style="display:inline-table;', b, '">', '<ins id="', a, '" style="display:block;', b, '">', d, "</ins></ins>"];
        return a.join("")
    };
    var ob = function (a, b) {
        for (var c = 0, d = a, f = 0; a != a.parent;) if (a = a.parent, f++, mb(a)) d = a, c = f;
        else if (b) break;
        return {
            win: d,
            level: c
        }
    }, pb = function (a) {
        a = ob(a, i);
        return a.win
    }, qb = function (a) {
        a = ob(a, m);
        return a.win
    }, rb = j,
        sb = function () {
            rb || (rb = pb(window));
            return rb
        };
    var A = function (a) {
        this.o = [];
        this.c = a || window;
        this.d = 0;
        this.n = j
    }, tb = function (a, b) {
        this.fn = a;
        this.win = b
    };
    A.prototype.enqueue = function (a, b) {
        0 == this.d && 0 == this.o.length && (!b || b == window) ? (this.d = 2, this.G(new tb(a, window))) : this.l(a, b)
    };
    A.prototype.l = function (a, b) {
        this.o.push(new tb(a, b || this.c));
        this.q()
    };
    A.prototype.D = function (a) {
        this.d = 1;
        a && (this.n = this.c.setTimeout(r(this.p, this), a))
    };
    A.prototype.p = function () {
        1 == this.d && (this.n != j && (this.c.clearTimeout(this.n), this.n = j), this.d = 0);
        this.q()
    };
    A.prototype.statusz = function () {
        return i
    };
    ha(A.prototype, "nq", A.prototype.enqueue);
    ha(A.prototype, "nqa", A.prototype.l);
    ha(A.prototype, "al", A.prototype.D);
    ha(A.prototype, "rl", A.prototype.p);
    ha(A.prototype, "sz", A.prototype.statusz);
    A.prototype.q = function () {
        this.c.setTimeout(r(this.$, this), 0)
    };
    A.prototype.$ = function () {
        if (0 == this.d && this.o.length) {
            var a = this.o.shift();
            this.d = 2;
            a.win.setTimeout(r(this.G, this, a), 0);
            this.q()
        }
    };
    A.prototype.G = function (a) {
        this.d = 0;
        a.fn()
    };
    var ub = function (a) {
        try {
            return a.sz()
        } catch (b) {
            return m
        }
    }, vb = function () {
        var a = sb().google_jobrunner;
        Va(a) && (ub(a) && Ua(a.nq) && Ua(a.nqa) && Ua(a.al) && Ua(a.rl)) && a.rl()
    };
    var B = !! window.google_async_iframe_id,
        C = B && window.parent || window,
        E = function () {
            if (B && !mb(C)) {
                for (var a = "." + v.domain; 2 < a.split(".").length && !mb(C);) v.domain = a = a.substr(a.indexOf(".") + 1), C = window.parent;
                mb(C) || (C = window)
            }
            return C
        }, wb = function (a) {
            B && a != a.parent && (vb(), a.google_async_iframe_close && a.setTimeout(function () {
                a.document.close()
            }, 0))
        }, xb = function () {
            if (!B) return j;
            var a = window.google_async_iframe_id;
            if (a) {
                for (var a = E().document.getElementById(a), b = 0; a && 3 > b; ++b) a = a.parentNode;
                return a
            }
            return j
        };
    var F = Array.prototype,
        yb = F.indexOf ? function (a, b, c) {
            return F.indexOf.call(a, b, c)
        } : function (a, b, c) {
            c = c == j ? 0 : 0 > c ? Math.max(0, a.length + c) : c;
            if (q(a)) return !q(b) || 1 != b.length ? -1 : a.indexOf(b, c);
            for (; c < a.length; c++) if (c in a && a[c] === b) return c;
            return -1
        }, zb = F.forEach ? function (a, b, c) {
            F.forEach.call(a, b, c)
        } : function (a, b, c) {
            for (var d = a.length, f = q(a) ? a.split("") : a, e = 0; e < d; e++) e in f && b.call(c, f[e], e, a)
        }, Ab = function (a) {
            var b = a.length;
            if (0 < b) {
                for (var c = Array(b), d = 0; d < b; d++) c[d] = a[d];
                return c
            }
            return []
        }, Bb = function (a,
        b, c) {
            return 2 >= arguments.length ? F.slice.call(a, b) : F.slice.call(a, b, c)
        };
    var G = function (a) {
        this.defaultBucket = [];
        this.layers = {};
        for (var b = 0, c = arguments.length; b < c; ++b) this.layers[arguments[b]] = ""
    }, Cb = function (a) {
        for (var b = new G, c = 0, d = a.defaultBucket.length; c < d; ++c) b.defaultBucket.push(a.defaultBucket[c]);
        x(a.layers, r(G.prototype.t, b));
        return b
    };
    G.prototype.statusz = function () {
        return i
    };
    G.prototype.t = function (a, b) {
        this.layers[b] = a
    };
    G.prototype.Z = function (a, b) {
        return "" == a ? "" : !b ? (this.defaultBucket.push(a), a) : this.layers.hasOwnProperty(b) ? this.layers[b] = a : ""
    };
    G.prototype.a = function (a, b, c) {
        return this.T(c) && !(1E-4 > Math.random()) && Math.random() < b ? (b = Math.floor(Math.random() * a.length), this.Z(a[b], c)) : ""
    };
    G.prototype.T = function (a) {
        return !a ? i : this.layers.hasOwnProperty(a) && "" == this.layers[a]
    };
    G.prototype.b = function (a) {
        return this.layers.hasOwnProperty(a) ? this.layers[a] : ""
    };
    G.prototype.geil = G.prototype.b;
    G.prototype.P = function () {
        var a = [],
            b = function (b) {
                "" != b && a.push(b)
            };
        x(this.layers, b);
        return 0 < this.defaultBucket.length && 0 < a.length ? this.defaultBucket.join(",") + "," + a.join(",") : this.defaultBucket.join(",") + a.join(",")
    };
    var Eb = function (a) {
        this.S = a;
        Db(this)
    }, Fb = {
        google_persistent_state: i,
        google_persistent_state_async: i
    }, Gb = {}, Hb = function (a) {
        a = a && Fb[a] ? a : B ? "google_persistent_state_async" : "google_persistent_state";
        if (Gb[a]) return Gb[a];
        if ("google_persistent_state_async" == a) var b = E(),
            c = {};
        else c = b = E();
        var d = b[a];
        return "object" != typeof d || "object" != typeof d.S ? b[a] = Gb[a] = new Eb(c) : Gb[a] = d
    }, Db = function (a) {
        H(a, 3, j);
        H(a, 4, 0);
        H(a, 5, 0);
        H(a, 6, 0);
        H(a, 7, (new Date).getTime());
        H(a, 8, {});
        H(a, 9, {});
        H(a, 10, {});
        H(a, 11, []);
        H(a, 12, 0);
        H(a, 14, {})
    }, Ib = function (a) {
        switch (a) {
        case 3:
            return "google_exp_persistent";
        case 4:
            return "google_num_sdo_slots";
        case 5:
            return "google_num_0ad_slots";
        case 6:
            return "google_num_ad_slots";
        case 7:
            return "google_correlator";
        case 8:
            return "google_prev_ad_formats_by_region";
        case 9:
            return "google_prev_ad_slotnames_by_region";
        case 10:
            return "google_num_slots_by_channel";
        case 11:
            return "google_viewed_host_channels";
        case 12:
            return "google_num_slot_to_show";
        case 14:
            return "gaGlobal"
        }
    }, I = function (a, b) {
        var c = Ib(b);
        return c = a.S[c]
    }, J = function (a, b, c) {
        return a.S[Ib(b)] = c
    }, H = function (a, b, c) {
        a = a.S;
        b = Ib(b);
        return a[b] === h ? a[b] = c : a[b]
    }, Jb = function (a, b) {
        return J(a, 3, b)
    };
    var Kb, Lb, Mb = function (a) {
        try {
            return a.statusz()
        } catch (b) {
            return m
        }
    }, K = function () {
        if (Kb && Mb(Kb)) return Kb;
        var a = Hb(),
            b = I(a, 3);
        return !b || !Va(b) || !Wa(b, G.prototype) || !Mb(b) ? Kb = Jb(a, new G(1, 2, 3, 4, 5, 6, 7, 9, 10, 11)) : Kb = b
    }, Nb = function () {
        Lb || (Lb = Cb(K()));
        return Lb
    }, Ob = {
        CONTROL: "86748120",
        EXPERIMENT_BADGE_AFTER_TITLE: "86748121",
        EXPERIMENT_BADGE_AFTER_TEXT: "86748122",
        EXPERIMENT_BADGE_BENEATH_CREATIVE: "86748123"
    }, Pb = {
        CONTROL: "86726840",
        EXPERIMENT_BADGE_AFTER_TITLE: "86726841",
        EXPERIMENT_BADGE_BENEATH_CREATIVE: "86726842"
    },
    Qb = {
        CONTROL: "317150300",
        EXPERIMENT: "317150301"
    }, Rb = {
        CONTROL: "33895180",
        EXPERIMENT: "33895181"
    };
    var Sb = {
        google: 1,
        googlegroups: 1,
        gmail: 1,
        googlemail: 1,
        googleimages: 1,
        googleprint: 1
    };

    function Tb(a) {
        if (Aa) return m;
        a = a.google_page_location || a.google_page_url;
        if (!a) return m;
        a = a.toString();
        0 == a.indexOf("http://") ? a = a.substring(7, a.length) : 0 == a.indexOf("https://") && (a = a.substring(8, a.length));
        var b = a.indexOf("/"); - 1 == b && (b = a.length);
        a = a.substring(0, b);
        a = a.split(".");
        b = m;
        3 <= a.length && (b = a[a.length - 3] in Sb);
        2 <= a.length && (b = b || a[a.length - 2] in Sb);
        return b
    }
    var Ub = function (a) {
        var b = K();
        return "44901228" == b.b(1) ? m : "44901229" == b.b(1) || Math.random() < ma ? 1 == Math.floor(a / 2) % 2 : m
    };
    var L = function (a, b, c) {
        c || (c = Ba ? "https" : "http");
        return [c, "://", a, b].join("")
    };
    var M = function (a, b) {
        this.I = a;
        this.m = b ? b.m : [];
        this.B = b ? b.B : m;
        this.M = b ? b.M : "";
        this.C = b ? b.C : 0;
        this.r = b ? b.r : "";
        this.f = b ? b.f : []
    };
    M.prototype.X = function (a, b) {
        var c = this.I[b],
            d = this.m;
        this.I[b] = function (b) {
            if (b && 0 < b.length) {
                var e = 1 < b.length ? b[1].url : j;
                d.push([a, - 1 != b[0].url.indexOf("&") ? "document" in n ? La(b[0].url) : b[0].url.replace(/&([^;]+);/g, function (a, b) {
                    switch (b) {
                    case "amp":
                        return "&";
                    case "lt":
                        return "<";
                    case "gt":
                        return ">";
                    case "quot":
                        return '"';
                    default:
                        if ("#" == b.charAt(0)) {
                            var c = Number("0" + b.substr(1));
                            if (!isNaN(c)) return String.fromCharCode(c)
                        }
                        return a
                    }
                }) : b[0].url, e])
            }
            c(b)
        }
    };
    var Vb = L(Da(m), "/pagead/osd.js");
    M.prototype.V = function () {
        this.B || (ab(E()), Ya(Vb), this.B = i)
    };
    M.prototype.s = function (a, b, c) {
        var d = this.m;
        if (0 < d.length) for (var f = a.document.getElementsByTagName("a"), e = 0; e < f.length; e++) for (var g = 0; g < d.length; g++) if (0 <= f[e].href.indexOf(d[g][1])) {
            var l = f[e].parentNode;
            if (d[g][2]) for (var p = l, k = 0; 4 > k; k++) {
                if (0 <= p.innerHTML.indexOf(d[g][2])) {
                    l = p;
                    break
                }
                p = p.parentNode
            }
            b(l, d[g][0], c || 0);
            d.splice(g, 1);
            break
        }
        if (0 < d.length && a != sb()) try {
            this.s(a.parent, b, c)
        } catch (V) {}
    };
    M.prototype.A = function (a) {
        this.s(this.I, a, 1);
        for (var b = this.f.length, c = 0; c < b; c++) {
            var d = this.f[c];
            d.J && a(d.J, d.ca, d.fa)
        }
    };
    M.prototype.setupOse = function (a) {
        if (this.getOseId()) return this.getOseId();
        var b = window.google_enable_ose,
            c;
        b === i ? c = 2 : b !== m && ((c = hb([2], sa)) || (c = hb([3], ta)));
        if (!c) return 0;
        this.C = c;
        this.r = String(a || 0);
        return this.getOseId()
    };
    M.prototype.getEid = function () {
        return ""
    };
    M.prototype.getOseExpId = function () {
        return this.M
    };
    M.prototype.getOseId = function () {
        return this.C
    };
    M.prototype.getCorrelator = function () {
        return this.r
    };
    M.prototype.z = function () {
        return this.m.length + this.f.length
    };
    M.prototype.registerAdBlock = function (a, b, c, d) {
        "js" == c ? this.X(a, "google_ad_request_done") : this.f.push(new Wb(a, b, c, d));
        this.V()
    };
    var Xb = function () {
        var a = E(),
            b = a.__google_ad_urls;
        if (!b) return a.__google_ad_urls = new M(a);
        try {
            b.getOseId()
        } catch (c) {
            return a.__google_ad_urls = new M(a, b)
        }
        return b
    }, Wb = function (a, b, c, d) {
        this.ca = a;
        this.fa = b;
        this.ga = c;
        this.J = d
    };
    s("Goog_AdSense_getAdAdapterInstance", Xb);
    s("Goog_AdSense_OsdAdapter", M);
    s("Goog_AdSense_OsdAdapter.prototype.numBlocks", M.prototype.z);
    s("Goog_AdSense_OsdAdapter.prototype.getBlocks", M.prototype.A);
    s("Goog_AdSense_OsdAdapter.prototype.getEid", M.prototype.getEid);
    s("Goog_AdSense_OsdAdapter.prototype.getOseExpId", M.prototype.getOseExpId);
    s("Goog_AdSense_OsdAdapter.prototype.getOseId", M.prototype.getOseId);
    s("Goog_AdSense_OsdAdapter.prototype.getCorrelator", M.prototype.getCorrelator);
    s("Goog_AdSense_OsdAdapter.prototype.setupOse", M.prototype.setupOse);
    s("Goog_AdSense_OsdAdapter.prototype.registerAdBlock", M.prototype.registerAdBlock);
    var N = function (a, b) {
        this.x = a !== h ? a : 0;
        this.y = b !== h ? b : 0
    }, Yb = function (a, b) {
        return new N(a.x + b.x, a.y + b.y)
    };
    var Zb = function (a, b, c) {
        for (var d in a) b.call(c, a[d], d, a)
    }, $b = "constructor hasOwnProperty isPrototypeOf propertyIsEnumerable toLocaleString toString valueOf".split(" "),
        ac = function (a, b) {
            for (var c, d, f = 1; f < arguments.length; f++) {
                d = arguments[f];
                for (c in d) a[c] = d[c];
                for (var e = 0; e < $b.length; e++) c = $b[e], Object.prototype.hasOwnProperty.call(d, c) && (a[c] = d[c])
            }
        };
    var O, bc, cc, dc, ec, fc, gc, hc, ic = function () {
        return n.navigator ? n.navigator.userAgent : j
    }, jc = function () {
        ec = dc = cc = bc = O = m;
        var a;
        if (a = ic()) {
            var b = n.navigator;
            O = 0 == a.indexOf("Opera");
            bc = !O && -1 != a.indexOf("MSIE");
            dc = (cc = !O && -1 != a.indexOf("WebKit")) && -1 != a.indexOf("Mobile");
            ec = !O && !cc && "Gecko" == b.product
        }
    };
    jc();
    var kc = O,
        Q = bc,
        R = ec,
        S = cc,
        lc = dc,
        mc = function () {
            var a = n.navigator;
            return a && a.platform || ""
        }, nc = mc(),
        oc = function () {
            fc = -1 != nc.indexOf("Mac");
            gc = -1 != nc.indexOf("Win");
            hc = -1 != nc.indexOf("Linux")
        };
    oc();
    var pc = fc,
        qc = gc,
        rc = hc,
        tc = function () {
            var a = "",
                b;
            kc && n.opera ? (a = n.opera.version, a = "function" == typeof a ? a() : a) : (R ? b = /rv\:([^\);]+)(\)|;)/ : Q ? b = /MSIE\s+([^\);]+)(\)|;)/ : S && (b = /WebKit\/(\S+)/), b && (a = (a = b.exec(ic())) ? a[1] : ""));
            return Q && (b = sc(), b > parseFloat(a)) ? String(b) : a
        }, sc = function () {
            var a = n.document;
            return a ? a.documentMode : h
        }, uc = tc(),
        vc = {}, T = function (a) {
            return vc[a] || (vc[a] = 0 <= Ra(uc, a))
        }, wc = {}, xc = function (a) {
            return wc[a] || (wc[a] = Q && !! document.documentMode && document.documentMode >= a)
        };
    var yc, zc = !Q || xc(9);
    !R && !Q || Q && xc(9) || R && T("1.9.1");
    Q && T("9");
    var Ac = function (a) {
        a = a.className;
        return q(a) && a.match(/\S+/g) || []
    }, Cc = function (a, b) {
        var c = Ac(a),
            d = Bb(arguments, 1),
            f = c.length + d.length;
        Bc(c, d);
        a.className = c.join(" ");
        return c.length == f
    }, Bc = function (a, b) {
        for (var c = 0; c < b.length; c++) 0 <= yb(a, b[c]) || a.push(b[c])
    };
    var Dc = function (a) {
        return a ? new U(W(a)) : yc || (yc = new U)
    }, Ec = function (a) {
        return q(a) ? document.getElementById(a) : a
    }, Fc = Ec,
        Hc = function (a, b) {
            Zb(b, function (b, d) {
                "style" == d ? a.style.cssText = b : "class" == d ? a.className = b : "for" == d ? a.htmlFor = b : d in Gc ? a.setAttribute(Gc[d], b) : 0 == d.lastIndexOf("aria-", 0) || 0 == d.lastIndexOf("data-", 0) ? a.setAttribute(d, b) : a[d] = b
            })
        }, Gc = {
            cellpadding: "cellPadding",
            cellspacing: "cellSpacing",
            colspan: "colSpan",
            frameborder: "frameBorder",
            height: "height",
            maxlength: "maxLength",
            role: "role",
            rowspan: "rowSpan",
            type: "type",
            usemap: "useMap",
            valign: "vAlign",
            width: "width"
        }, Ic = function (a) {
            a = a.document;
            a = "CSS1Compat" == a.compatMode ? a.documentElement : a.body;
            return new u(a.clientWidth, a.clientHeight)
        }, Jc = function (a) {
            var b = !S && "CSS1Compat" == a.compatMode ? a.documentElement : a.body,
                a = a.parentWindow || a.defaultView;
            return new N(a.pageXOffset || b.scrollLeft, a.pageYOffset || b.scrollTop)
        }, Lc = function (a, b, c) {
            return Kc(document, arguments)
        }, Kc = function (a, b) {
            var c = b[0],
                d = b[1];
            if (!zc && d && (d.name || d.type)) {
                c = ["<", c];
                d.name && c.push(' name="',
                Ja(d.name), '"');
                if (d.type) {
                    c.push(' type="', Ja(d.type), '"');
                    var f = {};
                    ac(f, d);
                    d = f;
                    delete d.type
                }
                c.push(">");
                c = c.join("")
            }
            c = a.createElement(c);
            d && (q(d) ? c.className = d : o(d) ? Cc.apply(j, [c].concat(d)) : Hc(c, d));
            2 < b.length && Mc(a, c, b, 2);
            return c
        }, Mc = function (a, b, c, d) {
            function f(c) {
                c && b.appendChild(q(c) ? a.createTextNode(c) : c)
            }
            for (; d < c.length; d++) {
                var e = c[d];
                ca(e) && !(da(e) && 0 < e.nodeType) ? zb(Nc(e) ? Ab(e) : e, f) : f(e)
            }
        }, Oc = function (a) {
            if (1 != a.nodeType) return m;
            switch (a.tagName) {
            case "APPLET":
            case "AREA":
            case "BASE":
            case "BR":
            case "COL":
            case "FRAME":
            case "HR":
            case "IMG":
            case "INPUT":
            case "IFRAME":
            case "ISINDEX":
            case "LINK":
            case "NOFRAMES":
            case "NOSCRIPT":
            case "META":
            case "OBJECT":
            case "PARAM":
            case "SCRIPT":
            case "STYLE":
                return m
            }
            return i
        },
        Pc = function (a, b) {
            a.appendChild(b)
        }, Qc = function (a, b) {
            Mc(W(a), a, arguments, 1)
        }, Rc = function (a) {
            return a && a.parentNode ? a.parentNode.removeChild(a) : j
        }, Sc = function (a, b) {
            if (a.contains && 1 == b.nodeType) return a == b || a.contains(b);
            if ("undefined" != typeof a.compareDocumentPosition) return a == b || Boolean(a.compareDocumentPosition(b) & 16);
            for (; b && a != b;) b = b.parentNode;
            return b == a
        }, W = function (a) {
            return 9 == a.nodeType ? a : a.ownerDocument || a.document
        }, Nc = function (a) {
            if (a && "number" == typeof a.length) {
                if (da(a)) return "function" == typeof a.item || "string" == typeof a.item;
                if ("function" == ba(a)) return "function" == typeof a.item
            }
            return m
        }, U = function (a) {
            this.k = a || n.document || document
        };
    U.prototype.createElement = function (a) {
        return this.k.createElement(a)
    };
    U.prototype.createTextNode = function (a) {
        return this.k.createTextNode(a)
    };
    U.prototype.R = function () {
        return "CSS1Compat" == this.k.compatMode
    };
    U.prototype.u = function () {
        return Jc(this.k)
    };
    U.prototype.appendChild = Pc;
    U.prototype.append = Qc;
    U.prototype.canHaveChildren = Oc;
    U.prototype.removeNode = Rc;
    U.prototype.contains = Sc;
    var Tc = function (a, b) {
        var c = b || w;
        a && c.top != c && (c = c.top);
        try {
            return c.document && !c.document.body ? new u(-1, - 1) : Ic(c || window)
        } catch (d) {
            return new u(-12245933, - 12245933)
        }
    }, Uc = function (a) {
        if (a == a.top) return 0;
        var b = [];
        b.push(a.document.URL);
        a.name && b.push(a.name);
        var c = i,
            a = Tc(!c, a);
        b.push(a.width.toString());
        b.push(a.height.toString());
        return jb(b.join(""))
    };
    var Vc = function () {
        var a = "";
        if (document.documentMode) {
            var b = document.createElement("IFRAME");
            b.frameBorder = 0;
            b.style.height = 0;
            b.style.width = 0;
            b.style.position = "absolute";
            if (document.body) {
                document.body.appendChild(b);
                try {
                    var c = b.contentWindow.document;
                    c.open();
                    c.write("<!DOCTYPE html>");
                    c.close();
                    a += c.documentMode
                } catch (d) {}
                document.body.removeChild(b)
            }
        }
        return a
    };
    var Wc = function (a) {
        this.c = a;
        a.google_iframe_oncopy || (a.google_iframe_oncopy = {
            handlers: {},
            log: [],
            shouldLog: 0.01 > Math.random() ? i : m
        });
        this.H = a.google_iframe_oncopy;
        a.setTimeout(r(this.Y, this), 3E4)
    };
    Ja("var i=this.id,s=window.google_iframe_oncopy,H=s&&s.handlers,h=H&&H[i],w=this.contentWindow,d;try{d=w.document}catch(e){}if(h&&d&&(!d.body||!d.body.firstChild)){if(h.call){i+='.call';setTimeout(h,0)}else if(h.match){i+='.nav';w.location.replace(h)}s.log&&s.log.push(i)}");
    Wc.prototype.set = function (a, b) {
        this.H.handlers[a] = b;
        this.c.addEventListener && this.c.addEventListener("load", r(this.O, this, a), m)
    };
    Wc.prototype.O = function (a) {
        var a = this.c.document.getElementById(a),
            b = a.contentWindow.document;
        if (a.onload && b && (!b.body || !b.body.firstChild)) a.onload()
    };
    Wc.prototype.Y = function () {
        if (this.H.shouldLog) {
            var a = this.H.log,
                b = this.c.document;
            a.length && (b = ["/pagead/gen_204?id=iframecopy&log=", y(a.join("-")), "&url=", y(b.URL.substring(0, 512)), "&ref=", y(b.referrer.substring(0, 512))].join(""), a.length = 0, bb(this.c, L(Ca(), b)))
        }
    };
    var Xc = function (a) {
        var a = a.webkitVisibilityState || a.mozVisibilityState || "",
            b = {
                visible: 1,
                hidden: 2,
                prerender: 3,
                preview: 4
            };
        return b[a] || 0
    };
    var Yc = function (a, b) {
        var c = W(a);
        return c.defaultView && c.defaultView.getComputedStyle && (c = c.defaultView.getComputedStyle(a, j)) ? c[b] || c.getPropertyValue(b) || "" : ""
    }, X = function (a, b) {
        return Yc(a, b) || (a.currentStyle ? a.currentStyle[b] : j) || a.style && a.style[b]
    }, Zc = function (a) {
        a = a ? W(a) : document;
        return Q && !xc(9) && !Dc(a).R() ? a.body : a.documentElement
    }, $c = function (a) {
        if (lc && S) {
            var b = a.ownerDocument.defaultView;
            if (b != b.top) return m
        }
        return !!a.getBoundingClientRect
    }, ad = function (a) {
        var b = a.getBoundingClientRect();
        Q && (a = a.ownerDocument, b.left -= a.documentElement.clientLeft + a.body.clientLeft, b.top -= a.documentElement.clientTop + a.body.clientTop);
        return b
    }, bd = function (a) {
        if (Q && !xc(8)) return a.offsetParent;
        for (var b = W(a), c = X(a, "position"), d = "fixed" == c || "absolute" == c, a = a.parentNode; a && a != b; a = a.parentNode) if (c = X(a, "position"), d = d && "static" == c && a != b.documentElement && a != b.body, !d && (a.scrollWidth > a.clientWidth || a.scrollHeight > a.clientHeight || "fixed" == c || "absolute" == c || "relative" == c)) return a;
        return j
    }, cd = function (a) {
        var b,
        c = W(a),
            d = X(a, "position"),
            f = R && c.getBoxObjectFor && !a.getBoundingClientRect && "absolute" == d && (b = c.getBoxObjectFor(a)) && (0 > b.screenX || 0 > b.screenY),
            e = new N(0, 0),
            g = Zc(c);
        if (a == g) return e;
        if ($c(a)) b = ad(a), a = Dc(c).u(), e.x = b.left + a.x, e.y = b.top + a.y;
        else if (c.getBoxObjectFor && !f) b = c.getBoxObjectFor(a), a = c.getBoxObjectFor(g), e.x = b.screenX - a.screenX, e.y = b.screenY - a.screenY;
        else {
            b = a;
            do {
                e.x += b.offsetLeft;
                e.y += b.offsetTop;
                b != a && (e.x += b.clientLeft || 0, e.y += b.clientTop || 0);
                if (S && "fixed" == X(b, "position")) {
                    e.x += c.body.scrollLeft;
                    e.y += c.body.scrollTop;
                    break
                }
                b = b.offsetParent
            } while (b && b != a);
            if (kc || S && "absolute" == d) e.y -= c.body.offsetTop;
            for (b = a;
            (b = bd(b)) && b != c.body && b != g;) if (e.x -= b.scrollLeft, !kc || "TR" != b.tagName) e.y -= b.scrollTop
        }
        return e
    }, ed = function (a, b) {
        var c = new N(0, 0),
            d = W(a) ? W(a).parentWindow || W(a).defaultView : window,
            f = a;
        do {
            var e = d == b ? cd(f) : dd(f);
            c.x += e.x;
            c.y += e.y
        } while (d && d != b && (f = d.frameElement) && (d = d.parent));
        return c
    }, dd = function (a) {
        var b = new N;
        if (1 == a.nodeType) {
            if ($c(a)) {
                var c = ad(a);
                b.x = c.left;
                b.y = c.top
            } else {
                var c = Dc(a).u(),
                    d = cd(a);
                b.x = d.x - c.x;
                b.y = d.y - c.y
            }
            R && !T(12) && (b = Yb(b, fd(a)))
        } else c = "function" == ba(a.getBrowserEvent), d = a, a.targetTouches ? d = a.targetTouches[0] : c && a.getBrowserEvent().targetTouches && (d = a.getBrowserEvent().targetTouches[0]), b.x = d.clientX, b.y = d.clientY;
        return b
    }, gd = function (a, b, c, d) {
        if (/^\d+px?$/.test(b)) return parseInt(b, 10);
        var f = a.style[c],
            e = a.runtimeStyle[c];
        a.runtimeStyle[c] = a.currentStyle[c];
        a.style[c] = b;
        b = a.style[d];
        a.style[c] = f;
        a.runtimeStyle[c] = e;
        return b
    }, hd = function (a) {
        var b = W(a),
            c = "";
        if (b.body.createTextRange) {
            b = b.body.createTextRange();
            b.moveToElementText(a);
            try {
                c = b.queryCommandValue("FontName")
            } catch (d) {
                c = ""
            }
        }
        c || (c = X(a, "fontFamily"));
        a = c.split(",");
        1 < a.length && (c = a[0]);
        return Ma(c, "\"'")
    }, id = /[^\d]+$/,
        jd = function (a) {
            return (a = a.match(id)) && a[0] || j
        }, kd = {
            cm: 1,
            "in": 1,
            mm: 1,
            pc: 1,
            pt: 1
        }, ld = {
            em: 1,
            ex: 1
        }, md = function (a) {
            var b = X(a, "fontSize"),
                c = jd(b);
            if (b && "px" == c) return parseInt(b, 10);
            if (Q) {
                if (c in kd) return gd(a, b, "left", "pixelLeft");
                if (a.parentNode && 1 == a.parentNode.nodeType && c in ld) return a = a.parentNode, c = X(a, "fontSize"), gd(a, b == c ? "1em" : b, "left", "pixelLeft")
            }
            c = Lc("span", {
                style: "visibility:hidden;position:absolute;line-height:0;padding:0;margin:0;border:0;height:1em;"
            });
            Pc(a, c);
            b = c.offsetHeight;
            Rc(c);
            return b
        }, nd = /matrix\([0-9\.\-]+, [0-9\.\-]+, [0-9\.\-]+, [0-9\.\-]+, ([0-9\.\-]+)p?x?, ([0-9\.\-]+)p?x?\)/,
        fd = function (a) {
            var b;
            Q ? b = "-ms-transform" : S ? b = "-webkit-transform" : kc ? b = "-o-transform" : R && (b = "-moz-transform");
            var c;
            b && (c = X(a, b));
            c || (c = X(a, "transform"));
            if (!c) return new N(0,
            0);
            a = c.match(nd);
            return !a ? new N(0, 0) : new N(parseFloat(a[1]), parseFloat(a[2]))
        };
    var od = {
        google_ad_channel: "channel",
        google_ad_host: "host",
        google_ad_host_channel: "h_ch",
        google_ad_host_tier_id: "ht_id",
        google_ad_section: "region",
        google_ad_type: "ad_type",
        google_adtest: "adtest",
        google_allow_expandable_ads: "ea",
        google_alternate_ad_url: "alternate_ad_url",
        google_alternate_color: "alt_color",
        google_bid: "bid",
        google_city: "gcs",
        google_color_bg: "color_bg",
        google_color_border: "color_border",
        google_color_line: "color_line",
        google_color_link: "color_link",
        google_color_text: "color_text",
        google_color_url: "color_url",
        google_contents: "contents",
        google_country: "gl",
        google_cpm: "cpm",
        google_cust_age: "cust_age",
        google_cust_ch: "cust_ch",
        google_cust_gender: "cust_gender",
        google_cust_id: "cust_id",
        google_cust_interests: "cust_interests",
        google_cust_job: "cust_job",
        google_cust_l: "cust_l",
        google_cust_lh: "cust_lh",
        google_cust_u_url: "cust_u_url",
        google_disable_video_autoplay: "disable_video_autoplay",
        google_ed: "ed",
        google_encoding: "oe",
        google_feedback: "feedback_link",
        google_flash_version: "flash",
        google_font_face: "f",
        google_font_size: "fs",
        google_hints: "hints",
        google_kw: "kw",
        google_kw_type: "kw_type",
        google_language: "hl",
        google_page_url: "url",
        google_region: "gr",
        google_reuse_colors: "reuse_colors",
        google_safe: "adsafe",
        google_tag_info: "gut",
        google_targeting: "targeting",
        google_ui_features: "ui",
        google_ui_version: "uiv",
        google_video_doc_id: "video_doc_id",
        google_video_product_type: "video_product_type"
    }, pd = {
        google_ad_block: "ad_block",
        google_ad_client: "client",
        google_ad_format: "format",
        google_ad_output: "output",
        google_ad_callback: "callback",
        google_ad_height: "h",
        google_ad_override: "google_ad_override",
        google_ad_slot: "slotname",
        google_ad_width: "w",
        google_ctr_threshold: "ctr_t",
        google_image_size: "image_size",
        google_last_modified_time: "lmt",
        google_max_num_ads: "num_ads",
        google_max_radlink_len: "max_radlink_len",
        google_mtl: "mtl",
        google_num_radlinks: "num_radlinks",
        google_num_radlinks_per_unit: "num_radlinks_per_unit",
        google_only_ads_with_video: "only_ads_with_video",
        google_rl_dest_url: "rl_dest_url",
        google_rl_filtering: "rl_filtering",
        google_rl_mode: "rl_mode",
        google_rt: "rt",
        google_skip: "skip",
        google_tdsma: "tdsma",
        google_tfs: "tfs",
        google_tl: "tl"
    }, qd = {
        google_only_pyv_ads: "pyv",
        google_only_userchoice_ads: "uc",
        google_scs: "scs",
        google_with_pyv_ads: "withpyv",
        google_previous_watch: "p_w",
        google_previous_searches: "p_s",
        google_yt_pt: "yt_pt",
        google_yt_up: "yt_up"
    };
    var rd = function (a) {
        var b = 2;
        try {
            a.top.document == a.document ? b = 0 : mb(a.top) && (b = 1)
        } catch (c) {}
        return b
    };

    function sd(a, b) {
        try {
            return a.top.document == b
        } catch (c) {}
        return m
    }
    function td(a, b, c, d) {
        c = c || a.google_ad_width;
        d = d || a.google_ad_height;
        if (sd(a, b)) return m;
        var f = b.documentElement;
        if (c && d) {
            var e = 1,
                g = 1;
            a.innerHeight ? (e = a.innerWidth, g = a.innerHeight) : f && f.clientHeight ? (e = f.clientWidth, g = f.clientHeight) : b.body && (e = b.body.clientWidth, g = b.body.clientHeight);
            if (g > 2 * d || e > 2 * c) return m
        }
        return i
    }
    function ud(a, b) {
        x(b, function (b, d) {
            a["google_" + d] = b
        })
    }
    var vd = function (a) {
        var b = E().google_top_url;
        if (b) return {
            url: b,
            isTopUrl: i,
            isJp: i
        };
        b = a.location.href;
        if (a == a.top) return {
            url: b,
            isTopUrl: i,
            isJp: m
        };
        var c = m,
            d = a.document;
        d && d.referrer && (b = d.referrer, a.parent == a.top && (c = i));
        return {
            url: b,
            isTopUrl: c,
            isJp: m
        }
    };

    function wd(a, b, c, d) {
        var f = rd(E()),
            a = !! a.google_referrer_url,
            e = 4;
        !b && 1 == f ? e = 5 : !b && 2 == f ? e = 6 : b && 1 == f ? e = 7 : b && 2 == f && (e = 8);
        e += 5 * a;
        c && (e |= 16);
        d && (e |= 32);
        return "" + e
    }
    function xd(a, b, c) {
        a.page_url = !c ? b.URL : b.referrer;
        a.page_location = j
    }

    function yd(a, b, c, d) {
        a.page_url = b.google_page_url;
        a.page_location = (!d ? c.URL : c.referrer) || "EMPTY"
    }
    function zd(a, b) {
        var c = {}, d = qb(window),
            d = vd(d),
            f = td(E(), b, a.google_ad_width, a.google_ad_height);
        c.iframing = wd(a, f, d.isTopUrl, d.isJp);
        a.google_page_url ? yd(c, a, b, f) : xd(c, b, f);
        c.last_modified_time = b.URL == c.page_url ? Date.parse(b.lastModified) / 1E3 : j;
        c.referrer_url = f ? a.google_referrer_url : a.google_page_url && a.google_referrer_url ? a.google_referrer_url : b.referrer;
        return c
    }

    function Ad(a) {
        for (var b = {}, c = a.URL.substring(a.URL.lastIndexOf("http")); - 1 < c.indexOf("%");) try {
            c = decodeURIComponent(c)
        } catch (d) {
            break
        }
        b.iframing = j;
        b.page_url = c;
        b.page_location = a.URL;
        b.last_modified_time = j;
        b.referrer_url = c;
        return b
    }
    function Bd(a) {
        var b = Cd(a, E().document);
        ud(a, b)
    }
    function Cd(a, b) {
        var c;
        return c = a.google_page_url == j && Dd[b.domain] ? Ad(b) : zd(a, b)
    }
    var Dd = {
        "ad.yieldmanager.com": i
    };
    var Ed = function (a, b, c) {
        b = r(b, n, a);
        a = window.onerror;
        window.onerror = b;
        try {
            c()
        } catch (d) {
            var c = d.toString(),
                f = "";
            d.fileName && (f = d.fileName);
            var e = -1;
            d.lineNumber && (e = d.lineNumber);
            b = b(c, f, e);
            if (!b) throw d;
        }
        window.onerror = a
    };
    s("google_protectAndRun", Ed);
    var Gd = function (a, b, c, d) {
        if (0.01 > Math.random()) {
            var f = v,
                a = ["/pagead/gen_204", "?id=jserror", "&jscb=", ya ? 1 : 0, "&jscd=", za ? 1 : 0, "&context=", y(a), "&msg=", y(b), "&file=", y(c), "&line=", y(d.toString()), "&url=", y(f.URL.substring(0, 512)), "&ref=", y(f.referrer.substring(0, 512))];
            a.push(Fd());
            a = L(Ca(), a.join(""));
            bb(w, a)
        }
        return !Aa
    };
    s("google_handleError", Gd);
    var Y = function (a) {
        Hd |= a
    }, Hd = 0,
        Fd = function () {
            var a = ["&client=", y(w.google_ad_client), "&format=", y(w.google_ad_format), "&slotname=", y(w.google_ad_slot), "&output=", y(w.google_ad_output), "&ad_type=", y(w.google_ad_type)];
            return a.join("")
        };
    var Ld = function () {
        window.google_ad_output == j && (window.google_ad_output = "html");
        if (z(window.google_ad_format)) {
            var a = window.google_ad_format.match(/^(\d+)x(\d+)_.*/);
            a && (window.google_ad_width = parseInt(a[1], 10), window.google_ad_height = parseInt(a[2], 10), window.google_ad_output = "html")
        }
        window.google_ad_format = Id(window.google_ad_format, String(window.google_ad_output), Number(window.google_ad_width), Number(window.google_ad_height), window.google_ad_slot, !! window.google_override_format);
        window.google_ad_client = Jd(window.google_ad_format, window.google_ad_client);
        Bd(window);
        window.google_flash_version == j && (window.google_flash_version = fb());
        window.google_ad_section = window.google_ad_section || window.google_ad_region || "";
        window.google_country = window.google_country || window.google_gl || "";
        a = (new Date).getTime();
        o(window.google_color_bg) && (window.google_color_bg = Kd(window.google_color_bg, a));
        o(window.google_color_text) && (window.google_color_text = Kd(window.google_color_text, a));
        o(window.google_color_link) && (window.google_color_link = Kd(window.google_color_link, a));
        o(window.google_color_url) && (window.google_color_url = Kd(window.google_color_url, a));
        o(window.google_color_border) && (window.google_color_border = Kd(window.google_color_border, a));
        o(window.google_color_line) && (window.google_color_line = Kd(window.google_color_line, a))
    }, Md = function (a) {
        x(od, function (b, c) {
            a[c] = j
        });
        x(pd, function (b, c) {
            a[c] = j
        });
        x(qd, function (b, c) {
            a[c] = j
        });
        a.google_container_id = j;
        a.google_enable_async = j;
        a.google_eids = j;
        a.google_page_location = j;
        a.google_referrer_url = j;
        a.google_show_ads_impl = j;
        a.google_ad_region = j;
        a.google_gl = j;
        a.google_iframe_name = j;
        a.google_loader_used = j
    }, Kd = function (a, b) {
        Y(2);
        return a[b % a.length]
    }, Jd = function (a, b) {
        if (!b) return "";
        b = b.toLowerCase();
        return b = z(a) ? Nd(b) : Od(b)
    }, Od = function (a) {
        a && "ca-" != a.substring(0, 3) && (a = "ca-" + a);
        return a
    }, Nd = function (a) {
        a && "ca-aff-" != a.substring(0, 7) && (a = "ca-aff-" + a);
        return a
    }, Id = function (a, b, c, d, f, e) {
        !a && "html" == b && (a = c + "x" + d);
        return a = (!a ? 0 : !f || e) ? a.toLowerCase() : ""
    };
    var Z = navigator;

    function Pd(a, b, c, d, f) {
        var e = Math.round((new Date).getTime() / 1E3),
            g = window.google_analytics_domain_name,
            a = "undefined" == typeof g ? Qd("auto", a) : Qd(g, a),
            l = -1 < b.indexOf("__utma=" + a + "."),
            g = -1 < b.indexOf("__utmb=" + a),
            p = -1 < b.indexOf("__utmc=" + a),
            k = Hb("google_persistent_state"),
            k = I(k, 14) || J(k, 14, {});
        l ? (b = b.split("__utma=" + a + ".")[1].split(";")[0].split("."), g && p ? k.sid = b[3] + "" : k.sid || (k.sid = e + ""), k.vid = b[0] + "." + b[1], k.from_cookie = i) : (k.sid || (k.sid = e + ""), k.vid || (k.vid = (Math.round(2147483647 * Math.random()) ^ Rd(b,
        c, d, f) & 2147483647) + "." + e), k.from_cookie = m);
        k.dh = a;
        k.hid || (k.hid = Math.round(2147483647 * Math.random()));
        return k
    }
    function Rd(a, b, c, d) {
        var f = [Z.appName, Z.version, Z.language ? Z.language : Z.browserLanguage, Z.platform, Z.userAgent, Z.javaEnabled() ? 1 : 0].join("");
        c ? f += c.width + "x" + c.height + c.colorDepth : window.java && (c = java.awt.Toolkit.getDefaultToolkit().getScreenSize(), f += c.screen.width + "x" + c.screen.height);
        f += a;
        f += d || "";
        for (a = f.length; 0 < b;) f += b-- ^ a++;
        return Sd(f)
    }

    function Sd(a) {
        var b = 1,
            c = 0,
            d;
        if (!(a == h || "" == a)) {
            b = 0;
            for (d = a.length - 1; 0 <= d; d--) c = a.charCodeAt(d), b = (b << 6 & 268435455) + c + (c << 14), c = b & 266338304, b = 0 != c ? b ^ c >> 21 : b
        }
        return b
    }
    function Qd(a, b) {
        if (!a || "none" == a) return 1;
        a = String(a);
        "auto" == a && (a = b, "www." == a.substring(0, 4) && (a = a.substring(4, a.length)));
        return Sd(a.toLowerCase())
    };
    var $ = function (a, b, c, d, f, e) {
        this.U = m;
        this.ba = a;
        this.F = f;
        this.K = e;
        this.ha = b;
        this.i = +c;
        this.h = +d;
        this.j = []
    };
    $.prototype.collapse = function () {
        var a = this.w();
        a && (this.aa(), this.U = m)
    };
    $.prototype.g = function (a) {
        this.e(a, "zIndex", "999999")
    };
    $.prototype.N = function (a, b, c) {
        this.e(a, "width", b + "px");
        this.e(a, "height", c + "px");
        this.g(a)
    };
    $.prototype.v = function () {
        var a = [],
            b = this.w();
        if (!b) return a;
        a.push(b);
        this.F && this.K && a.push(this.F.document.getElementById(this.K));
        return a
    };
    $.prototype.expand = function (a, b, c) {
        var d = this.v();
        if (!(0 >= d.length)) {
            for (var f = 0, e = d.length; f < e; ++f) this.N(d[f], a, b);
            d = d[d.length - 1];
            a > this.i && (0 == c || 3 == c) && this.e(d, "left", "-" + (a - this.i) + "px");
            b > this.h && (1 == c || 0 == c) && this.e(d, "top", "-" + (b - this.h) + "px");
            a = d.parentNode;
            b = a.parentNode;
            "ins" == a.nodeName.toLowerCase() && (this.g(a), this.g(b));
            for (a = b.parentNode; a && a.style && "body" != a.nodeName.toLowerCase(); a = a.parentNode) "visible" != a.style.overflow && this.e(a, "overflow", "visible");
            this.U = i
        }
    };
    $.prototype.L = function (a, b) {
        var c = this.v(),
            c = c[c.length - 1],
            c = dd(c),
            d = this.F || window,
            d = Ic(d || window),
            f = a - this.i,
            e = b - this.h,
            g = c.y,
            e = e > g,
            l = d.height - (c.y + this.h),
            g = e || l >= g,
            e = c.x,
            f = f > e,
            c = d.width - (c.x + this.i),
            c = f || c >= e,
            d = 2;
        g && !c ? d = 3 : !g && c ? d = 1 : !g && !c && (d = 0);
        return d
    };
    $.prototype.w = function () {
        this.Q || (this.Q = v.getElementById(this.ba));
        return this.Q
    };
    $.prototype.e = function (a, b, c) {
        this.j.push(new Td(a, b, c))
    };
    $.prototype.aa = function () {
        for (var a = 0, b = this.j.length; a < b; a++) this.j[a].undo();
        this.j.length = 0
    };
    var Td = function (a, b, c) {
        this.W = a;
        this.da = b;
        this.ea = a.style[b];
        this.W.style[b] = c
    };
    Td.prototype.undo = function () {
        this.W.style[this.da] = this.ea
    };
    var Ud = function (a, b) {
        var c = a.L(b.width, b.height);
        a.expand(b.width, b.height, c);
        return {
            width: b.width,
            height: b.height,
            direction: c
        }
    }, Vd = function (a) {
        a.collapse();
        return {}
    }, Wd = function (a, b) {
        var c = {}, d = new $(b.id, "", b.width, b.height, b.topMostFriendlyWindow, b.friendlyIframeId);
        c.expand = ga(Ud, d);
        c.collapse = ga(Vd, d);
        d = {};
        iframes.open(a, b, d, c)
    }, Xd = function () {
        iframes.setHandler("expandable", {
            onOpen: function (a) {
                var b = a.getOpenParams(),
                    c = b.container,
                    d = b.width,
                    f = b.height,
                    e = b.id,
                    g = e + "_anchor";
                if (b.friendlyIframeId) c = document.createElement("div"), document.body.appendChild(c);
                else {
                    var l = nb(g, d, f, "");
                    c ? (c.innerHTML = l, c = c.firstChild.firstChild) : (document.write(l), c = document.getElementById(g))
                }
                return a = a.openInto(c, {
                    id: e,
                    width: d,
                    height: f,
                    style: b.cssStyle
                })
            },
            onReady: function () {},
            onClose: function () {}
        })
    };
    var Yd = (new Date).getTime();
    var Zd = function (a) {
        var b = "google_unique_id";
        a[b] ? ++a[b] : a[b] = 1;
        return a[b]
    }, $d = function (a) {
        var b = "google_unique_id",
            a = a[b];
        return "number" == typeof a ? a : 0
    }, be = function () {
        var a = ae,
            b = Qb;
        if (window.google_top_experiment) {
            var c;
            switch (window.google_top_experiment) {
            case a.EXPERIMENT:
                c = b.EXPERIMENT;
                break;
            case a.CONTROL:
                c = b.CONTROL
            }
            c && K().a([c], 1, 10)
        }
    }, ce = function () {
        var a = Rb;
        window.google_adk_sa && (a = "control" == window.google_adk_sa ? a.CONTROL : a.EXPERIMENT, K().a([a], 1, 4))
    }, de = function () {
        window.google_top_experiment && be();
        window.google_adk_sa && ce()
    }, ee = function (a) {
        return a.google_slot_list && a.google_slot_list.join ? a.google_slot_list.join(",").slice(-100) : ""
    }, ae = {
        EXPERIMENT: "jp_e",
        CONTROL: "jp_c"
    };
    var fe = Da(m),
        ge = {};

    function he(a, b) {
        a && "" != a && (ge[b] = 1 == b ? ge[b] ? ge[b] + ("," + a) : a : a)
    }
    function ie() {
        var a = [];
        x(ge, function (b) {
            a.push(b)
        });
        return a.join(",")
    }
    function je(a, b) {
        if (o(a)) for (var c = 0; c < a.length; c++) q(a[c]) && he(a[c], b)
    }
    var ke = m;

    function le(a, b, c) {
        var d = "script";
        if (B ? 1 == $d(a) : !$d(a)) K().a(["30143102", "30143103"], qa, 5), K().a(["33895250", "33895251"], ra, 7);
        ke = me(a, b);
        ke || (a.google_allow_expandable_ads = m);
        var a = !ne(),
            f = Ob;
        if (ke && a) {
            K().a(["30143205", "30143206"], 0, 5);
            "undefined" != typeof window.iframes && Y(8);
            K().a(["33895220", "33895222", "33895224", "33895225"], na, 5);
            var e = [f.CONTROL, f.EXPERIMENT_BADGE_AFTER_TITLE, f.EXPERIMENT_BADGE_AFTER_TEXT, f.EXPERIMENT_BADGE_BENEATH_CREATIVE];
            K().a(e, oa, 5);
            var e = Pb,
                g = [e.CONTROL, e.EXPERIMENT_BADGE_AFTER_TITLE,
                e.EXPERIMENT_BADGE_BENEATH_CREATIVE];
            K().a(g, pa, 5);
            g = K().b(5);
            "33895222" == g || "33895224" == g || "33895225" == g || g == f.CONTROL || g == f.EXPERIMENT_BADGE_AFTER_TITLE || g == f.EXPERIMENT_BADGE_AFTER_TEXT || g == f.EXPERIMENT_BADGE_BENEATH_CREATIVE || g == e.CONTROL || g == e.EXPERIMENT_BADGE_AFTER_TITLE || g == e.EXPERIMENT_BADGE_BENEATH_CREATIVE ? b.write("<" + d + ' src="https://ssl.gstatic.com/gb/js/gcm_01fa0dcbd17f41ae40e2e164313785ce.js"></' + d + ">") : "33895223" == g ? b.write("<" + d + ' src="' + L(fe, "/pagead/js/gcm_01fa0dcbd17f41ae40e2e164313785ce.js") + '"></' + d + ">") : "33895221" == g ? b.write("<" + d + ' src="' + L(fe, "/pagead/js/expansion_embed_exp.js") + '"></' + d + ">") : b.write("<" + d + ' src="' + L(fe, "/pagead/expansion_embed.js") + '"></' + d + ">");
            ("33895224" == g || "33895225" == g) && b.write("<" + d + ' src="' + L(fe, "/pagead/expansion_embed.js") + '"></' + d + ">")
        }(c = a || c) && cb("msie") && !window.opera ? b.write("<" + d + ' src="' + L(fe, "/pagead/render_ads.js") + '"></' + d + ">") : b.write("<" + d + '>google_protectAndRun("ads_core.google_render_ad", google_handleError, google_render_ad);</' + d + ">")
    }

    function oe(a) {
        return a != j ? '"' + a + '"' : '""'
    }
    var pe = function (a, b) {
        if ("about:blank" === b) return b;
        var c = b.slice(-1),
            d = "?" == c || "#" == c ? "" : "&",
            f = [b],
            c = function (a, b) {
                if (a || 0 === a || a === m) "boolean" == typeof a && (a = a ? 1 : 0), Xa(f, d, b, "=", y(a)), d = "&"
            };
        x(a, c);
        return f.join("")
    }, qe = function () {
        var a = Q && T("8"),
            b = R && T("1.8.1"),
            c = S && T("525");
        return qc && (a || b || c) || pc && (c || b) || rc && (c || b) ? i : m
    };

    function ne() {
        return "object" == typeof ExpandableAdSlotFactory && "function" == typeof ExpandableAdSlotFactory.createIframe
    }

    function me(a, b) {
        var c = a.google_ad_width,
            d = a.google_ad_height,
            f = E();
        try {
            if (a.google_allow_expandable_ads === m || !b.body || "html" != a.google_ad_output || td(f, f.document, c, d) || !re(a) || isNaN(a.google_ad_height) || isNaN(a.google_ad_width) || !qe() || b.domain != a.location.hostname) return m
        } catch (e) {
            return m
        }
        return i
    }
    function re(a) {
        var b = a.google_ad_format;
        return z(b) || gb(a) && "468x15_0ads_al" != b ? m : i
    }
    var se = function () {
        var a = Xb(),
            b = Hb();
        return a.setupOse(I(b, 7))
    };

    function te(a, b, c, d) {
        B || Zd(a);
        var f = $d(a),
            c = pe({
                ifi: f
            }, c),
            c = c.substring(0, 1991),
            c = c.replace(/%\w?$/, ""),
            e = "script",
            g = "google_ads_frame" + f,
            l = K().b(5),
            p = j;
        if (("js" == a.google_ad_output || "json_html" == a.google_ad_output) && (a.google_ad_request_done || a.google_radlink_request_done)) b.write("<" + e + ' language="JavaScript1.1" src=' + oe(ue(c)) + "></" + e + ">");
        else if ("html" == a.google_ad_output) {
            var e = ke ? c.replace(/&ea=[^&]*/, "") + "&ea=0" : c,
                e = ['<iframe id="', g, '" name="', g, '" width=', oe(String(a.google_ad_width)), " height=",
                oe(String(a.google_ad_height)), ' frameborder="0" src=', oe(ue(e)), ' marginwidth="0" marginheight="0" vspace="0" hspace="0" allowtransparency="true" scrolling="no"></iframe>'].join(""),
                k = Ob,
                V = Pb,
                d = a.google_container_id || d || j;
            "object" == typeof iframes && "function" == typeof iframes.open && ("33895222" == l || "33895223" == l || "33895225" == l || l == k.CONTROL || l == k.EXPERIMENT_BADGE_AFTER_TITLE || l == k.EXPERIMENT_BADGE_AFTER_TEXT || l == k.EXPERIMENT_BADGE_BENEATH_CREATIVE || l == V.CONTROL || l == V.EXPERIMENT_BADGE_AFTER_TITLE || l == V.EXPERIMENT_BADGE_BENEATH_CREATIVE) ? (Xd(), iframes.setVersionOverride("gcm_01fa0dcbd17f41ae40e2e164313785ce.js"), d = {
                style: "expandable",
                id: g,
                rpcToken: "adsense_rpc_key",
                width: a.google_ad_width,
                height: a.google_ad_height,
                cssStyle: "left:0;position:absolute;top:0",
                topMostFriendlyWindow: E(),
                friendlyIframeId: a.google_async_iframe_id
            }, Wd(ue(c), d)) : ke && ne() ? a["google_expandable_ad_slot" + f] = ExpandableAdSlotFactory.createIframe(g, ue(c), a.google_ad_width, a.google_ad_height, d, E(), a.google_async_iframe_id) : a.google_container_id ? ve(a.google_container_id, b, e) : b.write(e);
            p = document.getElementById(g);
            B && we(a.google_async_iframe_id, e);
            if (xe) {
                if (3 != Xc(b)) ye("/pagead/ads?", p);
                else {
                    var D = function () {
                        ye("/pagead/ads?", p);
                        $a(v, "webkitvisibilitychange", D)
                    };
                    Za(b, "webkitvisibilitychange", D)
                }
                xe = m
            }
        }
        "30143103" == l && (1 == f && !a.google_container_id) && b.write('<iframe height=1 src="' + L(fe, "/pagead/s/iframes_api_loader.html") + '" style="position:absolute;visibility:hidden" width=1></iframe>');
        a = Xb();
        a.getOseId() && a.registerAdBlock(c, 1, String(window.google_ad_output || ""), p);
        return c
    }
    var we = function (a, b) {
        var c = "javascript:" + Qa(["<!DOCTYPE html><html><body>", b, "</body></html>"].join("")),
            d = E();
        (new Wc(d)).set(a, c)
    };

    function ze(a) {
        Md(a)
    }
    var Ce = function (a, b) {
        var c = "44901217" == Nb().b(2);
        if (!Ae(c)) return m;
        var d = !Tb(window) ? Ub($d(window)) ? ka("", "googleads2.g.doubleclick.net") : ka("", "googleads.g.doubleclick.net") : Ca(),
            c = Be(a);
        if (z(window.google_ad_format)) return window.google_ad_url = "about:blank", i;
        d = L(d, b + "");
        window.google_ad_url = pe(c, d);
        return i
    }, xe = m,
        De = function (a) {
            var b = K().b(9);
            return "html" === w.google_ad_output && 3 == Xc(v) && "373855200" === b ? (xe = i, Y(32), Ce(a, "/pagead/gen_204?id=prerender")) : Ce(a, "/pagead/ads?")
        }, He = function (a) {
            a.dt = Yd;
            B && window.google_bpp && (a.bpp = window.google_bpp);
            a.shv = xa();
            var b = !! window.google_test_1,
                c = !! window.google_test_2;
            b && (a.tsi = c ? "3" : "2");
            a.jsv = "/r20110914".replace("/", "");
            window.google_loader_used && (a.saldr = 1);
            var b = Hb(),
                c = I(b, 8),
                d = window.google_ad_section,
                f = window.google_ad_format,
                e = window.google_ad_slot;
            c[d] && (z(f) || (a.prev_fmts = c[d]));
            var g = I(b, 9);
            g[d] && (a.prev_slotnames = g[d].toLowerCase());
            f ? z(f) || (c[d] = c[d] ? c[d] + ("," + f) : f) : e && (g[d] = g[d] ? g[d] + ("," + e) : e);
            a.correlator = I(b, 7);
            if (window.google_ad_channel) {
                c = I(b, 10);
                d = "";
                f = window.google_ad_channel.split(Ee);
                for (e = 0; e < f.length; e++) g = f[e], c[g] ? d += g + "+" : c[g] = i;
                a.pv_ch = d
            }
            window.google_ad_host_channel && (b = Fe(window.google_ad_host_channel, I(b, 11)), a.pv_h_ch = b);
            ya && (a.jscb = 1);
            za && (a.jscd = 1);
            a.frm = window.google_iframing;
            a.adk = Ge();
            window.google_adk_sa && (a.adk_sa = window.google_adk_sa);
            b = E().document;
            b = Pd(b.domain, b.cookie, window.history.length, window.screen,
            b.referrer);
            a.ga_vid = b.vid;
            a.ga_sid = b.sid;
            a.ga_hid = b.hid;
            a.ga_fc = b.from_cookie;
            a.ga_wpids = window.google_analytics_uacct
        }, Ie = function (a) {
            var b = E(),
                c = m,
                d = Tc(c, b.top);
            d && (a.biw = d.width, a.bih = d.height);
            if (b.top != b && (b = Tc(c, b))) a.isw = b.width, a.ish = b.height
        }, Ge = function () {
            var a = B ? xb() : Ta;
            return lb(a)
        }, Je = function (a) {
            var b = Uc(E());
            0 != b && (a.ifk = b.toString())
        }, Ke = function (a) {
            var b = Xb();
            (b = b.getOseId()) && (a.oid = b)
        };

    function Fe(a, b) {
        for (var c = a.split("|"), d = -1, f = [], e = 0; e < c.length; e++) {
            var g = c[e].split(Ee);
            b[e] || (b[e] = {});
            for (var l = "", p = 0; p < g.length; p++) {
                var k = g[p];
                "" != k && (b[e][k] ? l += "+" + k : b[e][k] = i)
            }
            l = l.slice(1);
            f[e] = l;
            "" != l && (d = e)
        }
        c = "";
        if (-1 < d) {
            for (e = 0; e < d; e++) c += f[e] + "|";
            c += f[d]
        }
        return c
    }
    function Le() {
        var a = ["44901228", "44901229"];
        K().a(a, la, 1);
        a = ["44901218", "44901217"];
        K().a(a, ua, 2);
        "html" === w.google_ad_output && 3 == Xc(v) && (a = ["373855201", "373855200"], K().a(a, va, 9))
    }
    var Me = function () {
        if ("html" == w.google_ad_output) {
            var a = ["39482000", "39482001"];
            Nb().a(a, wa, 11)
        }
    }, Ne = function () {
        sb() != E() && Y(4);
        Ba && Y(16)
    };

    function Oe() {
        Ne();
        if (B ? 1 == $d(window) : !$d(window)) Le(), se();
        Me();
        var a = j,
            b = "";
        (a = window.google_async_iframe_id) ? a = E().document.getElementById(a) : (b = "google_temp_span", a = Pe(b));
        var c = De(a);
        a && a.id == b && Rc(a);
        c && (te(window, document, window.google_ad_url), ze(window));
        wb(window)
    }
    var Qe = function (a) {
        x(pd, function (b, c) {
            a[b] = window[c]
        });
        x(od, function (b, c) {
            a[b] = window[c]
        });
        x(qd, function (b, c) {
            a[b] = window[c]
        })
    }, Re = function (a) {
        o(window.google_eids) && 0 !== window.google_eids.length && Y(64);
        je(window.google_eids, 1);
        a.eid = ie();
        var b = Nb().P();
        0 < a.eid.length && 0 < b.length && (a.eid += ",");
        a.eid += b
    };

    function Se(a, b, c, d) {
        a = Gd(a, b, c, d);
        le(window, document);
        return a
    }
    function Te() {
        Ld()
    }

    function Ue(a) {
        for (var b = {}, a = a.split("?"), a = a[a.length - 1].split("&"), c = 0; c < a.length; c++) {
            var d = a[c].split("=");
            if (d[0]) try {
                b[d[0].toLowerCase()] = 1 < d.length ? window.decodeURIComponent ? decodeURIComponent(d[1].replace(/\+/g, " ")) : unescape(d[1]) : ""
            } catch (f) {}
        }
        return b
    }
    function Ve() {
        var a = window,
            b = Ue(document.URL);
        b.google_ad_override && (a.google_ad_override = b.google_ad_override, a.google_adtest = "on")
    }

    function ve(a, b, c) {
        if (a && (a = b.getElementById(a)) && c && "" != c.length) a.style.visibility = "visible", a.innerHTML = c
    }
    var We = function (a, b, c) {
        var d = a.indexOf(b);
        if (-1 === d) return a;
        b = d + b.length;
        return a.substr(0, d) + c + a.substr(b)
    }, ye = function (a, b) {
        var c = b.src,
            d = We(c, "/pagead/gen_204?id=prerender", a);
        d !== c && (b.src = d)
    }, Xe = function (a, b) {
        b.dff = hd(a).toLowerCase();
        b.dfs = md(a)
    }, Ye = function (a, b) {
        if ("html" == w.google_ad_output) try {
            var c = ed(a, window.top);
            b.adx = Math.round(c.x);
            b.ady = Math.round(c.y)
        } catch (d) {
            b.adx = -12245933, b.ady = -12245933
        }
    }, Ze = function (a) {
        a.ref = window.google_referrer_url;
        a.loc = window.google_page_location;
        "33895251" == K().b(7) && (a.aasl = ee(E()));
        var b = sb(),
            b = vd(b);
        b.url != a.url && (b.url != a.ref && b.url != a.loc) && (a.top = b.url);
        a.docm = Vc()
    }, Ae = function (a) {
        var b = Hb(),
            c = I(b, 8),
            d = I(b, 9),
            f = window.google_ad_section;
        if (z(window.google_ad_format)) {
            if (4 < J(b, 4, I(b, 4) + 1) && !a) return m
        } else if (gb(window)) {
            if (3 < J(b, 5, I(b, 5) + 1) && !a) return m
        } else {
            var e = J(b, 6, I(b, 6) + 1);
            if (window.google_num_slots_to_rotate) {
                if (Y(1), c[f] = "", d[f] = "", I(b, 12) || J(b, 12, (new Date).getTime() % window.google_num_slots_to_rotate + 1), I(b, 12) != e) return m
            } else if (!a && 6 < e && "" == f) return m
        }
        return i
    }, Be = function (a) {
        var b = {};
        Qe(b);
        He(b);
        ib(b);
        a && (Xe(a, b), Ye(a, b));
        Ie(b);
        Je(b);
        Re(b);
        Ke(b);
        Ze(b);
        b.fu = Hd;
        return b
    }, Pe = function (a) {
        var b = window.google_container_id,
            c = b && Fc(b) || Fc(a);
        !c && (!b && a) && (document.write("<span id=" + a + "></span>"), c = Fc(a));
        return c
    }, ue = function (a) {
        var b = (new Date).getTime() - Yd;
        return pe({
            dtd: 1E4 > b ? b : "M"
        }, a)
    }, Ee = /[+, ]/;
    window.google_render_ad = Oe;

    function $e() {
        Ve();
        de();
        var a = window.google_start_time;
        "number" == typeof a && (Yd = a, window.google_start_time = j);
        Ed("show_ads.google_init_globals", Se, Te);
        le(window, document)
    }
    Ed("show_ads.main", Gd, $e);
})()