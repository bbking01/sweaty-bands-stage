var awAffiliateRateSettings = {
    blockIdn:['#rate_profit_rate', '#rate_tier_price_container', '#rate_profit_rate_cur', '#rate_tier_price_cur_container'],
    rateType:['fixed', 'tier', 'fixedcur', 'tiercur'],
    callbackAfterHide:['deleteRequiredClassFromFixed', 'hideAllItemsFromTier', 'deleteRequiredClassFromFixedCur', 'hideAllItemsFromTierCur'],
    callbackBeforeShow:['addRequiredClassToFixed', 'showAllItemsFromTier', 'addRequiredClassToFixedCur', 'showAllItemsFromTierCur']
};
Event.observe(window, 'load', function () {
    awAffiliateAfterLoad();
});

function awAffiliateAfterLoad() {
    awAffiliateRateTypeUpdated();
    $('rate_type').observe('change', awAffiliateRateTypeUpdated);
}

function awAffiliateRateTypeUpdated() {
    $$('#rate_base_fieldset tr').each(function (item) {
        var i = 0;
        awAffiliateRateSettings.blockIdn.each(function (idn) {
            if (item.select(idn).length > 0) {
                if ($('rate_type').value == awAffiliateRateSettings.rateType[i]) {
                    var functionName = awAffiliateRateSettings.callbackBeforeShow[i];
                    if (functionName != null) {
                        window[functionName]();
                    }
                    item.show();
                }
                else {
                    item.hide();
                    var functionName = awAffiliateRateSettings.callbackAfterHide[i];
                    if (functionName != null) {
                        window[functionName]();
                    }
                }
            }
            i++;
        });
    });
    //hide/show calculation type field
    $('rate_rate_calculation_type').parentNode.parentNode.hide();
    //$('rate_rate_cur_calculation_type').parentNode.parentNode.hide();
    switch ($('rate_type').getValue()) {
        case 'tier':
        case 'tiercur':
            $('rate_rate_calculation_type').parentNode.parentNode.show();
            break;
    }
}

function hideAllItemsFromTier() {
    tierPriceControl.hideAllItems();
}

function hideAllItemsFromTierCur() {
    tierPriceCurControl.hideAllItems();
}

function showAllItemsFromTier() {
    tierPriceControl.showAllItems();
}

function showAllItemsFromTierCur() {
    tierPriceCurControl.showAllItems();
}

function deleteRequiredClassFromFixed() {
    $$(awAffiliateRateSettings.blockIdn)[0].removeClassName('required-entry');
    $$(awAffiliateRateSettings.blockIdn)[0].removeClassName('validate-greater-than-zero');
    $$(awAffiliateRateSettings.blockIdn)[0].removeClassName('validate-number');
    $$(awAffiliateRateSettings.blockIdn)[0].removeClassName('validate-percents');
}

function deleteRequiredClassFromFixedCur() {
    $$(awAffiliateRateSettings.blockIdn)[2].removeClassName('required-entry');
    $$(awAffiliateRateSettings.blockIdn)[2].removeClassName('validate-greater-than-zero');
    $$(awAffiliateRateSettings.blockIdn)[2].removeClassName('validate-number');
}

function addRequiredClassToFixed() {
    $$(awAffiliateRateSettings.blockIdn)[0].addClassName('required-entry');
    $$(awAffiliateRateSettings.blockIdn)[0].addClassName('validate-greater-than-zero');
    $$(awAffiliateRateSettings.blockIdn)[0].addClassName('validate-number');
    $$(awAffiliateRateSettings.blockIdn)[0].addClassName('validate-percents');
}

function addRequiredClassToFixedCur() {
    $$(awAffiliateRateSettings.blockIdn)[2].addClassName('required-entry');
    $$(awAffiliateRateSettings.blockIdn)[2].addClassName('validate-greater-than-zero');
    $$(awAffiliateRateSettings.blockIdn)[2].addClassName('validate-number');
}