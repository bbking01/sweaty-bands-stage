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

var AWReportForm = Class.create();
AWReportForm = {
    initialize:function (config) {
        this.config = config;
        Event.observe(window, 'load', function () {
            this.__checkPeriodType();
            this.__checkReportType();
            this._addEventObserves();
            if (config.formSpecified == true && this.isCreateReportAvailable()) {
                this.createReport();
            } else {
                this.hideReportFieldset();
            }
        }.bind(this));
    },

    periodTypeChanged:function (event) {
        this.__checkPeriodType();
    },

    reportTypeChanged:function (event) {
        this.__checkReportType();
    },

    createReport:function (event) {
        if (!this.isCreateReportAvailable()) {
            alert(this.config.msg.campaignNotSelected);
            return;
        }
        this.__disableCreateReportButton();
        this.showReportFieldset();
        this.__showProgressImages();
        this.__sendRequest();
    },

    hideReportFieldset:function () {
        $$('.report.fieldset')[0].setStyle({'display':'none'});
    },

    showReportFieldset:function () {
        $$('.report.fieldset')[0].setStyle({'display':'block'});
    },

    isCreateReportAvailable:function () {
        if ($('campaigns').options.length == 0) {//is campaign not found
            return false;
        }
        if (typeof($('campaigns').getValue()) == "null" || $('campaigns').getValue().length == 0) {//is campaign not selected
            return false;
        }
        return true;
    },

    __disableCreateReportButton:function () {
        $('create-report-btn').addClassName('disabled')
    },

    __enableCreateReportButton:function () {
        $('create-report-btn').removeClassName('disabled')
    },

    __showProgressImages:function () {
        var overlay = document.createElement('div');
        overlay.addClassName('aw-overlay-popup');
        overlay.setAttribute('id', 'aw-overlay');

        var messageBlock = document.createElement('div');
        messageBlock.setAttribute('id', 'waiting-message');
        var text = document.createTextNode(this.config.msg.reportCreating);
        messageBlock.appendChild(text);

        var containerBlock = $$('.report.fieldset')[0];
        containerBlock.appendChild(messageBlock);
        containerBlock.appendChild(overlay);

        var __left = containerBlock.getWidth() / 2 - messageBlock.getWidth() / 2 + 'px';
        var __top = (containerBlock.getHeight() / 2 - messageBlock.getHeight() / 2) + 'px';
        messageBlock.setStyle({'left':__left, 'top':__top});
    },

    __hideProgressImages:function () {
        if (typeof($('aw-overlay')) != 'undefined') {
            $('aw-overlay').remove();
        }
        if (typeof($('waiting-message')) != 'undefined') {
            $('waiting-message').remove();
        }
    },

    __sendRequest:function () {
        var form = $('create-report-form');
        form.request({
            method:'post',
            onComplete:function (transport) {
                try {
                    eval("var response = " + transport.responseText);
                }
                catch (e) {
                    if ('console' in window) {
                        console.log(e.message);
                    }
                    this.__hideProgressImages();
                    this.__enableCreateReportButton();
                    return;
                }
                if (response.error == 0) {
                    $('report-container').innerHTML = response.html;
                    this.__evalScripts(response.html);
                    $$('.report.fieldset')[0].setStyle({'display':'block'});
                    if ($$('#report-container .data-table').first()) {
                        decorateTable($$('#report-container .data-table').first().identify());
                    }
                } else {
                    $('report-container').innerHTML = '';
                    var messageDiv = document.createElement('div');
                    messageDiv.addClassName('error-messages');
                    var html = '';
                    response.messages.each(function (mess) {
                        html += mess + '<br />';
                    });
                    messageDiv.innerHTML = html;
                    $('report-container').appendChild(messageDiv);
                    $$('.report.fieldset')[0].setStyle({'display':'block'});
                }
                this.__hideProgressImages();
                this.__enableCreateReportButton();
            }.bind(this)
        });
    },

    //eval js scripts
    __evalScripts:function (string) {
        var __scripts = string.extractScripts();
        __scripts.each(function (script) {
            try {
                eval(script.replace(/var /gi, ""));
            }
            catch (e) {
                if ('console' in window) {
                    console.log(e.name);
                }
            }
        });
    },

    __detalizationHide:function (name, hide) {
        if ($$("#detalization option[value='" + name + "']")) {
            var element = $$("#detalization option[value='" + name + "']").first();
            if (hide) {
                if (Prototype.Browser.IE) {
                    element.writeAttribute('disabled', 'disabled');
                } else {
                    element.hide();
                }
            } else {
                if (Prototype.Browser.IE) {
                    element.writeAttribute('disabled', null);
                } else {
                    element.show();
                }
            }
        }
    },

    __checkDetalization:function (name) {
        if ($('detalization').getValue() == name) return true;
        return false;
    },

    __checkPeriodType:function () {
        if ($('date-period').getValue() == 'custom_period') {
            $('date-periodicity-container').setStyle({'display':'block'});
        } else {
            $('date-periodicity-container').setStyle({'display':'none'});
        }
        switch ($('date-period').getValue()) {
            case 'last_seven_days' ://Year
                if (this.__checkDetalization('year')) {
                    $('detalization').setValue('day');
                }
                this.__detalizationHide('year', true);
                this.__detalizationHide('month', false);
                break;
            case 'today' :
            case 'yesterday' :
            case 'this_month' :
                //Month & Year
                if (this.__checkDetalization('year') || this.__checkDetalization('month')) {
                    $('detalization').setValue('day');
                }
                this.__detalizationHide('year', true);
                this.__detalizationHide('month', true);
                break;
            default:
                //full
                this.__detalizationHide('year', false);
                this.__detalizationHide('month', false);
                this.__detalizationHide('day', false);
        }
    },

    __checkReportType:function () {
        if ($('report-type').getValue() == 'sales') {
            $('detalization-container').setStyle({'display':'block'});
        } else {
            $('detalization-container').setStyle({'display':'none'});
        }
    },

    _addEventObserves:function () {
        $('date-period').observe('change', this.periodTypeChanged.bind(this));
        $('report-type').observe('change', this.reportTypeChanged.bind(this));
        $('create-report-btn').observe('click', this.createReport.bind(this));
    }
};
