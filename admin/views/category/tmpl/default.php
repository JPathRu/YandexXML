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
    JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
	var argensYMLjversion = 3;
');
} else {
    JHtml::_('behavior.formvalidation');
    header('Content-Type: text/html;charset=UTF-8');
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>

            <script type="text/javascript" src="<?php echo JUri::root().'administrator/components/com_argensyml/assets/javascripts/jquery.min.js' ?>"></script>
            <script type="text/javascript" src="<?php echo JUri::root().'administrator/components/com_argensyml/views/yacategories/tmpl/assets/script.js' ?>"></script>
            <script src="<?php echo JUri::root(); ?>media/system/js/mootools-core.js" type="text/javascript"></script>
            <script src="<?php echo JUri::root(); ?>media/system/js/modal.js" type="text/javascript"></script>
            <script type="text/javascript">
                var argensYMLjversion = 2;
            </script>
        </head>
    <body>
    <?php
}


?>
<form action="<?php echo JRoute::_('index.php?option=com_argensyml&task=categories.saveYaCatAssoc')?>" method="post">
    <div class="form-horizontal">
        <?php if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
            <?php echo $this->form->getControlGroups('basic'); ?>
        <?php } else { ?>
            <fieldset class="adminform">
                <ul class="adminformlist">
                    <?php foreach ($this->form->getFieldset('basic') as $field) : ?>
                        <li><?php echo $field->label; ?>
                            <?php echo $field->input; ?></li>
                    <?php endforeach; ?>
                </ul>
            </fieldset>
        <?php } ?>

    </div>
    <input type="button" onclick="saveYaData(this)" class="btn btn-success" value="<?php echo JText::_('SAVE');?>">
    <?php echo JHtml::_('form.token'); ?>
</form>
<?php
if (!version_compare(JVERSION, '3.0.0', 'ge')) {
    ?>
    </body>
</html>
    <?php
}