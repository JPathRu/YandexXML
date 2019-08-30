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
$enable_type = $this->config->get('enable_type', 'y');
?>
<script type="text/javascript">
    function deleteXmlFile(id)
    {
        if (confirm ('<?php echo JText::_('DELETE_QUERY_STRING'); ?>'))
        {
            <?php if(version_compare(JVERSION, '3', '>=')) : ?>
            jQuery(':checkbox').removeAttr('checked');
            jQuery('#cb'+id).click();
            jQuery('input[name=task]').val('file.delete');
            jQuery('#adminForm').submit();
            <?php else : ?>
            $$('input[type=checkbox]').removeProperty('checked');
            $('cb'+id).setProperty('checked', 'checked');
            $$('input[name=task]').setProperty('value', 'file.delete');
            $('adminForm').submit();
            <?php endif; ?>
        }
    }

    function confirmUpload() {
        if (confirm("<?php echo JText::_('COM_ARGENSYML_REALLY_UPLOAD_FILE'); ?>")) {
            return true;
        } else {
            return false;
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_argensyml&view=items'); ?>" method="post" name="adminForm" id="adminForm">
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
        <table class="table table-striped span12" id="articleList">
            <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value=""
                           title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                </th>
                <th>
                    <?php echo JText::_('COM_ARGENSYML_LIST_NAME'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_ARGENSYML_LIST_FILE'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_ARGENSYML_CREATE_FILE'); ?>
                </th>
                <th>
                    <?php echo JText::_('COM_ARGENSYML_DYNAMIC_CREATE'); ?>
                </th>
                <?php if(in_array($enable_type, array('v', 'a'))) : ?>
                    <th>
                        <?php echo JText::_('COM_ARGENSYML_SEND_FILE'); ?>
                    </th>
                <?php endif; ?>
                <th>
                    <?php echo JText::_('COM_ARGENSYML_LIST_DELETE'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                $file = urlencode(JUri::root().'components/com_argensyml/yml/'. $item->filename.'.json');
                $fileExist = file_exists(JPATH_ROOT.'/components/com_argensyml/yml/'. $item->filename.'.json');
                ?>
                <tr class="row<?php echo $i % 2; ?>">

                    <td class="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_argensyml&view=item&id='.$item->id); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </td>
                    <td>
                        <?php if(!empty($item->file['url'])) : ?>
                        <a href="<?php echo $item->file['url']; ?>" target="_blank">
                            <?php echo $this->escape($item->file['name']); ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a
                            class="btn btn-success"
                            href="<?php echo JUri::root().'index.php?option=com_argensyml&task=view&id='.$item->id.'&pass='.$this->password.'&is_admin=1'; ?>">
                            <?php echo JText::_('JACTION_CREATE')?>
                        </a>
                    </td>
                    <td>
                        <a
                            class="btn btn-info"
                            href="<?php echo JUri::root().'index.php?option=com_argensyml&task=view&id='.$item->id.'&pass='.$this->password.'&show=1'; ?>"
                            target="_blank">
                            <?php echo JText::_('JACTION_CREATE')?>
                        </a>
                    </td>
                    <?php if(in_array($enable_type, array('v', 'a'))) : ?>
                        <td>
                            <?php if($fileExist) : ?>
                            <a
                                onclick="return confirmUpload();"
                                target="_blank"
                                href="http://vkexport.wkrs.ru/index.php?action=load_my_file&site=<?php echo $item->site_id; ?>&pass=<?php echo $item->site_pass; ?>&file=<?php echo $file; ?>"
                                class="btn btn-primary">
                                <?php echo JText::_('COM_ARGENSYML_SEND'); ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <td>
                        <?php if ($this->canDo->get( 'core.delete' )) : ?>
                            <button class="btn btn-danger" onclick="deleteXmlFile(<?php echo $i; ?>)">X</button>
                        <?php endif; ?>
                    </td>
                </tr>
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
</form>