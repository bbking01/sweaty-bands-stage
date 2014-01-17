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

var CWithdrawal = Class.create({
    initialize:function () {
    },

    init:function (gridJsObject, params) {
        this._gridJsObject = gridJsObject;
        this._params = params;
        this._messages = params.messages;

        gridJsObject.oldRowClickCallback = gridJsObject.rowClickCallback;
        gridJsObject.rowClickCallback = function (data, click) {
            var row = Event.findElement(click, 'TR');
            var td = Event.findElement(click, 'TD');
            /**
             * if td contains <a> or <input> or some other child elements then
             * calling old grid row click callback for event processing
             */
            if (row.title && td.childElementCount == 0) {
                this.rowClick(row);
                return;
            } else {
                gridJsObject.oldRowClickCallback(data, click);
            }
        }.bind(this);

        Event.observe($('back_to_withdrawal_list'), 'click', function () {
            this.back();
        }.bind(this));

        Event.observe($('withdrawal_save'), 'click', function () {
            this.save();
        }.bind(this));

        toggleVis('withdrawal_form');
        toggleVis('withdrawal-head');
    },

    editActionClick:function (element) {
        if (element.parentElement != undefined && element.parentElement.parentElement != undefined) {
            var tr = element.parentElement.parentElement;
            if (tr.tagName == 'TR') {
                this.rowClick(tr);
            }
        }
    },

    rowClick:function (row) {
        this._hideMessageBlock();
        this._removeMessages();
        this._transmitDataFromGrid(row);
        this._showWithdrawalRequestForm();
    },

    back:function () {
        this._hideWithdrawalRequestForm();
    },

    save:function () {
        this._hideMessageBlock();
        this._removeMessages();
        this._saveWithdrawalRequest();
    },

    _saveWithdrawalRequest:function () {
        var withdrawalRequestId = $('withdrawal_form').select('#withdrawal_request_id')[0].value;
        var affiliate_id = typeof(awAffiliateProfitConfig) != 'undefined' ? awAffiliateProfitConfig.affiliate_id : false;
        var _requestUrl = this._params.requestUrl;
        _requestUrl += 'form_key/' + FORM_KEY + '/id/' + withdrawalRequestId + '/affiliate_id/' + affiliate_id + '/';
        new Ajax.Request(_requestUrl, {
            method:'post',
            postBody:encodeURI('notice=' + $('withdrawal_details').value + '&status=' + $('withdrawal_status').getValue()),
            onComplete:function (transport) {
                try {
                    eval('var response = ' + transport.responseText);
                    if (response.error == 0) {
                        this._addMessage('success', response.message);
                        this._hideWithdrawalRequestForm();
                        this._gridJsObject.reload();
                        if ((typeof(response.current) != 'undefined') && (typeof(response.withdrawn) != 'undefined')) {
                            $('balance_current_balance').innerHTML = response.current;
                            $('balance_total_withdrawn').innerHTML = response.withdrawn;
                            $('general_total_affiliated').innerHTML=response.total;
                        }
                    }
                    else {
                        this._addMessage('error', response.message);
                    }
                }
                catch (e) {
                    this._addMessage('error', this._messages.incorrect_response);
                }
                this._showMessageBlock();
                return;
            }.bind(this)
        });
    },

    _addMessage:function (type, msg) {
        var block = $$('#withdrawal-messages .messages')[0];
        if (!block) {
            return;
        }
        var ul = document.createElement('ul');
        if (typeof(msg) == 'object') {
            msg.each(function (el) {
                var li = document.createElement('li');
                var span = document.createElement('span');
                span.innerHTML = el;
                li.appendChild(span);
                ul.appendChild(li);
            });
        } else if (typeof(msg) == 'string') {
            var li = document.createElement('li');
            var span = document.createElement('span');
            span.innerHTML = msg;
            li.appendChild(span);
            ul.appendChild(li);
        } else {
            return;
        }
        var li = document.createElement('li');
        if (type == 'error') {
            li.addClassName('error-msg');
        } else if (type == 'success') {
            li.addClassName('success-msg');
        } else {
            return;
        }
        li.appendChild(ul);
        block.appendChild(li);
    },

    _removeMessages:function () {
        var block = $$('#withdrawal-messages .messages')[0];
        if (!block) {
            return;
        }
        block.innerHTML = '';
    },

    _showMessageBlock:function () {
        var block = $$('#withdrawal-messages .messages')[0];
        if (!block) {
            return;
        }
        block.show();
    },

    _hideMessageBlock:function () {
        var block = $$('#withdrawal-messages .messages')[0];
        if (!block) {
            return;
        }
        block.hide();
    },

    _hideWithdrawalRequestForm:function () {
        toggleVis('withdrawal_form');
        toggleVis('withdrawal-head');
        $(this._params.gridContainerId).show();
    },

    _showWithdrawalRequestForm:function () {
        toggleVis('withdrawal_form');
        toggleVis('withdrawal-head');
        $(this._params.gridContainerId).hide();
    },

    _transmitDataFromGrid:function (element) {
        var html = element.getAttribute('title');
        $('withdrawal_form').select('#withdrawal_request_id')[0].value = html.strip();

        html = element.select('.withdr-created-at')[0].innerHTML;
        $('withdrawal_form').select('#created_at')[0].innerHTML = html.strip();

        html = element.select('.withdr-amount-requested')[0].innerHTML;
        $('withdrawal_form').select('#amount')[0].innerHTML = html.strip();

        html = element.select('.withdr-details')[0].innerHTML;
        if (html.strip() == '&nbsp;') {
            html = this._messages.details_not_defined;
        }
        $('withdrawal_form').select('#details')[0].innerHTML = html.strip();

        html = element.select('.withdr-status')[0].innerHTML;
        var selectElement = $('withdrawal_status');
        selectElement.select('option').each(function (option) {
            if (option.innerHTML.strip() == html.strip()) {
                selectElement.value = option.value;
                return;
            }
        });

        html = element.select('.withdr-withdrawal-details')[0].innerHTML;
        if (html.strip() == '&nbsp;') {
            html = '';
        }
        $('withdrawal_form').select('#withdrawal_details')[0].value = html.strip();
    }
});

var Withdrawal = new CWithdrawal();
