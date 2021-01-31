var multiCMS = {
	isInit: false,
	isOverlay: false,
	
	init: function(){
		if(multiCMS.isInit) return;
		
		if(typeof Ajax != "object") {
			alert("Sie müssen Prototype (http://www.prototypejs.org) laden, um diese Klasse (multiCMS) verwenden zu können");
			return;
		}
	
		multiCMS.isInit = true;
	},
	
	formHandler: function(formID, onSuccessFunction, onErrorFunction){
		multiCMS.init();
		if(!multiCMS.isInit) return;
		
		if(!$(formID)) alert("Formular nicht gefunden!");
		if($(formID).elements.length == 0) alert("Keine Daten zum Speichern gefunden!");
		
		setString = "formID="+formID;
		for(i = 0;i < $(formID).elements.length;i++) {
			if($(formID).elements[i].name == "") continue;
			
			if($(formID).elements[i].type == "radio"){
				if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
			} else if($(formID).elements[i].type == "checkbox"){
				if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"=1";
				else setString += "&"+$(formID).elements[i].name+"=0";
			} else setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
		}
		
		new Ajax.Request("/index.php",{method:'post', parameters: setString, onSuccess: function(transport){
			if(multiCMS.checkResponse(transport)){
				if(typeof onSuccessFunction == "function")
					onSuccessFunction(transport);
			} else
				if(typeof onErrorFunction == "function")
					onErrorFunction(transport);
				
		}});
	},

	callHander: function(HandlerName, action, values, onSuccessF){

		setString = "formID=nix&HandlerName="+HandlerName+"&action="+action;

		if(typeof values == "string"){
			if(values.indexOf("&") != 0)
				values = "&"+values;
			setString  = setString + values;
		}
		
		if(typeof values == "object"){
			jQuery.each(values, function(name, value) {
				setString += "&"+name+"="+value;
			});
		}


		new Ajax.Request("./index.php",{method:'post', parameters: setString, onSuccess: function(transport){
			if(typeof onSuccessF == "undefined") multiCMS.checkResponse(transport);
			else onSuccessF(transport);
		}});
	},
	
	checkResponse: function(transport) {
		console.log(transport.responseText);
		if(transport.responseText.search(/^error:/) > -1){
			eval("var message = "+transport.responseText.replace(/error:/,"")+";");
			alert("Es ist ein Fehler aufgetreten:\n"+message);
			return false;
		}
		if(transport.responseText.search(/^alert:/) > -1){
			eval("var message = "+transport.responseText.replace(/alert:/,"")+";");
			alert(message);
			return false;
		}
		if(transport.responseText.search(/^message:/) > -1){
			eval("var message = "+transport.responseText.replace(/message:/,"")+";");
			alert(message);
			return true;
		}
		if(transport.responseText.search(/^redirect:/) > -1){
			document.location.href=transport.responseText.replace(/redirect:/,"");
		}
		if(transport.responseText.search(/^location:/) > -1){
			document.location.href="index.php?p="+transport.responseText.replace(/location:/,"");
		}
		if(transport.responseText.search(/^permalink:/) > -1){
			document.location.href = transport.responseText.replace(/permalink:/,"");
		}
		if(transport.responseText.search(/^reload/) > -1){
			document.location.reload();
		}
		if(transport.responseText.search(/Fatal error/) > -1){
			alert(transport.responseText.replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
			return false;
		}
		return true;
	},
	
	showOverlay: function(){
		multiCMS.init();
		if(!multiCMS.isInit) return;
		
		if(!$('multiCMSOverlay')){
			var div = new Element('div', {'class': 'overlay', 'id': 'multiCMSOverlay', 'style': 'display:none;'});
			$$("body")[0].insert(div);
		}
		
		multiCMS.isOverlay = true;
		
		multiCMS.fitOverlay();
		
		if(typeof Effect == "object")
			new Effect.Appear('multiCMSOverlay', {duration:0.2});
		else
			$('multiCMSOverlay').style.display = "block";
		
	},
	
	fitOverlay: function(){
		if(!multiCMS.isOverlay) return;
		
		var pageSize = multiCMS.getPageSize();
		
		$('multiCMSOverlay').style.height = pageSize[1]+"px";
		$('multiCMSOverlay').style.width = pageSize[0]+"px";
	},
	
	hideOverlay: function(){
	
		multiCMS.isOverlay = false;
		
		if(typeof Effect == "object")
			new Effect.Fade('multiCMSOverlay', {duration:0.2});
		else
			$('multiCMSOverlay').style.display = "none";
	},
	
	/**
	 * Code from Lightbox
	 **/
    getPageSize: function() {
	        
	     var xScroll, yScroll;
		
		if (window.innerHeight && window.scrollMaxY) {	
			xScroll = window.innerWidth + window.scrollMaxX;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}
		
		var windowWidth, windowHeight;
		
		if (self.innerHeight) {	// all except Explorer
			if(document.documentElement.clientWidth){
				windowWidth = document.documentElement.clientWidth; 
			} else {
				windowWidth = self.innerWidth;
			}
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}	
		
		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else { 
			pageHeight = yScroll;
		}
	
		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){	
			pageWidth = xScroll;		
		} else {
			pageWidth = windowWidth;
		}

		return [pageWidth,pageHeight];
	}
}
if(Event.observe)
	Event.observe(window, 'resize', multiCMS.fitOverlay);
else {
	if($)
		$(window).resize(function() {
			multiCMS.fitOverlay()
		});
}
