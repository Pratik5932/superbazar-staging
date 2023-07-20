define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (
    ko,
    $,
    Component
) {
    'use strict';

    return Component.extend({
        
        initialize: function () {
            this._super().initChildren();
            return this;
        },
    
        initChildren: function () {
            this.messages = ko.observable('');
            this.createMessagesComponent();
        
            return this;
        },
    
        createMessagesComponent: function () {
            var self = this;
            setTimeout(function() {
                $.ajax({
                    url: self.url,
                    type: 'GET',
                    dataType: 'JSON',
                    complete: function (data) {
                        if (typeof data.responseText != 'undefined' && data.responseJSON.text) {
                            self.messages(data.responseJSON.text);
                        }
                    }
                });
            }, 100);
        
            return this;
        }
    });
});
