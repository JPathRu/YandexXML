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
JHTML::_('behavior.modal', 'a.modal');
$token = JSession::getFormToken();
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
        <table class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th><?php echo JText::_('COM_ARGENSYML_CATEGORY');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_TYPE');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_STORE');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_PICKUP');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_DELIVERY');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_DELIVERY_COST');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_BID');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_CBID');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_SALES_NOTES');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_ADULT');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_AGE');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_EDIT');?></th>
                <th><?php echo JText::_('COM_ARGENSYML_CLEAR');?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php require JPATH_ROOT . '/administrator/components/com_argensyml/views/yacategories/tmpl/default_item.php'; ?>
            <?php endforeach; ?>
            </tbody>
        </table>


        <div>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="folder" value="<?php echo $this->folder ?>"/>
            <input type="hidden" name="boxchecked" value="0"/>
            <?php echo JHtml::_('form.token'); ?>
        </div>

    </div>
    <?php endif; ?>
