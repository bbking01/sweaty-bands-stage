/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Seacrhautocomplete
 * @copyright  Copyright (c) 2003-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

Varien.searchForm.addMethods({
    presubmit: function() {

        var formTosubmit = this.form;
        var fieldTosubmit = this.field;

        formTosubmit = (function(_formTosubmit) {
            var old_formTosubmit= _formTosubmit.submit;

            if (typeof($$('#sac-results li.selected input')[0])!='undefined'){

                return  _formTosubmit.submit = (function (event) {

                    var  url = $$('#sac-results li.selected input')[0].value;

                    if (typeof(url)!='undefined'){

                       eval(url);

                       if (url.indexOf('window.open') == 0){
                            window.location.href = window.location.href;
                        }

                    //fieldTosubmit.value = "";
                    // if (Prototype.Browser.IE) Event.stop(event);
                    }
                })();
            }else{
                try{
                    old_formTosubmit.apply(this, arguments);
                    old_formTosubmit();
                }catch(e){
                //console.log(e);
                }
            }
            return true;
        })(formTosubmit);
    }
});

Varien.searchForm.prototype._selectAutocompleteItem = function(){}


var Searchcomplete = Class.create();
Searchcomplete.prototype = {
    field: "",
    initialize : function(){
        this.field = $('myInput');
    },
    initAutocomplete : function(url, destinationElement){
        var SACautocompleter = new Ajax.Autocompleter(
            this.field,
            destinationElement,
            url,
            {
                paramName: this.field.name,
                method: 'get',
                minChars: 3,
                frequency: queryDelay,
                onShow : function(element, update) {
                    var posSC = $('myInput').cumulativeOffset();
                    posSC.top = posSC.top + parseInt($('myInput').getHeight()) + 3;
                    if (!Prototype.Browser.IE)
                        posSC.left -= (parseInt($('myInput').getStyle('padding-left')) + 3);

                    $('myContainer').setStyle({
                        top: posSC.top+"px",
                        left: posSC.left+"px"
                    });

                    update.show();
                    $('myContainer').show();
                },
                onHide : function(element, update)
                {
                    $('myContainer').hide();
                    update.hide();
                    SACautocompleter.lastHideTime = new Date().getTime();
                },
                updateElement : function(element)
                {
                    return false;
                }
            }
            );
        SACautocompleter.startIndicator = function(){
            this.element.setStyle({
                backgroundImage: 'url("'+preloaderImage+'")',
                backgroundRepeat: 'no-repeat',
                backgroundPosition: 'right'
            });
        }
        SACautocompleter.stopIndicator = function(){
            this.element.style.backgroundImage = 'none';
        }
        SACautocompleter.onKeyPress = function(event) {
            var e=window.event || event;
            if (e.keyCode == Event.KEY_RETURN){
                var diff = new Date().getTime() - SACautocompleter.lastHideTime;
                var el = $$('#myContainer .selected')[0];
                if (diff > 250 || !el || el.hasClassName('aw_hidden')) {
                    searchForm.presubmit();
                    return;
                }
                el.click();
                Event.stop(e);
            }
        };

        SACautocompleter.customInit = function(element) {
            Event.observe(element, 'keydown', SACautocompleter.onKeyPress.bindAsEventListener(this));
        }
        SACautocompleter.customInit(this.field);
    }
}
