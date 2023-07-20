define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                filterBySimpleAction: '${ $.parentName }.simple_action:value'
            }
        },

        filterBySimpleAction: function (simpleAction) {
            if(simpleAction == 'by_percent'
                || simpleAction == 'by_fixed'
                || simpleAction == 'cart_fixed'
                || simpleAction == 'buy_x_get_y'){

                this.filter(true, "available_for_non_ap_action");
            }else{
                this.resetFilter();
            }

            return this;
        },

        resetFilter: function () {
            this.setOptions(this.initialOptions);
        }
    });
});

