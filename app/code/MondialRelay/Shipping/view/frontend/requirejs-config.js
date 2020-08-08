var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'MondialRelay_Shipping/js/view/billing-address': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'MondialRelay_Shipping/js/view/shipping-information': true
            },
            'Magento_Checkout/js/view/shipping': {
                'MondialRelay_Shipping/js/view/shipping': true
            }
        }
    }
};