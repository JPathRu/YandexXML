<?php
// No direct access
/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;
JHTML::_('behavior.modal', 'a.modal');
JFactory::getDocument()->addScriptDeclaration('var rootUrl = "'.JUri::root().'";')
?>
    <?php if($this->canDo !== false) : ?>
    <div class="row-fluid">
        <?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <?php else : ?>
            <div id="j-main-container" class="span12">
                <?php endif;?>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php require JPATH_ROOT . '/administrator/components/com_argensyml/views/categories/tmpl/default_item.php'; ?>
        <?php endforeach; ?>

        <div>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="folder" value="<?php echo $this->folder ?>"/>
            <input type="hidden" name="boxchecked" value="0"/>
            <?php echo JHtml::_('form.token'); ?>
        </div>

    </div>
        <div style="display: none">
            <div id="vk-form">
                <form action="<?php echo JRoute::_('index.php?option=com_argensyml&task=categories.saveCatAssoc')?>" method="post">
                    <?php echo $this->vkCatSelect; ?>
                    <input type="hidden" name="categoryId" class="categoryId" value="">
                    <input type="hidden" name="vkCategoryName" class="vkCategoryName" value="">
                    <input type="button" onclick="saveVKData(this)" class="btn btn-success" value="<?php echo JText::_('SAVE');?>">
                </form>
            </div>
        </div>
    <?php endif; ?>
