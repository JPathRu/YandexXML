<?php
/**
 * Yandex.Market XML
 *
 * @version 	2.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');


class argensymlControllerFile extends JControllerLegacy
{

	protected $folder = '';

    public function __construct($config = array())
    {
        parent::__construct($config);

        if(!isset($this->input))
        {
            $this->input = JFactory::getApplication()->input;
        }
    }

	protected function authoriseUser($action)
	{
		if (!JFactory::getUser()->authorise('core.' . strtolower($action), 'com_argensyml'))
		{
			// User is not authorised
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_' . strtoupper($action) . '_NOT_PERMITTED'));
			return false;
		}

		return true;
	}

	public function delete()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$paths	= $this->input->get('cid', array(), 'array');
		$folder = $this->input->getString('folder', '');

		$this->setRedirect('index.php?option=com_argensyml');

		// Nothing to delete
		if (empty($paths))
		{
			return true;
		}

		// Authorize the user
		if (!$this->authoriseUser('delete'))
		{
			return false;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		$ret = true;

		foreach ($paths as $path)
		{
			if ($path !== JFile::makeSafe($path))
			{
				$this->setMessage(JText::_('COM_ARGENSYML_ERROR_UNABLE_TO_DELETE_FILE'), 'error');
				continue;
			}

			$fullPath = JPath::clean($folder.'/'.$path);

			if (is_file($fullPath))
			{
				$ret = JFile::delete($fullPath);
				$this->setMessage(JText::_('COM_ARGENSYML_DELETE_COMPLETE'));
			}
		}

		return $ret;
	}

	public function add()
	{
        $password = JComponentHelper::getParams('com_argensyml')->get('password', 'asdakhecrnkjhsdfnhk');
        $link = JUri::root().'index.php?option=com_argensyml&task=view&pass='.$password.'&is_admin=1';
		JFactory::getApplication()->redirect($link);
	}
}
