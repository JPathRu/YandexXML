<?php

// No direct access
defined( '_JEXEC' ) or die;

/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
class argensymlViewBody extends JViewLegacy
{
    function setError($msg)
    {
        $db = JFactory::getDbo();
        $object = new stdClass();
        $object->errors = $msg;
        $db->insertObject('#__argensyml_errors', $object);
    }
}