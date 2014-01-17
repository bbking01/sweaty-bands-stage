
###
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
### 

AWAjaxCatalog = Class. create();
AWAjaxCatalog.prototype =
    ###
    Class construcructor
    ###
    initialize: (params) ->

        # Get initial params
        for key,value of params
            this[key] = params[key]
        

        if this.action_type is "button"
            document.observe "dom:loaded", (event) =>                                    
                ###
                Button click observe
                ###
                if $(this.button_id)
                    $(this.button_id).observe "click", (event) =>
                        this.loadNext();
                        return
                return    
        else 
            this.disabled_forever = false
            this.start_lock = true

            document.observe "dom:loaded", (event) =>      
                if this.needLoadNextBefore()
                    this.loadNext()
                return

            Event.observe window, "scroll", (event) =>
                ###
                User scroll document
                ###      
                if this.needLoadNextAfter()
                    this.loadNext()
                this.start_lock = false        
                return

        return this
    ###
    Need to load next page after scroll
    ###
    needLoadNextAfter: () ->
        if document.viewport
            top = document.viewport.getScrollOffsets().top
            height = document.viewport.getHeight();
            # Cross browser document height
            docHeight = Math.max(
                Math.max(document.body.scrollHeight, document.documentElement.scrollHeight),
                Math.max(document.body.offsetHeight, document.documentElement.offsetHeight),
                Math.max(document.body.clientHeight, document.documentElement.clientHeight))        

            return (((docHeight - top) <= (3 * height)) and not this.start_lock and not this.disabled_forever);
        return no    

    needLoadNextBefore: () ->
        result = no
        if document.viewport            
            $$('div.main').each( (el) =>
                screenHeight = document.viewport.getHeight()
                elementHeight = el.getHeight()
                if (screenHeight > elementHeight) 
                    result = yes                    
            )
        return result
    ###
    Load next products
    ###
    loadNext: () ->
        if this.isLoading()
            return

        this.showLoader yes       
        this.params['p'] = this.next_page           

        #      awajaxcatalog/base64.js        prototype.js
        #                ^                         ^
        params =  Base64.encode $H(this.params).toJSON()

        new Ajax.Request this.next_url.replace("{{page}}", params).replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')), {
            method: "get"
            onSuccess: (transport) =>
                try
                    response = eval('(' + transport.responseText + ')')
                catch exception
                    response = {}
                
                {success, content, next_page, error} = response
                if error
                    console.log? error
                if success 
                    if next_page > 0
                        this.next_page = next_page
                    else                         
                        this.showButton no

                    if content
                        this.appendContent this.evalInnerScripts(content)
                        this.useDecorator()    
                return        
            onComplete: (transport) => 
                this.showLoader no               

                if this.needLoadNextBefore() and (this.next_page > 0)
                    this.loadNext() 

                return
        }               
        return this

    ###
    Append new content
    ###
    appendContent: (content) ->
        if $(this.container_id)
            $(this.container_id).innerHTML += content
        return
    ###
    Show button
    ###
    showButton: (show) ->
        if this.action_type is "button"
            if show 
                this.getButtonContainer().removeClassName "hidden"
            else 
                this.getButtonContainer().addClassName "hidden"
        else 
            this.disabled_forever = true
                
        return

    ###
    Show loader
    ###
    showLoader: (show) ->
        if this.action_type is "button"
            if show
                this.getButton().addClassName "loading"
            else
                this.getButton().removeClassName "loading"
        else 
            if show
                this.getLoader().addClassName "loading"
            else 
                this.getLoader().removeClassName "loading"
        return
 
    ###
    Button
    ###
    getButton: () ->
        if $(this.button_id)
            $(this.button_id) 
 
    ###
    Loader
    ###
    getLoader: () ->
        if $(this.loader_id)
            $(this.loader_id) 

    ###
    Button with container
    ###
    getButtonContainer: () ->
        if $(this.button_container_id)
            $(this.button_container_id) 

    ###
    Is loading now
    ###
    isLoading: () ->
        if this.action_type is "button"
            this.getButton().hasClassName "loading"
        else 
            this.getLoader().hasClassName "loading"

    ###
    Eval Inner Scripts
    (required after ajax load content)
    ###
    evalInnerScripts: (content) ->
        content.evalScripts()
        return content

    ###
    Use List Decorator to redecorate list
    ### 
    useDecorator: () ->
        $$(this.decorate_clean).each (element)=>
            element.removeClassName "even"
            element.removeClassName "odd"
            element.removeClassName "last"
            element.removeClassName "first"
            return
        
        decorateGeneric $$(selection), ['odd','even','first','last'] for selection in this.decorate_decorate
        return this
