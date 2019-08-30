<?php
/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;

if(is_array($this->offers) && count($this->offers)){
    foreach ($this->offers as $offer){
         $attribute = '';
         foreach($offer->attribute as $k => $val){
             if(!empty($val))
                 $attribute .= ' '.$k.'="'.$val.'"';
         }
         unset($offer->attribute);
         ?>
            <offer<?php echo $attribute; ?>><?php
                    foreach ($offer as $k => $val):
                        foreach ($val as $v):
                            $attrib = '';
                            if(count($v['attrib'])){
                                foreach($v['attrib'] as $attr){
                                    $attrib .= ' '.$attr['name'].'="'.$attr['value'].'"';
                                }
                            }
                            ?><<?php echo $k.$attrib; ?>><?php
                            if(!is_array($v['value'])){
                                echo $v['value'];
                            }
                            else{
                                foreach ($v['value'] as $value){
                                    $attrib = '';
                                    if(count($value['attrib'])){
                                        foreach($value['attrib'] as $attrKey => $attrVal){
                                            $attrib .= ' '.$attrKey.'="'.$attrVal.'"';
                                        }
                                    }
                                    $vName = $value['name'];
                                    $vValue = $value['value'];
                                    if($vValue !== ''){
                                        ?><<?php echo $vName.$attrib; ?>><?php echo $vValue; ?></<?php echo $vName; ?>><?php
                                    }
                                    else{
                                        ?><<?php echo $vName.$attrib; ?>/><?php
                                    }
                                }
                            }

                            ?></<?php echo $k; ?>><?php
                        endforeach;
                    endforeach; ?></offer>
<?php
    }
} ?>
