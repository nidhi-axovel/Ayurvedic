var config = {
    paths: {
        //'mcolorpicker': 'Infortis_Infortis/js/jquery/plugins/mcolorpicker/mcolorpicker.min'
        'mcolorpicker': 'Infortis_Infortis/js/jquery/mcolorpicker/mcolorpicker'
    },
    shim: {
        'mcolorpicker': {
            'deps': ['jquery'],
            'init': function(jQuery) {
                //jQuery.fn.mColorPicker.defaults.imageFolder = require.toUrl('') + 'Infortis_Infortis/js/jquery/plugins/mcolorpicker/images/';
                jQuery.fn.mColorPicker.defaults.imageFolder = require.toUrl('') + 'Infortis_Infortis/js/jquery/mcolorpicker/images/';
                jQuery.fn.mColorPicker.init.replace = false;
                jQuery.fn.mColorPicker.init.allowTransparency = true;
                jQuery.fn.mColorPicker.init.showLogo = false;
            }
        }
    }
};
