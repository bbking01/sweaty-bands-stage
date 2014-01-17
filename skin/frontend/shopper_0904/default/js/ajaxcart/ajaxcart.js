//called from quick view iframe
function setAjaxData(data,iframe){
    //showMessage(data.message);
    if (data.status != 'ERROR' && jQuery('.cart-top-container').length) {
        jQuery('.cart-top-container').replaceWith(data.cart_top);
    }
}

function showMessage(message)
{
    jQuery('body').append('<div class="alert"></div>');
    var $alert = jQuery('.alert');
    $alert.slideDown(400);
    $alert.html(message).append('<button></button>');
    jQuery('button').click(function () {
        $alert.slideUp(400);
    });
    $alert.slideDown('400', function () {
        setTimeout(function () {
            $alert.slideUp('400', function () {
                jQuery(this).slideUp(400, function(){ jQuery(this).detach(); })
            });
        }, 7000)
    });
}

jQuery(function($) {

    $('.btn-cart').live('click', function () {
        if ( $(window).width() < 769 )  {
            return false;
        }
        var cart = $('.cart-top');
        var imgtodrag = $(this).parents('li.item').find('a.product-image img:not(.back_img)').eq(0);
        if (imgtodrag) {
            var imgclone = imgtodrag.clone()
                .offset({ top:imgtodrag.offset().top, left:imgtodrag.offset().left })
                .css({'opacity':'0.7', 'position':'absolute', 'height':'150px', 'width':'150px', 'z-index':'1000'})
                .appendTo($('body'))
                .animate({
                    'top':cart.offset().top + 10,
                    'left':cart.offset().left + 30,
                    'width':55,
                    'height':55
                }, 1000, 'easeInOutExpo');
            imgclone.animate({'width':0, 'height':0}, function(){ $(this).detach() });
        }
        return false;
    });


    if ( Shopper.quick_view ) {
        $('li.item').live({
            mouseenter: function(){ $(this).find('.quick-view').css('display', 'block'); },
            mouseleave: function(){ $(this).find('.quick-view').hide(); }
        });
    }

    $('.fancybox').live('click', function() {
        if ( $(window).width() < 769 )  {
            window.location = $(this).next().attr('href');
            return false;
        }
        $this = $(this);
        $.fancybox({
            hideOnContentClick:true,
            width:800,
            autoDimensions:true,
            type:'iframe',
            href: $this.attr('href'),
            showTitle:true,
            scrolling:'no',
            onComplete:function () {
                $('#fancybox-frame').load(function () { // wait for frame to load and then gets it's height
                    $('#fancybox-content').height($(this).contents().find('body').height() + 30);
                    $.fancybox.resize();
                });
            }
        });
        return false;
    });

    $('.show-options').live('click', function(){
        $('#fancybox' + $(this).attr('data-id')).trigger('click');
        return false;
    });
    $('.ajax-cart').live('click', function(){
        setLocationAjax($(this).attr('data-url'), $(this).attr('data-id'));
        return false;
    });

    function setLocationAjax(url, id)
    {
        url = url.replace("checkout/cart", "ajax/index");
        url += 'isAjax/1';
        $('#ajax_loading' + id).css('display', 'block');
        try {
            $.ajax({
                url:url,
                dataType:'jsonp',
                success:function (data) {
                    $('#ajax_loading' + id).css('display', 'none');
                    showMessage(data.message);
                    if (data.status != 'ERROR' && $('.cart-top-container').length) {
                        $('.cart-top-container').replaceWith(data.cart_top);
                    }
                }
            });
        } catch (e) {
        }
    }

});
