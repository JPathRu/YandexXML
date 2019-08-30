<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class argensymlModelItem extends JModelAdmin
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

	public function getTable($type = 'Item', $prefix = 'argensymlTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_argensyml.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_argensyml.edit.item.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if(version_compare( JVERSION, '3.0.0', 'ge' )){
			$this->preprocessData('com_argensyml.item', $data);
		}

		return $data;
	}

	public function getFilterCategory()
	{
		if(!class_exists($this->connector) || !method_exists($this->connector, 'getFilterCategory'))
		{
			return '';
		}

		$connectorClass = new $this->connector;
		return $connectorClass->getFilterCategory();
	}

	public function getFilterDateFrom()
	{
		return $this->getFilterDate('_from');
	}
	public function getFilterDateTo()
	{
		return $this->getFilterDate('_to');
	}

	private function getFilterDate($suffix = '')
	{
		if(version_compare( JVERSION, '3.0.0', 'ge' )){
			JHtml::_('jquery.framework');
			JHtml::_('script', 'system/html5fallback.js', false, true);
		}

		$attributes = array();
		$attributes['autocomplete'] = 'off';
		$attributes['autofocus'] = '';

		return JHtml::_('calendar', '', '', 'filter_calendar'.$suffix, '%Y-%m-%d', $attributes);
	}

	public function getProducts()
	{
		if(!class_exists($this->connector) || !method_exists($this->connector, 'getFilteredProducts'))
		{
			return '';
		}

		$category = $this->getState('filter_categories', -1);
		$dateFrom = $this->getState('filter_calendar_from', '');
		$dateTo = $this->getState('filter_calendar_to', '');
		$text = $this->getState('filter_text', '');
		$excludeProducts = $this->getState('exclude_products', array());
		$includeProducts = $this->getState('include_products', array());

		$params = array();

		if($this->connector == 'virtuemartConnector'){
			$root_id = $this->getState('root_id', 0);
			$params['root_id'] = $root_id;
		}

		$connectorClass = new $this->connector;
		return $connectorClass->getFilteredProducts($category, $dateFrom, $dateTo, $text, $excludeProducts, $includeProducts, $params);
	}
}
