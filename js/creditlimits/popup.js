var SIM_POPUP = Class.create();
var CLOSE_IMG = null;
SIM_POPUP.prototype = {
	popUpWindow : null,
	popUpBox: null,
	
	initialize: function() {
			
		if(!$('sim_popup_window')){
			
			var a = document.createElement('div');
			a.innerHTML = '<div id="sim_popup_window" ></div>'
					    + '<div id="sim_popup_box" style="position:absolute; left:0; top:0; border:2px solid #E7E4D4; overflow-x:hidden;">'
							+ '<div style="float:right; padding-right:10px;" id="sim_popup_close">'
								+ '<img  src="'+CLOSE_IMG+'" alt="Close" />'
							+ '</div>'
							+ '<div id="sim_popup_content"></div>'
						+ '</div>';							
			document.body.appendChild(a);						
		}
		
		this.popUpWindow = $('sim_popup_window');
		this.popUpBox    = $('sim_popup_box');

		Element.hide('sim_popup_window');
		Element.hide('sim_popup_box');
		
		$('sim_popup_close').observe('click', this.close.bind(this));

    },
	showPopUpDiv :function (htm){
		if(htm){
			this.setPopupContent(htm);
		}
		this.initPopUp();		
	},
	setPopupContent : function (htm){
		$('sim_popup_content').innerHTML = htm;
		//this.initPopUpBox();
	},
	
	getDocHeight : function ()
	{
		var D = document;
    	return Math.max
		(
		 	Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        	Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        	Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    	);
	},
	
	initPopUp : function(){
		var wH = document.viewport.getHeight();
		var wW = document.viewport.getWidth();
		
		var tWH = this.getDocHeight();
				
		this.popUpWindow.setStyle({'height':tWH+'px'});
		this.popUpWindow.setStyle({'width' :wW +'px'});
	
		this.toggleSelectsUnderBlock(this.popUpWindow, false);
		
		Element.hide('sim_popup_box');
		Element.show('sim_popup_window');
		
	},
	initPopUpBox : function (h, w , l , t){
	
		h = (h)? h : 200 ; 
		w = (w)? w : 400 ; 

/*		if(!l){
			l = (window.innerWidth - w)/2 
			  + document.viewport.getScrollOffsets()[0]; 
		}
		
		if(!t){
			t = (window.innerHeight - h)/2 
			  + document.viewport.getScrollOffsets()[1]; 
		}*/

		if(!l){
			l = (document.viewport.getWidth() - w)/2 
			  + document.viewport.getScrollOffsets()[0]; 
		}
		
		if(!t){
			t = (document.viewport.getHeight() - h)/2 
			  + document.viewport.getScrollOffsets()[1]; 
		}
		
		if(h !='auto'){
			h = h + "px";
		}
		
		this.popUpBox.setStyle({'height':h});
		this.popUpBox.setStyle({'width' :w +'px'});
		this.popUpBox.setStyle({'left'  :l +'px'});
		this.popUpBox.setStyle({'top'   :t +'px'});
		this.popUpBox.setStyle({'di'   :t +'px'});
		
		Element.show('sim_popup_box');

	},
	close : function (){
		this.toggleSelectsUnderBlock(this.popUpWindow, true);
		Element.hide('sim_popup_window');
		Element.hide('sim_popup_box');
		
	},
    toggleSelectsUnderBlock : function(block, flag){

		if(Prototype.Browser.IE){
        var selects = document.getElementsByTagName("select");
        for(var i=0; i<selects.length; i++){
        	if(flag){
                if(selects[i].needShowOnSuccess){
                    selects[i].needShowOnSuccess = false;
                    selects[i].style.visibility = '';
                }
            }else{
                if(Element.visible(selects[i])){
                    //selects[i].style.visibility = 'hidden';
                    //selects[i].needShowOnSuccess = true;
                }
            }
        }
    }
}

};
