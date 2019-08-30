<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if (version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('behavior.formvalidator');
    JHtml::_('formbehavior.chosen', 'select');
} else {
    JHtml::_('behavior.formvalidation');
}

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
	
	var JUriRoot = "'.JUri::root().'";
');

$enable_type = $this->config->get('enable_type', 'y');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_argensyml&id=' . $this->item->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-validate">
    <div class="form-horizontal">
        <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic')); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'basic', JText::_('COM_ARGENSYML_BASIC_PARAMS', true)); ?>
            <?php if ($enable_type == 'a') : ?>
                <?php echo $this->form->getControlGroups('filetype'); ?>
            <?php else : ?>
                <input type="hidden" name="jform[type]" value="<?php echo $enable_type; ?>">
            <?php endif; ?>
            <?php echo $this->form->getControlGroups('basic'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.start', 'myTab', array('useCookie' => 1, 'active' => 'basic')); ?>
            <?php echo JHtml::_('tabs.panel', JText::_('COM_ARGENSYML_BASIC_PARAMS'), 'basic'); ?>
            <?php if ($enable_type == 'a') : ?>
                <div class="width-60 fltlft">
                    <fieldset class="adminform">
                        <ul class="adminformlist">
                            <?php foreach ($this->form->getFieldset('filetype') as $field) : ?>
                                <li><?php echo $field->label; ?>
                                    <?php echo $field->input; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </fieldset>
                </div>
            <?php else : ?>
                <input type="hidden" name="jform[type]" value="<?php echo $enable_type; ?>">
            <?php endif; ?>
            <div class="width-60 fltlft">
                <fieldset class="adminform">
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('basic') as $field) : ?>
                            <li><?php echo $field->label; ?>
                                <?php echo $field->input; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
            </div>
            <div style="clear: both"></div>
        <?php } ?>

        <?php if (in_array($enable_type, array('y', 'a'))) : ?>
            <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'yandex', JText::_('COM_ARGENSYML_YANDEX_PARAMS', true)); ?>
                <?php echo $this->form->getControlGroups('yandex'); ?>
            <?php } else { ?>
                <?php echo JHtml::_('tabs.panel', JText::_('COM_ARGENSYML_YANDEX_PARAMS'), 'yandex'); ?>
                <div class="width-60 fltlft">
                    <fieldset class="adminform">
                        <ul class="adminformlist">
                            <?php foreach ($this->form->getFieldset('yandex') as $field) : ?>
                                <li><?php echo $field->label; ?>
                                    <?php echo $field->input; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </fieldset>
                </div>
                <div style="clear: both"></div>
            <?php } ?>


            <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php } ?>
        <?php endif; ?>
        <?php if (in_array($enable_type, array('v', 'a'))) : ?>
            <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'vkontakte', JText::_('COM_ARGENSYML_VKONTAKTE_PARAMS', true)); ?>
                <?php echo $this->form->getControlGroups('vkontakte'); ?>
            <?php } else { ?>
                <?php echo JHtml::_('tabs.panel', JText::_('COM_ARGENSYML_VKONTAKTE_PARAMS'), 'vkontakte'); ?>
                <div class="width-60 fltlft">
                    <fieldset class="adminform">
                        <ul class="adminformlist">
                            <?php foreach ($this->form->getFieldset('vkontakte') as $field) : ?>
                                <li><?php echo $field->label; ?>
                                    <?php echo $field->input; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </fieldset>
                </div>
                <div style="clear: both"></div>
            <?php } ?>

            <a target="_blank"
               href="http://vkexport.wkrs.ru/index.php?action=token&preset=<?php echo $this->item->id; ?>&site=<?php echo $this->item->site_id; ?>&pass=<?php echo $this->item->site_pass; ?>"
               class="btn btn-primary"><?php echo JText::_('COM_ARGENSYML_GET_TOKEN'); ?></a>
            <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php } ?>
        <?php endif; ?>


        <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
            <?php $leftClass = 'span7'; $rightClass = 'span5';  ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'shop_settings', JText::_('COM_ARGENSYML_SHOP_SETTINGS', true)); ?>
        <?php } else { ?>
            <?php $leftClass = 'width-60 fltlft'; $rightClass = 'width-40 fltrt';  ?>
            <?php echo JHtml::_('tabs.panel', JText::_('COM_ARGENSYML_SHOP_SETTINGS'), 'shop_settings'); ?>
        <?php } ?>
        <div class="row-fluid">
            <div class="<?php echo $leftClass; ?>">
                <?php $field = $this->form->getField('shop_settings');
                echo $this->form->getField('shop_settings')->getInput(); ?>
            </div>
            <div class="<?php echo $rightClass; ?>">
                <h3><?php echo JText::_('COM_ARGENSYML_PRODUCTS'); ?></h3>
                <div class="filter">
                    <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
                        <div class="control-group">
                            <div class="control-label">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_CATEGORY', true); ?></label>
                            </div>
                            <div class="controls">
                                <?php echo $this->filterCategory; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_DATE_FROM', true); ?></label>
                            </div>
                            <div class="controls">
                                <?php echo $this->filterDateFrom; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_DATE_TO', true); ?></label>
                            </div>
                            <div class="controls">
                                <?php echo $this->filterDateTo; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_TEXT', true); ?></label>
                            </div>
                            <div class="controls">
                                <input type="text" class="infobox" value="" id="filter_text">
                            </div>
                        </div>
                    <?php } else { ?>
                        <ul class="adminformlist">
                            <li style="display: table">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_CATEGORY', true); ?></label>

                                <?php echo $this->filterCategory; ?>
                            </li>
                            <li style="display: table">

                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_DATE_FROM', true); ?></label>

                                <?php echo $this->filterDateFrom; ?>
                            </li>
                            <li style="display: table">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_DATE_TO', true); ?></label>

                                <?php echo $this->filterDateTo; ?>
                            </li>
                            <li style="display: table">
                                <label><?php echo JText::_('COM_ARGENSYML_FILTER_TEXT', true); ?></label>
                                <input type="text" class="infobox" value="" id="filter_text">
                            </li>
                        </ul>
                    <?php } ?>

                </div>
                <button type="button" class="btn btn-info"
                        onclick="loadProducts()"><?php echo JText::_('COM_ARGENSYML_LOAD_PRODUCTS'); ?></button>
                <ul id="products" class="products-dragable"></ul>
            </div>
        </div>
        <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        <?php } else { ?>
            <div style="clear: both"></div>
            <?php echo JHtml::_('tabs.end'); ?>
        <?php } ?>

    </div>
    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
