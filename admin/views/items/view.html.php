<?php

// No direct access
defined('_JEXEC') or die;

/**
 * Yandex.Market XML
 *
 * @version    1.0
 * @author        Arkadiy, Joomline
 * @copyright    © 2015. All rights reserved.
 * @license    GNU/GPL v.3 or later.
 */
class argensymlViewItems extends JViewLegacy
{

    protected $items;

    /**
     * Method to display the current pattern
     * @param type $tpl
     */
    public function display($tpl = null)
    {
        $canDo = argensymlHelper::getActions('component');
        $config = JComponentHelper::getParams('com_argensyml');
        $this->items = $this->get('Items');
        $this->folder = $this->get('Folder');
        $this->password = $config->get('password', 'asdakhecrnkjhsdfnhk');
        $this->user = JFactory::getUser();
        $this->canDo = $canDo;
        $this->config = JComponentHelper::getParams('com_argensyml');

        $app = JFactory::getApplication();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('errors')
            ->from('#__argensyml_errors');
        $errors = $db->setQuery($query)->loadColumn();

        $db->truncateTable('#__argensyml_errors');

        if (count($errors)) {
            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }
        }

        argensymlHelper::addSubmenu('items');
        $this->sidebar = JHtmlSidebar::render();
        $this->setToolBar($canDo);
        parent::display($tpl);
    }

    /**
     * Method to display the toolbar
     */
    protected function setToolBar($canDo)
    {
        JToolBarHelper::title(JText::_('COM_ARGENSYML_MAIN'));

        if ($canDo !== false)
        {
            if ($canDo->get('core.create'))
            {
                JToolBarHelper::addNew('item.add');
            }

            if ($canDo->get('core.delete'))
            {
                JToolBarHelper::deleteList('DELETE_QUERY_STRING', 'items.delete', 'JTOOLBAR_DELETE');
                JToolBarHelper::divider();
            }

            if ($canDo->get('core.admin'))
            {
                if (version_compare(JVERSION, '3.0.0', 'ge'))
                {
                    $icon = 'folder-3';
                }
                else{
                    $icon = 'edit';
                }
            }

            if ($canDo->get('core.admin'))
            {
                JToolBarHelper::preferences('com_argensyml');
            }
        }
        else
        {
            JToolBarHelper::preferences('com_argensyml');
        }


    }
}