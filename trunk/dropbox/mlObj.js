
var getCssText = (function() {
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
	return function(in_elem) {
		var dummy = new _cDummyElement(in_elem);
		var s1 = _currentStyle(document, in_elem);
		var s2 = dummy.currentStyle();
		var cssText = [];
		for (var cssDomProp in _cssProp) {
			if (s1[cssDomProp] != s2[cssDomProp]) {
				cssText.push(_cssProp[cssDomProp] + ': ' + s1[cssDomProp] + ';');
			}
		}
		dummy.delete();
		if (cssText.length > 0) {
			return cssText.join(' ');
		} else {
			return null;
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
			_child : []
		};
		if (in_node.hasAttributes()) {
			var nodes = in_node.attributes;
			for(var i = 0; i < nodes.length; i++) {
				ml._attr[nodes.item(i).nodeName] = nodes.item(i).nodeValue;
			}
		}
		var cssText = getCssText(in_node);
		if (cssText) {
			ml._attr.style = cssText;
		}
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

function conv2dataScheme(in_img)
{
	var canvas = document.createElement('CANVAS');
	canvas.width = in_img.width;
	canvas.height = in_img.height;
	canvas.getContext('2d').drawImage(in_img, 0, 0, in_img.width, in_img.height, 0, 0, canvas.width, canvas.height);
	return canvas.toDataURL();
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
			in_ml._attr.src = conv2dataScheme(in_node);
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

