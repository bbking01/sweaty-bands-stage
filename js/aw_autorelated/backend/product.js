document.observe('dom:loaded', function () {
    var removeButton = $$('#viewed_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
    if (typeof removeButton != 'undefined')
        removeButton.hide();

    removeButton = $$('#related_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
    if (typeof removeButton != 'undefined')
        removeButton.hide();
});

var CAWAutorelatedProductBlockForm = Class.create({
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

new CAWAutorelatedProductBlockForm('awautorelatedproductblockform');
