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
jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.component.controllerform' );
jimport( 'joomla.application.component.controlleradmin' );
jimport( 'joomla.application.component.modeladmin' );

$controller = JControllerLegacy::getInstance( 'argensyml' );
$controller->execute( JFactory::getApplication()->input->get('task') );
$controller->redirect();