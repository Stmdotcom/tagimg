/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var supercore = { 
	utils:{}
};


supercore.consts = {
	_entityMap : {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': '&quot;',
			"'": '&#39;',
			"/": '&#x2F;'
	}
};

supercore.utils = {

	isFunction:function(obj){
		return typeof obj === "function";
	},
	isInt : function(value){
		return !isNaN(value) && parseInt(value) == value;
	},
	tirmTo : function(string,length){
		return string.substring(0,length);
	},
	arrayToString : function(thedata){
		var userString = '';
		var comma = '';
		for(var re in thedata){
			userString += comma + thedata[re];
			comma = ',';
		}
		return userString;
	},
	escapeChar : function(string, find,replace){
		return String(string).replace(find,replace);
	},
	escapeHtml : function(string){
		return String(string).replace(/[&<>"'\/]/g, function (s) {
			return supercore.consts._entityMap[s];
		});
	},
	waitForPause : function(ms, callback) {
		var timer;
		return function() {
			var self = this, args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function() {
				callback.apply(self, args);
			}, ms);
		};
	},
	isArrayEmpty : function(array,index,strict){
		index = index || 0;
		strict = strict || false;
		if(typeof array[index] === 'undefined' || array[index] === null){
			return true;
		}else{
			if(strict){
				var t = array[index];
				if(typeof t == 'string' || t instanceof String){
					t = array[index];
					t.trim();
				}
				return !(t != '' && t !== null);
			}
			return false;
		}
	},
	valueExists : function(val) {	//Checks if a value exits
		return !(typeof val === 'undefined' || val === null);
	},
	stringExists : function(val) {	//Checks if a string Exists
		if (typeof val === 'undefined' || val === null){
			return false;
		}else{
			val =  String(val);
			val.trim();
			return (val.length > 0);
		}
	},
	valToString : function(val) {	//Value to string
		if (typeof val === 'undefined' || val === null){
			return '';
		}else{
			return val + '';
		}
	},
	isEmptyObj : function(obj){ //Checks if object is empty
		if (obj !== null && typeof obj === 'object'){
			return (Object.keys(obj).length === 0);
		}else{
			return false;
		}
	},
	dateToTimestamp : function(date, offset){ //Convert a Date object to a UNIX timestamp
		if (Object.prototype.toString.call(date) === '[object Date]'){
			var timestamp = date.getTime() / 1000; //Convert micro time to UNIX
			if (typeof offset === 'undefined' || offset === null){}
			else{
				timestamp = timestamp + (offset); //Calc offset
			}
			return Math.floor(timestamp);
		}else{
			return 0;
		}
	},
	getSize : function (obj){ //Get the size on an object
		//Alternative method
		//Object.keys(myArray).length
		var size = 0, key;
		for (key in obj) {
			if (obj.hasOwnProperty(key)) size++;
		}
		return size;
	},
	arrayMerge : function (arraya,arrayb){ //Array concat with de-dup
		//Only run if contents in the arrays
		if (arrayb.length <= 0){
			return arraya;
		}else if (arraya.length <= 0){
			return arrayb;
		}
		var a = arraya.concat(arrayb);
		for(var i=0; i<a.length; ++i) {
			for(var j=i+1; j<a.length; ++j) {
				if(a[i] === a[j])
					a.splice(j--, 1);
			}
		}
		return a;
	},
	pad : function (n, width, z) {
		z = z || '0';
		n = n + '';
		return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
	},
	remove : function(array,i){
		return array.splice(i, 1);
	},
	dataToString : function(data,what,sep){
		var theReturn = '';
		var comma = '';
		for(var t in data){ //Get the array pos of the selected
			if (typeof data[t][what] === 'undefined' || data[t][what] === null){
				theReturn += comma + '';
			}else{
				theReturn += comma + data[t][what];
			}
			comma = sep;
		}
		return theReturn;
	}
};


//SERVER RESPONSE BLOCK
supercore.ServerResponse = function(sresponse){
	this.state = sresponse.state;
	this.data = sresponse.data;
	this.data_2 = sresponse.data_2;
	this.data_3 = sresponse.data_3;
	this.message = sresponse.message;
};

supercore.ServerResponse.prototype.getMessage = function(){
	return this.message;
};

supercore.ServerResponse.prototype.getData = function(){
	return this.data;
};

supercore.ServerResponse.prototype.getData_2 = function(){
	return this.data_2;
};

supercore.ServerResponse.prototype.getData_3 = function(){
	return this.data_3;
};

supercore.ServerResponse.prototype.showMessage = function(){
	var good = this.isGood();
	if (good){
		if (this.message !== ''){
			show_feedback(this.message);
		}else{
			show_feedback("No Message", true);
		}
	}else{
		if (this.message !== ''){
			show_feedback(this.message, true);
		}else{
			show_feedback("No Message", true);
		}
	}
};

supercore.ServerResponse.prototype.isGood = function(){
	if (this.state == 'good' || this.state == 'pass'){
		return true;
	}else{
		return false;
	}
};
//END SERVER RESPONSE BLOCK


