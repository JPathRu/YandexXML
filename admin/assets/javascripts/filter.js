/**
 * Created by arkadiy on 03.02.16.
 */


var loadProducts = function () {
    var
        filterCategories,
        adminForm = jQuery('#adminForm'),
        excludeProducts = jQuery('#exclude_products'),
        includeProducts = jQuery('#include_products'),
        filterCalendarFrom = jQuery('#filter_calendar_from'),
        filterCalendarTo = jQuery('#filter_calendar_to'),
        filterText = jQuery('#filter_text'),
        productsDiv = jQuery('#products'),
        itemId = jQuery('#jform_id'),
        excludeProductsVal = [],
        includeProductsVal = [],
        url = JUriRoot+'administrator/index.php?option=com_argensyml&task=item.get_ajax_products'
        ;

    if(jQuery('#filter_categories_valuehidden').length > 0){
        filterCategories = jQuery('#filter_categories_valuehidden');
    }
    else if(jQuery('#filter_categories').length > 0){
        filterCategories = jQuery('#filter_categories');
    }


    excludeProducts.find('input').each(function () {
        excludeProductsVal.push(jQuery(this).val());
    });

    includeProducts.find('input').each(function () {
        includeProductsVal.push(jQuery(this).val());
    });

    var data = {
        'filter_categories': filterCategories.val(),
        'filter_calendar_from': filterCalendarFrom.val(),
        'filter_calendar_to': filterCalendarTo.val(),
        'filter_text': filterText.val(),
        'id': itemId.val(),
        'exclude_products': excludeProductsVal,
        'include_products': includeProductsVal
    };

    jQuery.ajax({
        type: "POST",
        url: url,
        data: data,
        dataType : "json"
    }).done(function(json) {
        productsDiv.html('');
        if(json.length > 0)
        {
            for(var i=0;i<json.length;i++)
            {
                jQuery('<li>').html(json[i].name+' ('+json[i].sku+')<input type="hidden" value="'+json[i].id+'">')
                    .appendTo(productsDiv);
            }
            initDrag();
        }
        var dfg = 1;
    });
};

jQuery(function() {
    initDrag();
});

function initDrag(){
    jQuery( "#products,#include_products,#exclude_products" ).sortable({
        connectWith: "#products,#include_products,#exclude_products",
        update: function(event, ui) {
            var name;
            switch(ui.item.parents('.products-dragable').attr('id')){
                case('exclude_products'):
                    name = 'jform[shop_settings][exclude_products][]';
                    break;
                case('include_products'):
                    name = 'jform[shop_settings][include_products][]';
                    break;
                default:
                    name = '';
                    break;
            }
            ui.item.find('input').attr('name', name);
        }
    });
}
