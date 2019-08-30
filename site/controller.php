<?php

// No direct access
defined( '_JEXEC' ) or die;

require_once JPATH_ROOT . '/administrator/components/com_argensyml/helpers/argensyml.php';

/**
 * Yandex.Market XML
 *
 * @version    1.0
 * @author        Arkadiy, Joomline
 * @copyright    © 2015. All rights reserved.
 * @license    GNU/GPL v.3 or later.
 */
class argensymlController extends JControllerLegacy
{

    private 
        $config, 
        $model,
        $endTime,
        $file,
        $fileUrl,
        $item,
        $layout;

    /**
     * Methot to load and display current view
     * @param Boolean $cachable
     */
    function view()
    {
        $startTime = time();
        $app = JFactory::getApplication();
        $this->config = JComponentHelper::getParams('com_argensyml');
        $this->endTime = $startTime + (int)$this->config->get('time', 60) - 2;

        $this->model = JModelLegacy::getInstance('File', 'argensymlModel');
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_argensyml/models', 'argensymlModel');
        $itemModel = $this->getModel('Item');
        $itemsModel = $this->getModel('Items');

        $id = $app->input->getInt('id', 0);

        if($id == 0)
        {
            $app->redirect(JUri::root().'administrator/index.php?option=com_argensyml&view=items', JText::_('COM_ARGENSYML_ID_IS_NULL'));
        }

        $item = $itemModel->getItem($id);
        $this->item = argensymlHelper::createConfig($item);

        $fileType = $this->item->get('type', 'y');
        $ext = $fileType == 'y' ? '.xml' : '.json';
        $filename = $this->item->get('filename', 'argensyml');
        $this->file = JPATH_ROOT . '/components/com_argensyml/yml/' . $filename . $ext;
        $this->fileUrl = JUri::root() . 'components/com_argensyml/yml/' . $filename . $ext;
        $this->layout = $fileType == 'y' ? 'default' : 'json';

        $allow = JComponentHelper::getParams('com_argensyml')->get('allow', '');
        $password = $app->input->getString('pass', '2');
        $show = $app->input->getInt('show', 0);
        $continue = $app->input->getInt('continue', 0);
        $is_admin = $app->input->getInt('is_admin',0);
        $limitstart = $app->input->getInt('limitstart',0);

        $configPass = $this->config->get('password', '1');
        $limit = $this->config->get('limit', 200);

        if ($password != $configPass) {
            $app->close('Password wrong');
        }

        if(!$itemsModel->allow($allow))
        {
            $app->close('This site not allowed');
        }

        $url = array(
            'index.php?option=com_argensyml&task=view&pass='.$configPass
        );
        if($is_admin){
            $url[] = 'is_admin=1';
        }
        if($show){
            $url[] = 'show=1';
        }

        if(!$continue)
        {
            $this->createHead();
        }

        $this->createBody($limitstart, $limit, $url);

        $this->createFoot();

        if($is_admin)
        {
            $app->redirect(JUri::root().'administrator/index.php?option=com_argensyml&view=items');
        }

        if ($show)
        {
            $app->redirect($this->fileUrl);
        }

        $app->close();
    }

    private function createHead()
    {
        $tz = JFactory::getConfig()->get('offset');
        $date = new JDate('now', $tz);
        $now = $date->format('Y-m-d h:i');

        $view = $this->getView("head", 'html');
        $view->setLayout($this->layout);

        if($this->item->get('type', 'y') == 'v'){
            $view->preset = $this->item->get('id', 0);
            $view->group = $this->item->get('group_id', '');
            $view->album = $this->item->get('album_id', '');
        }
        else{
            $view->currency = $this->item->get('currency', 'RUB');
            $view->shop_name = $this->item->get('shop_name', '');
            $view->company_name = $this->item->get('company_name', '');
            $view->delivery = $this->item->get('delivery', 0);
            $view->delivery_time = $this->item->get('delivery_time', 0);
            $view->order_before = $this->item->get('order_before', '');
            $view->local_delivery_cost = $this->item->get('local_delivery_cost', '');
            $view->cpa = $this->item->get('cpa', -1);
        }

        $view->categories = $this->model->getCategoryData();
        $view->date = $now;

        if($this->layout == 'default')
        {
            $html = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        }
        else{
            $html = '';
        }

        $html .= $view->loadTemplate();

        file_put_contents($this->file, $html);
        unset($view, $html);
    }

    private function createBody($limitstart, $limit, $url)
    {
        $go = true;

        while($go)
        {
            if($this->endTime <= time())
            {
                $url[] = 'limitstart='.$limitstart;
                $url = implode('&', $url);
                JFactory::getApplication()->redirect($url);
            }

            $view = $this->getView("body", 'html');
            $view->setLayout($this->layout);
            $view->offers = $this->model->getOfferData($limitstart, $limit);
            if(!is_array($view->offers) || !count($view->offers))
            {
                $go = false;
                continue;
            }
            $limitstart += $limit;

            $html = $view->loadTemplate();
            file_put_contents($this->file, $html, FILE_APPEND);
            unset($view, $html);
        }
    }

    private function createFoot()
    {
        $view = $this->getView("foot", 'html');
        $view->setLayout($this->layout);
        $html = $view->loadTemplate();
        file_put_contents($this->file, $html, FILE_APPEND);
    }
}