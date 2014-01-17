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

var AWWithdrawalRequest = Class.create();
AWWithdrawalRequest = {
    initialize:function (config) {
        this.config = config;
        this.container = $(config.container);
        this.form = $(config.form);
        this.varienForm = new VarienForm(config.form);
        if (typeof($(this.config.cancelEl)) != 'undefined') {
            $(config.cancelEl).observe('click', AWWithdrawalRequest.hidePopup.bind(AWWithdrawalRequest));
        }
        if (typeof($(this.config.submitEl)) != 'undefined') {
            $(config.submitEl).observe('click', AWWithdrawalRequest.submit.bind(AWWithdrawalRequest));
        }
    },

    showPopup:function () {
        this.container.setStyle({'display':'block'});
        var __left = document.viewport.getWidth() / 2 - this.form.getWidth() / 2 + 'px';
        var __top = (document.viewport.getHeight() / 2 - this.form.getHeight() / 2) + 'px';
        this.form.setStyle({'left':__left, 'top':__top});
    },

    hidePopup:function () {
        this.container.setStyle({'display':'none'});
        this.form.setStyle({'left':'-10000px'});
    },

    submit:function () {
        if (this.varienForm.validator.validate()) {
            this.varienForm.submit();
            this.showWaitingPopup();
        }
    },

    showWaitingPopup:function () {
        this.form.select('.fieldset')[0].hide();
        this.form.select('.buttons-set')[0].hide();
        $(this.config.waitingBlock).setStyle({'display':'block'});
    }
};
