<?php
/**
 * Yandex.Market XML
 *
 * @version 	2.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015-2019. All rights reserved.
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

class hikashopConnector extends Mainconnector
{
    private $enableShop, $characteristics, $customfields;

    function __construct()
    {
        parent::__construct();

        $helper = JPATH_ROOT . '/administrator/components/com_hikashop/helpers/helper.php';
        $product = JPATH_ROOT . '/administrator/components/com_hikashop/classes/product.php';

        if(JFile::exists($helper) && JFile::exists($product))
        {
            include_once $helper;
            include_once $product;
            require_once JPATH_ROOT . '/administrator/components/com_hikashop/types/categorysub.php';
            $this->enableShop = true;
        }
        else{
            $this->enableShop = false;
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
            ->select('c.`category_id`, c.`category_parent_id`, c.`category_name`')
            ->from('#__hikashop_category AS c')
            ->where('c.category_published = 1')
            ->where('c.category_type = '.$this->db->quote('product'))
            ->order('c.category_parent_id ASC, c.category_ordering ASC');

        if($fileType == 'v'){
            $this->query->select('v.`vk_id`')
                ->leftJoin('`#__argensyml_cat_assoc` AS v ON v.`category_id` = c.`category_id`');
        }

        $result = $this->db->setQuery($this->query)->loadObjectList();

        if(!is_array($result) || !count($result))
        {
            $result = array();
        }

	    if(count($this->includeCategories))
	    {
	    	$aCats = array();
		    foreach ( $result as $item ) {
			    $aCats[(int)$item->category_id] = $item;
	    	}
		    $catIds = array();

		    foreach ( $this->includeCategories as $includeCategory ) {
			    $catIds[] = $includeCategory;

			    while (isset($aCats[$aCats[$includeCategory]->category_parent_id])){
				    $includeCategory = $aCats[$includeCategory]->category_parent_id;
				    $catIds[] = $includeCategory;
			    }
	    	}

		    foreach ( $result as $k => $v ) {
			    if(!in_array($v->category_id, $catIds)){
			    	unset($result[$k]);
			    }
	    	}
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
            $this->setError('Hikashop not installed.');
            return array();
        }

        $this->query->clear()
            ->select('p.`product_id`')
            ->from('`#__hikashop_product` AS p')
            ->where('p.`product_parent_id` = 0')
            ->where('p.`product_published` = 1');

        if(count($this->includeCategories))
        {
            $this->query->leftJoin('#__hikashop_product_category as c ON c.product_id = p.product_id');
        }

        if (count($this->includeCategories) && count($this->includeProducts))
        {
            $this->query->where('(`c`.`category_id` IN (' . implode(',', $this->includeCategories) . ')
                OR `p`.`product_id` IN (' . implode(',', $this->includeProducts) . '))');
        }
        else if(count($this->includeCategories))
        {
            $this->query->where('`c`.`category_id` IN (' . implode(',', $this->includeCategories) . ')');
        }
        else if(count($this->includeProducts))
        {
            $this->query->where('`p`.`product_id` IN (' . implode(',', $this->includeProducts) . ')');
        }

        if (count($this->excludeProducts)) {
            $this->query->where('`p`.`product_id` NOT IN (' . implode(',', $this->excludeProducts) . ')');
        }

        $fileType = $this->item->get('type', 'y');

        $ids = $this->db->setQuery($this->query, $limitstart, $limit)->loadColumn();

        if(is_array($ids) && count($ids))
        {
            $productClass = hikashop_get('class.product');
            $productClass->getProducts($ids, 'import');

            unset($productClass->all_products, $productClass->variants);

            $config = hikashop_config();
            $currencyCode = $this->item->get('currency', 'RUB');
            $main_currency_id = $config->get('main_currency',1);
            $uploadfolder = $config->get('uploadfolder');

            $currencyClass = hikashop_get('class.currency');
            $currencyId = $this->getCurrId($currencyCode);

            $uri = JUri::getInstance();
            $siteUrl = $uri->toString(array('scheme', 'host'));

            foreach($productClass->products as $product)
            {
                $oldPrice = 0;
                if(is_array($product->prices) && count($product->prices))
                {
                    $oPrice = $this->getMinCountPrice($product->prices);
                    $price = $currencyClass
                        ->convertUniquePrice($oPrice->price_value, $oPrice->price_currency_id, $currencyId);
                    if($product->product_msrp > 0){
                        $oldPrice = ($currencyId == 0) ? 0 : $currencyClass
                            ->convertUniquePrice($product->product_msrp, $main_currency_id, $currencyId);
                    }
                }
                else
                {
                    $price = ($currencyId == 0) ? 0 : $currencyClass
                        ->convertUniquePrice($product->product_msrp, $main_currency_id, $currencyId);
                }

                if((float)$price == 0){
                    $messages[] = 'Product id='.$product->product_id.' name="'.$product->product_name.'" error. Price  not set.';
                    continue;
                }

                $vendor = $this->getManufacturerName($product->product_manufacturer_id);

                $weightHelper = hikashop_get('helper.weight');
                $weight = $weightHelper->convert($product->product_weight, $product->product_weight_unit, 'kg');

                if($this->item->get('canonical', 0) && !empty($product->product_canonical)){
                    $url = $siteUrl.$product->product_canonical;
                }
                else{
                    $link = 'index.php?option='.HIKASHOP_COMPONENT.'&ctrl=product&task=show&cid='.$product->product_id;
                    if(!empty($product->product_alias))
                    {
                        $link .= '&name='.$product->product_alias;
                    }

                    $url = hikashop_frontendLink($link);
                }

                if(strpos($url, '&') !== false)
                {
                    if(StringHelper1::strpos($url, JUri::root(false, '')) !== false)
                    {
                        $url = str_replace(JUri::root(false, ''), '', $url);
                    }

                    $url = JRoute::_($url, false);
                    $url = mb_substr($url, 1);
                }

                if(StringHelper1::strpos($url, JUri::root(false, '')) === false)
                {
                    $url = JUri::root(false, ''). $url;
                }

                $images = array();
                $imgLimit = $this->item->get('images_limit', 1);
                if(is_array($product->images) && count($product->images)){
                    for ($i=0; $i<$imgLimit;$i++) {
                        if(empty($product->images[$i])){
                            continue;
                        }
                        $images[] = JUri::root(false, '').$uploadfolder.$product->images[$i]->file_path;
                    }
                }

                if(count($this->includeCategories) && count($product->categories) > 1){
	                $categoryId = 0;
	                foreach ( $product->categories as $category ) {
		                if(in_array($category, $this->includeCategories)){
			                $categoryId = $category;
			                break;
		                }
	                }
                }
                else{
	                $categoryId = isset($product->categories[0]) ? $product->categories[0] : 0;
                }

                $offer = new offetObject();
                $offer->product_id = $product->product_id;
                $offer->group_id = 0;
                $offer->product_quantity = $product->product_quantity;
                $offer->url = $url;
                $offer->price = $price;
                $offer->currencyId = $currencyCode;
                $offer->categoryId = $categoryId;
                $offer->vendor = $vendor;
                $offer->vendorCode = $product->product_code;
                $offer->picture = $images;
                $offer->name = $product->product_name;
                $offer->description = $product->product_description;
                $offer->downloadable = ($product->product_weight > 0) ? 'false' : 'true';
                $offer->weight = $weight;
                $offer->old_price = $oldPrice;

                //Характеристики
                if ($this->item->get('attributes', 'none') == 'params')
                {
                    $offer->params = array();
                    $characteristics = $this->getCharacteristics($product->product_id);
                    if (is_array($characteristics) && count($characteristics))
                    {
                        foreach ($characteristics as $characteristic)
                        {
                            $offer->params[] = array(
                                'value' => $characteristic['value'],
                                'attrib' => array(
                                    0 => array(
                                        'name' => 'name',
                                        'value' => $characteristic['name']
                                    )
                                )
                            );
                        }
                    }
                }
                else if($this->item->get('attributes', 'none') == 'customfields')
                {
                    $offer->params = array();
                    $customfields = $this->getCustomfields($product);
                    if (is_array($customfields) && count($customfields))
                    {
                        foreach ($customfields as $customfield)
                        {
                            $offer->params[] = array(
                                'value' => $customfield['value'],
                                'attrib' => array(
                                    0 => array(
                                        'name' => 'name',
                                        'value' => $customfield['name']
                                    )
                                )
                            );
                        }
                    }
                }

                if($this->item->get('related', 0)){
                    $related = $this->getRelated($product->product_id);
                    if(!empty($related)){
                        $offer->related = $related;
                    }
                }

                $offers[]= $offer;
            }
        }


        return $offers;
    }

    public function loadCustomParams($params, $field)
    {
        $values = json_decode($field->value, true);
        $include_categories = (empty($values["include_categories"])) ? array() : $values["include_categories"];

        $cat = new hikashopCategorysubType;
        $cat->type = 'product';
        $categoriesSelect = $cat->displayMultiple($field->name . '[include_categories][]', $include_categories);

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

        $related = (empty($values["related"])) ? 0 : $values["related"];
        $canonical = (empty($values["canonical"])) ? 0 : $values["canonical"];

        $options = array();
        $options[] = JHTML::_('select.option', 'none', JText::_('JNO'));
//        $options[] = JHTML::_('select.option', 'attr', JText::_('COM_ARGENSYML_SHOP_ATTRIBUTES'));
        $options[] = JHTML::_('select.option', 'params', JText::_('COM_ARGENSYML_SHOP_PARAMS'));
        $options[] = JHTML::_('select.option', 'customfields', JText::_('COM_ARGENSYML_SHOP_CUSTOMFIELDS'));
        $attributes = (empty($values["attributes"])) ? 'none' : $values["attributes"];

        $customParams = array(
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_INCLUDE_CATS'),
                'input' => $categoriesSelect
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
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_USE_CANONICAL_URL'),
                'input' => JHTML::_('select.booleanlist', $field->name.'[canonical]', 'class="inputbox" ', $canonical)
            )
        );
        return $customParams;
    }

    public function getFilterCategory()
    {
        $cat = new hikashopCategorysubType;
        $cat->type = 'product';
        $categoriesSelect = $cat->displaySingle('filter_categories', '');
        return $categoriesSelect;
    }

    public function getFilteredProducts($category=0, $dateFrom='', $dateTo='', $text='', array $excludeProducts=array(), array $includeProducts=array(), array $params=array())
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT p.product_id AS id, p.product_code AS sku, p.product_name AS name')
            ->from('#__hikashop_product AS p')
            ->leftJoin('#__hikashop_product_category as c ON c.product_id = p.product_id')
            ->where('product_published = 1')
            ->group('p.product_id')
        ;

        if($category>0)
        {
            $query->where('c.category_id = '.(int)$category);
        }

        if($dateFrom!='')
        {
            if(StringHelper1::strlen($dateFrom) < 11) $dateFrom .= ' 00:00:00';
            $d = new JDate($dateFrom);
            $dateFrom = $d->toUnix();
            $query->where('p.product_modified >= '.$db->quote($dateFrom));
        }

        if($dateTo!='')
        {
            if(StringHelper1::strlen($dateTo) < 11) $dateTo .= ' 23:59:59';
            $d = new JDate($dateTo);
            $dateTo = $d->toUnix();
            $query->where('p.product_modified <= '.$db->quote($dateTo));
        }

        if(!empty($text))
        {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(StringHelper1::strtolower($text)), true) . '%'));
            $query->where('p.product_name LIKE LOWER(' . $search . ')');
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

    private function getMinCountPrice($aPrices)
    {
        $count = count($aPrices);
        $minVal = 0;
        for($i=0; $i<$count; $i++)
        {
            if($i == 0)
            {
                $minElem = $i;
                $minVal = $aPrices[$i]->price_min_quantity;
                continue;
            }

            if($minVal > $aPrices[$i]->price_min_quantity){
                $minElem = $i;
                $minVal = $aPrices[$i]->price_min_quantity;
            }
        }
        return $aPrices[$minElem];
    }

    private function getCurrId($currencyCode)
    {
        $this->query->clear()
            ->select('`currency_id`')
            ->from('`#__hikashop_currency`')
            ->where('`currency_code` = '.$this->db->quote($currencyCode));
        return (int)$this->db->setQuery($this->query)->loadResult();
    }
    private function getManufacturerName($manufacturer_id)
    {
        $this->query->clear()
            ->select('`category_name`')
            ->from('`#__hikashop_category`')
            ->where('`category_type` = '.$this->db->quote('manufacturer'))
            ->where('`category_id` = '.$this->db->quote($manufacturer_id));
        return (string)$this->db->setQuery($this->query)->loadResult();
    }

    private function getProducts(array $products)
    {
        if(!count($products)){
            return array();
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`product_id`, `product_code` AS sku, `product_name` AS name')
            ->from('`#__hikashop_product`')

            ->where('`product_id` IN ('.implode(', ', $products).')');
        $result = $db->setQuery($query)->loadObjectList('product_id');
        return $result;
    }

    function getCategoriesForParent($parentId, $type='vk')
    {
        if($parentId == 0) $parentId = 1;

        $this->query->clear()
            ->select('c.`category_id` AS id, c.`category_parent_id` AS parent_id, c.`category_name` AS name')
            ->select('(SELECT COUNT(*) FROM #__hikashop_category AS sc WHERE sc.`category_parent_id` = c.`category_id`) AS hasChild')
            ->from('#__hikashop_category AS c')
            ->where('c.category_published = 1')
            ->where('c.category_type = '.$this->db->quote('product'))
            ->where('c.`category_parent_id` = '.(int)$parentId)
            ->order('c.`category_ordering` ASC');

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
            ->from('#__hikashop_product_related')
            ->where('product_id = '.(int)$product_id)
            ->where('product_related_type = '.$db->quote('related'))
        ;
        $result = $db->setQuery($query,0,30)->loadColumn();
        if(!is_array($result) || !count($result)){
            return '';
        }
        return implode(',',$result);
    }

    private function getCharacteristics($product_id)
    {
        $return = array();
        $aCharacteristics = $this->loadCharacteristics();
        if(!count($aCharacteristics)){
            return $return;
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('variant_characteristic_id')
            ->from('#__hikashop_variant')
            ->where('variant_product_id = '.(int)$product_id);
        $result = $db->setQuery($query)->loadColumn();
        if(!is_array($result) || !count($result)){
            return $return;
        }

        foreach ($result as $key)
        {
            $key = (int)$key;
            if(!isset($aCharacteristics[$key]) || $aCharacteristics[$key]['parent'] == 0
                || !isset($aCharacteristics[$aCharacteristics[$key]['parent']])){
                continue;
            }

            $return[] = array(
                'name' => $aCharacteristics[$aCharacteristics[$key]['parent']]['value'],
                'value' => $aCharacteristics[$key]['value']
            );
        }

        return $return;
    }

    private function loadCharacteristics()
    {
        if(is_array($this->characteristics)){
            return $this->characteristics;
        }

        $this->characteristics = array();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('characteristic_id, characteristic_parent_id, characteristic_value')
            ->from('#__hikashop_characteristic')
        ;

        $result = $db->setQuery($query)->loadObjectList();

        if(is_array($result) && count($result)){
            foreach ($result as $v){
                $this->characteristics[(int)$v->characteristic_id] = array(
                    'parent' => (int)$v->characteristic_parent_id,
                    'value' => $v->characteristic_value);
            }
        }
        return $this->characteristics;
    }

    private function getCustomfields($product)
    {
        $return = array();
        $aCustomfields = $this->loadCustomfields();
        if(!count($aCustomfields)){
            return $return;
        }

        foreach ($aCustomfields as $key => $customfield)
        {
            if(empty($product->$key)){
                continue;
            }
            $productValue = $product->$key;
            $customName = $customfield['field_realname'];

            switch ($customfield['field_type']){
                case '':

                case 'text':
	            case 'link':
                case 'textarea':
                case 'wysiwyg':
                case 'date':
                case 'zone':
                case 'coupon':
                case 'customtext':
                    $customValue = $productValue;
                    if(!empty($customValue)){
                        $return[] = array(
                            'name' => $customName,
                            'value' => $customValue
                        );
                    }
                    break;
                case 'radio':
                case 'singledropdown':
                    $customValue = isset($customfield['field_value'][$productValue])
                        ? $customfield['field_value'][$productValue] : '';
                    if(!empty($customValue)){
                        $return[] = array(
                            'name' => $customName,
                            'value' => $customValue
                        );
                    }
                    break;
                case 'checkbox':
                case 'multipledropdown':
                    $productValues = explode(',', $productValue);
                    if(count($productValues)){
                        foreach ($productValues as $productVal){
                            $customValue = isset($customfield['field_value'][$productVal])
                                ? $customfield['field_value'][$productVal] : '';
                            if(!empty($customValue)){
                                $return[] = array(
                                    'name' => $customName,
                                    'value' => $customValue
                                );
                            }
                        }
                    }
                    break;
                case 'file':
                case 'image':
                case 'ajaxfile':
                case 'ajaximage':
                default:
                    break;
            }
        }

        return $return;
    }

    private function loadCustomfields()
    {
        if(is_array($this->customfields)){
            return $this->customfields;
        }

        $this->customfields = array();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('field_id, field_type, field_realname, field_value, field_namekey')
            ->from('#__hikashop_field')
            ->where('field_published = 1')
            ->where('field_table = '.$db->quote('product'))
        ;

        $result = $db->setQuery($query)->loadObjectList();

        if(is_array($result) && count($result)){
            foreach ($result as $v){
                $this->customProductFields[] = $v->field_namekey;
                $values = array();
                if(!empty($v->field_value)){
                    $field_data = explode("\n", $v->field_value);
                    if(count($field_data)){
                        foreach($field_data as $fd) {
                            list($fk,$fv,$fsort) = explode('::', $fd);
                            if(!empty($fk) && !empty($fv)){
                                $values[$fk] = $fv;
                            }
                        }
                    }
                }
                $this->customfields[$v->field_namekey] = array(
                    'field_realname' => $v->field_realname,
                    'field_type' => $v->field_type,
                    'field_value' => $values);
            }
        }

        return $this->customfields;
    }


}