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


class argensymlControllerItem extends JControllerForm
{

    function get_ajax_products()
    {
        $model = $this->getModel();
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_argensyml');
        $connector = $params->get('connector');

        if($connector == 'virtuemart')
        {
            $item = $model->getItem($app->input->getInt('id', 0));
            $root_id = 0;
            if(!empty($item->shop_settings))
            {
                $item->shop_settings = json_decode($item->shop_settings);
                if(!empty($item->shop_settings->root_id))
                {
                    $root_id = $item->shop_settings->root_id;
                }
            }
            $model->setState('root_id', $root_id);
        }

        $model->setState('filter_categories', $app->input->getInt('filter_categories', -1));
        $model->setState('filter_calendar_from', $app->input->getString('filter_calendar_from', ''));
        $model->setState('filter_calendar_to', $app->input->getString('filter_calendar_to', ''));
        $model->setState('filter_text', $app->input->getString('filter_text', ''));
        $model->setState('exclude_products', $app->input->get('exclude_products', array(), 'array'));
        $model->setState('include_products', $app->input->get('include_products', array(), 'array'));

        echo json_encode($model->getProducts());
        $app->close();
    }

    function save($key = null, $urlVar = null)
    {
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            return parent::save($key, $urlVar);
        }
        else {
            // Check for request forgeries.
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

            // Initialise variables.
            $app   = JFactory::getApplication();
            $lang  = JFactory::getLanguage();
            $model = $this->getModel();
            $table = $model->getTable();
            $data  = JRequest::getVar('jform', array(), 'post', 'array');
            $checkin = property_exists($table, 'checked_out');
            $context = "$this->option.edit.$this->context";
            $task = $this->getTask();

            // Determine the name of the primary key for the data.
            if (empty($key))
            {
                $key = $table->getKeyName();
            }

            // To avoid data collisions the urlVar may be different from the primary key.
            if (empty($urlVar))
            {
                $urlVar = $key;
            }

            $recordId = JRequest::getInt($urlVar);



            // Populate the row id from the session.
            $data[$key] = $recordId;

            // The save2copy task needs to be handled slightly differently.
            if ($task == 'save2copy')
            {
                // Check-in the original row.
                if ($checkin && $model->checkin($data[$key]) === false)
                {
                    // Check-in failed. Go back to the item and display a notice.
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
                    $this->setMessage($this->getError(), 'error');

                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($recordId, $urlVar), false
                        )
                    );

                    return false;
                }

                // Reset the ID and then treat the request as for Apply.
                $data[$key] = 0;
                $task = 'apply';
            }

            // Access check.
            if (!$this->allowSave($data, $key))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
                $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_list
                        . $this->getRedirectToListAppend(), false
                    )
                );

                return false;
            }

            // Validate the posted data.
            // Sometimes the form needs some posted data, such as for plugins and modules.
            $form = $model->getForm($data, false);

            if (!$form)
            {
                $app->enqueueMessage($model->getError(), 'error');

                return false;
            }

            // Test whether the data is valid.
            $validData = $model->validate($form, $data);

            // Check for validation errors.
            if ($validData === false)
            {
                // Get the validation messages.
                $errors = $model->getErrors();

                // Push up to three validation messages out to the user.
                for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
                {
                    if ($errors[$i] instanceof Exception)
                    {
                        $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                    }
                    else
                    {
                        $app->enqueueMessage($errors[$i], 'warning');
                    }
                }

                // Save the data in the session.
                $app->setUserState($context . '.data', $data);

                // Redirect back to the edit screen.
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                    )
                );

                return false;
            }

            // Attempt to save the data.
            if (!$model->save($validData))
            {
                // Save the data in the session.
                $app->setUserState($context . '.data', $validData);

                // Redirect back to the edit screen.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
                $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                    )
                );

                return false;
            }

            // Save succeeded, so check-in the record.
            if ($checkin && $model->checkin($validData[$key]) === false)
            {
                // Save the data in the session.
                $app->setUserState($context . '.data', $validData);

                // Check-in failed, so go back to the record and display a notice.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
                $this->setMessage($this->getError(), 'error');

                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                    )
                );

                return false;
            }

            $this->setMessage(
                JText::_(
                    ($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
                        ? $this->text_prefix
                        : 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
                )
            );

            // Redirect the user and adjust session state based on the chosen task.
            switch ($task)
            {
                case 'apply':
                    // Set the record data in the session.
                    $recordId = $model->getState($this->context . '.id');
                    $this->holdEditId($context, $recordId);
                    $app->setUserState($context . '.data', null);
                    $model->checkout($recordId);

                    // Redirect back to the edit screen.
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($recordId, $urlVar), false
                        )
                    );
                    break;

                case 'save2new':
                    // Clear the record id and data from the session.
                    $this->releaseEditId($context, $recordId);
                    $app->setUserState($context . '.data', null);

                    // Redirect back to the edit screen.
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend(null, $urlVar), false
                        )
                    );
                    break;

                default:
                    // Clear the record id and data from the session.
                    $this->releaseEditId($context, $recordId);
                    $app->setUserState($context . '.data', null);

                    // Redirect to the list screen.
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_list
                            . $this->getRedirectToListAppend(), false
                        )
                    );
                    break;
            }

            // Invoke the postSave method to allow for the child class to access the model.
            $this->postSaveHook($model, $validData);

            return true;
        }

    }
}
