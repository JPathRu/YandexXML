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
class argensymlViewCategory extends JViewLegacy
{

    protected $item, $form, $user, $canDo;

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
        $this->canDo = $canDo;

        parent::display($tpl);
        JFactory::getApplication()->close();
    }
}