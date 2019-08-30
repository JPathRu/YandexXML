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
class argensymlViewYaCategories extends JViewLegacy
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
        $this->catSelect = $this->get('CatSelect');
        $this->canDo = $canDo;
        $this->config = JComponentHelper::getParams('com_argensyml');

        $document = JFactory::getDocument();
        if(version_compare( JVERSION, '3.0.0', 'ge' )){
            JHtml::_('jquery.framework');
            $document->addScriptDeclaration('var argensYMLjversion = 3');
        }
        else{
            $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.min.js');
            $document->addScriptDeclaration('var argensYMLjversion = 2');
        }

        $document->addScript(JUri::root().'administrator/components/com_argensyml/views/yacategories/tmpl/assets/script.js');

        argensymlHelper::addSubmenu('yacategories');
        $this->sidebar = JHtmlSidebar::render();
        $this->setToolBar($canDo);
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