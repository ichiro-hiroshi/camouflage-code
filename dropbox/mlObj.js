
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, '');
}

function cTextTree(in_srcText)
{
	this.srcText = in_srcText;
	this.tree = this.compose(in_srcText);
}

cTextTree.prototype = {
	metaPair : {
		PAREN		: {O : '(', C : ')'},
		BRACKET		: {O : '[', C : ']'},
		BRACE		: {O : '{', C : '}'},
		THAN		: {O : '<', C : '>'},
		SQUOTATION	: {O : "'", C : "'"},
		DQUOTATION	: {O : '"', C : '"'}
	},
	tree : null,
	findPair : function(in_text) {
		var cand = {
			name : null,
			pair : {
				O : Number.POSITIVE_INFINITY,
				C : Number.NEGATIVE_INFINITY
			}
		};
		for (var name in this.metaPair) {
			var pair = this.metaPair[name];
			var O = in_text.indexOf(pair.O);
			if (O == -1) {
				continue;
			}
			/* todo : nested-case */
			var C = in_text.indexOf(pair.C, O + 1);
			if (C == -1) {
				continue;
			}
			if ((O != C) && (cand.pair.O > O)) {
				cand = {
					name : name,
					pair : {
						O : O,
						C : C
					}
				};
			}
		}
		if (cand.name) {
			return cand;
		} else {
			return null;
		}
	},
	compose : function(in_text) {
		var cand = this.findPair(in_text);
		if (!cand) {
			return [in_text.trim()];
		} else {
			var node = {
				name : cand.name,
				child : this.compose(in_text.substr(cand.pair.O + 1, (cand.pair.C - cand.pair.O) - 1))
			};
			var ret = [node];
			if (cand.pair.O > 0) {
				ret.unshift(in_text.substr(0, cand.pair.O).trim());
			}
			if (cand.pair.C < in_text.length - 1) {
				ret = ret.concat(this.compose(in_text.substr(cand.pair.C + 1)));
			}
			return ret;
		}
	},
	textContents : function() {
		var _makeArray = function(in_nodes) {
			var ret = [];
			for (var i = 0; i < in_nodes.length; i++) {
				if (typeof(in_nodes[i]) == 'string') {
					ret.push(in_nodes[i]);
				} else {
					ret = ret.concat(_makeArray(in_nodes[i].child));
				}
			}
			return ret;
		};
		return _makeArray(this.tree);
	},
	debugPrint : function() {
		var _writeList = function(in_nodes) {
			document.write('<ul>');
			for (var i = 0; i < in_nodes.length; i++) {
				if (typeof(in_nodes[i]) == 'string') {
					document.write('<li>' + in_nodes[i] + '</li>');
				} else {
					document.write('<li>' + in_nodes[i].name);
					_writeList(in_nodes[i].child);
					document.write('</li>');
				}
			}
			document.write('</ul>');
		};
		_writeList(this.tree);
	}
};

function imgDataScheme(in_img)
{
	var canvas = document.createElement('CANVAS');
	canvas.width = in_img.width;
	canvas.height = in_img.height;
	canvas.getContext('2d').drawImage(in_img,
		0, 0, in_img.width, in_img.height,
		0, 0, canvas.width, canvas.height);
	return canvas.toDataURL();
}

HTMLImageElement.prototype.srcDataScheme = function() {
	this.src = imgDataScheme(this);
}

function urlDataScheme(in_url, in_callback)
{
	var img = document.createElement('IMG');
	img.src = in_url;
	img.addEventListener('load', (function() {
		return function(in_event) {
			(in_callback)(imgDataScheme(img));
		};
	})(), false);
}

var computedCss = (function() {
	var _cssProp = {
		alignContent : '',
		alignItems : '',
		alignSelf : '',
		alignmentBaseline : '',
		background : '',
		backgroundAttachment : '',
		backgroundClip : '',
		backgroundColor : '',
		backgroundImage : '',
		backgroundOrigin : '',
		backgroundPosition : '',
		backgroundPositionX : '',
		backgroundPositionY : '',
		backgroundRepeat : '',
		backgroundRepeatX : '',
		backgroundRepeatY : '',
		backgroundSize : '',
		baselineShift : '',
		border : '',
		borderBottom : '',
		borderBottomColor : '',
		borderBottomLeftRadius : '',
		borderBottomRightRadius : '',
		borderBottomStyle : '',
		borderBottomWidth : '',
		borderCollapse : '',
		borderColor : '',
		borderImage : '',
		borderImageOutset : '',
		borderImageRepeat : '',
		borderImageSlice : '',
		borderImageSource : '',
		borderImageWidth : '',
		borderLeft : '',
		borderLeftColor : '',
		borderLeftStyle : '',
		borderLeftWidth : '',
		borderRadius : '',
		borderRight : '',
		borderRightColor : '',
		borderRightStyle : '',
		borderRightWidth : '',
		borderSpacing : '',
		borderStyle : '',
		borderTop : '',
		borderTopColor : '',
		borderTopLeftRadius : '',
		borderTopRightRadius : '',
		borderTopStyle : '',
		borderTopWidth : '',
		borderWidth : '',
		bottom : '',
		boxShadow : '',
		boxSizing : '',
		bufferedRendering : '',
		captionSide : '',
		clear : '',
		clip : '',
		clipPath : '',
		clipRule : '',
		color : '',
		colorInterpolation : '',
		colorInterpolationFilters : '',
		colorProfile : '',
		colorRendering : '',
		content : '',
		counterIncrement : '',
		counterReset : '',
		cursor : '',
		direction : '',
		display : '',
		dominantBaseline : '',
		emptyCells : '',
		enableBackground : '',
		fill : '',
		fillOpacity : '',
		fillRule : '',
		filter : '',
		flex : '',
		flexBasis : '',
		flexDirection : '',
		flexFlow : '',
		flexGrow : '',
		flexShrink : '',
		flexWrap : '',
		float : '',
		floodColor : '',
		floodOpacity : '',
		font : '',
		fontFamily : '',
		fontKerning : '',
		fontSize : '',
		fontStretch : '',
		fontStyle : '',
		fontVariant : '',
		fontWeight : '',
		glyphOrientationHorizontal : '',
		glyphOrientationVertical : '',
		height : '',
		imageRendering : '',
		justifyContent : '',
		kerning : '',
		left : '',
		letterSpacing : '',
		lightingColor : '',
		lineHeight : '',
		listStyle : '',
		listStyleImage : '',
		listStylePosition : '',
		listStyleType : '',
		margin : '',
		marginBottom : '',
		marginLeft : '',
		marginRight : '',
		marginTop : '',
		marker : '',
		markerEnd : '',
		markerMid : '',
		markerStart : '',
		mask : '',
		maskType : '',
		maxHeight : '',
		maxWidth : '',
		maxZoom : '',
		minHeight : '',
		minWidth : '',
		minZoom : '',
		objectFit : '',
		objectPosition : '',
		opacity : '',
		order : '',
		orientation : '',
		orphans : '',
		outline : '',
		outlineColor : '',
		outlineOffset : '',
		outlineStyle : '',
		outlineWidth : '',
		overflow : '',
		overflowWrap : '',
		overflowX : '',
		overflowY : '',
		padding : '',
		paddingBottom : '',
		paddingLeft : '',
		paddingRight : '',
		paddingTop : '',
		page : '',
		pageBreakAfter : '',
		pageBreakBefore : '',
		pageBreakInside : '',
		pointerEvents : '',
		position : '',
		quotes : '',
		resize : '',
		right : '',
		shapeRendering : '',
		size : '',
		speak : '',
		src : '',
		stopColor : '',
		stopOpacity : '',
		stroke : '',
		strokeDasharray : '',
		strokeDashoffset : '',
		strokeLinecap : '',
		strokeLinejoin : '',
		strokeMiterlimit : '',
		strokeOpacity : '',
		strokeWidth : '',
		tabSize : '',
		tableLayout : '',
		textAlign : '',
		textAnchor : '',
		textDecoration : '',
		textIndent : '',
		textLineThroughColor : '',
		textLineThroughMode : '',
		textLineThroughStyle : '',
		textLineThroughWidth : '',
		textOverflow : '',
		textOverlineColor : '',
		textOverlineMode : '',
		textOverlineStyle : '',
		textOverlineWidth : '',
		textRendering : '',
		textShadow : '',
		textTransform : '',
		textUnderlineColor : '',
		textUnderlineMode : '',
		textUnderlineStyle : '',
		textUnderlineWidth : '',
		top : '',
		touchActionDelay : '',
		transition : '',
		transitionDelay : '',
		transitionDuration : '',
		transitionProperty : '',
		transitionTimingFunction : '',
		unicodeBidi : '',
		unicodeRange : '',
		userZoom : '',
		vectorEffect : '',
		verticalAlign : '',
		visibility : '',
		whiteSpace : '',
		widows : '',
		width : '',
		wordBreak : '',
		wordSpacing : '',
		wordWrap : '',
		writingMode : '',
		zIndex : '',
		zoom : ''
	};
	for (var cssDomProp in _cssProp) {
		_cssProp[cssDomProp] = (function(in_cssDomProp) {
			var cssName = '';
			for (var i = 0; i < in_cssDomProp.length; i++) {
				var char = in_cssDomProp.charAt(i);
				if (char.match(/[A-Z]/)) {
					cssName += '-' + char.toLowerCase();
				} else {
					cssName += char;
				}
			}
			return cssName;
		})(cssDomProp);
	}
	var _currentStyle = function(in_doc, in_elem) {
		return in_elem.currentStyle || in_doc.defaultView.getComputedStyle(in_elem, '');
	};
	function _cDummyElement(in_elem)
	{
		this.iframe = document.createElement('IFRAME');
		this.iframe.style.visibility = 'hidden';
		this.iframe.style.width = '0px';
		this.iframe.style.height = '0px';
		in_elem.parentNode.insertBefore(this.iframe, in_elem);
		var doc = this.iframe.contentWindow.document;
		doc.open();
		doc.write('<html><body></body></html>');
		doc.close();
		var elem = doc.createElement(in_elem.nodeName);
		doc.body.appendChild(elem);
		this.currentStyle = (function(in_doc, in_elem) {
			return function() {
				return _currentStyle(in_doc, in_elem);
			};
		})(doc, elem);
		this.delete = function() {
			this.iframe.parentNode.removeChild(this.iframe);
			this.iframe = null;
		};
	}
	return {
		loading : 0,
		compose : function(in_elem, in_callback) {
			var dummy = new _cDummyElement(in_elem);
			var s1 = _currentStyle(document, in_elem);
			var s2 = dummy.currentStyle();
			var css = {};
			for (var cssDomProp in _cssProp) {
				if (s1[cssDomProp] == s2[cssDomProp]) {
					continue;
				}
				css[_cssProp[cssDomProp]] = s1[cssDomProp];
				var parsed = (new cTextTree(s1[cssDomProp])).textContents();
				if (parsed[0] == 'url') {
					this.loading++;
					urlDataScheme(parsed[1], (function(self, in_cssProp) {
						return function(in_dataScheme) {
							css[in_cssProp] = 'url(' + in_dataScheme + ')';
							if (--self.loading == 0) {
								(in_callback)(css);
							}
						};
					})(this, _cssProp[cssDomProp]));
				}
			}
			dummy.delete();
			if (this.loading == 0) {
				(in_callback)(css);
			}
		}
	};
})();

var TYPE = {
	ELEMENT_NODE				: 1,
	ATTRIBUTE_NODE				: 2,
	TEXT_NODE					: 3,
	CDATA_SECTION_NODE			: 4,
	ENTITY_REFERENCE_NODE		: 5,
	ENTITY_NODE 				: 6,
	PROCESSING_INSTRUCTION_NODE	: 7,
	COMMENT_NODE				: 8,
	DOCUMENT_NODE				: 9,
	DOCUMENT_TYPE_NODE			: 10,
	DOCUMENT_FRAGMENT_NODE		: 11,
	NOTATION_NODE				: 12
};

function domNode2mlObj(in_node, in_filter)
{
	switch (in_node.nodeType) {
	case TYPE.ELEMENT_NODE :
		var ml = {
			_name : in_node.nodeName.toUpperCase(),
			_attr : {},
			_child : [],
			_ready : false
		};
		if (in_node.hasAttributes()) {
			var nodes = in_node.attributes;
			for(var i = 0; i < nodes.length; i++) {
				ml._attr[nodes.item(i).nodeName] = nodes.item(i).nodeValue;
			}
		}
		computedCss.compose(in_node, (function(in_ml) {
			return function(in_css) {
				var cssText = [];
				for (var prop in in_css) {
					cssText.push(prop + ': ' + in_css[prop] + ';');
				}
				if (cssText.length > 0) {
					in_ml._attr.style = cssText.join(' ');
				}
				in_ml._ready = true;
			};
		})(ml));
		if (in_node.hasChildNodes()) {
			var nodes = in_node.childNodes;
			for(var i = 0; i < nodes.length; i++) {
				ml._child.push(domNode2mlObj(nodes.item(i), in_filter));
			}
		}
		if (in_filter) {
			return (in_filter)(ml, in_node);
		} else {
			return ml;
		}
	case TYPE.TEXT_NODE :
		return in_node.nodeValue;
	default :
		break;
	}
	return null;
}

function mlObj2domNode(in_ml, in_doc)
{
	var doc = in_doc || document;
	if (!in_ml) {
		/* default */
		return null;
	}
	if (typeof(in_ml) == 'string') {
		/* TYPE.TEXT_NODE */
		return doc.createTextNode(in_ml);
	} else {
		/* TYPE.ELEMENT_NODE */
		var elem = doc.createElement(in_ml._name);
		for (prop in in_ml._attr) {
			elem.setAttribute(prop, in_ml._attr[prop]);
		}
		for (var i = 0; i < in_ml._child.length; i++) {
			var node = mlObj2domNode(in_ml._child[i], in_doc);
			if (node) {
				elem.appendChild(node);
			}
		}
		return elem;
	}
}

function staticPageFilter(in_ml, in_node)
{
	switch (in_ml._name) {
	case 'STYLE' :
	case 'SCRIPT' :
	case 'NOSCRIPT' :
		return null;
	case 'IMG' :
		if (in_ml._attr.src) {
			in_ml._attr.src = imgDataScheme(in_node);
		}
		break;
	default :
		break;
	}
	var style = in_node.currentStyle || document.defaultView.getComputedStyle(in_node, '');
	if (style.display == 'none') {
		return null;
	} else {
		return in_ml;
	}
}

/*

usage :
	var mlObj = domNode2mlObj(document.body, staticPageFilter);
	...
	document.documentElement.appendChild(mlObj2domNode(mlObj, document));

*/

