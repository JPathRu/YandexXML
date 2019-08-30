<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class argensymlTableCategory extends JTable
{
	public function __construct(&$_db)
	{
		parent::__construct('#__argensyml_yacat', 'category_id', $_db);
	}

	public function bind($array, $ignore = array())
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$array['params'] = json_encode($array['params']);
		}

		return parent::bind($array, $ignore);
	}
}
