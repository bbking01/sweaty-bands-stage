jQuery.extend({
	getLinkParam: function ( link, param ) {
		var QueryString = (function(a) {
			if (a == "") return {};
			var b = {};
			for (var i = 0; i < a.length; ++i)
			{
				var p=a[i].split('=');
				if (p.length != 2) continue;
				b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
			}
			return b;
		})(link.substr(link.indexOf('?')+1).split('&'));
		if (typeof(QueryString[param]) == "undefined") return '';
		return QueryString[param];
	}
});

jQuery(function($){

    if (typeof(Queldorei_Priceslider) == "undefined") return;

    function reload(url) {
        $.blockUI({ message:null, overlayCSS: {opacity:0.16, zIndex:99999} });
        $('.col-main').first().load(url, function(){
            $.unblockUI();
            _resizeLimit = {};
            $(window).resize();
            /*$('.col-main .category-products').scrollToMe();*/
        });
    }

    //toolbar sort and per page
    $('.toolbar select').live('change', function(){
        reload($(this).val());
        return false;
    });
    //toolbar
    $('.toolbar .toolbar-dropdown a, .toolbar .sort-order a').live('click', function(){
        reload($(this).attr('href'));
        return false;
    });
    //toolbar view mode
    $('.view-mode a').live('click', function(){
        var slidervalue = $("#slider").slider('values');
        var ids = $("#category").val();
        var correctbaseurl = $('#slider-baseurl').val() + 'priceslider/slider/view?min=' + slidervalue[0] + '&max=' + slidervalue[1] + '&id=' + ids + '&mode=' + $(this).attr('class');
        reload(correctbaseurl);
        return false;
    });
    //pager
    $('.pager .pages a').live('click', function(){
	    $.cookie("queldorei_priceslider_page_"+Queldorei_Priceslider.currency_code+Queldorei_Priceslider.category_id, $.getLinkParam( $(this).attr('href'), 'p'), { path: '/' });
        reload($(this).attr('href'));
        return false;
    });

    //slider
    function reloadProducts() {
        var ids = $("#category").val();
        var slidervalue = jQuery("#slider").slider('values');
        var correctbaseurl = $('#slider-baseurl').val() + 'priceslider/slider/view?min=' + slidervalue[0] + '&max=' + slidervalue[1] + '&id=' + ids;
        if ( Queldorei_Priceslider.request_params != '' ) {
            correctbaseurl += '&' + Queldorei_Priceslider.request_params;
        }
        reload(correctbaseurl);
    }
    $("#slider").slider({range:true,
        min:0,
        max:$("#max-price").val(),
        values:[Queldorei_Priceslider.slider_min, Queldorei_Priceslider.slider_max],
        slide:function (event, ui) {
            $("#slider-min").html(Queldorei_Priceslider.currency+ui.values[0]);
            $("#slider-max").html(Queldorei_Priceslider.currency+ui.values[1]);
            },
	    create:function (event, ui) { reloadProducts(); $('#slider').draggable(); },
        stop:function (event, ui) {
            $.cookie("queldorei_priceslider_min_"+Queldorei_Priceslider.currency_code+Queldorei_Priceslider.category_id, ui.values[0], { path: '/' });
            $.cookie("queldorei_priceslider_max_"+Queldorei_Priceslider.currency_code+Queldorei_Priceslider.category_id, ui.values[1], { path: '/' });
            reloadProducts();
        }
    });
});