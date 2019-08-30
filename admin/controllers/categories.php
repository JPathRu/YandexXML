<?php
/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

defined('_JEXEC') or die;


class argensymlControllerCategories extends JControllerAdmin
{
    public function getModel($name = 'Categories', $prefix = 'argensymlModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    function listCats(){
        JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_argensyml&view=categories', false));
    }

    function listYaCats(){
        JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_argensyml&view=yacategories', false));
    }

    function getChildCats(){
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id',0);
        $type = $app->input->getCmd('type','vk');
        if($type == 'vk'){
            $model = $this->getModel();
            $tpl = JPATH_ROOT . '/administrator/components/com_argensyml/views/categories/tmpl/default_item.php';
        }
        else{
            $model = $this->getModel('YaCategories', 'argensymlModel');
            $tpl = JPATH_ROOT . '/administrator/components/com_argensyml/views/yacategories/tmpl/default_item.php';
        }

        $items = $model->getItems($id);
        if(is_array($items) && count($items)){
            foreach ($items as $i => $item){
                require $tpl;
            }
        }
        $app->close();
    }

    function saveCatAssoc()
    {
        $app = JFactory::getApplication();
        $vkCategoryId = $app->input->getInt('vkCategoryId',0);
        $categoryId = $app->input->getInt('categoryId',0);
        $vkCategoryName = $app->input->getString('vkCategoryName','');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from('#__argensyml_cat_assoc')
            ->where('category_id = '.(int)$categoryId);
        $result = $db->setQuery($query,0,1)->loadResult();

        if($result)
        {
            $query->clear()
                ->update('#__argensyml_cat_assoc')
                ->set('vk_id = '.$db->quote($vkCategoryId))
                ->set('vk_name = '.$db->quote($vkCategoryName))
                ->where('category_id = '.(int)$categoryId);
            try{
                $db->setQuery($query)->execute();
            }
            catch(Exception $e){
                echo json_encode(array('error' => 1, 'msg' => $e->getMessage()));
                $app->close();
            }
        }
        else
        {
            $object = new stdClass();
            $object->vk_id = $vkCategoryId;
            $object->vk_name = $vkCategoryName;
            $object->category_id = $categoryId;
            if(!$db->insertObject('#__argensyml_cat_assoc', $object)){
                echo json_encode(array('error' => 1, 'msg' => 'Error insert data'));
                $app->close();
            }
        }
        echo json_encode(array('error' => 0, 'msg' => 'Ok'));
        $app->close();
    }

    function saveYaCatAssoc()
    {
        if (version_compare(JVERSION, '3.0.0', 'ge'))
        {
            JSession::checkToken() or jexit(json_encode(array('error' => 1, 'msg' => JText::_('JINVALID_TOKEN'))));
            $data   = $this->input->get('jform', array(), 'array');
        }
        else{
            $data   = JRequest::getVar('jform',	array(), 'request', 'array');
        }

        $app = JFactory::getApplication();

        $db = JFactory::getDbo();
        $query = "INSERT INTO #__argensyml_yacat (`category_id`, `offer_type`, `type_prefix`, `cpa`, `store`, `pickup`, 
          `local_delivery_cost`, `delivery`, `delivery_time`, `order_before`, `bid`, `cbid`, `sales_notes`, `adult`, `age`)
			  VALUES ('{$data["category_id"]}', '{$data["offer_type"]}', '{$data["type_prefix"]}', '{$data["cpa"]}', 
			    '{$data["store"]}', '{$data["pickup"]}', 
			    '{$data["local_delivery_cost"]}', '{$data["delivery"]}', '{$data["delivery_time"]}', '{$data["order_before"]}', 
			    '{$data["bid"]}', '{$data["cbid"]}', '{$data["sales_notes"]}', '{$data["adult"]}', '{$data["age"]}')
			  ON DUPLICATE KEY UPDATE
              `offer_type` = VALUES (`offer_type`),
              `type_prefix` = VALUES (`type_prefix`),
              `cpa` = VALUES (`cpa`),
              `store` = VALUES (`store`),
              `pickup` = VALUES (`pickup`),
              `local_delivery_cost` = VALUES (`local_delivery_cost`),
              `delivery` = VALUES (`delivery`),
              `delivery_time` = VALUES (`delivery_time`),
              `order_before` = VALUES (`order_before`),
              `bid` = VALUES (`bid`),
              `cbid` = VALUES (`cbid`),
              `sales_notes` = VALUES (`sales_notes`),
              `adult` = VALUES (`adult`),
              `age` = VALUES (`age`)
			";

        $db->setQuery($query);

        if($db->execute()){
            echo json_encode(array('error' => 0, 'msg' => 'Ok'));
        }
        else{
            echo json_encode(array('error' => 1, 'msg' => 'Error store data'));
        }
        $app->close();
    }

    function clean()
    {
        if (version_compare(JVERSION, '3.0.0', 'ge'))
        {
            $category_id   = $this->input->getInt('category_id', 0);
        }
        else{
            $category_id = JRequest::getInt('category_id', 0);
        }

        $app = JFactory::getApplication();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__argensyml_yacat')->where('category_id = '.(int)$category_id);
        if($db->setQuery($query)->execute()){
            echo json_encode(array('error' => 0, 'msg' => 'Ok'));
        }
        else{
            echo json_encode(array('error' => 1, 'msg' => 'Error store data'));
        }
        $app->close();
    }
}
