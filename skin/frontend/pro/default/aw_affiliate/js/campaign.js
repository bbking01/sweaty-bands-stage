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
 * @package    AW_Affiliate
 * @copyright  Copyright (c) 2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var AWGenerateLink = Class.create();
AWGenerateLink = {
    initialize: function(config) {
        this.config = config;
        this.url = config.url;
        Event.observe(window, 'load', function(){
            $('generate-link-btn').observe('click', this.generateLink.bind(this));
        }.bind(this));
    },

    generateLink: function() {
        this._showProgressMessage();
        this._sendRequest();
    },

    _showProgressMessage: function() {
        var overlay = document.createElement('div');
        overlay.addClassName('aw-overlay-popup');
        overlay.setAttribute('id', 'aw-overlay');

        var messageBlock = document.createElement('div');
        messageBlock.setAttribute('id', 'waiting-message');
        var text = document.createTextNode(this.config.msg.generateLink);
        messageBlock.appendChild(text);

        var containerBlock = $$('.generate-link.fieldset')[0];
        containerBlock.appendChild(messageBlock);
        containerBlock.appendChild(overlay);

        var __left = containerBlock.getWidth()/2 - messageBlock.getWidth()/2 + 'px';
        var __top = (containerBlock.getHeight()/2 - messageBlock.getHeight()/2) + 'px';
        messageBlock.setStyle({'left': __left, 'top': __top});
    },

    _hideProgressMessage: function() {
        if (typeof($('aw-overlay')) != 'undefined') {
            $('aw-overlay').remove();
        }
        if (typeof($('waiting-message')) != 'undefined') {
            $('waiting-message').remove();
        }
    },

    _sendRequest: function() {
        var _url = this.url;
        var _params = 'link_to_generate=' + encodeURIComponent($('link-to-generate').getValue())+'&traffic_source_generate=' + encodeURIComponent($('traffic-source-generate').getValue())
        new Ajax.Request(_url, {
           parameters: _params,
           onComplete:  this._onRequestComplete.bind(this)
        });
    },

    _onRequestComplete: function(transport) {
        this._hideProgressMessage();
        try {
            $$('.generate-link .error-messages').each(function(el){
                el.remove();
            });
            eval("var response = " + transport.responseText);
            if (response.error == 0) {
                $('result').setValue(response.result);
                $('result').focus();
                $('result').select();
            }
            else {
                var messageDiv = document.createElement('div');
                messageDiv.addClassName('error-messages');
                var html = '';
                response.messages.each(function(mess){
                    html += mess + '<br />';
                });
                messageDiv.innerHTML = html;
                $$('.generate-link')[0].insertBefore(messageDiv, $$('.generate-link')[0].select('.row')[0]);
            }
        }
        catch(e) {
            if (typeof(console) == "object"){
                console.log(e.message);
            }
            return;
        }
    }
}