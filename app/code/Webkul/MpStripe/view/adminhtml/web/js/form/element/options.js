define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        initialize: function () {
            // this._super();
            // var businessProfile = uiRegistry.filter('index = business_type');
            var value = this._super().initialValue; 
            if (value) {
                var individual = uiRegistry.filter('visibleValue = individual');
                if (individual.length) {
                    for (var i = 0; i< individual.length; i++) {
                        if (individual[i].visibleValue == value) {
                            individual[i].show();
                            if (individual[i].index == "individual_id_number" && individual[i].value() == '') {
                                individual[i].hide();    
                            }
                        } else {
                            individual[i].hide();
                        }
                    }
                }            
    
                var company = uiRegistry.filter('visibleValue = company');
                if (company.length) {
                    for (var i = 0; i< company.length; i++) {
                        if (company[i].visibleValue == value) {
                            company[i].show();
                        } else {
                            company[i].hide();
                        }
                    }
                }
            }
            return this;

        },      

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var individual = uiRegistry.filter('visibleValue = individual');
            if (individual.length) {
                for (var i = 0; i< individual.length; i++) {
                    if (individual[i].visibleValue == value) {
                        individual[i].show();
                    } else {
                        individual[i].hide();
                    }
                }
            }            

            var company = uiRegistry.filter('visibleValue = company');
            if (company.length) {
                for (var i = 0; i< company.length; i++) {
                    if (company[i].visibleValue == value) {
                        company[i].show();
                    } else {
                        company[i].hide();
                    }
                }
            }
            return this._super();
        },
    });
});