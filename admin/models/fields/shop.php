<?php
defined('_JEXEC') or die('Restricted access');

class JFormFieldShop extends JFormField
{
    public $type = 'Shop';

    public function getInput()
    {
        $params = JComponentHelper::getParams('com_argensyml');
        $connector = $params->get('connector');
        if(!empty($connector)){
            $file = JPATH_ROOT.'/administrator/components/com_argensyml/connectors/'.$connector.'.php';
            if(is_file($file)){
                require_once JPATH_ROOT.'/administrator/components/com_argensyml/connectors/mainconnector.php';
                require_once $file;
                $classname = $connector.'Connector';
                $connectorClass = new $classname;

                if(method_exists($connectorClass, 'loadCustomParams')){
                    $data = $connectorClass->loadCustomParams($params, $this);
                    if(is_array($data) && count($data)){
                        $html ='';
                        $html .= '<table>';
                        foreach($data as $v){
                            $html .= '
                                <tr>
                                    <td style="padding: 0 10px;">'.$v['label'].'</td>
                                    <td>'.$v['input'].'</td>
                                </tr>';
                        }
                        $html .= '</table>';
                        return $html;
                    }

                }
            }
        }
        return JText::_('COM_ARGENSYML_CUSTOM_PARAMS_EMPTY');
    }
}
