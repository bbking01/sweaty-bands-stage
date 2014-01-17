/**
 * Created with JetBrains PhpStorm.
 * User: pp
 * Date: 27.08.12
 * Time: 18:07
 * To change this template use File | Settings | File Templates.
 */

function toggleCustomMessage(show)
{
    var msg = $('recipient_message_container');
    if (show) {
        msg && msg.show() && msg.down('textarea').enable();
    } else {
        msg && msg.hide();
        var msg = $('recipient_message');
        msg && (msg.value = '') && msg.disable();
        processMessage(msg);
    }
}

var lastValidMessage = '';

function toggleGiftcertRecipient(type)
{
    var myself = $('recipient_myself'), someone = $('recipient_info'), print = $('toself_printed');
    var name = $('recipient_name'), email = $('recipient_email'), address = $('recipient_address');
    var msgBox = $('recipient_message_container'), msg = $('recipient_message');

    if (type=='myself') {
//        $$('#recipient_info input, #recipient_info textarea').each(function(el){ el.value = '' });
        processMessage('recipient_message');

        if(myself){
            myself.show();
            myself.select('input, textarea').invoke('enable');
        }

        if(someone){
            someone.hide();
            someone.select('input, textarea').invoke('disable');
        }

        name && name.removeClassName('required-entry');
        email && email.removeClassName('required-entry');
        address && address.removeClassName('required-entry');

        if (print) {
            print.checked = msg.value ? true : false;
            if (print.checked) {
                msgBox && msgBox.show() && msgBox.down('textarea').enable();
            } else {
                msgBox && msgBox.hide();
            }
        } else {
            msgBox && msgBox.hide();
        }
    } else {
        if (print) {
            print.checked = false;
        }

        if(myself){
            myself.hide();
            myself.select('input, textarea').invoke('disable');
        }


        if(someone){
            someone.show();
            someone.select('input, textarea').invoke('enable');
        }
//        myself && myself.hide();
        if(someone && someone.show() && !someone.hasClassName('tabbed')){
            // if not using tabs and have someone option enabled, show common message box, else hide it
            msgBox && msgBox.show() && msgBox.down('textarea').enable();
        } else {
            msgBox && msgBox.hide() && msgBox.down('textarea').disable();
        }

        name && name.addClassName('required-entry');
        if (!(email && address)) {
            email && email.addClassName('required-entry');
            address && address.addClassName('required-entry');
        }
    }
}

function initiateDeliveryTypes(container, typeClass) {
    var typeClass = typeClass || '.delivery_type';
    var del_types = $$(typeClass); // radio buttons that toggle virtual/physical delivery options
    if(! del_types.length ){ // if no delivery types, then only one must be allowed (hopefully) but no need to stay hidden for sure.
        return;
    }

    var containerId = container || 'ugc-address-form';
    var formContainer = $(containerId); // container that holds all those options
    del_types.each(function(el){
        enableDisable(el, formContainer)
        el.observe('click', function(e){
            enableDisable(this,formContainer);
        });
        el.observe('change', function(e){
            enableDisable(this,formContainer);
        });
    });
}

function enableDisable(radio, formContainer) {
    var radio = $(radio);
    if (!radio) { // if radio is not valid element, bail out
        return;
    }
    if(radio.checked) { // if radio is checked, get its value and enable those elements that have such class parent
        var enabled = '.' + radio.getValue();
        var enabledInputs = enabled + ' input,' + enabled + ' textarea,' + enabled + ' select'; // form elements are child elements to target classes
        if (formContainer) {
            formContainer.select('input, textarea, select').invoke('disable');
            formContainer.select('.ugc-form-item').invoke('hide');// hide all and disable them
            formContainer.select(enabled).invoke('show');
            formContainer.select(enabledInputs).invoke('enable'); // then enable selected ones
            formContainer.show();
        }
    }
}

function initPdfPreview(buttonId, url) {
    var button = $(buttonId);
    if(button){
        button.observe('click', function(e){
            Event.stop(e);
            var form = $(this).up('form');
            var data = form.serialize();
            url += '?' + data;
            window.open(url);
        });

    }
}
/*

Event.observe(window, 'load', function(e){
    var qty_box = $('qty');
    var rec_types = $$('.recipient_type');
    rec_types.each(function(el){
        var radio = $(el);
        if(radio.readAttribute('checked')) {
            var target = radio.identify().sub('recipient_type_', '');
            toggleGiftcertRecipient(target);
        }
    });
});

*/
