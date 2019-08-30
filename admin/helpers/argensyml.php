<?php

defined('_JEXEC') or die;
/**
 * Yandex.Market XML
 *
 * @version 	2.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
class argensymlHelper
{
    public static $extension = 'com_argensyml';

	public static function getActions( $section, $recordId = 0 )
	{
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_argensyml/models', 'argensymlModel');
        $model = JModelLegacy::getInstance('items', 'argensymlModel');
        $allow = JComponentHelper::getParams('com_argensyml')->get('allow', '');
		$user = JFactory::getUser();
		$result = new JObject;

		if ( empty( $recordId ) )
        {
			$assetName = 'com_argensyml';
		}
        else
        {
			$assetName = 'com_argensyml.' . $section . '.' . (int)$recordId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.delete'
		);

		foreach ( $actions as $action )
        {
			$result->set( $action, $user->authorise( $action, $assetName ) );
		}

        if(!$model->allow($allow))
        {
            $result = false;
        }

		return $result;
	}

	public static function createConfig($item)
	{
		$conf = new ConfigItem($item);
		return $conf;
	}

    public static function addSubmenu($vName)
    {
        $config = JComponentHelper::getParams('com_argensyml');
        $enable_type = $config->get('enable_type', 'y');

        JHtmlSidebar::addEntry(
            JText::_('COM_ARGENSYML_ITEMS'),
            'index.php?option=com_argensyml&view=items',
            $vName == 'items'
        );

        if($enable_type == 'v' || $enable_type == 'a')
            JHtmlSidebar::addEntry(
                JText::_('COM_ARGENSYML_CATEGORIES_VK'),
                'index.php?option=com_argensyml&view=categories',
                $vName == 'categories'
            );

        if($enable_type == 'y' || $enable_type == 'a')
            JHtmlSidebar::addEntry(
                JText::_('COM_ARGENSYML_CATEGORIES_YA'),
                'index.php?option=com_argensyml&view=yacategories',
                $vName == 'yacategories'
            );

    }
}

class ConfigItem
{
	private $data;

	public function __construct($item)
	{
		$shop_settings =  json_decode($item->shop_settings, true);
		$this->data = array();
		$this->data["id"] = $item->id;
		$this->data["shop_name"] = $item->shop_name;
  		$this->data["company_name"] = $item->company_name;
  		$this->data["currency"] = $item->currency;
  		$this->data["offer_type"] = $item->offer_type;
  		$this->data["filename"] = $item->filename;
  		$this->data["store"] = $item->store;
  		$this->data["pickup"] = $item->pickup;
  		$this->data["delivery"] = $item->delivery;
  		$this->data["delivery_time"] = $item->delivery_time;
  		$this->data["order_before"] = $item->order_before;
  		$this->data["local_delivery_cost"] = $item->local_delivery_cost;
  		$this->data["bid"] = $item->bid;
  		$this->data["cbid"] = $item->cbid;
		$this->data["type"] = $item->type;
  		$this->data["name"] = $item->name;
  		$this->data["site_id"] = $item->site_id;
  		$this->data["site_pass"] = $item->site_pass;
  		$this->data["group_id"] = $item->group_id;
  		$this->data["album_id"] = $item->album_id;
  		$this->data["sales_notes"] = $item->sales_notes;
  		$this->data["cpa"] = (int)$item->cpa;
  		$this->data["images_limit"] = (int)$item->images_limit;
  		$this->data["free_shipping"] = (int)$item->free_shipping;
  		$this->data["delete_missing"] = (int)$item->delete_missing;

  		$this->data["shopSettings"] = null;
		if(is_array($shop_settings) && count($shop_settings))
		{
			$this->data["shopSettings"] = 1;
			foreach($shop_settings as $k => $v){
				$this->data[$k] = $v;
			}
		}
	}

	public function get($name, $default=null)
	{
		return isset($this->data[$name]) ? $this->data[$name] : $default;
	}
}