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

Event.observe(window, 'load', function () {
    var awAffiliateProfit = function () {
        return {
            add:function () {
                if (!awAffiliateProfitConfig.validator.validate()) {
                    awAffiliateProfit._removeMessages();
                    awAffiliateProfit._addMessage('error', awAffiliateProfitConfig.incorrectValidation);
                    awAffiliateProfit._showMessageBlock();
                    return false;
                }
                awAffiliateProfit._hideMessageBlock();
                awAffiliateProfit._removeMessages();
                awAffiliateProfit._addProfit();
                awAffiliateProfit._showMessageBlock();
                awAffiliateProfit._removeTabsTags();
            },
            _addProfit:function () {
                var _requestUrl = awAffiliateProfitConfig.requestUrl + 'form_key/' + FORM_KEY;
                var post = 'amount=' + $('profit_amount').value;
                /*post += '&details=' + $('profit_details').value;*/
                post += '&notice=' + $('profit_notice').value;
                post += '&campaign_id=' + $('profit_campaign').value;
                new Ajax.Request(_requestUrl, {
                    method:'post',
                    postBody:encodeURI(post),
                    onComplete:function (transport) {
                        try {
                            eval('var response = ' + transport.responseText);
                            if (response.error == 0) {
                                awAffiliateProfit._addMessage('success', response.message);
                                awAffiliateProfitsGridJsObject.reload();
                                if( (typeof(response.current) != 'undefined') &&(typeof(response.withdrawn) != 'undefined')) {
                                   $('balance_current_balance').innerHTML=response.current;
                                   $('balance_total_withdrawn').innerHTML=response.withdrawn;
                                   $('general_total_affiliated').innerHTML=response.total;
                                }

                            }
                            else {
                                awAffiliateProfit._addMessage('error', response.message);
                            }
                        }
                        catch (e) {
                            awAffiliateProfit._addMessage('error', awAffiliateProfitConfig.ajaxJsError);
                        }
                        return;
                    }
                });
            },
            _addMessage:function (type, msg) {
                var block = $$('#profit-messages .messages')[0];
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
                }
                else if (typeof(msg) == 'string') {
                    var li = document.createElement('li');
                    var span = document.createElement('span');
                    span.innerHTML = msg;
                    li.appendChild(span);
                    ul.appendChild(li);
                }
                else {
                    return;
                }
                var li = document.createElement('li');
                if (type == 'error') {
                    li.addClassName('error-msg');
                }
                else if (type == 'success') {
                    li.addClassName('success-msg');
                }
                else {
                    return;
                }
                li.appendChild(ul);
                block.appendChild(li);
            },

            _removeMessages:function () {
                var block = $$('#profit-messages .messages')[0];
                if (!block) {
                    return;
                }
                block.innerHTML = '';
            },

            _showMessageBlock:function () {
                var block = $$('#profit-messages .messages')[0];
                if (!block) {
                    return;
                }
                block.show();
            },

            _hideMessageBlock:function () {
                var block = $$('#profit-messages .messages')[0];
                if (!block) {
                    return;
                }
                block.hide();
            },

            _removeTabsTags:function () {
                awaffiliate_affiliate_tabsJsTabs.tabs.each(function (tab) {
                    tab.removeClassName('changed');
                    tab.removeClassName('error');
                });
            }
        };
    }();
    Event.observe($('profit_add'), 'click', function () {
        awAffiliateProfit.add();
    });
});
