(function () {
	function p() {
	}
	var q = function () {
		this.prefix = "gtm.";
		this.f = {}
	};
	q.prototype.set = function (a, c) {
		this.f[this.prefix + a] = c
	};
	q.prototype.get = function (a) {
		return this.f[this.prefix + a]
	};

	function isFunction(a) {
		return "function" == typeof a
	}
	function r(a) {
		return "[object Array]" == Object.prototype.toString.call(Object(a))
	}
	function getIndex(arr, el) {
		if (Array.prototype.indexOf) {
			return arr.indexOf(el);
		}
		for (var i = 0; i < arr.length; i++) {
			if (arr[i] === el) {
				return i;
			}
		}
		return -1
	}

	function ba(a) {
		return a ? a.replace(/^\s+|\s+$/g, "") : ""
	}
	function ca() {
		return new Image(1, 1)
	}
	function t() {
		return new Date
	}
	function v(a) {
		var c = document.getElementsByTagName("script")[0];
		c.parentNode.insertBefore(a, c)
	}
	function w(a, c, b) {
		return ("http:" == window.location.protocol ? c : a) + b
	}

	function x(a, c) {
		var b = document.createElement("script");
		b.type = "text/javascript";
		b.async = true;
		b.src = a;
		c && (b.onload = b.onreadystatechange = function () {
			if (!b.readyState || "loaded" == b.readyState || "complete" == b.readyState) b.onload = p, b.onreadystatechange = p, c()
		});
		v(b)
	}
	function da(a) {
		var c = document.createElement("iframe");
		c.height = "0";
		c.width = "0";
		c.style.display = "none";
		c.style.visibility = "hidden";
		v(c);
		c.src = a
	}
	function y(a) {
		var c = ca();
		c.onload = function () {
			c.onload = null
		};
		c.src = a
	}

	function ea(a, c, b, d) {
		var e = "addEventListener",
			f = "attachEvent";
		if (a[e]) a[e](c, b, !! d);
		else if (a[f]) a[f]("on" + c, b)
	}
	function Ka() {
		return C
	}

	function E() {
		var a = D.get("gtm.url");
		a || (a = window.location, a = a.hash ? a.href.replace(a.hash, "") : a.href);
		return a
	}

	function Sa(a) {
		return String(a["1"]) == String(a["2"])
	}
	var ab = function (a) {
		return RegExp(a["2"]).test(a["1"])
	};
	var db = function (a, c, b) {
		if ("SCRIPT" == c.nodeName && "text/gtmscript" == c.type) {
			var d = document.createElement("script");
			d.async = false;
			d.type = "text/javascript";
			d.text = c.text || c.textContent || c.innerHTML || "";
			c.charset && (d.charset = c.charset);
			if (c = c.getAttribute("data-gtmsrc")) d.src = c, d.onload = d.onreadystatechange = function () {
				if (!d.readyState || "loaded" == d.readyState || "complete" == d.readyState) d.onload = p, d.onreadystatechange = p, b()
			};
			a.appendChild(d);
			c || b()
		} else if (c.innerHTML && 0 <= c.innerHTML.toLowerCase().indexOf("<script")) {
			for (var e = []; c.firstChild;) e.push(c.removeChild(c.firstChild));
			a.appendChild(c);
			I(c, e, b)()
		} else a.appendChild(c), b()
	}, I = function (a, c, b) {
		return function () {
			if (0 < c.length) {
				var d = c.shift();
				db(a, d, I(a, c, b))
			} else b()
		}
	}, eb = function (a) {
		var c = document.createElement("div");
		c.innerHTML = "A<div>" + a + "</div>";
		c = c.lastChild;
		for (a = []; c.firstChild;) a.push(c.removeChild(c.firstChild));
		return a
	}, fb = function (a) {
		I(document.body, eb(a["3"]), p)()
	};
	var _e = Ka;
	_e.b = "e";
	var _eq = Sa;
	_eq.b = "eq";
	var _html = fb;
	_html.b = "html";
	var _re = ab;
	_re.b = "re";
	var _u = E;
	_u.b = "u";
	var J = function () {
		this.c = []
	};
	J.prototype.set = function (a, c) {
		this.c.push([a, c]);
		return this
	};
	J.prototype.d = function () {
		for (var a = {}, c = 0; c < this.c.length; c++) a[K(this.c[c][0])] = K(this.c[c][1]);
		return a
	};

	function L(a) {
		this.index = a;
		this.d = function () {
			return M(lb[a])
		}
	}
	function _M(a) {
		return new L(a)
	}

	function N(a) {
		this.d = function () {
			for (var c = [], b = 0; b < a.length; b++) c.push(K(O[a[b]]));
			return c.join("")
		}
	}
	function _T(a) {
		return new N(arguments)
	}
	function P(a) {
		this.d = function () {
			for (var c = String(K(a[0])), b = 1; b < a.length; b++) c = z[a[b]](c);
			return c
		}
	}
	function _E(a, c) {
		return new P(arguments)
	}

	function K(a) {
		var c = a;
		if (a instanceof L || a instanceof J || a instanceof N || a instanceof P) return a.d();
		if (r(a)) for (var c = [], b = 0; b < a.length; b++) c[b] = K(a[b]);
		else if ("object" == typeof a) for (b in c = {}, a) a.hasOwnProperty(b) && (c[b] = K(a[b]));
		return c
	}
	var Q = 12,
		O = mb([_re, _u, _M(0), '.*', _eq, _e, _M(1), 'gtm.js', _html, '\x3cscript type\x3d\x22text/gtmscript\x22 data-gtmsrc\x3d\x22http://localhost/test-env/20121025-seq/test1.js\x22\x3e\x3c/script\x3e\n\x3cscript type\x3d\x22text/gtmscript\x22\x3elog.push(\x221-2\x22);\x3c/script\x3e\n', _T(9), '\x3cscript type\x3d\x22text/gtmscript\x22 data-gtmsrc\x3d\x22http://localhost/test-env/20121025-seq/test2.js\x22\x3e\x3c/script\x3e\n\x3cscript type\x3d\x22text/gtmscript\x22\x3elog.push(\x222-2\x22);\x3c/script\x3e\n', _T(11), '\x3cscript type\x3d\x22text/gtmscript\x22\x3ealert(\x22hello\x22);\x3c/script\x3e\n', _T(13)]),
		nb = R(0, "0:0,0:1,1:2,2:3,0:4,0:5,1:6,2:7,0:8,3:10,3:12,3:14"),
		lb = R(1, "C,g"),
		S = R(1, "N,QD"),
		T = R(1, "AM,AU,Ak"),
		ob = R(2, "D:H::");

	function mb(a) {
		for (var c = [], b = 0; b < a.length; b++) c[b] = W(b, a);
		return c
	}
	function W(a, c) {
		var b = c[a],
			d = b;
		if (b instanceof L || b instanceof P || b instanceof N) d = b;
		else if (r(b)) for (var d = [], e = 0; e < b.length; e++) d[e] = W(b[e], c);
		else if ("object" == typeof b) for (e in d = new J, b) b.hasOwnProperty(e) && d.set(c[e], W(b[e], c));
		return d
	}

	function R(a, c) {
		for (var b = c ? c.split(",") : [], d = 0; d < b.length; d++) {
			var e = b[d] = b[d].split(":");
			0 == a && (e[1] = O[e[1]]);
			if (1 == a) for (var f = X(e[0]), e = b[d] = {}, g = 0; g < Q; g++) f[g] && (e[nb[g][0]] = nb[g][1]);
			if (2 == a) for (g = 0; 4 > g; g++) e[g] = X(e[g]);
		}
		return b
	}

	function X(a) {
		for (var c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_", b = [], d = 0; d < Q; d++) {
			var e = a && a.charAt(Math.floor(d / 6)) || "A";
			b[d] = 1 << d % 6 & c.indexOf(e) ? 1 : 0
		}
		return b
	}
	var C, Y = {
		set: function (a, c) {
			D.set(a, c)
		},
		event: function (a) {
			C = a;
			var c = tb();
			C = null
		}
	};
	var Z = {
		push: function (a) {
			for (var c = arguments, b = 0; b < c.length; b++) try {
				if (isFunction(c[b][0])) c[b][0]();
				else Y[c[b][0]].apply(Y, [].slice.call(c[b], 1))
			} catch (d) {}
		}
	};

	function ub() {
		var a = window["gtm"];
		if (a) {
			var c = a.a;
			r(c) && Z.push.apply(Z, c);
			a.a = Z
		}
	}
	var $ = [];

	function vb(a) {
		for (var c in a) a.hasOwnProperty(c) && Y.set(c, a[c]);
		a.event && Y.event(a.event)
	}
	function wb() {
		for (; null == C && 0 < $.length;) vb($.shift())
	}

	function xb() {
		var a = window["dataLayer"];
		if (a) {
			var c = a.push;
			a.push = function () {
				var b = [].slice.call(arguments, 0);
				c.apply(a, b);
				$.push.apply($, b);
				wb()
			};
			$.push.apply($, a.slice(0));
			wb()
		}
	}
	var D = new q;

	function M(a) {
		return a["0"](a)
	}
	function yb(a) {
		return function (c) {
			if (void 0 === a[c]) {
				var b = S[c] && K(S[c]);
				a[c] = [b && M(b), b]
			}
			return a[c]
		}
	}
	function zb(a, c) {
		for (var b = 0; b < Q; b++) if (a[0][b] && !c(b)[0] || a[2][b] && c(b)[0]) return false;
		return true
	}

	function Ab(a, c, b) {
		for (var d = 0; d < Q; d++) c[d] = c[d] || a[1][d], b[d] = b[d] || a[3][d]
	}
	function Bb(a, c) {
		for (var b = D.get("tagTypeBlacklist") || [], d = [], e = 0; e < Q; e++) a[e] && (!c[e] && 0 > getIndex(b, T[e]["0"].b)) && (d[e] = K(T[e]));
		return d
	}
	function Cb(a, c, b) {
		for (var d = D.get("tagTypeBlacklist") || [], e = 0; e < Q; e++) if (a[e] && !c[e] && 0 > getIndex(d, T[e]["0"].b)) {
			M(b[e])
		}
	}

	function tb() {
		var a = [];
		for (var d = X(), e = X(), f = yb([]), b = 0; b < ob.length; b++) {
			var g = ob[b],
				u = zb(g, f);
			u && Ab(g, d, e);
		}
		f = Bb(d, e);
		Cb(d, e, f);
		return a
	}
	ub();
	xb();
	var Db = false;

	function Eb() {
		if (!Db) {
			Db = true;
			var a = window["dataLayer"];
			a ? a.push({
				event: "gtm.dom"
			}) : Y.event("gtm.dom")
		}
	}
	function Fb() {
		"complete" === document.readyState ? Eb() : ea(window, "load", Eb)
	}
	Fb();
	var _vs = "res_ts:1351350786388000,tpl_cl:35601122,srv_cl:35415459,ds:live";
})()