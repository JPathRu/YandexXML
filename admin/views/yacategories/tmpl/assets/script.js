/**
 * Created by arkadiy on 17.02.16.
 */

function expandCat(button, catId)
{
    var child = jQuery('.parent-'+catId);
    var $button = jQuery(button);

    if(child.length == 0)
    {
        jQuery.ajax({
            url: rootUrl + 'administrator/index.php?option=com_argensyml&task=categories.getChildCats&id=' + catId+'&type=ya',
            dataType: 'html'
        }).done(function(data) {
            if(data != ''){
                jQuery('#category-'+catId).after(data);
            }
        });
        $button.val('-');
    }
    else{
        if(child.is(':hidden')){
            child.show();
            $button.val('-');
        }
        else {
            child.hide();
            $button.val('+');
        }
    }

}

function loadYaForm(element)
{
    var $element = jQuery(element);
    var form = jQuery('#vk-form form');
    var catId = $element.attr('data-id');
    jQuery('.categoryId', form).val(catId);

    if(argensYMLjversion == 2){
        SqueezeBox.open(element, {  handler: 'iframe' });
    }
    else{
        var url = rootUrl + 'administrator/index.php?option=com_argensyml&view=category&category_id='+catId;
        SqueezeBox.open(url, { handler: 'ajax' });
    }

}

function cleanRow(element, token)
{
    if(!confirm('Удалить все данные категории?')){
        return;
    }

    var $element = jQuery(element);
    var catId = $element.attr('data-id');
    var tr = jQuery('#category-'+catId);
    var url = rootUrl + 'administrator/index.php?option=com_argensyml&task=categories.clean&category_id='+catId;
    jQuery.ajax({
        url: url,
        method: 'POST',
        data: token+'=1',
        dataType: 'json'
    }).done(function(data) {
        if(data.error == 1){
            alert(data.msg);
        }
        else{
            tr.find('.offer_type').text('');
            tr.find('.store').text('');
            tr.find('.pickup').text('');
            tr.find('.delivery').text('');
            tr.find('.local_delivery_cost').text('');
            tr.find('.bid').text('');
            tr.find('.cbid').text('');
            tr.find('.sales_notes').text('');
            tr.find('.adult').text('');
            tr.find('.age').text('');
        }
    });
}

function saveYaData(element){
    var form = jQuery(element).parent();
    var catId = jQuery('#jform_category_id').val();
    var tr;
    if(argensYMLjversion == 2){
        tr = jQuery('#category-'+catId, window.parent.document);
    }
    else{
        tr = jQuery('#category-'+catId);
    }

    
    var data = form.serialize();
    jQuery.ajax({
        url: form.attr('action'),
        data: data,
        method: 'POST',
        dataType: 'json'
    }).done(function(data) {
        if(data.error == 1){
            alert(data.msg);
        }
        else{
            tr.find('.offer_type').text(jQuery('#jform_offer_type').val());
            tr.find('.store').text(jQuery('#jform_store option:selected').text());
            tr.find('.pickup').text(jQuery('#jform_pickup option:selected').text());
            tr.find('.delivery').text(jQuery('#jform_delivery option:selected').text());
            tr.find('.local_delivery_cost').text(jQuery('#jform_local_delivery_cost').val());
            tr.find('.bid').text(jQuery('#jform_bid').val());
            tr.find('.cbid').text(jQuery('#jform_cbid').val());
            tr.find('.sales_notes').text(jQuery('#jform_sales_notes').val());
            tr.find('.adult').text(jQuery('#jform_adult option:selected').text());
            tr.find('.age').text(jQuery('#jform_age').val());

            if(argensYMLjversion == 2){
                if(typeof(window.parent.SqueezeBox.close) == 'function'){
                    window.parent.SqueezeBox.close();
                }
                else {
                    document.getElementById('sbox-window').close();
                }
            }
            else{
                SqueezeBox.close();
            }
        }
    });
}