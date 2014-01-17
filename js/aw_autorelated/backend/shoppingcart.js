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
 * @package    AW_Autorelated
 * @copyright  Copyright (c) 2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var CAWAutorelatedShoppingCartBlockForm = Class.create({
    initialize:function (name) {
        window[name] = this;

        this.selectors = {
            orderSelect:'order',
            orderAttributeSelect:'order_attribute',
            orderSortDirectionSelect:'order_direction'
        };

        document.observe('dom:loaded', this.init.bind(this));
    },

    init:function () {
        if ((this._orderSelect = $(this.selectors.orderSelect))) {
            this._orderSelect.observe('change', this.checkOrderSelect.bind(this));
        }
        this.checkOrderSelect();
        var removeButton = $$('#order_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
        if (typeof removeButton != 'undefined')
            removeButton.hide();
        removeButton = $$('#related_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
        if (typeof removeButton != 'undefined')
            removeButton.hide();
    },

    checkOrderSelect:function () {
        switch (parseInt(this._orderSelect.value)) {
            case 0:
            case 1:
                $(this.selectors.orderAttributeSelect).up().up().hide();
                $(this.selectors.orderSortDirectionSelect).up().up().hide();
                break;
            case 2:
                $(this.selectors.orderAttributeSelect).up().up().show();
                $(this.selectors.orderSortDirectionSelect).up().up().show();
                break;
        }
    }
});

new CAWAutorelatedShoppingCartBlockForm('awautorelatedproductblockform');
