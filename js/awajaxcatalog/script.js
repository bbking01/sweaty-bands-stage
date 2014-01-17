/*
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
 * @package    AW_Ajaxcatalog
 * @copyright  Copyright (c) 2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
*/

var AWAjaxCatalog;
var __bind = function(fn, me){
    return function(){
        return fn.apply(me, arguments);
    };
};
AWAjaxCatalog = Class.create();
AWAjaxCatalog.prototype = {
    /*
      Class construcructor
      */
    initialize: function(params) {
        for (var key in params) {
            this[key] = params[key];
        }
        if (this.action_type === "button") {
            document.observe("dom:loaded", __bind(function(event) {
                /*
                        Button click observe
                        */        if ($(this.button_id)) {
                    $(this.button_id).observe("click", __bind(function(event) {
                        this.loadNext();
                    }, this));
                }
            }, this));
        } else {
            this.disabled_forever = false;
            this.start_lock = true;
            document.observe("dom:loaded", __bind(function(event) {
                if (this.needLoadNextBefore()) {
                    this.loadNext();
                }
            }, this));
            Event.observe(window, "scroll", __bind(function(event) {
                /*
                        User scroll document
                        */        if (this.needLoadNextAfter()) {
                    this.loadNext();
                }
                this.start_lock = false;
            }, this));
        }
        return this;
    },
    /*
      Need to load next page after scroll
      */
    needLoadNextAfter: function() {
        var docHeight, height, top;
        if (document.viewport) {
            top = document.viewport.getScrollOffsets().top;
            height = document.viewport.getHeight();
            docHeight = Math.max(Math.max(document.body.scrollHeight, document.documentElement.scrollHeight), Math.max(document.body.offsetHeight, document.documentElement.offsetHeight), Math.max(document.body.clientHeight, document.documentElement.clientHeight));
            return ((docHeight - top) <= (3 * height)) && !this.start_lock && !this.disabled_forever;
        }
        return false;
    },
    needLoadNextBefore: function() {
        var result;
        result = false;
        if (document.viewport) {
            $$('div.main').each(__bind(function(el) {
                var elementHeight, screenHeight;
                screenHeight = document.viewport.getHeight();
                elementHeight = el.getHeight();
                if (screenHeight > elementHeight) {
                    return result = true;
                }
            }, this));
        }
        return result;
    },
    /*
      Load next products
      */
    loadNext: function() {
        var params;
        if (this.isLoading()) {
            return;
        }
        this.showLoader(true);
        this.params['p'] = this.next_page;
        params = Base64.encode(Object.toJSON($H(this.params)));
        new Ajax.Request(this.next_url.replace("{{page}}", params).replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')), {
            method: "get",
            onSuccess: __bind(function(transport) {
                var content, error, next_page, response, success;
                try {
                    response = eval('(' + transport.responseText + ')');
                } catch (exception) {
                    response = {};
                }
                success = response.success, content = response.content, next_page = response.next_page, error = response.error;
                if (error) {
                    if (typeof console.log === "function") {
                        console.log(error);
                    }
                }
                if (success) {
                    if (next_page > 0) {
                        this.next_page = next_page;
                    } else {
                        this.next_page = 0;
                        this.showButton(false);
                    }
                    if (content) {
                        this.appendContent(this.evalInnerScripts(content));
                        this.useDecorator();
                    }
                }
            }, this),
            onComplete: __bind(function(transport) {
                this.showLoader(false);
                if (this.needLoadNextBefore() && (this.next_page > 0)) {
                    this.loadNext();
                }
            }, this)
        });
        return this;
    },
    /*
      Append new content
      */
    appendContent: function(content) {
        if ($(this.container_id)) {
            $(this.container_id).innerHTML += content;
        }
    },
    /*
      Show button
      */
    showButton: function(show) {
        if (this.action_type === "button") {
            if (show) {
                this.getButtonContainer().removeClassName("hidden");
            } else {
                this.getButtonContainer().addClassName("hidden");
            }
        } else {
            this.disabled_forever = true;
        }
    },
    /*
      Show loader
      */
    showLoader: function(show) {
        if (this.action_type === "button") {
            if (show) {
                this.getButton().addClassName("loading");
            } else {
                this.getButton().removeClassName("loading");
            }
        } else {
            if (show) {
                this.getLoader().addClassName("loading");
            } else {
                this.getLoader().removeClassName("loading");
            }
        }
    },
    /*
      Button
      */
    getButton: function() {
        if ($(this.button_id)) {
            return $(this.button_id);
        }
    },
    /*
      Loader
      */
    getLoader: function() {
        if ($(this.loader_id)) {
            return $(this.loader_id);
        }
    },
    /*
      Button with container
      */
    getButtonContainer: function() {
        if ($(this.button_container_id)) {
            return $(this.button_container_id);
        }
    },
    /*
      Is loading now
      */
    isLoading: function() {
        if (this.action_type === "button") {
            return this.getButton().hasClassName("loading");
        } else {
            return this.getLoader().hasClassName("loading");
        }
    },
    /*
      Eval Inner Scripts
      (required after ajax load content)
      */
    evalInnerScripts: function(content) {
        content.evalScripts();
        return content;
    },
    /*
      Use List Decorator to redecorate list
      */
    useDecorator: function() {
        var selection, _i, _len, _ref;
        $$(this.decorate_clean).each(__bind(function(element) {
            element.removeClassName("even");
            element.removeClassName("odd");
            element.removeClassName("last");
            element.removeClassName("first");
        }, this));
        _ref = this.decorate_decorate;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            selection = _ref[_i];
            decorateGeneric($$(selection), ['odd', 'even', 'first', 'last']);
        }
        return this;
    }
};