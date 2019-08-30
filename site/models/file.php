<?php

// No direct access
defined( '_JEXEC' ) or die;


/**
 * Yandex.Market XML
 *
 * @version 	2.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

jimport('joomla.application.component.modellist');
jimport('joomla.filesystem.file');

class argensymlModelFile extends JModelList
{
    private
        $config,
        $connector,
        $connectorClassName,
        $connectorPath,
        $connectorFile,
        $connectorExist
    ;

    function __construct()
    {
        $this->config = JComponentHelper::getParams('com_argensyml');
        $this->connector = $this->config->get('connector', 'hikashop');
        $this->connectorClassName = $this->connector.'Connector';
        $this->connectorPath = JPATH_ROOT . '/administrator/components/com_argensyml/connectors/';
        $this->connectorFile = $this->connectorPath . $this->connector . '.php';
        if(JFile::exists($this->connectorFile))
        {
            require_once $this->connectorPath . 'mainconnector.php';
            require_once $this->connectorFile;
            $this->connectorExist = true;
        }
        else
        {
            $this->connectorExist = false;
        }
    }

	public function getCategoryData()
	{
        if(!$this->connectorExist){
            return false;
        }
		$connectorClass = new Mainconnector;
		$categories = $connectorClass->getCategories();
		return  $categories;
	}

	public function getOfferData($limitstart, $limit)
	{
        if(!$this->connectorExist){
            return false;
        }
		$connectorClass = new Mainconnector;
		$offers = $connectorClass->getOffers($limitstart, $limit);
		return $offers;
	}
}