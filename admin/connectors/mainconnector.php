<?php
/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
defined( '_JEXEC' ) or die;
require_once JPATH_ROOT . '/administrator/components/com_argensyml/helpers/argensyml.php';

if (version_compare(JVERSION, '3.5.0', 'ge'))
{
    if(!class_exists('StringHelper1')){
        class StringHelper1 extends \Joomla\String\StringHelper{}
    }
}
else
{
    if(!class_exists('StringHelper1')){
        jimport('joomla.string.string');
        class StringHelper1 extends JString{}
    }
}

class Mainconnector
{
    protected
        $db,
        $query,
        $config,
        $item,
        $offerType,
        $offerTypeParams,
        $includeCategories,
        $excludeProducts,
        $includeProducts,
        $fileType,
        $yandexCats
    ;
    private $offerTypes;

    function __construct()
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $model = JModelLegacy::getInstance('Item', 'argensymlModel');

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
        $this->config = JComponentHelper::getParams('com_argensyml');
        $this->connector = $this->config->get('connector', 'hikashop');
        $this->connectorClassName = $this->connector.'Connector';
        $this->yandexCats = array();

        $item = $model->getItem($id);
        $this->item = argensymlHelper::createConfig($item);

        $include_categories = $this->item->get('include_categories', array());
        $this->includeCategories = (
            !is_array($include_categories)
            || !count($include_categories)
            || in_array('-1', $include_categories)
        ) ? array() : $include_categories;

        $this->excludeProducts = $this->item->get('exclude_products', array());
        $this->includeProducts = $this->item->get('include_products', array());
        $this->fileType = $this->item->get('type', 'y');

        $this->offerType = $this->item->get('offer_type', 'basic');
        $this->offerTypes = array(
            'vendor.model' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'url', 'price', 'currencyId', 'categoryId', 'delivery', 'vendor', 'model'
                ),
                'notRequired' => array(
                    'market_category', 'store', 'pickup', 'delivery-options', 'typePrefix', 'picture',
                    'vendorCode', 'description', 'sales_notes', 'manufacturer_warranty', 'country_of_origin',
                    'downloadable', 'adult', 'age', 'barcode', 'cpa', 'rec', 'expiry', 'weight', 'dimensions', 'param',
                    'group_id', 'oldprice', 'rec', 'adult', 'age', 'cpa'
                )
            ),
            'book' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'price', 'currencyId', 'categoryId', 'name'
                ),
                'notRequired' => array(
                    'url', 'market_category', 'picture', 'store', 'pickup', 'delivery', 'delivery-options',
                    'author', 'publisher', 'series', 'year', 'ISBN', 'volume', 'part', 'language', 'binding',
                    'page_extent', 'table_of_contents', 'description', 'downloadable', 'age', 'group_id', 'oldprice',
                    'rec', 'adult', 'age', 'cpa'
                )
            ),
            'audiobook' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'price', 'currencyId', 'categoryId', 'name'
                ),
                'notRequired' => array(
                    'url', 'market_category', 'picture', 'author', 'publisher', 'series', 'year', 'ISBN', 'volume',
                    'part', 'language', 'table_of_contents', 'performed_by', 'performance_type', 'storage', 'format',
                    'recording_length', 'description', 'downloadable', 'age', 'group_id', 'oldprice', 'rec',
                    'adult', 'age', 'cpa'
                )
            ),
            'artist.title' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'price', 'currencyId', 'categoryId', 'title', 'delivery'
                ),
                'notRequired' => array(
                    'url', 'market_category', 'picture', 'store', 'pickup', 'artist', 'year', 'media',
                    'description', 'age', 'barcode', 'group_id', 'oldprice', 'rec', 'adult', 'age', 'cpa'
                )
            ),
            'tour' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'price', 'currencyId', 'categoryId', 'days', 'name', 'included', 'transport', 'delivery'
                ),
                'notRequired' => array(
                    'url', 'market_category', 'picture', 'store', 'pickup', 'worldRegion', 'country',
                    'region', 'dataTour', 'hotel_stars', 'room', 'meal', 'description', 'age', 'group_id', 'oldprice',
                    'rec', 'adult', 'age', 'cpa'
                )
            ),
            'event-ticket' => array(
                'offerAttributes' => array(
                    'id', 'bid', 'cbid', 'available', 'type', 'group_id'
                ),
                'required' => array(
                    'price', 'currencyId', 'categoryId', 'name', 'place', 'date', 'delivery'
                ),
                'notRequired' => array(
                    'url', 'market_category', 'picture', 'store', 'pickup', 'hall_plan', 'is_premiere',
                    'is_kids', 'description', 'age', 'group_id', 'oldprice', 'rec', 'adult', 'age', 'cpa'
                )
            )
        );
        $this->offerTypeParams = $this->getOfferType();
    }

    function getCategories()
    {
        $connectorClass = new $this->connectorClassName;
        $result = $connectorClass->getCategories();

        $categories = array();

        foreach($result as $v)
        {
            $category = new stdClass();
            $category->id = $v->category_id;

            if($this->fileType == 'v'){
                $category->vk_id = $v->vk_id;
            }
            else{
                $category->parentId = $v->category_parent_id;
                $category->name = $this->cleanText($v->category_name);
            }

            $categories[] = $category;
        }

        return  $categories;
    }

    function getOffers($limitstart, $limit){
        $connectorClass = new $this->connectorClassName;
        $result = $connectorClass->getOffers($limitstart, $limit);

        if($this->fileType == 'v'){
            return $result;
        }

	    $dispatcher	= JEventDispatcher::getInstance();
	    JPluginHelper::importPlugin('argensyml');

	    $deleteMissing = $this->item->get('delete_missing', 0);

        $offers = array();
        foreach ($result as $product)
        {
        	if($deleteMissing && $product->product_quantity == 0){
        		continue;
	        }

	        $dispatcher->trigger('onBeforeBuildYandexOffer', array(&$product));

            $yandex_cat = $this->getYandexCat($product->categoryId);

            $offer = new stdClass();
            $offer->attribute = new stdClass();
            if($this->issetAttribute('id'))
                $offer->attribute->id = $product->product_id;
            if($this->issetAttribute('type'))
                $offer->attribute->type = empty($yandex_cat->offer_type) ? $this->offerType : $yandex_cat->offer_type;
            if($this->issetAttribute('bid'))
                $offer->attribute->bid = $yandex_cat->bid === false ? $this->item->get('bid') : $yandex_cat->bid;
            if($this->issetAttribute('cbid'))
                $offer->attribute->cbid = $yandex_cat->cbid === false ? $this->item->get('cbid') : $yandex_cat->cbid;
            if($this->issetAttribute('available'))
                $offer->attribute->available = ($product->product_quantity > 0 || $product->product_quantity == '-1') ? 'true' : 'false';
            if($this->issetAttribute('group_id') && !empty($product->group_id))
                $offer->attribute->group_id = $product->group_id;

            $type = !empty($yandex_cat->offer_type) ? $yandex_cat->offer_type : null;
            $this->offerTypeParams = $this->getOfferType($type);
            $requiredParams = array_flip($this->offerTypeParams['required']);

            $nameName = 'title';
            if(isset($requiredParams['model'])){
                $nameName = 'model';
            }
            else if (isset($requiredParams['name'])){
                $nameName = 'name';
            }

            $store = $yandex_cat->store === false ? $this->item->get('store') : $yandex_cat->store;
            $value = $store ? 'true' : 'false';
            $this->setParam($offer, $requiredParams, 'store', $value);

            $pickup = $yandex_cat->pickup === false ? $this->item->get('pickup') : $yandex_cat->pickup;
            $value = $pickup ? 'true' : 'false';
            $this->setParam($offer, $requiredParams, 'pickup', $value);

            $delivery = ($yandex_cat->delivery === false) ? $this->item->get('delivery', 0) : $yandex_cat->delivery;
            $delivery = $delivery > 0 ? 'true' : 'false';
            $this->setParam($offer, $requiredParams, 'delivery', $delivery);

            if($delivery == 'true'){
                $local_delivery_cost = $yandex_cat->local_delivery_cost === false ? $this->item->get('local_delivery_cost', 0) : $yandex_cat->local_delivery_cost;
                $delivery_time = $yandex_cat->delivery_time === false ? $this->item->get('delivery_time', 0) : $yandex_cat->delivery_time;
                $free_shipping = (int)$this->item->get('free_shipping', 0);

                if($free_shipping > 0 && (float)$product->price >= $free_shipping){
                    $local_delivery_cost = 0;
                }

                $attributes = array(
                    'cost' => $local_delivery_cost,
                    'days' => $delivery_time,
                );

                $order_before = $yandex_cat->order_before === false ? $this->item->get('order_before', 0) : $yandex_cat->order_before;
                if(!empty($order_before)){
                    $attributes['order-before'] = $order_before;
                }

                $aDelivery = array(
                    0 => array(
                        'name' => 'option',
                        'value' => '',
                        'attrib' => $attributes
                    )
                );

                $this->setParam($offer, $requiredParams, 'delivery-options', $aDelivery);
            }

            if(!empty($yandex_cat->type_prefix)){
                $this->setParam($offer, $requiredParams, 'typePrefix', $yandex_cat->type_prefix);
            }

            if((int)$yandex_cat->cpa > -1){
                $this->setParam($offer, $requiredParams, 'cpa', $yandex_cat->cpa);
            }

            $sales_notes = $yandex_cat->sales_notes === false ? $this->item->get('sales_notes', '') : $yandex_cat->sales_notes;
            $this->setParam($offer, $requiredParams, 'sales_notes', $sales_notes);

            $adult = ($yandex_cat->adult === false) ? $this->item->get('adult', 0) : $yandex_cat->adult;
            if($adult > 0){
                $this->setParam($offer, $requiredParams, 'adult', 'true');
            }

            $age = ($yandex_cat->age === false) ? $this->item->get('age', 0) : $yandex_cat->age;
            if($age > 0){
                $this->setParam($offer, $requiredParams, 'age', $yandex_cat->age, array(array('name' => 'unit', 'value' => 'year')));
            }

            $this->setParam($offer, $requiredParams, 'url', $product->url);
            $this->setParam($offer, $requiredParams, 'price', (float)$product->price);
            $this->setParam($offer, $requiredParams, 'currencyId', $product->currencyId);
            $this->setParam($offer, $requiredParams, 'categoryId', $product->categoryId);
            $this->setParam($offer, $requiredParams, 'vendor', $product->vendor);
            $this->setParam($offer, $requiredParams, 'vendorCode', $product->vendorCode);
            $this->setParam($offer, $requiredParams, $nameName, $this->cleanText($product->name));
            $this->setParam($offer, $requiredParams, 'description', $this->cleanText($product->description, true));
            $this->setParam($offer, $requiredParams, 'downloadable', $product->downloadable);
            $this->setParam($offer, $requiredParams, 'weight', round((float)$product->weight, 2));

            if(is_string($product->picture)){
                $this->setParam($offer, $requiredParams, 'picture', $product->picture);
            }
            else if(is_array($product->picture) && count($product->picture)){
                foreach ($product->picture as $picture){
                    $this->setParam($offer, $requiredParams, 'picture', $picture);
                }
            }


            if(!empty($product->related)){
                $this->setParam($offer, $requiredParams, 'rec', (string)$product->related);
            }

            if(!empty($product->country_of_origin)){
                $this->setParam($offer, $requiredParams, 'country_of_origin', $product->country_of_origin);
            }

            if(isset($product->params) && is_array($product->params) && count($product->params)){
                foreach ($product->params as $f) {
                    $this->setParam($offer, $requiredParams, 'param', $f['value'], $f['attrib']);
                }
            }

            if((float)$product->old_price > 0){
                $this->setParam($offer, $requiredParams, 'oldprice', (float)$product->old_price);
            }

            if(count($requiredParams)){
                $requiredParamsString = implode(', ', array_keys($requiredParams));
                $message = 'Product id='.$product->product_id.' name="'.$product->name.'" error. Required params '.$requiredParamsString.' not set.';
                $this->setError($message);
                continue;
            }

	        $dispatcher->trigger('onAfterBuildYandexOffer', array(&$offer));

            $offers[]= $offer;
        }
        return $offers;
    }

    function getOfferType($type=null)
    {
        if($type){
            if(isset($this->offerTypes[$type]))
                return $this->offerTypes[$type];
            else
                return false;
        }
        else if(isset($this->offerTypes[$this->offerType]))
        {
            if(isset($this->offerTypes[$this->offerType]))
                return $this->offerTypes[$this->offerType];
            else
                return false;
        }
    }

    function getParamType($paramName)
    {
        if(in_array($paramName, $this->offerTypeParams['required']))
        {
            return 'required';
        }
        else if (in_array($paramName, $this->offerTypeParams['notRequired']))
        {
            return 'notRequired';
        }
        return false;
    }

    function issetAttribute($attribute)
    {
        if(in_array($attribute, $this->offerTypeParams['offerAttributes']))
        {
            return true;
        }
        return false;
    }

    function setParam(&$offer, &$requiredParams, $paramName, $value, array $attrib = array())
    {
        if($value != '' && $this->getParamType($paramName) !== false)
        {
            if(!isset($offer->$paramName))
            {
                $offer->$paramName = array();
            }

            if(is_array($attrib) && count($attrib))
            {
                for($i=0;$i<count($attrib);$i++)
                {
                    $attrib[$i]['value'] = htmlspecialchars($attrib[$i]['value']);
                }
            }

            if(!is_array($value)){
                $value = htmlspecialchars($value);
            }
            array_push($offer->$paramName, array(
                'value' => $value,
                'attrib' => $attrib
            ));
            unset($requiredParams[$paramName]);
        }
    }

    function cleanText($text, $cleanPluginTags=false)
    {
        if($cleanPluginTags)
        {
            $text = preg_replace("/{.*}/", '', $text);
            $text = preg_replace("/[.*]/", '', $text);
        }
        $text = StringHelper1::trim(strip_tags($text));
        $text = htmlspecialchars($text);
        return $text;
    }

    function setError($msg)
    {
        $db = JFactory::getDbo();
        $object = new stdClass();
        $object->errors = $msg;
        $db->insertObject('#__argensyml_errors', $object);
    }

    function memoryUsage($usage) {
        $usage = $usage/1024/1024;
        printf("MB used: %d<br/>", $usage);
    }

    private function getYandexCat($categoryId)
    {
        if(!isset($this->yandexCats[$categoryId]))
        {
            $this->query->clear()->select('*')
                ->from('#__argensyml_yacat')
                ->where('`category_id` = ' . (int)$categoryId)
            ;
            $result = $this->db->setQuery($this->query,0,1)->loadObject();

            if(!is_object($result))
            {
                $result = new stdClass();
                $result->category_id = false;
                $result->offer_type = false;
                $result->type_prefix = false;
                $result->cpa = -1;
                $result->store = false;
                $result->pickup = false;
                $result->local_delivery_cost = false;
                $result->delivery_time = false;
                $result->order_before = false;
                $result->delivery = false;
                $result->bid = false;
                $result->cbid = false;
                $result->sales_notes = false;
                $result->adult = false;
                $result->age = false;

            }

            $this->yandexCats[$categoryId] = $result;
        }

        return $this->yandexCats[$categoryId];
    }

}

final class offetObject{
    var
        $product_id,
        $group_id,
        $product_quantity,
        $url,
        $price,
        $currencyId,
        $categoryId,
        $vendor,
        $vendorCode,
        $picture,
        $name,
        $description,
        $downloadable,
        $weight,
        $params,
        $old_price,
        $related
    ;
}