jQuery(function ($) {

    $('.add-to-links .link-wishlist').live('click', function () {
        ajaxWishlist($(this).attr('href'), $(this).attr('data-id'));
        return false;
    });
    $('.add-to-links .link-compare').live('click', function () {
        ajaxCompare($(this).attr('href'), $(this).attr('data-id'));
        return false;
    });

    function showMessage(message)
    {
        $('body').append('<div class="alert"></div>');
        $('.alert').slideDown(400);
        $('.alert').html(message).append('<button></button>');
        $('button').click(function () {
            $('.alert').slideUp(400);
        });
        $('.alert').slideDown('400', function () {
            setTimeout(function () {
                $('.alert').slideUp('400', function () {
                    $(this).slideUp(400, function(){ $(this).detach(); })
                });
            }, 7000)
        });
    }

    function ajaxCompare(url, id)
    {
        url = url.replace("catalog/product_compare/add", "ajaxwishlist/index/compare");
        url += 'isAjax/1/';
        $('#ajax_loading' + id).css('display', 'block');
        $.ajax({
            url:url,
            dataType:'jsonp',
            success:function (data) {
                $('#ajax_loading' + id).css('display', 'none');
                showMessage(data.message);
                if (data.status != 'ERROR' ) {
                    $('.block-compare').replaceWith(data.sidebar);
                    $('.compare-top-container').replaceWith(data.top_block);
                    $('.col-left').masonry('reload');
                }
            }
        });
    }

    function ajaxWishlist(url, id) {
        url = url.replace("wishlist/index", "ajaxwishlist/index");
        url += 'isAjax/1/';
        $('#ajax_loading' + id).css('display', 'block');
        $.ajax({
            url:url,
            dataType:'jsonp',
            success:function (data) {
                $('#ajax_loading' + id).css('display', 'none');
                showMessage(data.message);
                if (data.status != 'ERROR') {
                    if ($('.block-wishlist').length) {
                        $('.block-wishlist').replaceWith(data.sidebar);
                        $('.col-left').masonry('reload');
                    } else {
                        $('.header-container .links').replaceWith(data.toplink);
                    }
                }
            }
        });
    }

});