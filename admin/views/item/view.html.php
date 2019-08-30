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
class argensymlViewItem extends JViewLegacy
{

    protected $items;

    /**
     * Method to display the current pattern
     * @param type $tpl
     */
    public function display($tpl = null)
    {
        $canDo = argensymlHelper::getActions('component');
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->user = JFactory::getUser();
        $this->config = JComponentHelper::getParams('com_argensyml');
        $this->canDo = $canDo;
        $this->filterCategory = $this->get('FilterCategory');
        $this->filterDateFrom = $this->get('FilterDateFrom');
        $this->filterDateTo = $this->get('FilterDateTo');
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

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

        $this->setToolBar($canDo);
        if(version_compare( JVERSION, '3.0.0', 'ge' )){
            JHtml::_('jquery.framework');
            JHtml::_('script', 'system/html5fallback.js', false, true);
        }
        else{
            $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.min.js');
        }

        JHTML::_('behavior.tooltip');
        $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.ui.core.js');
        $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.ui.widget.js');
        $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.ui.mouse.js');
        $document->addScript(JURI::root().'administrator/components/com_argensyml/assets/javascripts/jquery.ui.sortable.js');
        $document->addScript(JUri::root().'administrator/components/com_argensyml/assets/javascripts/filter.js');
        $document->addStyleSheet(JURI::root().'administrator/components/com_argensyml/assets/css/style.css');

        parent::display($tpl);
    }

    /**
     * Method to display the toolbar
     */
    protected function setToolBar($canDo)
    {
        JToolBarHelper::title(JText::_('COM_ARGENSYML_MAIN'));

        if ($canDo !== false) {
            if ($canDo->get('core.create')) {
                JToolBarHelper::save('item.save');
                JToolBarHelper::apply('item.apply');
                JToolBarHelper::cancel();
            }


        }
    }
}