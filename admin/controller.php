
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
class argensymlController extends JControllerLegacy
{

	/**
	 * Methot to load and display current view
	 * @param Boolean $cachable
	 */
	function display($cachable = false, $urlparams = false)
	{
		$this->default_view = 'items';
		parent::display();
        return $this;
	}
}