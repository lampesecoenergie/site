var config = {
    map: {
        '*': {
            gls: 'Acyba_GLS/js/gls'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {'Acyba_GLS/js/view/shipping': true},
            'Magento_Checkout/js/action/set-shipping-information': {'Acyba_GLS/js/action/set-shipping-information': true}
        }
    }
};