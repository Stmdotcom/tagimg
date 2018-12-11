
$(document).ready(function() {	
	if ($("#files").length > 0){
		document.getElementById('files').addEventListener('change', handleFileSelect, false);
	}
});

/*
function submittagsearch(){  
    $.ajax({ // create an AJAX call...
		data: $('#searchtagform').serialize(), // get the form data
		type: 'post', // GET or POST
        dataType: 'json',
		url: 'index.php?a=json&jaction=searchtag', // the file to call
		success: function(response) { // on success.. 
            console.log(response);
            if (response.result == 'ok'){
                $.get('index.php?a=json&jaction=loadimagetags&imageid=' + response.imageid, function(data){
					$('#tagarea').html(data);
                });
            }else{
                alert("Tag add fail " + response.result);
            }
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);
            alert("Tag add fail");
			return false;
		}
	});     
}*/


var currentImgId = -1;
function slideshow(move){
	var currentindex = loadedimg.indexOf(String(currentImgId));
	var newIndex = currentindex + move;
	if (newIndex < 0){
		newIndex = 0;
	}
	if (typeof loadedimg[newIndex] == 'undefined'){
		newIndex = 0;
	}
	loadImageDynamic(loadedimg[newIndex]);
}

function applyDrowpdown(element){
    $(element + ' .tagDropDown').autocomplete({
        source: function(request, response) {
            //console.log(request);
            var ddtype = this.element.attr('ddtype');
            var injectURL = '';
            if (ddtype.length > 0){
                injectURL = "&type=" + ddtype;
            }
            
            console.log(ddtype);
            $.ajax({
                url: 'index.php?a=json&jaction=autotag' + injectURL,
                dataType: 'jsonp',
                data: {
                    maxRows: 12,
                    q: request.term
                },
                success:function(data){	
                    if (data.result != null){
                        response($.map(data.result, function(item) {
                            return {
                                label: item.name,
                                value: item.name
                            };
                        }));
                    }
                }
            });
        }
    });
}



function dump(){
	console.log(loadedimg);
	console.log(loadedimg.indexOf('12'));
}

function loadImageDynamic(id){
        $.ajax({ // create an AJAX call...
		type: 'get', // GET or POST
        dataType: 'json',
		url: 'index.php?a=json&jaction=loadimage&id=' + id, // the file to call
		success: function(response) { // on success.. 
            console.log(response);
            
            var response = new supercore.ServerResponse(response);
            if (response.isGood()){
                replaceContent(2,response.data_2,response.data);
				flipContent(2);
				applyDrowpdown('#sidebarBox_2');
				currentImgId = id;
            }else{
                alert("Request fail");
            }
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);
            alert("Tag add fail");
			return false;
		}
	});
    
}

//var state = 1;
function flipContent(state){
    if (state === 2){
        $('#contentBox').hide();
        $('#sidebarBox').hide();
        $('#contentBox_2').show();
        $('#sidebarBox_2').show();
    }else if (state === 1){
        $('#contentBox_2').hide();
        $('#sidebarBox_2').hide();
        $('#contentBox').show();
        $('#sidebarBox').show();
    }
}

function replaceContent(type,content,sidebar){
    if (type > 1){
        type = '_'+ type;
    }else{
		type = '';
    }

    if (content !== null){
        $('#contentBox' + type).html(content);
    }
    if (sidebar !== null){
        $('#sidebarBox' + type).html(sidebar);
    }
}







function submittag(){
    
    $.ajax({ // create an AJAX call...
		data: $('#newtagform').serialize(), // get the form data
		type: 'post', // GET or POST
        dataType: 'json',
		url: 'index.php?a=json&jaction=savetag', // the file to call
		success: function(response) { // on success.. 
            console.log(response);
            if (response.result == 'ok'){
                $.get('index.php?a=json&jaction=loadimagetags&imageid=' + response.imageid, function(data){
					$('#tagarea').html(data);
                });
            }else{
                alert("Tag add fail " + response.result);
            }
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);
            alert("Tag add fail");
			return false;
		}
	});
}

function submitfreshtag(){
    
    $.ajax({ // create an AJAX call...
		data: $('#freshtagform').serialize(), // get the form data
		type: 'post', // GET or POST
        dataType: 'json',
		url: 'index.php?a=json&jaction=savefreshtag', // the file to call
		success: function(response) { // on success.. 
            console.log(response);
            if (response.result == 'ok'){
				alert("Tag added " + response.result);
            }else{
				alert("Tag add fail " + response.result);
            }
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
            console.log(XMLHttpRequest);
            console.log(textStatus);
            console.log(errorThrown);
            alert("Tag add fail");
			return false;
		}
	});
}

function submitDirectory(formID){
    
    var data = $('#'+formID).serialize();
    var meth = $('#'+formID).attr('method');
    var url = $('#'+formID).attr('action');
    //var group = $('#'+formID).attr('action');
    directoryiterator( data,meth, url);
}



function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object

    // Loop through the FileList and render image files as thumbnails.
	$('#list').html("");
    for (var i = 0, f; f = files[i]; i++) {

      // Only process image files.
      if (!f.type.match('image.*')) {
        continue;
      }

      var reader = new FileReader();

      // Closure to capture the file information.
	  reader.onerror = function(evt) {
					switch(evt.target.error.code) {
					  case evt.target.error.NOT_FOUND_ERR:
						alert('File Not Found!');
						break;
					  case evt.target.error.NOT_READABLE_ERR:
						alert('File is not readable');
						break;
					  case evt.target.error.ABORT_ERR:
						break; // noop
					  default:
						alert('An error occurred reading this file.');
					};
				  };
	  
      reader.onload = (function(theFile) {
        return function(e) {
          // Render thumbnail.
          var span = document.createElement('span');
          span.innerHTML = ['<img class="thumb" src="',
							e.target.result,
                            '" title="', escape(theFile.name), '"/>'].join('');
          document.getElementById('list').insertBefore(span, null);
        };
      })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
}




function directoryiterator(adata,atype,aurl){
    console.log("Hit itter");
        $.ajax({ // create an AJAX call...
		data: adata, // get the form data
		type: atype, // GET or POST
        dataType: 'json',
		url: aurl, // the file to call
		success: function(response) { // on success..
            console.log(response);
            if (response.done == 'f'){
                directoryiterator(response,atype,aurl);
            }else{
               alert('Done!!');
            }
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			return false;
		}
	});
}


function loadImageLoop(response){
    console.log("Loop has data");
    console.log(response);
    if (response.done == 'f')
    {
        skip = response.skip;
        dir = response.thedir;
        dest = response.thedest;
        var result = ajaxImageLoadRequest(skip,dir,dest);
        console.log("Looping again with data");
            console.log(result);
        loadImageLoop(result);
    }else{
        console.log("DONE");
      //  data: {status: 'ok'}; 
        return 'ok';
    }
}


function ajaxImageLoadRequest(skip,dir,dest){
    console.log("CALL AJAX REQUEST");
    $.ajax({ // create an AJAX call...
		data: {skip: skip,
               thedir: dir,
                thedest: dest}, // get the form data
        type: 'GET', // GET or POST
        dataType: 'json',
		url: 'index.php?a=json&jaction=loaddir', // the file to call
		success: function(response) { // on success..
			return response;
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			return false;
		}
	});
}
function formAjaxSubmit(formID){
	//alert ('hit'+formID);
	$.ajax({ // create an AJAX call...
		data: $('#'+formID).serialize(), // get the form data
		type: $('#'+formID).attr('method'), // GET or POST
		url: $('#'+formID).attr('action'), // the file to call
		success: function(response) { // on success..
			return response;
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			return false;
		}
	});
	return false; // cancel original event to prevent form submitting
}

function formAjaxSubmitTarget(formID,target){
	//alert ('hit'+formID);
	$.ajax({ // create an AJAX call...
		data: $('#'+formID).serialize(), // get the form data
		type: $('#'+formID).attr('method'), // GET or POST
		url: $('#'+formID).attr('action'), // the file to call
		success: function(response) { // on success..
			$('#'+target).html(response); // update the DIV - should I use the DIV id?
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			$('#'+target).html(errorThrown); // update the DIV - should I use the DIV id?
		}
	});
	return false; // cancel original event to prevent form submitting
}


//Clears a intergration on a customer level
function callGenericJSON(thejson,theparams){

	$.ajax({
		url: '/console.php?a=json&jaction=' + thejson + theparams,
		type: 'GET',
		dataType: 'json',
		success: function(q){
			var response = new supercore.ServerResponse(q);
			response.showMessage();	
		},
		error: function(){
			show_feedback("Error",true);
		}
	});
}


var imageView = function(){
	var dataStore = {};
	
	return{
		get : function(name){
			return dataStore[name];
		},
		set : function(name,data){
			dataStore[name] = data;
		}
	};
};

function getZip(){
    
}


function requestDirectoryLoad(){

}

function deletetheimage(){
    $("#deleteimage").submit();
}

function updateProgress(){
	
}