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
jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.component.controllerform' );
jimport( 'joomla.application.component.controlleradmin' );
jimport( 'joomla.application.component.modeladmin' );

if (!JFactory::getUser()->authorise('core.manage', 'com_argensyml'))
{
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('argensymlHelper', __DIR__ . '/helpers/argensyml.php');

$config = JComponentHelper::getParams('com_argensyml');

$lang      = JFactory::getLanguage();
$lang->load('com_argensyml', __DIR__, null, false, true);

$controller = JControllerLegacy::getInstance( 'argensyml' );
$controller->execute( JFactory::getApplication()->input->get('task') );
$controller->redirect();