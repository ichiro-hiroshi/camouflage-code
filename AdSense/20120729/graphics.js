(function () {
    AdSenseGraphics.POS_TOP_LEFT_ = 0;
    AdSenseGraphics.POS_TOP_RIGHT_ = 1;
    AdSenseGraphics.POS_BOTTOM_LEFT_ = 2;
    AdSenseGraphics.POS_BOTTOM_RIGHT_ = 3;
    AdSenseGraphics.X_INTERCEPT_TOP_ = 0;
    AdSenseGraphics.X_INTERCEPT_BOTTOM_ = 1;
    AdSenseGraphics.Y_INTERCEPT_LEFT_ = 2;
    AdSenseGraphics.Y_INTERCEPT_RIGHT_ = 3;
    AdSenseGraphics.USER_AGENT_ = navigator.userAgent;
    AdSenseGraphics.IS_OPERA_ = "undefined" != typeof opera;
    AdSenseGraphics.IS_IE_ = !AdSenseGraphics.IS_OPERA_ && -1 != AdSenseGraphics.USER_AGENT_.indexOf("MSIE");
    AdSenseGraphics.IS_SAFARI_ = !AdSenseGraphics.IS_OPERA_ && -1 != AdSenseGraphics.USER_AGENT_.indexOf("Safari");
    AdSenseGraphics.S_CURVE_CLASS_NAME_ = "curve";
    AdSenseGraphics.ROUNDED_CORNER_BG_CLASS_NAME_ = "rc_bg";
    AdSenseGraphics.ROUNDED_CORNER_BORDER_CLASS_NAME_ = "rc_border";
    AdSenseGraphics.SIGMOID_FUNCTION_WIDTH = 12;
    AdSenseGraphics.SIGMOID_FUNCTION_OFFSET = 6;

    function AdSenseGraphics() {}
    AdSenseGraphics.prototype.getPixelLeftX_ = function (a) {
        return a
    };
    AdSenseGraphics.prototype.getPixelRightX_ = function (a) {
        return a + 1
    };
    AdSenseGraphics.prototype.getPixelBottomY_ = function (a) {
        return a
    };
    AdSenseGraphics.prototype.getPixelTopY_ = function (a) {
        return a + 1
    };
    AdSenseGraphics.prototype.computeSigmoid_ = function (a, c, b) {
        a = 12 * a / c - 6;
        return b / (1 + Math.exp(a))
    };
    AdSenseGraphics.prototype.computeSigmoidInverse_ = function (a, c, b) {
        if (0 >= a) return c;
        if (a >= b) return 0;
        a /= b;
        a = Math.log((1 - a) / a);
        return c * (a + 6) / 12
    };
    AdSenseGraphics.prototype.computeCircle_ = function (a, c) {
        var b = Math.sqrt(Math.pow(c, 2) - Math.pow(a, 2));
        return isNaN(b) ? 0 : b
    };
    AdSenseGraphics.prototype.addStyleRule_ = function (a, c) {
        if (AdSenseGraphics.IS_IE_) document.styleSheets[0].addRule(a, c);
        else {
            var b = document.createElement("style");
            b.type = "text/css";
            var d = AdSenseGraphics.IS_SAFARI_ ? "innerText" : "innerHTML";
            b[d] = a + "{" + c + "}";
            d = document.getElementsByTagName("head")[0];
            d.appendChild(b)
        }
    };
    AdSenseGraphics.prototype.createDiv_ = function (a, c, b, d, f) {
        var g = document.createElement("div"),
            e = "position:absolute;overflow:hidden;left:",
            e = e + a,
            e = e + "px;top:",
            e = e + c,
            e = e + "px;width:",
            e = e + b,
            e = e + "px;height:",
            e = e + d,
            e = e + "px;";
        null != f && (e += "opacity:", e += f, AdSenseGraphics.IS_IE_ && (e += ";filter: alpha(opacity=", e += Math.round(100 * f), e += ");"));
        g.style.cssText = e;
        return g
    };
    AdSenseGraphics.prototype.getCirclePixelIntercepts_ = function (a, c, b) {
        var d = Array(4);
        d[AdSenseGraphics.Y_INTERCEPT_LEFT_] = this.computeCircle_(this.getPixelLeftX_(a), b);
        d[AdSenseGraphics.Y_INTERCEPT_RIGHT_] = this.computeCircle_(this.getPixelRightX_(a), b);
        d[AdSenseGraphics.X_INTERCEPT_BOTTOM_] = this.computeCircle_(this.getPixelBottomY_(c), b);
        d[AdSenseGraphics.X_INTERCEPT_TOP_] = this.computeCircle_(this.getPixelTopY_(c), b);
        return d
    };
    AdSenseGraphics.prototype.getSigmoidPixelIntercepts_ = function (a, c, b, d) {
        var f = Array(4);
        f[AdSenseGraphics.Y_INTERCEPT_LEFT_] = this.computeSigmoid_(this.getPixelLeftX_(a), b, d);
        f[AdSenseGraphics.Y_INTERCEPT_RIGHT_] = this.computeSigmoid_(this.getPixelRightX_(a), b, d);
        f[AdSenseGraphics.X_INTERCEPT_BOTTOM_] = this.computeSigmoidInverse_(this.getPixelBottomY_(c), b, d);
        f[AdSenseGraphics.X_INTERCEPT_TOP_] = this.computeSigmoidInverse_(this.getPixelTopY_(c), b, d);
        return f
    };
    AdSenseGraphics.prototype.getSigmoidAntiAliasOpacity_ = function (a, c, b, d) {
        b = this.getSigmoidPixelIntercepts_(a, c, b, d);
        a = this.getAntiAliasOpacity_(a, c, b);
        return -1 == a ? 0 : a
    };
    AdSenseGraphics.prototype.getCircleAntiAliasOpacity_ = function (a, c, b, d) {
        b = this.getCirclePixelIntercepts_(a, c, b);
        a = this.getAntiAliasOpacity_(a, c, b);
        return -1 == a ? 0 : d ? 1 - a : a
    };
    AdSenseGraphics.prototype.getAntiAliasOpacity_ = function (a, c, b) {
        var d = 0,
            f = Array(2),
            g = Array(2),
            e = !1,
            l = !1,
            h = !1,
            m = !1,
            j = this.getPixelBottomY_(c),
            c = this.getPixelTopY_(c),
            k = this.getPixelLeftX_(a),
            a = this.getPixelRightX_(a);
        b[AdSenseGraphics.Y_INTERCEPT_LEFT_] >= j && b[AdSenseGraphics.Y_INTERCEPT_LEFT_] < c ? (e = !0, f[0] = 0, g[0] = b[AdSenseGraphics.Y_INTERCEPT_LEFT_] - j) : b[AdSenseGraphics.X_INTERCEPT_TOP_] >= k && b[AdSenseGraphics.X_INTERCEPT_TOP_] < a && (l = !0, f[0] = b[AdSenseGraphics.X_INTERCEPT_TOP_] - k, g[0] = 1);
        if (!l && !e) return -1;
        b[AdSenseGraphics.Y_INTERCEPT_RIGHT_] >= j && b[AdSenseGraphics.Y_INTERCEPT_RIGHT_] < c ? (h = !0, f[1] = 1, g[1] = b[AdSenseGraphics.Y_INTERCEPT_RIGHT_] - j) : b[AdSenseGraphics.X_INTERCEPT_BOTTOM_] >= k && b[AdSenseGraphics.X_INTERCEPT_BOTTOM_] < a && (m = !0, f[1] = b[AdSenseGraphics.X_INTERCEPT_BOTTOM_] - k, g[1] = 0);
        e && h ? (f = g[0] <= g[1] ? g[0] : g[1], g = g[0] > g[1] ? g[0] : g[1], d = f + (g - f) / 2) : e && m ? d = g[0] * f[1] / 2 : l && h ? d = 1 - (1 - f[0]) * (1 - g[1]) / 2 : l && m && (g = f[0] <= f[1] ? f[0] : f[1], f = f[0] > f[1] ? f[0] : f[1], d = g + (f - g) / 2);
        return d
    };
    AdSenseGraphics.prototype.createSigmoidCurve = function (a, c, b, d, f, g) {
        this.addStyleRule_("#" + a.id + " ." + AdSenseGraphics.S_CURVE_CLASS_NAME_ + " div", "background-color: " + d);
        a.style.display = "none";
        d = this.createDiv_(0, 0, c, b);
        d.className = AdSenseGraphics.S_CURVE_CLASS_NAME_;
        for (var e, l = b - 1, h, m, j, k = b - 1, p = 0; p < c; ++p) {
            e = l;
            l = Math.floor(this.computeSigmoid_(p + 1, c, b));
            m = f ? p : c - p;
            h = g ? 0 : b - l;
            0 < l && (h = this.createDiv_(m, h, 1, l), d.appendChild(h));
            for (var n = l; n <= e; ++n) j = this.getSigmoidAntiAliasOpacity_(p, n, c, b), h = g ? n : k - n, h = this.createDiv_(m, h, 1, 1, j), d.appendChild(h)
        }
        a.appendChild(d);
        a.style.display = ""
    };
    AdSenseGraphics.prototype.applyRoundedCorner = function (a, c, b, d, f, g, e, l) {
        this.addStyleRule_("#" + a.id + " ." + AdSenseGraphics.ROUNDED_CORNER_BG_CLASS_NAME_ + " div", "background-color: " + d);
        this.addStyleRule_("#" + a.id + " ." + AdSenseGraphics.ROUNDED_CORNER_BORDER_CLASS_NAME_ + " div", "background-color: " + f);
        var c = c + g,
            b = b + g,
            h = a.style;
        h.display = "none";
        a.innerHTML = "";
        h.position = "absolute";
        h.borderWidth = "0px";
        h.backgroundColor = "transparent";
        var f = g + "px solid " + f,
            m = l == AdSenseGraphics.POS_TOP_LEFT_ || l == AdSenseGraphics.POS_BOTTOM_LEFT_,
            l = l == AdSenseGraphics.POS_TOP_LEFT_ || l == AdSenseGraphics.POS_TOP_RIGHT_,
            j = c - e;
        if (0 < j) {
            var k = m ? c - j : 0,
                j = this.createDiv_(k, 0, j, b - g),
                k = j.style;
            k.backgroundColor = d;
            l ? k.borderTop = f : k.borderBottom = f;
            a.appendChild(j)
        }
        j = b - e;
        0 < j && (k = l ? b - j : 0, j = this.createDiv_(0, k, c - g, j), k = j.style, k.backgroundColor = d, m ? k.borderLeft = f : k.borderRight = f, a.appendChild(j));
        this.createRoundedCorner_(a, c, b, e, g, m, l);
        h.display = ""
    };
    AdSenseGraphics.prototype.createRoundedCorner_ = function (a, c, b, d, f, g, e) {
        var l = 0 < f,
            f = d - f,
            c = g ? 0 : c - d,
            h = e ? 0 : b - d,
            b = this.createDiv_(c, h, d, d);
        b.className = AdSenseGraphics.ROUNDED_CORNER_BG_CLASS_NAME_;
        var m = this.createDiv_(c, h, d, d);
        m.className = l ? AdSenseGraphics.ROUNDED_CORNER_BORDER_CLASS_NAME_ : AdSenseGraphics.ROUNDED_CORNER_BG_CLASS_NAME_;
        for (var j = f, k = d, p = f, n = d, r, s, t = d - 1, q = 0; q < d; ++q) {
            c = g ? t - q : q;
            j = p;
            k = n;
            p = Math.ceil(this.computeCircle_(q + 1, f));
            n = Math.floor(this.computeCircle_(q + 1, d));
            r = l ? j : n;
            h = e ? d - r : 0;
            h = this.createDiv_(c,
            h, 1, r);
            b.appendChild(h);
            for (var o = n; o <= k; ++o) s = this.getCircleAntiAliasOpacity_(q, o, d, !1), h = e ? t - o : o, h = this.createDiv_(c, h, 1, 1, s), m.appendChild(h);
            if (l) {
                k = n - r;
                0 < k && (h = e ? d - r - k : r, h = this.createDiv_(c, h, 1, k), m.appendChild(h));
                for (o = p - 1; o < j; ++o) s = this.getCircleAntiAliasOpacity_(q, o, f, !0), h = e ? t - o : o, h = this.createDiv_(c, h, 1, 1, s), m.appendChild(h)
            }
        }
        a.appendChild(b);
        a.appendChild(m)
    };
    AdSenseGraphics.prototype.drawSCurveWithCanvas = function (a, c, b, d, f, g) {
        var e = document.createElement("canvas");
        if (!e || !e.getContext) return !1;
        e.width = c;
        e.height = b;
        a.appendChild(e);
        a = e.getContext("2d");
        a.save();
        a.beginPath();
        a.moveTo(0, 0);
        !f && !g ? (a.moveTo(c, 0), a.lineTo(c, b), a.lineTo(0, b), a.bezierCurveTo(2 * c / 3, b, c / 3, 0, c, 0)) : f && !g ? (a.lineTo(0, b), a.lineTo(c, b), a.bezierCurveTo(c / 3, b, 2 * c / 3, 0, 0, 0)) : !f && g ? (a.lineTo(c, 0), a.lineTo(c, b), a.bezierCurveTo(c / 3, b, 2 * c / 3, 0, 0, 0)) : (a.lineTo(0, b), a.bezierCurveTo(2 * c / 3, b,
        c / 3, 0, c, 0), a.lineTo(0, 0));
        a.closePath();
        a.fillStyle = d;
        a.fill();
        a.restore();
        return !0
    };
    var asg = new AdSenseGraphics;
    if ("undefined" != typeof window.rcl) for (var rc, i = 0; i < rcl.length; i++) rc = rcl[i], asg.applyRoundedCorner(document.getElementById(rc[0]), rc[1], rc[2], rc[3], rc[4], rc[5], rc[6], rc[7]);
    "undefined" != typeof window.sc && (asg.drawSCurveWithCanvas(document.getElementById(sc[0]), sc[1], sc[2], sc[3], sc[4], sc[5]) || asg.createSigmoidCurve(document.getElementById(sc[0]), sc[1], sc[2], sc[3], sc[4], sc[5]));
})()