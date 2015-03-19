window.Flurry = window.Flurry || {};

Flurry.NamedId = Flurry.NamedId || function(n, i){
	return {
		name 	: 	n,
		id		:	i
	}
};
Flurry.Url = Flurry.Url || function(optUrl){
	var fUrl = optUrl || window.location.href;
	var fBase = "";
	var fVars = getUrlVars(); 
	
	function getUrlVars(){
		fBase = url = fUrl;
	    var vars = {};
	    var noHashUrl = url;
	    if (url.indexOf('#') >= 0){
	    	noHashUrl = url.slice(0, url.indexOf('#'));
	    }

	    var qMarkIndex = url.indexOf('?');
	    if (qMarkIndex >= 0){
	    	
	    	fBase = url.slice(0, qMarkIndex);
	    	
	    	var params = url.slice(qMarkIndex + 1).split('&');
	    	for(var i = 0; i < params.length; i++)
	    	{
	    		var param = params[i].split('=');
	    		if (param[1] && param[1].length){
	    			vars[param[0]] = param[1];
	    		}
	    	}
	    }
	    
	    return vars;
	}
	
	var pub = {
		base : function(){
			return fBase;
		},
		params : function(){
			return fVars;
		},
		toString : function(){
			return new Flurry.UrlParams()
				.addParams(fVars)
				.buildUrl(fBase)
			;
		}
	};
	return pub;
	
}
Flurry.UrlParams = Flurry.UrlParams || function(optUrl, addParams){
	var fMap = {};
	
	var fUrl = new Flurry.Url(optUrl);
	var fBase = fUrl.base();
	var fVars = fUrl.params();
	
	if (addParams){
		addAllFromUrl();
	}
	
	function addAllFromUrl(){
		$.extend(fMap,fVars);
	}
	
	var makeParamsString = function(map){
		var str = "";
		
		$.each(map, function(key, value){
			str += key + "=" + value;
			str += "&";
			
		});
		
		var trimmed = str.substr(0, str.length-1);
		return trimmed;
	};
	
	var paramsPublic = {
		//optional to ignoreEncoding on value, default false so encoding will normally be used
		addParam : function(key, value, ignoreEncoding){
			var safeValue = encodeURIComponent(value);
			if (ignoreEncoding){
				safeValue = value;
			}
			fMap[key] = safeValue;
			return this;
		},
		addParams : function(paramsMap, ignoreEncoding){
			$.each(paramsMap, function(key, value){
				paramsPublic.addParam(key, value, ignoreEncoding);
			});
			return this;
		},
		addParamFromUrl : function(key){
			var value = fVars[key];
			if (value != undefined){
				fMap[key] = value;
			}
			return this;
		},
		addAllFromUrl : function(){
			addAllFromUrl();
			return this;
		},
		removeParam : function(key){
			delete fMap[key];
			return this;
		},
		//does not return this object
		clone : function(){
			var newParams = new Flurry.UrlParams()
				.addParams(fMap)
			;
			return newParams;
		},
		getParamsString : function(extraParams){
			var newMap = $.extend({}, fMap, extraParams);
			return makeParamsString(newMap);
		},
		getParamsMap : function(){
			return fMap;
		},
		getUrlObj : function(){
			return fUrl;
		},
		buildUrl : function(baseUrl, extraParams){
			var url = baseUrl || fBase;
			var moreParams = extraParams || {};

			var startSymbol = "?";
			if (url.indexOf("?") > 0){
				startSymbol = "&";
			}
			var paramString = this.getParamsString(moreParams);
			if (paramString.length > 0){
				url += startSymbol + paramString;
			}
			return url;
		}
	};

	return paramsPublic;
}

Flurry.Lightbox = Flurry.Lightbox || function(divId, width){
	var fRootDiv = $("#"+divId);
	if (fRootDiv.length <= 0){
		fRootDiv = document.createElement("div");
		$(document).append(fRootDiv);
	}else{
		fRootDiv.html("");
	}
	var fLightboxDivId = divId; 
	$(fRootDiv).attr("id", fLightboxDivId);
	
	var widthSafe = width || "600px";
	
	var fYuiLightbox = new YAHOO.widget.Panel(fLightboxDivId, { 
	    width:widthSafe,  
	    fixedcenter: true,  
	    constraintoviewport: true,  
	    underlay:"shadow",  
	    close:true,  
	    visible:false,  
	    draggable:true,
	    zIndex: 999,
		effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.25} } 
	);

	fYuiLightbox.render(document.body);
	fYuiLightbox.hideEvent.subscribe(lightboxDeactivate);
	
	var getCloseBox = function(){
		return $("#" + fLightboxDivId + " a.container-close");
	}
	
	var closeOutlineFix = function(){
		var close = getCloseBox();
		close.css("outline", "none");
		close.css("borderWidth", "0px");
	};
	
	var pub = {
		setTitle : function(html){
			fYuiLightbox.setHeader(html);
			fYuiLightbox.render(document.body);
			closeOutlineFix();
			return this;
		},
		setBody : function(html){
			fYuiLightbox.setBody(html);
			fYuiLightbox.render(document.body);
			return this;
		},
		setFooter : function(html){
			if (html == null){
				$("#" + fLightboxDivId + " .ft").remove();
			}else{
				fYuiLightbox.setFooter(html);
			}
			fYuiLightbox.render(document.body);
			return this;
		},
		toggle : function(){
			lightboxToggleLink(fYuiLightbox)
			closeOutlineFix();
			return this;
		},
		//does not return this
		getYuiBox : function(){
			return fYuiLightbox;
		},
		getCloseBox : function() {
			return getCloseBox();
		}
	};
	return pub;
};

Flurry.HiddenText = Flurry.HiddenText || function($inputText){
	var fRoot = $inputText;
	var fText =  $("<div />")
		.addClass("inputTextText")
		.insertAfter(fRoot);
	;
	var pub = {
		msg : function(msg){
			fText.empty().text(msg);
			fRoot.val("");
			return this;
		},
		val : function(v){
			fText.empty();
			fRoot.val(v);
			return this;
		},
		append : function ($el){
			fText.empty().append($el);
		},
		makeText : function(){
			fRoot.prop("type", "hidden")
				.prop('disabled', true)
			;
			fText.show();
			return this;
		},
		makeInput : function(){
			fRoot.prop("type", "text")
				.prop('disabled', true)
			;
			fText.hide();
			return this;
		}
	};
	return pub;
};

/**
 * puts a text input and button in the width of the wrapper?
 */
Flurry.FieldUpdate = Flurry.FieldUpdate || function($wrapper, buttonClickHandler){
	var fRoot = $wrapper;
	var fInput = $("<input />")
		.prop("type", "text")
	;
	var fButton = $("<button />")
		.text("Update")
		.css("margin-left", "10px")
	;
	fRoot
		.append(fInput)
		.append(fButton)
	;
	
	var pub = {
		getInput : function(){
			return fInput;
		},
		getButton : function(){
			return fButton;
		},
		getWrapper : function(){
			return fRoot;
		}
	};
	
	fButton.click(function(e){
		var val = fInput.valTitleCheck();
		
		buttonClickHandler(pub, val);
		
		e.preventDefault();
		e.stopPropagation();
	});
	
	return pub;
};