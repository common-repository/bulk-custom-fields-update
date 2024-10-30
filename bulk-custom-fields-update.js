jQuery(document).ready(function ($) {

    //Get current meta key
    var customfield = jQuery('#customfield').val();
    jQuery('#customfield').change(function () {
        customfield = jQuery('#customfield').val();

        //Send meta key to PHP callback
        data = {
            action: 'zbcfu_zedna_get_results',
            fieldvariable: customfield
        };

        //Get data from PHP callback and process them
        $.post(ajaxurl, data, function (response) {
            var fieldsarray = $.parseJSON(response);
            
            jQuery(function () {
                jQuery("#customvalue").autocomplete({
                    source: fieldsarray
                });
            });
        });
        jQuery("#previewfields span").html('for '+customfield);
        return false;
    });

    jQuery('#previewfields').click(function () {
        customfield2 = jQuery('#customfield').val();

        //Send meta key to PHP callback
        data2 = {
            action: 'zbcfu_zedna_get_results2',
            fieldvariable2: customfield2
        };

        //Get data from PHP callback and process them
        $.post(ajaxurl, data2, function (response2) {
            var fieldsarray2 = $.parseJSON(response2);
            var fieldsarray2string = fieldsarray2.join("<br>");
            jQuery(function () {
                jQuery(".previewfields").html(fieldsarray2string);
            });
        });
        return false;
    });
});