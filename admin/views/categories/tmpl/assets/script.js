/**
 * Created by arkadiy on 17.02.16.
 */

function expandCat(button, catId)
{
    var childDiv = jQuery('#parent-'+catId);
    var $button = jQuery(button);

    if(childDiv.length == 0)
    {
        jQuery.ajax({

            url: rootUrl + 'administrator/index.php?option=com_argensyml&task=categories.getChildCats&id=' + catId,
            dataType: 'html'
        }).done(function(data) {
            if(data != ''){
                jQuery('#category-'+catId).after(data);
            }
        });
        $button.val('-');
    }
    else{
        if(childDiv.is(':hidden')){
            childDiv.show();
            $button.val('-');
        }
        else {
            childDiv.hide();
            $button.val('+');
        }
    }

}

function loadVKForm(element)
{
    var $element = jQuery(element);
    var form = jQuery('#vk-form form');
    var catId = $element.attr('data-id');
    jQuery('.categoryId', form).val(catId);

    SqueezeBox.open($('vk-form'), {
        handler: 'clone',
        size: {x: 400, y: 100}
    });
}

function saveVKData(element){
    var form = jQuery(element).parent();
    var select = jQuery('.vkCatSelect', form);
    var selecttext = select.find('option:selected').text();
    var catId = jQuery('.categoryId', form).val();
    jQuery('.vkCategoryName', form).val(selecttext);
    
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
            var vkA = jQuery('#category-'+catId+' .vkdata');
            vkA.text(selecttext);
            SqueezeBox.close();
        }
    });
}