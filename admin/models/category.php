<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class argensymlModelCategory extends JModelAdmin
{
	public function getTable($type = 'Category', $prefix = 'argensymlTable', $config = array())
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
		$form = $this->loadForm('com_argensyml.category', 'category', array('control' => 'jform', 'load_data' => $loadData));
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
        $category_id = $app->input->getInt('category_id', 0);
		$data = $app->getUserState('com_argensyml.edit.category.data.'.$category_id, array());

		if (empty($data))
		{
			$data = $this->getItem($category_id);
                        $data->category_id = $category_id;
		}

		if(version_compare( JVERSION, '3.0.0', 'ge' )){
			$this->preprocessData('com_argensyml.category', $data);
		}

		return $data;
	}

	public function getItem($pk = null)
	{
        return parent::getItem($pk);
	}
}
