<?php
// No direct access
/**
 * Yandex.Market XML
 *
 * @version    1.0
 * @author        Arkadiy, Joomline
 * @copyright    © 2015. All rights reserved.
 * @license    GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;
if(empty($item->offer_type)){
    $item->store = $item->pickup = $item->delivery = $item->bid = $item->cbid = $item->sales_notes = $item->adult = $item->age = '';
}
else{
    $item->store = $item->store == 1 ? JText::_('JYES') : JText::_('JNO');
    $item->pickup = $item->pickup == 1 ? JText::_('JYES') : JText::_('JNO');
    $item->delivery = $item->delivery == 1 ? JText::_('JYES') : JText::_('JNO');
    $item->adult = $item->adult == 1 ? JText::_('JYES') : JText::_('JNO');
}
?>

<tr id="category-<?php echo $item->id; ?>" class="parent-<?php echo $item->parent_id; ?>" data-parent="<?php echo $item->parent_id; ?>">
    <td class="childs">
        <?php if ($item->hasChild) { ?>
            <input type="button" value="+" class="btn btn-small btn-info"
                   onclick="expandCat(this, <?php echo $item->id; ?>)">
        <?php } ?>
    </td>
    <td class="ctegory"><?php echo $item->name; ?></td>
    <td class="offer_type"><?php echo $item->offer_type; ?></td>
    <td class="store"><?php echo $item->store; ?></td>
    <td class="pickup"><?php echo $item->pickup; ?></td>
    <td class="delivery"><?php echo $item->delivery; ?></td>
    <td class="local_delivery_cost"><?php echo $item->local_delivery_cost; ?></td>
    <td class="bid"><?php echo $item->bid; ?></td>
    <td class="cbid"><?php echo $item->cbid; ?></td>
    <td class="sales_notes"><?php echo $item->sales_notes; ?></td>
    <td class="adult"><?php echo $item->adult; ?></td>
    <td class="age"><?php echo $item->age; ?></td>
    <td class=""><a class="vkdata" href="<?php echo JUri::root(); ?>administrator/index.php?option=com_argensyml&view=category&category_id=<?php echo $item->id ?>" onclick="loadYaForm(this); return false;" data-id="<?php echo $item->id ?>"><?php echo JText::_('COM_ARGENSYML_EDIT');?></a>
    <td class=""><a class="vkdata" href="#" onclick="cleanRow(this, '<?php echo $token; ?>'); return false;" data-id="<?php echo $item->id ?>"><?php echo JText::_('COM_ARGENSYML_CLEAR');?></a>
    </td>
</tr>