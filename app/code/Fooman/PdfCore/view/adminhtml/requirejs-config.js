//spectrum is bundled with Magento 2.3.0+
//use the bundled version and fallback to the PdfCore one when it's not available
var config = {
    paths: {
        'spectrum': [
            'jquery/spectrum/spectrum',
            'Fooman_PdfCore/js/spectrum'
        ]
    },
    map: {
        '*': {
            foomanPdfCoreColourPicker: 'Fooman_PdfCore/js/pick-colour'
        }
    }
};
