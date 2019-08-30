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
class argensymlModelYaCategories extends JModelList
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
        $retutn = $connectorClass->getCategoriesForParent($id, 'ya');

        return $retutn;
	}
}