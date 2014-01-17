/**
 * Created with JetBrains PhpStorm.
 * User: pp
 */
var ugcMultiple = function (config) {
    var tabsContainerId = config.tabs_container_id || 'ugc-form-container';
    var addTitle = config.add_title || 'Add Recipient';
    var recipientLabel = config.recipient_label || 'Recipient #{recipient}';
    var missingContainerMsg = config.missing_container_msg || 'Recipient info container not found';
    var recipientInfoContainer = $(tabsContainerId);
    if (!recipientInfoContainer) {
        console.warn(missingContainerMsg);
        return;
    }
    recipientInfoContainer.addClassName('tabbed');
    var tabIdBase = "tab-recipient-";
    var tabTplTxt = '<div class="tab-content" id="';
    tabTplTxt += tabIdBase + '#{recipient}"><h5 class="tab-title"><a class="tab-link" href="#';
    tabTplTxt += tabIdBase + '#{recipient}">' + recipientLabel + '</a></h5>';
    var tabTpl = new Template(tabTplTxt); // this will be fall back link
    var tabTitleTpl = new Template(' #{recipient}');
    var initialContent = recipientInfoContainer.innerHTML;
    var recipients = [1];// we start with one recipient

//    var messageArea = $('recipient_message_container');
//    if (messageArea) {
//        initialContent += messageArea.innerHTML;
//    }

    var firstTab = tabTpl.evaluate({recipient:1}) + initialContent + '</div>';
    recipientInfoContainer.update(firstTab);
    recipientInfoContainer.insert({top:'<input type="hidden" name="multiple-recipients" id="multiple-recipients" value="1">'});
    firstTab = $(tabIdBase + '1');

    updateTabElements(firstTab, 1);

    if (typeof(MT) == 'undefined') {
        var MyTabs = new mt(tabsContainerId, 'div.tab-content');

//        MyTabs.removeTabTitles('h5.tab-title');
        MyTabs.addTab(tabIdBase + '1', tabTitleTpl.evaluate({recipient:1}));
        MyTabs.makeActive(tabIdBase + '1');
        recipientInfoContainer.insert({top:'<a href="#" id="add-recipient" title="' + addTitle + '">[ + ]</a><br/>'});
        $('add-recipient').observe('click', function (e) {
            Event.stop(e);
            insertTab(recipientInfoContainer, initialContent);
        });
        window.mt.prototype.removeTab = function (tab_id) {
            var tabBarEntryId = 'mt-' + tab_id;
            var tabBarElmnt = $(tabBarEntryId);
            var tab = $(tab_id);
            if (tab) {
                Element.remove(tab)
            }

            if (tabBarElmnt) {
                tabBarElmnt.stopObserving();
                Element.remove(tabBarElmnt);
            }
        }
    }

    function updateRecipients() {
        var recipientsEl = $('multiple-recipients');
        if (recipientsEl) {
            recipientsEl.setValue(recipients.join(','));
        }
    }

    function updateQty() {
        var qtyBox = $('qty');
        if (qtyBox) {
            if (parseInt(qtyBox.getValue()) != recipients.length) {
                qtyBox.setValue(recipients.length);
            }
        }
    }

    function insertTab(container, content) {
        var current = recipients.last() + 1;
        recipients.push(current);
        var tabId = tabIdBase + current;
        var tab = tabTpl.evaluate({recipient:current}) + content + '</div>';
        container.insert(tab);
        tab = $(tabId).hide();
        updateTabElements(tab, current);
        updateRecipients();
        updateQty();
        MyTabs.addTab(tabId, tabTitleTpl.evaluate({recipient:current}));
        MyTabs.tabs.push(tab);
        addCloseHandle(current);
        return tab;
    }

    function addCloseHandle(tabIdx) {
        var tabId = tabIdBase + tabIdx;
        var tabHandleId = 'mt-' + tabId;
        $(tabHandleId).insert({top:'<a href="#" class="gift-tab-close-handle" id="close-' + tabId + '">[-]</a> '});
        $('close-' + tabId).observe('click', function (e) {
            Event.stop(e);
            removeTab(tabIdx);
        });
    }


    function removeTab(value) {
        if (recipients.length == 1) {
            return; // always leave one tab active
        }
        var tabId = tabIdBase + value;
        var idx = recipients.indexOf(value);
        if (idx != -1) {
            recipients.splice(idx, 1);
        }
        updateRecipients();
        updateQty();
        var tab = $(tabId);
        if (tab && tab.visible()) {// prevent having no tab displayed if removing current active tab
            var tmp = idx;
            var next = false;
            while (tmp >= 0) {
                if (next = recipients[tmp]) {
                    break;
                }
                tmp--;
            }
            if (!next) {
                next = recipients.first();
            }
            MyTabs.makeActive(tabIdBase + next);
        }
        MyTabs.removeTab(tabId);
    }

    function updateTabElements(tab, incrementId) {
        var elements = tab.select('input,textarea');
        elements.each(function (el) {
            var id = el.identify();
            var newId = id + '-' + incrementId;
            var name = el.readAttribute('name');
            var newName = name + '-' + incrementId;

            var label = el.previous('label');
            if (label) {
                label.writeAttribute('for', newId);
            } else if(label = el.up('label')){

            }

            el.writeAttribute('name', newName);
            el.id = newId;
        });

        var formParts = tab.select('.ugc-form-data-part');

        formParts.each(function(el){
            var id = el.identify();
            var newId = id + '-' + incrementId;
            el.id = newId;
            if(el.hasClassName('ugc-address-form')){
                initiateDeliveryTypes(newId, '#ugc-select-delivery-type-' + incrementId + ' .delivery_type');
            }
        });
    }

    function matchTabsToQty(value) {
//        if (!recipientInfoContainer.visible()) {
//            return;
//        }
        while (recipients.length < value) {
            insertTab(recipientInfoContainer, initialContent);
        }

        while (recipients.length > value) {
            removeTab(recipients.last());
            if (recipients.length == 1) {
                break;
            }
        }
    }

    var qtyBox = $('qty');
    if (qtyBox) {
        qtyBox.observe('blur', function (e) {
            var value = parseInt($F(this));
            matchTabsToQty(value);
        });
    }
}