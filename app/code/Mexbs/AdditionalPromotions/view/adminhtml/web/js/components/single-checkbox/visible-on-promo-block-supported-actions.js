define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Mexbs_AdditionalPromotions/js/model/ap-simple-actions'
], function (Element, ApSimpleActions) {
    'use strict';
    return Element.extend({
        isInSupportedActionArray: function(action){
            return (typeof ApSimpleActions.getConfig() != 'undefined')
                && (typeof ApSimpleActions.getConfig().promoBlockSupportedActions != 'undefined')
                && (ApSimpleActions.getConfig().promoBlockSupportedActions.indexOf(action) > -1);
        },
        setSimpleAction: function (value){
            this.simpleAction = value;
            this.visible(this.isInSupportedActionArray(this.simpleAction));
        }
    });
});
