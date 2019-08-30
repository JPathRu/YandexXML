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
class argensymlViewCategories extends JViewLegacy
{

    protected $items;

    /**
     * Method to display the current pattern
     * @param type $tpl
     */
    public function display($tpl = null)
    {
        $canDo = argensymlHelper::getActions('component');
        $this->items = $this->get('Items');
        $this->vkCatSelect = $this->get('VkCatSelect');
        $this->canDo = $canDo;
        $this->config = JComponentHelper::getParams('com_argensyml');
        JFactory::getDocument()->addScript(JUri::root().'administrator/components/com_argensyml/views/categories/tmpl/assets/script.js');
        $this->setToolBar($canDo);
        argensymlHelper::addSubmenu('categories');
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Method to display the toolbar
     */
    protected function setToolBar($canDo)
    {
        JToolBarHelper::title(JText::_('COM_ARGENSYML_CATEGORIES'));
        JToolBarHelper::back('JTOOLBAR_BACK', JRoute::_('index.php?option=com_argensyml&view=items'));
    }
}