window.Flurry = window.Flurry || {};
Flurry.Widgets = Flurry.Widgets || {};

(function(){

	/**
	 * autocomplete widget, has features like queueing of commands as data loads, loading data dynamically
	 * or at initialization, handling hinting, should represent a list of entities that have a unique ID
	 * but are displayed as a string (like project or version)
	 * 
	 * input : text input element, can be textarea
	 * config : { NOTE: some fields are actually required here
	 
	 * 	//data
	 * 		type : useful shortcuts for schemas, supports: project, event, segment, funnel
	 *		schema : { 
	 *			*fields : list of field names for yui's DataSource //REQUIRED if type is not set
	 *			resultFormatter : function(oResultData, sQuery, sResultMatch), redraws the li before display
	 * 		}
	 *
	 * 		dataUrl : url to fetch json data
	 * 		or
	 * 		data : json data
	 * 
	 * 		dataErrorMsg : error message if data fails in some way (connection or no results), default: "No results found."
	 *		dataTransform : function(data) that will take result of ajax url and transform to more usable object
	 *		

	 *	//misc
	 *		hint : starting message,
	 *		maxResults : maximum results displayed at once, 0 for unlimited
	 *		hidden : name of hidden input to store id, if not set, will not be created
	 *		heightFix : assumed height of input in pixels, useful if input is hidden at creation
	 * } 
	 * Events: typeahead.blur, typeahead.dataError, typeahead.update(args: e, id, name)
	 * Listens to: typeahead.dataLoaded, focus.typeahead
	 * 
	 * input.on("typeahead.update", function(e, id, name){});
	 *
	 * EXTENDS: InputHint
	 */
	Flurry.Widgets.AutoComplete = Flurry.Widgets.AutoComplete || function(input, config){
		var _defaults = {
			wrapperClass : "acWrapper",
			
			type : null,
			schema : null,

			dataUrl : null,
			data : [],

			dataErrorMsg : "No results found.",
			dataTransform : function(data){ return data; },

			hint : "",
			maxResults : 10,
			hidden : null,
			heightFix : 14
		};
		var _config = $.extend(true, {}, _defaults, config);	//recursive copy

		//reference statics
		var _uniqueCount = this.sUniqueCounter.get();
		var _schemas = this.sSchemas;

		var _input = $.maker(input);
		var _inputHint = null;
		var _hidden = null;
		var _wrapper = null;
		var _container = null;
		
		var _dataDefaults = {
			values : {
				id : 0,
				name : ""
			},
			yuiSource : null,
			json : {}, 
			map : {}
		};
		var _data = $.extend(true, {}, _dataDefaults);	//recursive copy
		
		var _dataLoaded = false;	//should check this and queue up actions for things that require data
		
		construct("autocompleteWidget", _input, function(){
			//build structure
			var count = _uniqueCount;
			var containerId = "chooseACContainer_" + count;
			var textId = _input.attr("id");
			if (!textId){
				textId = "chooseACInput_" + count;
				_input.attr("id", textId);
			}
			
			_wrapper = $('<div />')
				.addClass("yui-ac relative")
				.addClass(_config.wrapperClass)
			;
			_input.wrap(_wrapper);
			_wrapper = _input.parents("." + _config.wrapperClass);//wrap makes a clone
			
			
			_container = $('<div />')
				.insertAfter(_input)
				.attr("id", containerId)
			;
			
			_hidden = $("<input />")
				.attr("type", "hidden")
			;

			initialize();
		});
		
		function initialize(silent){
			//input hint (extend)
			_inputHint = new Flurry.Widgets.InputHint(input, config);
			
			//schema from type
			var schemaTemplate = _schemas[_config.type];
			if (_config.type != null && schemaTemplate != null){
				_config.schema = $.extend(true, {}, schemaTemplate);
			}
			$.extend(_config.schema, {//seems like yui needs these fields to stay in these positions
				nameIndex : 0,
				idIndex : 1
			});
			
			//make form element
			if (_config.hidden != null){
				_hidden
					.attr("name", _config.hidden)
					.insertAfter(_input)
				;
			}
			//handle data load
			_input
				.off("typeahead.dataLoaded")
				.on("typeahead.dataLoaded", function(e, dataSource){
					_dataLoaded = true;
					makeAutoComplete(_input, _container, dataSource);
					setInitialValue(silent);
				})
			;
			
			//start process of loading data
			makeYuiData();
		}

		//check values stored in _data.values to initialize
	    function setInitialValue(silent){
	    	//if user tries to setValue before data is loaded, it will just be put into _data.values obj 
	    	var v = _data.values
	    	saveSelection(v.id, v.name, silent);
	    	if (!silent){
	    		_input.trigger("typeahead.update", [v.id, v.name]);
	    	}
	    	fixDrawing();
	    }
		
		//do all the drawing fixes here
		function fixDrawing(){
			var containerTrim = 4;
			var inputHeight = _input.outerHeight(true);
			//fix for hidden inputs
			if (_input.height() == 0){
				inputHeight += _config.heightFix;
			}
			_container
				.css({
					"top" : inputHeight + "px",
					"width" : (_input.outerWidth(true)-containerTrim) + "px"
				})
			;
			_wrapper
				.css({
					"line-height" : inputHeight + "px",
					"height" : inputHeight + "px",
					"width" : _container.width() + "px"
				})
			;
			fixZIndex();
			drawArrow(_wrapper);
		}
		function fixZIndex(){
			var wrappers = $("." + _config.wrapperClass);  //should be sorted in page descending order
			var numWrappers = wrappers.length;
			var startingZIndex = 800;
			if (numWrappers > 0){
				$.each(wrappers, function(index, wrapper){
					$(wrapper).css({
						"z-index" : startingZIndex + (numWrappers - index)//increase as you head down the page
					})
				});
			}
		}
		function drawArrow(wrapper){
			var arrowClass = "acDownArrow";
			//get or create arrow
			var arrow = wrapper.find("." + arrowClass);
			if (arrow.length == 0){
				//arrow icon
				arrow = $("<img />")
					.addClass(arrowClass)
					.attr("src", "../images/icons/contentBarDownArrow_black.png")
					.addClass("absoluteRight")
					.insertAfter(_input)
					.click(function(e){
						if (!_inputHint.isDisabled()){
							_input.focus();
						}
					})
					.on("arrow.resized", function(e){
						var height = _input.outerHeight(true);
						var arrowTop = Math.round((height - arrow.height())/2);
						
						arrow.css({
							"top" : arrowTop + "px",
							"right" : "4px",
							"cursor" : "pointer"
						});
					})
				;
			}
			if (_inputHint.isDisabled()){
				arrow.hide();
			}else{
				arrow.show();
			}
			arrow.trigger("arrow.resized");	//think about moving this up to _input
		}
		
		//load data, ajax, pick next step
		function makeYuiData(){
			if (_config.dataUrl){
				loadJsonFromUrl(_config.dataUrl);
			}else{
				loadJson(_config.data);
			}
		}
		
		//load json data using ajax, fire off nex step
		function loadJsonFromUrl(url){
			$.ajax({
				url : url, 
				cache:false,
				dataType : 'json',
				success : function(data){
					loadJson(data);
				},
				error : function(jqXHR, error, errorThrown){
					showError(_config.dataErrorMsg);
				}
			});
		}
		//save json into class, fire off next step
		function loadJson(data){
			_data.json = data;
			//data check for no results
			var cleanedData = _config.dataTransform(data);
			if (cleanedData.length == 0){
				showError(_config.dataErrorMsg);
			}else{
				buildDataSource(cleanedData, _config.schema);
			}
		}
		function buildDataSource(data, schema){
			//build id lookup map
			var nameField = schema.fields[schema.nameIndex];
			var idField = schema.fields[schema.idIndex];
			$.each(data, function(index, obj){
				_data.map[obj[idField]] = obj[nameField];
			});

			//http://yuilibrary.com/projects/yui2/ticket/2149648
			var responseResults = {
				"stupidYahoo" : data
			};
			_data.yuiSource = new YAHOO.util.LocalDataSource(responseResults);
		    // Optional to define fields for single-dimensional array
			_data.yuiSource.responseSchema = {
	    		resultsList : "stupidYahoo",
			    fields : schema.fields
			};
			
			_input.trigger("typeahead.dataLoaded", [_data.yuiSource]);
		}
		function makeAutoComplete(input, container, dataSource){
			var yuiAC = new YAHOO.widget.AutoComplete(input.attr("id"), container.attr("id"), dataSource);
			yuiAC.prehighlightClassName = "yui-ac-prehighlight";
			yuiAC.useShadow = true;
			yuiAC.animSpeed = 0.1; 
			yuiAC.animVert = 0.1;
			yuiAC.queryMatchContains = true;
			yuiAC.queryMatchCase = false;
			yuiAC.minQueryLength = 0;
			yuiAC.queryDelay = 0.1;
			yuiAC.autoSnapContainer = true;
			yuiAC.allowBrowserAutocomplete = false;
			
			if (_config.maxResults != 0){// <= 0 means unlimited 
				yuiAC.maxResultsDisplayed = _config.maxResults;
			}
			
			yuiAC.applyLocalFilter = true;

			//height control
		    $(yuiAC._elBody).css("overflowY", "auto");
		    $(yuiAC._elBody).css("maxHeight", "150px");

		    if (_config.schema.resultFormatter != null){
		    	yuiAC.formatResult = _config.schema.resultFormatter;  
		    }
		    
		    _input
		    	.off("focus.typeahead")
		    	.on("focus.typeahead", function(e){
		    		if (!yuiAC.isContainerOpen()){
			    		setTimeout(function() { // For IE
		    				yuiAC.sendQuery("");
			    		},0);
		    		}
		    	})
		    ;
		    
			yuiAC.forceSelection = false;	//dont mess with this value.  it'll beat you up.
		    yuiAC.unmatchedItemSelectEvent.subscribe(function(e, args){//triggered on blur
		    	if (_inputHint.val() != _data.values.name){
		    		_inputHint.val("");	//we control the forceSelection here
		    		saveSelection(null, "");
		    	}
		    	_input.trigger("typeahead.blur");
		    })
		    
		    /**
		     Parameters:
				type <String> Name of the event.
				args[0] <YAHOO.widget.AutoComplete> The AutoComplete instance.
				args[1] <HTMLElement> The selected <li> element item.
				args[2] <Object> The data returned for the item, either as an object, or mapped from the schema into an array.
			*/
		    yuiAC.itemSelectEvent.subscribe(function (type, args){
		    	var obj = args[2];
		    	var id = obj[_config.schema.idIndex];
		    	var name = obj[_config.schema.nameIndex];
		    	saveSelection(id, name);
		    	_input.prop("containerExpanded", false);
		    });
		    /**
		     Parameters:
				type <String> Name of the event.
				args[0] <YAHOO.widget.AutoComplete> The AutoComplete instance.
				args[1] <HTMLElement> The <li> element item arrowed to.
		     */
		    yuiAC.itemArrowToEvent.subscribe(function (type , args){		//make sure the ac window scrolls as you'd expect
		    	var li = $.maker(args[1]);
		    	var container = li.parents(".yui-ac-bd");
		    
		    	var liPosition = li.position().top;
				var liHeight = li.innerHeight();

				var currentScroll = container.scrollTop();  //how far down the scroll bar has moved in the window
				var windowHeight = container.innerHeight();	//visible height in window
				
				var windowBottom = currentScroll + windowHeight;	//this determines the portion of the container that is visible to the user
				var windowTop = currentScroll;

				var liBottomPosition = currentScroll + liPosition + 1*liHeight;  //leave row of buffer
				var liTopPosition = currentScroll + liPosition - liHeight;  //leave row of buffer
				
				if (windowBottom < liBottomPosition){
					//going down and the li is below the buffer range of the window
					var newScroll = liBottomPosition + liHeight - windowHeight;
					container.scrollTop(newScroll);	//scroll down a bit to accomodate the new rows
				}else if (windowTop > liTopPosition){
					//going up and the li is above the buffer range of the window
					var newScroll = currentScroll - liHeight;
					container.scrollTop(newScroll);	//scroll up a bit to accomodate the new rows
				}
    		});
		    
			_data.autocomplete = yuiAC;
			
			fixDrawing();
		}
		
		function saveSelection(id, name, silent){
			//safe casting
			id = id || 0;
			name = name || "";

			//sometimes id is used before data is loaded
			if (_dataLoaded){
				if (id > 0 && name == ""){
					name = getNameById(id) || "";
				}
			}
			
			//data model
			var oldData = $.extend({}, _data.values);
			_data.values.id = id;
			_data.values.name = name;
			_hidden.val(_data.values.id);
			
			//triggers
			if ((oldData.id != id || oldData.name != name) && (!silent && _dataLoaded)){
				_input.trigger("typeahead.update", [id, name]);
			}

			//hint and text value
			if (_inputHint.val() != name){
				_inputHint.val(name);
			}
			_inputHint.toggle(true);
			
			fixDrawing();
		}
		
		function showError(msg){
			//disable, show hint
			_inputHint.hint(msg, true);
			_inputHint.disable(true);
			_input.trigger("typeahead.dataError");
			fixDrawing();
		}
		
		function getNameById(id){
			return _data.map[id];
		}
		 
		var pub = {
			id : function(id, silent){
				if (id != null){
					var name = getNameById(id) || null;
					saveSelection(id, name, silent);
				}else{
					return _data.values.id;
				}
			},
			name : function(id, name, silent){
				if (id != null && name != null){
					saveSelection(id, name, silent);
				}else{
					return _data.values.name;
				}
			},
			getNameById : function(id){
				return getNameById(id);
			},
			redraw : function(){
				fixDrawing();
			},
			destroy : function(){
				_inputHint.disable(false);
				_inputHint.destroy();
				if (_data.autocomplete){	//might not be created if empty data
					_data.autocomplete.destroy();
				}
				_dataLoaded = false;
				_data = $.extend(true, {}, _dataDefaults);	//recursive copy
			},
			remake : function(newConfig, silent){
				pub.destroy();
				_config = $.extend(true, _config, newConfig);	//recursive copy, only overwrites the new values
				initialize(silent);
			}
		};
		
		return $.extend({}, _inputHint, pub);
	};
	if (!Flurry.Widgets.AutoComplete.sUniqueCounter){
		Flurry.Widgets.AutoComplete.prototype.sUniqueCounter = new AutoCounter(0);
	}
	if (!Flurry.Widgets.AutoComplete.sSchemas){
		Flurry.Widgets.AutoComplete.prototype.sSchemas = {
			"project" : {
				"fields" : ["ProjectName", "ProjectID", "ProjectPlatform"],
				"resultFormatter" : function(oResultData, sQuery, sResultMatch) {
		    		return "<div class='acIconedDiv'>" + getPlatformImg(oResultData[2]) + "<span class='iconed'>" + oResultData[0] + "</span></div>";
		    	}
			},
			"event" : {
				"fields" : ["EventName", "EventID"]
			},
			"segment" : {
				"fields" : ["name", "id"]
			},
			"funnel" : {
				"fields" : ["name", "id"]
			}
		};
	}
	
	/**
	 * Input text with instructional text when the input text is empty
	 * 
	 * currently this mirrors automatic functionality provided by the "title" attribute
	 * in the future, this will be consolidated, but for now, avoid setting both
	 * 
	 * input : text input element, can be textarea
	 * config : { all field below are optional, including the config obj
 	 * 		hint : text to be used in the hint
	 * 		attr : name of attribute that will contain the hint
	 * } 
	 * Events: hint.shown, hint.hidden, hint.updated.hint, hint.updated.value
	 * Listens to focus.hint, blur.hint, 
	 */
	Flurry.Widgets.InputHint = Flurry.Widgets.InputHint || function(input, config){
		var _input = $.maker(input);
		
		var _defaults = {
			hint : null,
			attr : "hintText",
			hintCssClass : "textPrefill"
		};
		var _config = $.extend({}, _defaults, config);
		
		construct("inputHint", _input, function(){
			//if loading via hint
			setHint(_config.hint);
			
			//initialize
			if (_input.is("[" + _config.attr + "]")){
				//unset/set hint state to catch load values
				//these functions will ignore unprocessable states
				hideHint();
				drawHint();
			}
			
			_input
				.on({
					"focus.hint" : function(e){
						hideHint();
					},
					"blur.hint" : function(e){
						drawHint();
					}
				})
			;
			
		});
		
		/* private */
		function drawHint(){ //smart enough to do nothing if state should not support hint
			if (hasHint() && isEmpty()){
				_input
					.addClass(_config.hintCssClass)
					.val(getHint())
				;
			}
			_input.trigger("hint.shown");
		}
		function hideHint(){//smart enough to do nothing if state should not support hint
			if (isHint()){
				_input
					.val("")
					.removeClass(_config.hintCssClass)
				;
			}
			_input.trigger("hint.hidden");
		}
		function isHint(){
			return hasHint() && _input.val() == getHint();
		}
		function hasHint(){
			var hint = getHint();
			return (hint != null && hint.length > 0);
		}
		function getHint(){
			return _input.attr(_config.attr);
		}
		function setHint(text){
			if (text != null && text != "" && _config.hint != null){
				_input.attr(_config.attr, text);
			}
			_input.trigger("hint.updated.hint");
		}
		
		//takes into account hinting
		function getVal(){
			if (isHint()){
				return "";
			}else{
				return _input.val();
			}
		}
		//takes into account hinting
		function setVal(text){
			hideHint();
			_input.val(text);
			drawHint();
			_input.trigger("hint.updated.value");
		}
		//takes into account hinting
		function isEmpty(){
			return getVal().length == 0;
		}
		
		var pub = {
			toggle : function(flag){
				if (flag){
					drawHint();
				}else{
					removeHint();
				}
			},
			hint : function(text, force){//changes the hint attribute, not the input val unless forced
				if (text != null){
					setHint(text);
					if (force){
						setVal("");//set to blank, which will force hint
					}
				}else{
					return getHint();
				}
			},
			val : function(text){
				if (text != null){
					setVal(text);
				}else{
					return getVal();
				}
			},
			disable : function(flag){
				var flagSafe = flag || false;
				_input.prop("disabled", flagSafe);
			},
			isEmpty : function(){
				return isEmpty();
			},
			isDisabled : function(){
				return _input.is(":disabled");
			},
			destroy : function(){
				_input
					.removeAttr(_config.attr)
					.removeClass(_config.hintCssClass)
					.removeClass("inputHint")
				;
			}
		};
		return pub;
	}
	
	
	
	
	/**
	 * This is a special lightbox where the lightbox contains a form  
	 * 
	 * contentId for hidden div that contains content form for window, must be attached to page
	 * config : { all field below are optional, including the config obj
	 * 		saveForm : function that will save the state of the form before the window is opened, should return a json object
	 * 		resetForm(obj) : function that will take an object (the one returned by saveForm) and use it to populate the form in the lightbox
	 * 		title : window title
	 * 		width: width of window
	 * } 
	 * Events: lightboxForm.opened, lightboxForm.closed
	 */
	Flurry.Widgets.LightboxForm = Flurry.Widgets.LightboxForm || function(contentId, config){
		var _content = $.maker(contentId);
		var _form;
		var _lightbox;
		
		var _defaults = {
			saveForm : function(){ return {}; },
			resetForm : function(formObj){},
			width : null,
			title : ""
		};
		var _config = $.extend({}, _defaults, config);
		
		construct("lightboxFormBody", _content, function(){
			_lightbox = new Flurry.Lightbox("yui_" + contentId, _config.width)
				.setBody(_content[0])
				.setTitle(_config.title)
			;

			_content.show();
			
			_lightbox.getCloseBox()
				.on("click", function(e){
					cancel();
				})
			;
		});

		function cancel(){
			_config.resetForm(_form);
			_content.trigger("lightboxForm.reset");
			close();
		}

		function close(){
			hideLightbox(_lightbox.getYuiBox());
			_content.trigger("lightboxForm.closed");
		}
		
		var pub = {
			showWindow : function(){
				_form = _config.saveForm();
				showLightbox(_lightbox.getYuiBox());
				_content.trigger("lightboxForm.opened")
			},
			hideWindow : function(reset){
				if (reset){
					cancel();
				}else{
					close();
				}
			},
			setError : function(msg){
				var span = $("<span />")
					.addClass("error")
					.text(msg)
				;
				_lightbox.setFooter(span[0]);
				_content.trigger("lightboxForm.error");
			},
			clearError : function(){
				_lightbox.setFooter(null);
				_content.trigger("lightboxForm.errorCleared");
			}
		};
		
		return $.extend({}, _lightbox, pub);
	};
	
	
	/**
	 * rootDiv - the div that will be made into a DropDown
	 * config : { all field below are optional, including the config obj
	 * 		states : {
	 * 			key : { //unique key for state
	 * 				buttonContent : content to show in button, jQuery obj
	 * 				hintText : text to show in hint, string, 
	 * 			}
	 * 		},
	 * 		startState : name of key to start with, string,
	 * 		click : click handler
	 * 		gravity : tooltip anchor position, see Flurry.Widgets.Hint
	 * } 
	 * Events: toggleButton.clicked, toggleButton.stateUpdate
	 */
	Flurry.Widgets.ToggleButton = Flurry.Widgets.ToggleButton || function(rootDiv, config){
		var _root = $.maker(rootDiv);
		var _button = $("<div />");
		var _hint = $("<div />");
		
		//better way to do this would be to have a default object and extend custom from defaults
		var _config = config || {};
		var _states = _config.states || {}; 
		var _stateString = _config.startState || null;
		var _clickHandler = _config.click || $.noop;
		var _gravity = _config.gravity || "ew";
		
		construct("toggleButton", _root, function(){
			//the code below rewrites various vars that were html elems.  
			//make button
			_button = new ButtonBox(_button, {
				width : 34,
				height : 34,
				autoCenter : true
			});
			
			//make hint
			_hint = new Flurry.Widgets.Hint(_button.getRoot(), {
				gravity : _gravity,
				onHover : false
			});
			
			//attach to root
			_root
				.append(_button.getRoot())
				.append(_hint.getRoot()) //still need to update layout
			;
			
			//button on mouseover/out should show/hide some hint
			_button.getRoot()
				.on("buttonBox.over", function(e){
					_hint.show();
				})
				.on("buttonBox.normal", function(e){
					_hint.hide();
				})
			;
			
			//start state
			updateState(_stateString);
			
			_root.on("click", function(){
				$(this).trigger("toggleButton.clicked");
				_hint.show();
				_clickHandler();
			})
		});

		//private
		function updateState(stateString){
			var state = _states[stateString];
			if (state){
				_stateString = stateString;
				//hint
				_hint.text(state.hintText);
				//button
				_button.content(state.buttonContent);
				_root.trigger("toggleButton.stateUpdate");
			}
		}
		
		var pub = {
			state : function(stateString){
				if (stateString){
					updateState(stateString);
				}else{
					return _stateString;
				}
			},
			//getters and setters
			getRoot : function(){ return _root; }
		};
		
		return pub;
	};

	/**
	 * rootDiv - the div that will be made into a DropDown
	 * config : { all field below are optional, including the config obj
	 *   menuLocation : the div that will be the parent submenu.  
	 *     default : parent will be root
	 *   
	 *   id : unique parent div id if you wish to display ids on various objects
	 *     default : objects will not contain ids if this is blank
	 *     
	 *   rows : [
	 *   	{
	 *   		id : optional, applies id to row if supplied
	 *   		text : the text of the row
	 *   		callback : click handler function,
	 *   		checked : this row has been selected and will show up activated, will not actually trigger click handler
	 *   	},...
	 *   ]
	 * } 
	 * * Events: dropDown.rowAdded
	 */
	Flurry.Widgets.DropDown = Flurry.Widgets.DropDown || function(rootDiv, config){
		var _root = $.maker(rootDiv);
		var _subMenu = $("<div />");
		var _selectedRow = $('<span />');
		var _arrow = $("<div />").addClass("inlineBlock");
		
		var _config = config || {};
		_config.menuLocation = $.maker(_config.menuLocation || function(){
			var menuLocation = $("<div />")
				.insertAfter(_root)
			;
			return menuLocation;
		}());
		_config.rows = _config.rows || {};
		
		construct("dropDownActiveTab", _root, function(){
			//the code below rewrites various vars that were html elems.  
			_selectedRow = new SelectedRow(_selectedRow);
			_arrow = new Arrow(_arrow);
			_subMenu = new SubMenu(_subMenu);

			if (_config.id){
				_root.attr("id", "dropDownActiveTab_" + _config.id);
				_subMenu.getRoot().attr("id", "dropDownDiv_" + _config.id);
			}
			
			_root
				.addClass("relative ")
				.addClass("titleDropDown")
				.append(_selectedRow.getRoot())
				.append(_arrow.getRoot())
			;
			_config.menuLocation.append(_subMenu.getRoot());
			
			//this draws the submenu on click as well as handling the off click
			_root.click(function(e) {
				var menu = _subMenu.getRoot();
				globalMenuHandleClick(e, _root, menu);
				
				if (menu.is(":visible")){
					var scrollWindow = menu.find(".dropDownSubmenu");
					var activeRow = getActiveRow();
					
					var top = activeRow.position().top;
					scrollWindow.scrollTop(top + scrollWindow.scrollTop());
				}
				
				return;
			});

			//this function uses the classed version of the vars
			$.each(_config.rows, function(index, row){
				addRow(row);
			});

			//give css classes to certain objects
			_subMenu.getRoot().find(".dropDownSubmenu")
				.addClass("titleDropDownSubmenu")
				.find("div").addClass("titleDropDownText")
			;
		});
		
		//private
		function addRow(row){
			var madeRow = _subMenu.addRow(_selectedRow.getRoot(), row.text, row.callback, row.checked);
			if (row.id){
				madeRow.attr("id", row.id);
			}
			_root.trigger("dropDown.rowAdded")
			return madeRow;
		}
		function getActiveRow(){
			return _subMenu.getActiveRow();
		}
		
		var pub = {
			//returns jQuery row element
			//see class comments for row object
			addRow : function(row){
				return addRow(row);
			},
			//returns the currently active row
			getActiveRow : function(){
				return getActiveRow();
			},
			//getters and setters
			getRoot : function(){ return _root; },
			getMenu : function(){ return _subMenu; },
			getArrow : function(){ return _arrow; },
			getSelectedRow : function(){ return _selectedRow; }
		};
		
		/**
		 * rootSpan - the span that will be made into a SelectedRow
		 * config : { all field below are optional, including the config obj
		 * } 
		 * Events: selectedRow.textUpdate
		 */
		function SelectedRow(rootSpan){
			var _root = $.maker(rootSpan);
			
			(function constructor(){

			})();
			
			//private
			
			var pub = {
				getRoot : function(){ return _root; },
				
				text : function(optText){ 
					if (optText){
						_root.trigger("selectedRow.textUpdate");
					}
					return pub.getRoot().text(optText); 
				}
			};
			
			return pub;
		}
		//end SelectedRow
		
		/**
		 * rootDiv - the div that will be made into a SubMenu
		 * config : { all field below are optional, including the config obj
		 * } 
		 * Events: subMenu.rowAdded
		 */
		function SubMenu(rootDiv){
			var _root = $.maker(rootDiv);
			var _rows = [];
			
			var _containerClass = "dropDownSubmenu";
			var _rowContainer = $("<div />");
			
			construct("dropDownDiv", _root, function(){
				_rowContainer
					.addClass(_containerClass)
				;
				_root
					.addClass("relative")
					.append(_rowContainer)
				;
				_root.hide();
			});
			
			//private
			var _activeClass = "active";
			var _inactiveDropDownClass = "inactiveDropDown";
			var _activeDropDownClass = "activeDropDown";
			var _overDropDownClass = "overDropDown";
			function removeDropdownState($row)
			{
				$row.removeClass(_inactiveDropDownClass)
					.removeClass(_activeDropDownClass)
					.removeClass(_overDropDownClass);
			}
			function resetDropdown($row)
			{
				removeDropdownState($row);
				if ($row.hasClass(_activeClass)){
					$row.addClass(_activeDropDownClass);
				}else{
					$row.addClass(_inactiveDropDownClass);
				}
			}
			function activateRow($row, selectionText){
				//update row text
				selectionText.html($row.data("name"));
				
				//submenu highlighting
				$row.parent().find("."+_activeClass).removeClass(_activeClass);
				$row.addClass(_activeClass);
				$row.parent().find("div").each(function(){
					resetDropdown($(this)); 
				});
			}
			
			var pub = {
				/**
				 * selectedRowText - the text to update to indicate a new row has been chosen, will be replaced with row html
				 * name - the content of the row to display
				 * handleClick - click handler for the row
				 * 
				 * returns jQuery row element
				 */
				addRow : function (selectedRowText, name, handleClick, checked){
					
					var row = $("<div />");
					
					var clickHandler = handleClick || $.noop;

					row
						.data("name", name)
						.html(name)
						.addClass(_inactiveDropDownClass)
						.hover(
							function() {
								removeDropdownState($(this));
								$(this).addClass(_overDropDownClass);
							}, function() {
								resetDropdown($(this));
							}
						)
						.click(function(e){
							var status = clickHandler(e);
							if (status || status == undefined){
								//special override to make sure menu is hidden after click
								_root.addClass("hiddenMenu");
								activateRow($(this), selectedRowText);
							}else{
								e.stopPropagation();
							}
						})
					;
					
					_rowContainer.append(row);
					_rows.push(row);
					
					if (_rows.length == 1 || checked){
						activateRow(row, selectedRowText);
					}

					_root.trigger("subMenu.rowAdded");
					return row;
				},
				
				//getters and setters
				getRoot : function(){ return _root; },
				getRows : function(index){
					if (index){
						return _rows[index];
					}
					return _rows; 
				}, 
				getActiveRow : function(){
					return _root.find("." + _activeClass);
				}
			};
			
			return pub;
		};
		//end SubMenu
		
		
		/**
		 * rootDiv - the div that will be made into an arrow
		 * config : { all field below are optional, including the config obj
		 * } 
		 */
		function Arrow(rootDiv){
			var _root = $.maker(rootDiv);
			var _button = new ButtonBox(_root);
			var _arrowImg = $("<img />"); 
			
			construct("arrow", _root, function(){
				_button.content(_arrowImg);
				_arrowImg
					.attr("src", "../images/icons/contentBarDownArrow_black.png")
					.attr("border", "0px")
				;
				_root
					.addClass("arrowDiv")
				;
			});
			
			var pub = {
				getRoot : function(){ return _root; }
			};
			
			return pub;
		};
		//end Arrow
		
		return pub;
	};
	//end DropDown
	
	/**
	 * requires the jquery tipsy plugin be included, which it currently is on all pages
	 * 
	 * root - the element that the hint will attach to
	 * config : { all field below are optional, including the config obj
	 *    text : text to display in hint
	 *    html : is the tooltip content html
	 *    hidden : start hidden, default true
	 *    gravity : direction to draw the arrow // nw | n | ne | w | e | sw | s | se | >>  ns | ew, special commands to indicate that arrow should be either ns or ew,
	 *    onHover : whether or not to trigger on hover, default true
	 * } 
	 * Events: hint.textUpdate, hint.hidden, hint.shown
	 */
	Flurry.Widgets.Hint = Flurry.Widgets.Hint || function(root, config){
		var _root = $.maker(root);
		
		var defaults = {
				text : "",
				html : false,
				hidden : true,
				gravity : "s"
		};
		var _config = $.extend({}, defaults, config);
		//special case gravity helpers
		if ("ns" == _config.gravity){
			_config.gravity =  $.fn.tipsy.autoNS;
		}else if ("ew" == _config.gravity){
			_config.gravity =  $.fn.tipsy.autoWE;
		}
		
		_config.trigger = (_config.onHover) ? "hover" : "manual";
		
		var _tipsyConfig = {
			fade : true,
			gravity : _config.gravity,
			html : _config.html,
			offset : 10,
			title : "tipsyText",
			trigger : _config.trigger
		}
		
		var _text = "";
		
		construct("toggleButtonHint", _root, function(){
			setText(_config.text);
			_root.tipsy(_tipsyConfig);
			
			if (!_config.hidden){
				show();
			}else{
				hide();
			}
		});
		
		//private
		function setText(text){
			_text = text || " ";
			_root.attr(_tipsyConfig.title, _text);
			_root.trigger("hint.textUpdate")
		}
		function hide(){
			_root
				.tipsy("hide")
				.trigger("hint.hidden")
			;
		}
		function show(){
			_root
				.tipsy("show")
				.trigger("hint.shown")
			;
		}
		
		//public
		var pub = {
			text : function(text){
				if (text){
					setText(text);
				}else{
					return _text;
				}
			},
			show : function(){ show(); },
			hide : function(){ hide(); },
			getRoot : function(){ return _root; }
		};
		
		return pub;
	};
	//end Hint
	
	//misc helper widgets - private
	
	/**
	 * rootDiv - the div that will be made into a button wrapper
	 * config : { all field below are optional, including the config obj
	 *    width : starting width, default: unset
	 *    height : starting height, default: unset
	 *    autoCenter : autocenter content, default: false
	 * } 
	 * Events: buttonBox.normal, buttonBox.over, buttonBox.down, buttonBox.contentUpdated
	 */
	function ButtonBox(rootDiv, config){
		var _root = $.maker(rootDiv);
		var _config = config || {};
		var _width = _config.width;
		var _height = _config.height;
		var _autoCenter = _config.autoCenter || false; 
		
		construct("buttonBox", _root, function(){
			if (_width){
				_root.css("width", _width + "px");
			}
			if (_height){
				_root.css("height", _height + "px");
			}
			//mouseovers - populate the mouseovers
			removeState(_root);
			mouseoverHandlers(_root);
		});
		
		//private
		//remove class-based states
		function removeState($div){
			$div.removeClass("over")
				.removeClass("down");
		};
		
		function mouseoverHandlers($div){
			function normal() {
				removeState($div);
				_root.trigger("buttonBox.normal");
			}
			function over(){
				removeState($div);
				$div
					.addClass("over")
				;
				_root.trigger("buttonBox.over");
			}
			function down() {
				removeState($div);
				$div
					.addClass("down")
				;
				_root.trigger("buttonBox.down");
			}
			$div
				.hover(over,normal)
				.mousedown(down)
				.mouseup(normal)
			;
		}
		
		var pub = {
			content : function(html, center){
				var content = $.maker(html);
				_root.empty().append(content);
				if (center || _autoCenter){
					var rootHeight = _root.outerHeight();
					var contentHeight = content.outerHeight();
					
					var diff = (rootHeight-contentHeight)/2;
					_root.css({
						"text-align" : "center"
					});
					content.css({
						"top" : diff + "px"
					}).addClass("relative");
				}
				_root.trigger("buttonBox.contentUpdated");
			},
			getRoot : function(){ return _root; }
		};
		
		return pub;
	};
	//end ButtonBox
	
	/**
	 * ensures that the identity class is attached to the root
	 */
	function construct(idClass, root, initFunc){
		var _idClass = idClass;
		var _root = root;
		
		_root.addClass(_idClass);
		initFunc();
	};
})();
