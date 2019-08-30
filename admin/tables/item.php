<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class argensymlTableItem extends JTable
{
	public function __construct(&$_db)
	{
		parent::__construct('#__argensyml_items', 'id', $_db);
	}

	public function bind($array, $ignore = array())
	{
		if (isset($array['shop_settings']) && is_array($array['shop_settings']))
		{
			$array['shop_settings'] = json_encode($array['shop_settings']);
		}

		if (isset($array['shop_settings']))
		{
			if(version_compare( JVERSION, '3.0.0', 'ge' )){
				$array['filename'] = JApplicationHelper::stringURLSafe($array['filename']);
			}
			else{
				$array['filename'] = JApplication::stringURLSafe($array['filename']);
			}
		}

		return parent::bind($array, $ignore);
	}
}
