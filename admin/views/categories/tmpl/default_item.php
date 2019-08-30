<?php
// No direct access
/**
 * Yandex.Market XML
 *
 * @version 	2.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;
$item->vk_name = !empty($item->vk_name) ? $item->vk_name : JText::_('ADD');
?>
<div class="row-fluid parent-<?php echo $item->parent_id; ?>" id="category-<?php echo $item->id; ?>">
    <div class="expand span1">
        <?php if($item->hasChild){ ?>
            <input type="button" value="+" class="btn btn-small btn-info" onclick="expandCat(this, <?php echo $item->id; ?>)">
        <?php } ?>
    </div>
    <div class="span5"><?php echo $item->name; ?></div>
    <div class="span5">
        <a class="vkdata" href="#" onclick="loadVKForm(this); return false;" data-id="<?php echo $item->id ?>"><?php echo $item->vk_name ?></a></div>
</div>