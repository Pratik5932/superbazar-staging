require([
    'jquery',
    'mage/translate',
    'jquery/validate'],
    function($){
        $.validator.addMethod(
            'validate-hexadecimal-color-length', function (v) {
                return $.mage.isEmptyNoTrim(v) || /^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/.test(v);
            }, $.mage.__('Invalid color Hex code.'));
    }
);