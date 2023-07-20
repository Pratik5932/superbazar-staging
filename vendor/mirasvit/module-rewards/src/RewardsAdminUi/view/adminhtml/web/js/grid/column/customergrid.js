define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, registry, el) {
    'use strict';

    return el.extend({
        /**
         * Callback method to handle changes of selected items.
         *
         * @param {Array} selected - An array of currently selected items.
         */
        onSelectedChange: function (selected) {
            var transactionForm = registry.get('rewards_transaction_form.rewards_transaction_form');
            transactionForm.source.set('data.in_transaction_user', selected.toArray());

            this._super();
        },
    });
});
