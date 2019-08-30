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
jimport('joomla.filesystem.file');

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

class joomshoppingConnector extends Mainconnector
{
    private $langTag,
            $enableShop,
            $currencyValues,
            $attributes,
            $extraFields,
            $extraFieldsValues
    ;

    function __construct($loadAttributes=true)
    {
        parent::__construct();
        $this->langTag = JFactory::getLanguage()->getTag();

        $factory = JPATH_ROOT.'/components/com_jshopping/lib/factory.php';
        $functions = JPATH_ROOT.'/components/com_jshopping/lib/functions.php';

        if(JFile::exists($factory) && JFile::exists($functions))
        {
            include_once $factory;
            include_once $functions;
            $this->enableShop = true;
        }
        else{
            $this->enableShop = false;
        }
        if($loadAttributes)
        {
            $this->attributes = $this->item->get('attributes', 'none');
        }

    }

    function getCategories()
    {
        if(!$this->enableShop)
        {
            return array();
        }

        $fileType = $this->item->get('type', 'y');

        $this->query->clear()
            ->select('c.`category_id`, c.`category_parent_id`, c.`name_'.$this->langTag.'` as category_name')
            ->from('#__jshopping_categories AS c')
            ->where('c.category_publish = 1')
            ->order('c.`category_parent_id` ASC, c.`ordering` ASC');

        if($fileType == 'v'){
            $this->query->select('v.`vk_id`')
                ->leftJoin('`#__argensyml_cat_assoc` AS v ON v.`category_id` = c.`category_id`');
        }

        if(count($this->includeCategories))
        {
            $this->query->where('c.`category_id` IN ('.implode(',', $this->includeCategories).')');
        }

        $result = $this->db->setQuery($this->query)->loadObjectList();

        if(!is_array($result) || !count($result))
        {
            $result = array();
        }

        return $result;
    }

    function getOffers($limitstart, $limit)
    {
        $offers = array();
        $app = JFactory::getApplication();
        $messages = array();

        if(!$this->enableShop)
        {
            $message = 'JoomShopping not installed.';
            $this->setError($message);
            return array();
        }

        $isOldShop = true;
        try
        {
            $this->query->clear()->select('`product_full_image`')->from('`#__jshopping_products`');
            $this->db->setQuery($this->query,0,1)->loadObject();
        }
        catch (Exception $e)
        {
            $isOldShop = false;
        }

        $imageField = $isOldShop ? 'product_full_image' : 'image';
        $nameField = 'name_'.$this->langTag;
        $aliasField = 'alias_'.$this->langTag;
        $descField = 'short_description_'.$this->langTag;

        switch($this->attributes){
            case 'params' :
                $selectedFields = '`p`.*';
                $this->loadExtraFields();
                break;
            case 'attr' :
            case 'none' :
            default :
                $selectedFields = '`p`.`product_id`, `p`.`product_ean`, `p`.`product_quantity`, `p`.`unlimited`,
                    `p`.`product_availability`, `p`.`currency_id`, `p`.`product_old_price`, `p`.`product_buy_price`,
                    `p`.`product_price`, `p`.`min_price`, `p`.`different_prices`, `p`.`product_weight`,
                    `p`.`'.$imageField.'`, `p`.`product_manufacturer_id`, `p`.`vendor_id`, `p`.`name_'.$this->langTag.'`,
                    `p`.`alias_'.$this->langTag.'`, `p`.`short_description_'.$this->langTag.'`';
                break;
        }

        $this->query->clear()
            ->select($selectedFields.', `pc`.`category_id`, `man`.`name_'.$this->langTag.'` as manufacturer_name')
            ->from('#__jshopping_products as p')
            ->innerJoin('`#__jshopping_products_to_categories` as pc USING(`product_id`)')
            ->leftJoin('#__jshopping_manufacturers as man ON `p`.`product_manufacturer_id` = `man`.`manufacturer_id`')
            ->where('parent_id = 0')
            ->where('product_publish = 1')
        ;

        if (count($this->includeCategories) && count($this->includeProducts))
        {
            $this->query->where('(`pc`.`category_id` IN ('.implode(',', $this->includeCategories).')
                OR `p`.`product_id` IN ('.implode(',', $this->includeProducts).'))');
        }
        else if(count($this->includeCategories))
        {
            $this->query->where('`pc`.`category_id` IN ('.implode(',', $this->includeCategories).')');
        }
        else if(count($this->includeProducts))
        {
            $this->query->where('`p`.`product_id` IN (' . implode(',', $this->includeProducts) . ')');
        }

        if(count($this->excludeProducts))
        {
            $this->query->where('`p`.`product_id` NOT IN ('.implode(',', $this->excludeProducts).')');
        }

        $rows = $this->db->setQuery($this->query, $limitstart, $limit)->loadObjectList();

        $currencyCode = $this->item->get('currency', 'RUB');
        $currencyId = $this->getCurrId($currencyCode);

        if(!$currencyId)
        {
            $message = 'Currency ID for currency '.$currencyCode.' not find.';
            $this->setError($message);
            return array();
        }

        $jshopConfig = JSFactory::getConfig();
        $image_path = $jshopConfig->image_product_live_path;
        $noimage = $jshopConfig->noimage;

        foreach($rows as $product)
        {
            if(in_array($product->product_id, $this->excludeProducts))
            {
                continue;
            }


            $price = ($currencyId == $product->currency_id) ? $product->product_price
                : $this->convertPrice($product->product_price, $product->currency_id, $currencyId);
            $old_price = ($currencyId == $product->currency_id) ? $product->product_old_price
                : $this->convertPrice($product->product_old_price, $product->currency_id, $currencyId);

            if((float)$price == 0){
                $messages[] = 'Product id='.$product->product_id.' name="'.$product->$nameField.'" error. Price  not set.';
                continue;
            }

            $vendor = $product->manufacturer_name;
            $weight = $product->product_weight;

            $url = str_replace('&amp;', '&', SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='
                .$product->category_id.'&product_id='.$product->product_id, 1));
            $url = JUri::root(false, '').substr($url,1);

            if($isOldShop){
                $imgName = $product->$imageField;
            }
            else{
                $imgName = 'full_'.$product->$imageField;
            }

            $imgLimit = $this->item->get('images_limit', 1);
            if($imgLimit == 1){
                $image = !empty($product->$imageField) ? $image_path.'/'.$imgName : $image_path.'/'.$noimage;
                $images = array($image);
            }
            else{
                $images = $this->getImages($product->product_id, $imgLimit, $image_path, $noimage);
            }

            $offer = new offetObject();
            $offer->product_id = $product->product_id;
            $offer->group_id = 0;
            $offer->product_quantity = $product->product_quantity;
            $offer->url = $url;
            $offer->price = $price;
            $offer->currencyId = $currencyCode;
            $offer->categoryId = $product->category_id;
            $offer->vendor = $vendor;
            $offer->vendorCode = $product->product_ean;
            $offer->picture = $images;
            $offer->name = $product->$nameField;
            $offer->description = $product->$descField;
            $offer->downloadable = ($weight > 0) ? 'false' : 'true';
            $offer->weight = $weight;
            $offer->old_price = $old_price;

            if($this->item->get('related', 0)){
                $related = $this->getRelated($product->product_id);
                if(!empty($related)){
                    $offer->related = $related;
                }
            }

            if($this->attributes == 'attr')
            {
                if($this->loadAttributes($offer, $offers, $currencyId, $product)){
                    continue;
                }
            }

            //Характеристики
            if($this->attributes == 'params' && count($this->extraFields))
            {
                $offer->params = array();

                foreach($this->extraFields as $extraField)
                {
                    if($extraField->allcats == 1 || (is_array($extraField->cats)
                            && in_array($product->category_id, $extraField->cats)))
                    {
                        $extraFieldName = 'extra_field_'.$extraField->id;
                        switch($extraField->type)
                        {
                            case '0'://Список
                                $extraFieldValue = (isset($this->extraFieldsValues[$extraField->field_id][$extraField->id][$product->$extraFieldName]))
                                    ? $this->extraFieldsValues[$extraField->field_id][$extraField->id][$product->$extraFieldName]->value
                                    : '';
                                break;
                            case '1'://Текст
                                $extraFieldValue = (!empty($product->$extraFieldName)) ? $product->$extraFieldName : '';
                                break;
                            case '-1'://Множественный список
                            default:
                                $extraFieldValue = '';
                                break;
                        }

                        if($extraFieldValue)
                        {
                            $offer->params[] = array(
                                'value' => $extraFieldValue,
                                'attrib' => array(
                                    0 => array(
                                        'name' => 'name',
                                        'value' => $extraField->name
                                    )
                                )
                            );
                        }
                    }
                }
            }
            $offers[]= $offer;
        }

        return $offers;
    }

    private function loadAttributes($parentOffer, &$offers, $currencyId, $product)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__jshopping_products_attr')
            ->where('product_id = '.(int)$parentOffer->product_id)
        ;
        $result = $db->setQuery($query)->loadObjectList();
        if(is_array($result) && count($result)){
            $attrNames = $this->loadAttrNames();
            $attrValues = $this->loadAttrValues();
            $product_id = $parentOffer->product_id;
            foreach ($result as $item)
            {
                $offer = clone $parentOffer;
                $price = ($currencyId == $product->currency_id) ? $item->price
                    : $this->convertPrice($item->price, $product->currency_id, $currencyId);
                $old_price = ($currencyId == $product->currency_id) ? $item->old_price
                    : $this->convertPrice($item->old_price, $product->currency_id, $currencyId);
                $offer->product_id = $product_id . 'child' . $item->product_attr_id;
                $offer->group_id = $product_id;
                $offer->product_quantity = $item->count;
                $offer->vendorCode = $item->ean;
                $offer->price = $price;
                $offer->old_price = $old_price;
                $offer->downloadable = ($item->weight > 0) ? 'false' : 'true';
                $offer->weight = $item->weight;
                unset(
                    $item->product_attr_id,
                    $item->product_id,
                    $item->buy_price,
                    $item->price,
                    $item->old_price,
                    $item->count,
                    $item->ean,
                    $item->weight,
                    $item->weight_volume_units,
                    $item->ext_attribute_product_id
                );
                $offer->params = array();
                foreach ($item as $k => $v) {
                    if(strpos($k,'attr_') === false){
                        continue;
                    }

                    $k = (int)str_replace('attr_', '', $k);

                    $offer->params[] = array(
                        'value' => $attrValues[$k][$v],
                        'attrib' => array(
                            0 => array(
                                'name' => 'name',
                                'value' => $attrNames[$k]
                            )
                        )
                    );
                }
                $offers[]= $offer;
            }
            return true;
        }
        return false;
    }

    private function loadAttrNames()
    {
        static $attrNames;
        if(is_null($attrNames)){
            $attrNames = array();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('attr_id, `name_'.$this->langTag.'` as name')
                ->from('#__jshopping_attr')
            ;
            $result = $db->setQuery($query)->loadObjectList();
            if(!is_array($result)){
                return $attrNames;
            }

            foreach ($result as $item) {
                $attrNames[$item->attr_id] = $item->name;
            }
        }
        return $attrNames;
    }

    private function loadAttrValues()
    {
        static $attrValues;
        if(is_null($attrValues)){
            $attrValues = array();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('value_id, attr_id, `name_'.$this->langTag.'` as name')
                ->from('#__jshopping_attr_values')
            ;
            $result = $db->setQuery($query)->loadObjectList();
            if(!is_array($result)){
                return $attrValues;
            }

            foreach ($result as $item) {
                if(!isset($attrValues[$item->attr_id])){
                    $attrValues[$item->attr_id] = array();
                }
                $attrValues[$item->attr_id][$item->value_id] = $item->name;
            }
        }
        return $attrValues;
    }

    private function getCurrId($currencyCode)
    {
        $this->query->clear()
            ->select('`currency_id`')
            ->from('`#__jshopping_currencies`')
            ->where('`currency_code_iso` = '.$this->db->quote($currencyCode))
            ->where('`currency_publish` = 1');
        return (int)$this->db->setQuery($this->query)->loadResult();
    }

    private function convertPrice($price, $productCurrencyId, $currencyId)
    {
        if(!is_array($this->currencyValues) && !count($this->currencyValues))
        {
            $this->loadCurrencyValues();
        }

        $koeff = $this->currencyValues[$currencyId]/$this->currencyValues[$productCurrencyId];

        return $price*$koeff;
    }

    private function loadExtraFields()
    {
        $this->query->clear()
            ->select('`id`, `allcats`, `cats`, `type`, `multilist`, `name_'.$this->langTag.'` as name')
            ->from('`#__jshopping_products_extra_fields`')
            ->order('`ordering` ASC');
        $result = $this->db->setQuery($this->query)->loadObjectList();
        $fields = array();
        if(is_array($result) && count($result))
        {
            foreach($result as $v)
            {
                $v->cats = unserialize($v->cats);
                $fields[$v->id] = $v;
            }
        }
        $this->extraFields = $fields;

        $this->query->clear()
            ->select('`id`, `field_id`, `name_'.$this->langTag.'` as value')
            ->from('`#__jshopping_products_extra_field_values`')
            ->order('`ordering` ASC');
        $result = $this->db->setQuery($this->query)->loadObjectList();
        $fieldsValues = array();
        if(is_array($result) && count($result))
        {
            foreach($result as $v)
            {
                $fieldsValues[$v->field_id][$v->id] = $v;
            }
        }
        $this->extraFieldsValues = $fieldsValues;
    }

    private function loadCurrencyValues()
    {
        $this->query->clear()
            ->select('`currency_id`, `currency_value`')
            ->from('`#__jshopping_currencies`');
        $result = $this->db->setQuery($this->query)->loadObjectList('currency_id');

        $currs = array();
        foreach($result as $v)
        {
            $currs[$v->currency_id] = $v->currency_value;
        }

        $this->currencyValues = $currs;
    }

    public function loadCustomParams($params, $field)
    {
        if(!is_file(JPATH_ROOT.'/administrator/components/com_jshopping/jshopping.php')){
            return array();
        }
        $values = json_decode($field->value, true);

        $include_categories = (empty($values["include_categories"])) ? array() : $values["include_categories"];
        $categories = buildTreeCategory(0,1,0);
        $all = new stdClass();
        $all->category_id = '-1';
        $all->name = JText::_('JALL');
        $all->disable = false;
        $categories = array_merge(array($all), $categories);

        $exclude_products = (empty($values["exclude_products"])) ? array() : $values["exclude_products"];
        $exclude_products_input = '<ul class="products-dragable" id="exclude_products">';
        if(count($exclude_products)){
            $products = $this->getProducts($exclude_products);
            foreach($exclude_products as $v)
            {
                if(!isset($products[$v])) continue;
                $exclude_products_input .= '
                <li>
                    '.$products[$v]->name.' ('.$products[$v]->sku.')'.'
                    <input type="hidden" name="'. $field->name . '[exclude_products][]" value="'.$v.'">
                </li>
                ';
            }
        }
        $exclude_products_input .= '</ul>';

        $include_products = (empty($values["include_products"])) ? array() : $values["include_products"];
        $include_products_input = '<ul class="products-dragable" id="include_products">';
        if(count($include_products)){
            $products = $this->getProducts($include_products);

            foreach($include_products as $v)
            {
                if(!isset($products[$v])) continue;

                $include_products_input .= '
                <li>
                    '.$products[$v]->name.' ('.$products[$v]->sku.')'.'
                    <input type="hidden" name="'. $field->name . '[include_products][]" value="'.$v.'">
                </li>
                ';
            }
        }
        $include_products_input .= '</ul>';

        $attributes = (empty($values["attributes"])) ? 'none' : $values["attributes"];
        $related = (empty($values["related"])) ? 0 : $values["related"];

        $options = array();
        $options[] = JHTML::_('select.option', 'none', JText::_('JNO'));
        $options[] = JHTML::_('select.option', 'attr', JText::_('COM_ARGENSYML_SHOP_ATTRIBUTES'));
        $options[] = JHTML::_('select.option', 'params', JText::_('COM_ARGENSYML_SHOP_PARAMS'));

        return array(
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_INCLUDE_CATS'),
                'input' => JHTML::_('select.genericlist', $categories, $field->name.'[include_categories][]', 'class="inputbox" size="10" multiple = "multiple" ', 'category_id', 'name', $include_categories)
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_INCLUDE_PRODUCTS'),
                'input' => $include_products_input
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_EXCLUDE_PRODUCTS'),
                'input' => $exclude_products_input
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_ATTRIBUTES_LABEL'),
                'input' => JHTML::_('select.genericlist', $options, $field->name.'[attributes]', 'class="inputbox" ', 'value', 'text', $attributes)
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_ENABLE_RELATED'),
                'input' => JHTML::_('select.booleanlist', $field->name.'[related]', 'class="inputbox" ', $related)
            )
        );
    }

    private function getProducts(array $products)
    {
        if(!count($products)){
            return array();
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`product_id`, `product_ean` AS sku, `name_'.$this->langTag.'` AS name')
            ->from('`#__jshopping_products`')
            ->where('`product_id` IN ('.implode(', ', $products).')');
        $result = $db->setQuery($query)->loadObjectList('product_id');
        return $result;
    }

    public function getFilterCategory()
    {
        $categories = buildTreeCategory(0,1,0);
        $all = new stdClass();
        $all->category_id = '';
        $all->name = JText::_('JSELECT');
        $all->disable = false;
        $categories = array_merge(array($all), $categories);
        $categoriesSelect = JHTML::_('select.genericlist', $categories, '', 'class="inputbox" size="1"', 'category_id', 'name', '', 'filter_categories');
        return $categoriesSelect;
    }

    public function getFilteredProducts($category=0, $dateFrom='', $dateTo='', $text='', array $excludeProducts=array(), array $includeProducts=array(), array $params=array())
    {
        $root_id = !empty($params['root_id']) ? (int)$params['root_id'] : 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT p.product_id AS id, p.product_ean AS sku, p.`name_'.$this->langTag.'` AS name')
            ->from('#__jshopping_products as p')
            ->innerJoin('`#__jshopping_products_to_categories` as c USING(`product_id`)')
            ->where('p.parent_id = 0')
            ->where('p.product_publish = 1')
        ;

        if($category>0)
        {
            $query->where('c.category_id = '.(int)$category);
        }

        if($dateFrom!='')
        {
            $d = new JDate($dateFrom);
            $dateFrom = $d->format('Y-m-d').' 00:00:00';
            $query->where('p.date_modify >= '.$db->quote($dateFrom));
        }

        if($dateTo!='')
        {
            $d = new JDate($dateTo);
            $dateTo = $d->format('Y-m-d').' 23:59:59';
            $query->where('p.date_modify <= '.$db->quote($dateTo));
        }

        if(!empty($text))
        {
            $text = StringHelper1::strtolower($text);
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($text), true) . '%'));
            $query->where('p.`name_'.$this->langTag.'` LIKE LOWER(' . $search . ')');
        }

        if(count($excludeProducts))
        {
            $query->where('p.product_id NOT IN ('.implode(', ', $excludeProducts).')');
        }

        if(count($includeProducts))
        {
            $query->where('p.product_id NOT IN ('.implode(', ', $includeProducts).')');
        }

        $result = $db->setQuery($query)->loadObjectList();
        return $result;
    }

    function getCategoriesForParent($parentId, $type='vk')
    {

        $this->query->clear()
            ->select('c.`category_id` AS id, c.`category_parent_id` AS parent_id, c.`name_'.$this->langTag.'` as name')
            ->select('(SELECT COUNT(*) FROM #__jshopping_categories AS sc WHERE sc.`category_parent_id` = c.`category_id`) AS hasChild')
            ->from('#__jshopping_categories AS c')
            ->where('c.category_publish = 1')
            ->where('c.`category_parent_id` = '.(int)$parentId)
            ->order('c.`ordering` ASC');

        if($type == 'ya'){
            $this->query->select('ya.*')
                ->leftJoin('#__argensyml_yacat as ya ON ya.`category_id` = c.`category_id`');
        }
        else{
            $this->query->select('vk_id, vk_section_id, vk_name')
                ->leftJoin('#__argensyml_cat_assoc as vk ON vk.`category_id` = c.`category_id`');
        }
        $result = $this->db->setQuery($this->query)->loadObjectList();

        if(!is_array($result))
        {
            return array();
        }

        return $result;
    }

    private function getRelated($product_id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('product_related_id')
            ->from('#__jshopping_products_relations')
            ->where('product_id = '.(int)$product_id);
        $result = $db->setQuery($query,0,30)->loadColumn();
        if(!is_array($result) || !count($result)){
            return '';
        }
        return implode(',',$result);
    }

    private function getImages($product_id, $limit, $image_path, $noimage)
    {
        $return = array();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('image_name')
            ->from('#__jshopping_products_images')
            ->where('product_id = '.(int)$product_id)
            ->order('ordering')
        ;
        $result = $db->setQuery($query,0,$limit)->loadColumn();
        if(!is_array($result) || !count($result)){
            return $return;
        }

        foreach ($result as $imgName){
            $return[] = $image_path.'/'.$imgName;
        }

        return $return;
    }
}