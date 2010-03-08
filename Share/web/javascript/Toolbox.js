var Toolbox = {
	setLocation: function(url){
		window.location.href = Toolbox.makeURL(url);
	},
	makeURL: function(url){
		base = document.getElementsByTagName('base');
		has_http = /^http:/;
		if (!has_http.test(url)) {
			if(url.substr(0,1) != '/'){
				if (base.length > 0) {
					base = document.getElementsByTagName('base')[0].href;
				} else if(redirect_url != undefined){
					base = redirect_url;
				} else {
					base = '';
				}
				if(base.substr(base.length-1) != '/'){
					base = base+'/';
				}
				url = base+url;
			}
		}
		return url;	
	},
	openPopup: function(url, width, height, name){
		var win = window.open(Toolbox.makeURL(url),name,'resizable=yes,location=no,menubar=no,scrollbars=yes,status=no,toolbar=no,fullscreen=no,width='+width+',height='+height);
	},
	toggleElement: function(element){
		if(element.style.display == 'none'){
			this.showElement(element);
		} else {
			new Effect.Parallel([
				new Effect.BlindUp(element, { sync: true }), 
				new Effect.Fade(element, { sync: true }) 
				], { duration: 0.5 });
		}		
	},
	showElement: function(element){
		if(element.style.display == 'none'){
			new Effect.Parallel([
				new Effect.BlindDown(element, { sync: true }), 
				new Effect.Appear(element, { sync: true }) 
				], { duration: 0.5 });
		}
	},
	
    String: {
		substr: function(string, length, smart, cutsymbol){
			if(!cutsymbol){
		        cutsymbol = '...';
		    }
		    len = string.length;
		    if(len > length){
		        if(smart){
		            cutlen = len - length;
		 
		            cutleft = Math.floor(cutlen / 2);
		            cutright = Math.ceil(cutlen / 2);
		 
		            leftsplit = Math.floor(len / 2);
		            rightsplit = Math.floor(len / 2);
		 
		 
		            left = string.substring(0, leftsplit);
		            left = left.substring(0, (left.length - cutleft));
		 
		            right = string.substring(rightsplit);
		            right = right.substring(cutright);
		 
		            return left+cutsymbol+right;
		        } else {
		        	if(string.length > length){
		        		return string.substring(0, length)+cutsymbol;
		        	} else {
		        		return string.substring(0, length);
		        	}
		            
		        }
		    } else {
		        return string;
		    }
		},
		
		substrWrite: function(string, length, smart, cutsymbol){
			document.write('<span class="substr" title="'+string+'">'+Toolbox.substr(string, length, smart, cutsymbol)+'</span>');
		}
	},	
	
	XML: {
		convertToXHTML: function(elm){
			if(elm.nodeName){
				switch(elm.nodeName){
					case '#text':
						return document.createTextNode(elm.nodeValue);
						break;
					default:
						var iElement = document.createElement(elm.nodeName);
						for (var i = 0; i < elm.attributes.length; i++) {
							iElement.setAttribute(elm.attributes[i].nodeName, elm.attributes[i].nodeValue);
						}
						for (var i = 0; i < elm.childNodes.length; i++) {
							iElement.appendChild(Toolbox.convertXMLToXHTML(elm.childNodes[i]));
						}
						return iElement;
						break;
				}
			}
		}
	},
	
	scrollToElement: function(element, relative){
		if(!relative){
			var target = 0;
			var relative = document.body;
			window.scrollTo(0, element.cumulativeOffset().top);
		} else {
			var target = element.cumulativeOffset().top - relative.cumulativeOffset().top;
			this._scroll(relative, target);
		}
	},
	
	_scroll: function(element, target){
		new Effect.Tween(null, element.scrollTop, target, {
			duration: .2
		}, this._scrollTo.bind(this, element));	
	},
	_scrollTo: function(element, p){
		element.scrollTop = p;
	}
};




/**
 * Toolbox list class.
 * 
 * This class implements pseudo associative
 * arrays
 */
var List = Base.extend({
	constructor: function(){
		this.items = new Array();
		this.keys = new Array();
		
		this.position = 0;
		this.counter = 0;
		this.length = 0;
	},
	
	empty: function(){
		this.constructor();
	},
	
	push: function(value, key){
		if(!key){
			key = this.counter++;
		}
		this.items.push(value);
		this.keys.push(key);
		this.length++;
		return value;
	},
	
	remove: function(key){
		var items = new Array();
		var keys = new Array();
		
		for(var i = 0; i <= this.keys.length; i++){
			if(this.keys[i] && this.keys[i] != key){
				items.push(this.items[i]);
				keys.push(this.keys[i]);
			} else {
				this.length--;
			}
		}
		this.items = items;
		this.keys = keys;
	},
	
	get: function(key){
		var key = this._getItemId(key);
		if(key !== false){
			return this.items[key];
		}
		return false;
	},
	
	each: function(){
		var position = this.position++;
		if(this.items[position]){
			return new Array(this.keys[position], this.items[position]);
		} else {
			return false;
		}
	},
	
	reset: function(){
		this.position = 0;
	},
	
	search: function(value){
		for(var i = 0; i <= this.items.length; i++){
			if(this.items[i] == value){
				return this.keys[i];
			}
		}
		return false;
	},
			
	_getItemId: function(key){
		for(var i = 0; i <= this.keys.length; i++){
			if(this.keys[i] == key){
				return i;
			}
		}
		return false;
	}
});