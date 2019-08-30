<?php

// No direct access
defined( '_JEXEC' ) or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');

/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	� 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
class argensymlModelCategories extends JModelList
{
    var $connector;

	function __construct($config = array())
    {
        parent::__construct($config);
        $params = JComponentHelper::getParams('com_argensyml');
        $connector = $params->get('connector');
        if(!empty($connector)){
            $file = JPATH_ROOT.'/administrator/components/com_argensyml/connectors/'.$connector.'.php';
            if(is_file($file)){
                require_once JPATH_ROOT.'/administrator/components/com_argensyml/connectors/mainconnector.php';
                require_once $file;
                $this->connector = $connector.'Connector';
            }
        }
    }

    public function getItems($id=0)
	{

        if(!class_exists($this->connector) || !method_exists($this->connector, 'getCategoriesForParent'))
        {
            return array();
        }

        $connectorClass = new $this->connector(false);
        $retutn = $connectorClass->getCategoriesForParent($id, 'vk');

        return $retutn;
	}

    public function getVkCatSelect()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('site_id, site_pass')
            ->from('#__argensyml_items')
            ->where('type='.$db->quote('v'));
        $result = $db->setQuery($query,0,1)->loadObject();

        if(empty($result)){
            return '';
        }
        $url = 'http://vkexport.wkrs.ru/index.php?action=get_data&site='.$result->site_id.'&pass='.$result->site_pass.'&method=market.getCategories';

        $cats = $this->open_http($url);
        $cats = json_decode($cats, true);

        $options = array();
        if(!empty($cats["response"]) && is_array($cats["response"]) && count($cats["response"])){
            foreach ($cats["response"] as $cat)
            {
                if(!is_array($cat)){
                    continue;
                }
                $options[] = JHtml::_('select.option', $cat["id"], $cat["section"]["name"].' > '.$cat["name"]);
            }

            return JHTML::_('select.genericlist', $options, 'vkCategoryId', ' class="inputbox vkCatSelect" style="width:100%;"');
        }
        else{
            return '';
        }
    }

    private function open_http($url, $method = false, $params = null)
    {
        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method);
        if ($method == true && isset($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch,  CURLOPT_HTTPHEADER, array(
            'Content-Length: '.strlen($params),
            'Cache-Control: no-store, no-cache, must-revalidate',
            "Expires: " . date("r")
        ));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}