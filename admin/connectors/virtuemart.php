<?php
/**
 * Yandex.Market XML
 *
 * @version    1.0
 * @author        Arkadiy, Joomline
 * @copyright    © 2015. All rights reserved.
 * @license    GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;
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

class virtuemartConnector extends Mainconnector
{
    private $langTag,
        $enableShop,
        $attributes,
        $enable_child;

    function __construct()
    {
        parent::__construct();

        $config = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
        $calculationh = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/calculationh.php';
        $prodModel = JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/product.php';
        $shopfunctions = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/shopfunctions.php';

        if (JFile::exists($config)
            && JFile::exists($calculationh)
            && JFile::exists($prodModel)
            && JFile::exists($shopfunctions)
        ) {
            if(!class_exists('vObject')){
                include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/vobject.php';
            }
            if(!class_exists('VmModel')){
                include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/vmmodel.php';
            }
            include_once $config;
            include_once $calculationh;
            include_once $prodModel;
            include_once $shopfunctions;
            include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/html.php';
            $this->enableShop = true;
            VmConfig::loadConfig();
            $this->langTag = (string)VmConfig::get('vmlang', 'en_gb');
        } else {
            $this->enableShop = false;
        }


        if(is_null($this->item->get('shopSettings', null)))
        {
            $this->enableShop = false;
            return;
        }

        $this->attributes = $this->item->get('attributes', 'none');
        $this->enable_child = $this->item->get('enable_child', 0);
    }

    function getCategories()
    {
        if (!$this->enableShop) {
            return array();
        }

        $fileType = $this->item->get('type', 'y');

        $this->query->clear()
            ->select('`cc`.`category_parent_id`, `cc`.`category_child_id` AS `category_id`, `cl`.`category_name`')
            ->from('`#__virtuemart_categories` AS c')
            ->from('`#__virtuemart_category_categories` AS cc')
            ->from('`#__virtuemart_categories_' . $this->langTag . '` AS cl')
            ->where('`cc`.`category_child_id` = `c`.`virtuemart_category_id`')
            ->where('`cl`.`virtuemart_category_id` = `cc`.`category_child_id`')
            ->where('`c`.`published` = 1')
            ->order('`cc`.`category_child_id` ASC, `cl`.`category_name` ASC');

        if($fileType == 'v'){
            $this->query->select('v.`vk_id`')
                ->leftJoin('`#__argensyml_cat_assoc` AS v ON v.`category_id` = c.`virtuemart_category_id`');
        }

        if (count($this->includeCategories)) {
            $this->query->where('`c`.`virtuemart_category_id` IN (' . implode(',', $this->includeCategories) . ')');
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
        $app = JFactory::getApplication('adminisreator');
        $messages = array();

        if (!$this->enableShop) {
            $message = 'Virtuemart not installed.';
            $this->setError($message);
            return array();
        }

        $productModel = new VirtueMartModelProduct();
        $calculator = calculationHelper::getInstance();

        $root_id = $this->item->get('root_id', 0);

        $this->query->clear()
            ->select('DISTINCT p.virtuemart_product_id, p.product_parent_id,
                p.product_sku, p.virtuemart_vendor_id, p.product_in_stock, p.product_weight, p.product_weight_uom,
                pl.product_name, pl.product_s_desc,
                pp.product_tax_id, pp.product_discount_id, pp.product_price,
                pp.product_override_price, pp.override, pp.product_currency,
                ml.mf_name, ml.virtuemart_manufacturer_id, ml.mf_email,
                c.virtuemart_category_id')
            ->from('#__virtuemart_products AS p')
            ->leftJoin('#__virtuemart_product_categories as c ON c.virtuemart_product_id = p.virtuemart_product_id')
            ->leftJoin('#__virtuemart_categories as cc on cc.virtuemart_category_id = c.virtuemart_category_id')
            ->leftJoin('#__virtuemart_products_' . $this->langTag . ' AS pl ON pl.virtuemart_product_id = p.virtuemart_product_id')
            ->leftJoin('#__virtuemart_product_prices AS pp ON pp.virtuemart_product_id = p.virtuemart_product_id')
            ->leftJoin('#__virtuemart_product_manufacturers AS m ON m.virtuemart_product_id = p.virtuemart_product_id')
            ->leftJoin('#__virtuemart_manufacturers_' . $this->langTag . ' AS ml ON m.virtuemart_manufacturer_id = ml.virtuemart_manufacturer_id')
            ->where('p.published = 1')
            ->where('pl.product_name != ""')
            ->group('p.virtuemart_product_id')
            ->where('cc.published = 1')
            ->where('p.product_parent_id = '.$this->db->quote($root_id));

        if (count($this->includeCategories) && count($this->includeProducts))
        {
            $this->query->where('(`cc`.`virtuemart_category_id` IN (' . implode(',', $this->includeCategories) . ')
                OR `p`.`virtuemart_product_id` IN (' . implode(',', $this->includeProducts) . '))');
        }
        else if(count($this->includeCategories))
        {
            $this->query->where('`cc`.`virtuemart_category_id` IN (' . implode(',', $this->includeCategories) . ')');
        }
        else if(count($this->includeProducts))
        {
            $this->query->where('`p`.`virtuemart_product_id` IN (' . implode(',', $this->includeProducts) . ')');
        }

        if (count($this->excludeProducts)) {
            $this->query->where('`p`.`virtuemart_product_id` NOT IN (' . implode(',', $this->excludeProducts) . ')');
        }

        $rows = $this->db->setQuery($this->query, $limitstart, $limit)->loadObjectList('virtuemart_product_id');

        if(!is_array($rows) || !count($rows))
        {
            return null;
        }

        $parentProductIds = array_keys($rows);

        $childRows = array();
        if ($this->enable_child) {
            //запрос дочерних товаров
            $this->query->clear()
                ->select('DISTINCT p.virtuemart_product_id, p.product_parent_id,
                p.product_sku, p.virtuemart_vendor_id, p.product_in_stock, p.product_weight, p.product_weight_uom,
                pl.product_name, pl.product_s_desc,
                pp.product_tax_id, pp.product_discount_id, pp.product_price,
                pp.product_override_price, pp.override, pp.product_currency,
                ml.mf_name, ml.virtuemart_manufacturer_id, ml.mf_email,
                0 as virtuemart_category_id')
                ->from('#__virtuemart_products AS p')
                ->leftJoin('#__virtuemart_products_' . $this->langTag . ' AS pl ON pl.virtuemart_product_id = p.virtuemart_product_id')
                ->leftJoin('#__virtuemart_product_prices AS pp ON pp.virtuemart_product_id = p.virtuemart_product_id')
                ->leftJoin('#__virtuemart_product_manufacturers AS m ON m.virtuemart_product_id = p.virtuemart_product_id')
                ->leftJoin('#__virtuemart_manufacturers_' . $this->langTag . ' AS ml ON m.virtuemart_manufacturer_id = ml.virtuemart_manufacturer_id')
                ->where('p.published = 1')
                ->where('p.product_parent_id > 0')
                ->where('p.product_parent_id IN (' . implode(', ', $parentProductIds) . ')')
                ->group('p.virtuemart_product_id');

            $childRows = $this->db->setQuery($this->query)->loadObjectList('virtuemart_product_id');

            if (!is_array($childRows) || count($childRows) == 0) {
                $this->enable_child = false;
            }
        }

        $currencyCode = $this->item->get('currency', 'RUB');
        $currencyId = $this->getCurrId($currencyCode);

        if (!$currencyId) {
            $message = 'Currency ID for currency ' . $currencyCode . ' not find.';
            $this->setError($message);
            return array();
        }

        $customFields = array();
        $settingsCustomFields = $this->item->get('custom_fields', array());

        if ($this->attributes == 'params' && is_array($settingsCustomFields) && count($settingsCustomFields))
        {
            $customFields = $this->getCustomFields();
            $customFieldValues = $this->getCustomFieldValues();
        }

        $parentChilds = array();
        if ($this->enable_child) {
            foreach ($childRows as $product) {
                if ($product->product_parent_id > 0 && isset($rows[$product->product_parent_id])) {
                    $parentChilds[$product->product_parent_id][] = $product->virtuemart_product_id;
                }
            }

            $rows = $rows + $childRows;
        }

        foreach ($rows as $k => $product) {
            $parentId = $product->product_parent_id;
            $productId = $product->virtuemart_product_id;
            $parentProduct = new stdClass();

            if ($parentId != $root_id) {
                unset($rows[$k]);
                if (!isset($rows[$parentId])) {
                    continue;
                }
                $parentProduct = $rows[$parentId];
            }

            if ($parentId != $root_id && !isset($rows[$product->product_parent_id])) {
                continue;
            }

            if ($this->enable_child && $parentId ==  $root_id && isset($parentChilds[$productId]) && count($parentChilds[$productId])) {//пропускаем родительский если есть дочерние
                continue;
            }

            if (method_exists($productModel, 'getRawProductPrices')) {
                $productModel->getRawProductPrices($product, 0, array(1), 1);
            }

            $prices = $calculator->getProductPrices($product);
            $oldPrice = 0;
            $price = $prices['salesPrice'];

            if((float)$price > 0 && !empty($prices['basePriceVariant']) && $prices['basePriceVariant'] > $prices['salesPrice']){
                $oldPrice = $prices['basePriceVariant'];
            }

            if ((float)$price == 0 && $parentId != $root_id)
            {
                if (method_exists($productModel, 'getRawProductPrices')) {
                    $productModel->getRawProductPrices($parentProduct, 0, array(1), 1);
                }

                $prices = $calculator->getProductPrices($parentProduct);
                $price = $prices['salesPrice'];

                if(!empty($prices['basePriceVariant']) && $prices['basePriceVariant'] > $prices['salesPrice']){
                    $oldPrice = $prices['basePriceVariant'];
                }

            }

            if ((float)$price == 0) {
                $messages[] = 'Product id=' . $productId . ' name="' . $product->product_name . '" error. Price  not set.';
                continue;
            }

            if($price == 0)
            {
                $price = (float)$product->product_price;
            }

            $vendor = $product->mf_name;

            if ($product->product_weight == 0 && $parentId != $root_id) {
                $weight = ShopFunctions::convertWeightUnit($parentProduct->product_weight, $parentProduct->product_weight_uom, 'KG');
            } else {
                $weight = ShopFunctions::convertWeightUnit($product->product_weight, $product->product_weight_uom, 'KG');
            }

            if (!$this->item->get('child_url', 0) && $parentId != $root_id) {
	            $urlProductId = $parentProduct->virtuemart_product_id;
	            $urlCatId = $parentProduct->virtuemart_category_id;
            } else {
	            $urlProductId = $productId;
	            $urlCatId = $product->virtuemart_category_id;
            }
	        $url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $urlProductId . '&virtuemart_category_id=' . $urlCatId);

	        $url = JUri::root(false, '') . substr($url, 1);

            $image = $this->getImage($productId);

            if (empty($image) && $parentId != $root_id) {
                $image = $this->getImage($parentProduct->virtuemart_product_id);
            }
            $child_name = $this->item->get('child_name', '');
            $child_desc = $this->item->get('child_desc', '');
            $product_name = (empty($child_name) && $parentId != $root_id) ? $parentProduct->product_name : $product->product_name;
            $product_desc = (empty($child_desc) && $parentId != $root_id) ? $parentProduct->product_s_desc : $product->product_s_desc;
            $category_id = ($parentId != $root_id) ? $parentProduct->virtuemart_category_id : $product->virtuemart_category_id;

            $offer = new offetObject();
            $offer->product_id = $product->virtuemart_product_id;
            $offer->group_id = $product->product_parent_id;
            $offer->product_quantity = $product->product_in_stock;
            $offer->url = $url;
            $offer->price = $price;
            $offer->currencyId = $currencyCode;
            $offer->categoryId = $category_id;
            $offer->vendor = $vendor;
            $offer->vendorCode = $product->product_sku;
            $offer->picture = $image;
            $offer->name = $product_name;
            $offer->description = $product_desc;
            $offer->downloadable = ($weight > 0) ? 'false' : 'true';
            $offer->weight = $weight;
            $offer->old_price = $oldPrice;

            if($this->item->get('related', 0)){
                $related = $this->getRelated($product->virtuemart_product_id);
                if(!empty($related)){
                    $offer->related = $related;
                }
            }

            if ($this->item->get('email_country', '') && !empty($product->mf_email)) {//страна происхождения из мыла производителя
                $offer->country_of_origin = $product->mf_email;
            }

            $settingsCustomFields = $this->item->get('custom_fields', array());
            if ($this->attributes == 'params' && is_array($settingsCustomFields) && count($settingsCustomFields)) {//дополнительные поля вирта
                if (isset($customFieldValues[$productId]) || isset($customFieldValues[$parentId])) {
                    $childField = isset($customFieldValues[$productId]) ? $customFieldValues[$productId] : array();
                    $parentField = ($parentId != $root_id && isset($customFieldValues[$parentId])) ? $customFieldValues[$parentId] : array();
                    $offer->params = array();

                    foreach ($settingsCustomFields as $v) {
                        $field = false;
                        if (isset($childField[$v]) && count($childField[$v])) {
                            $field = $childField[$v];
                        } else if (isset($parentField[$v]) && count($parentField[$v])) {
                            $field = $parentField[$v];
                        }

                        if ($field === false) {
                            continue;
                        }

                        $fieldSettings = $customFields[$v];

                        $attribs = array();
                        $attribs[] = array('name' => 'name', 'value' => $fieldSettings->custom_title);

                        if (!empty($fieldSettings->custom_desc)) {
                            $attribs[] = array('name' => 'unit', 'value' => $fieldSettings->custom_desc);
                        }

                        foreach ($field as $f) {
                            $offer->params[] = array('value' => $f, 'attrib' => $attribs);
                        }
                    }
                }
            }

            $offers[] = $offer;
        }
        return $offers;
    }


    private function getCurrId($currencyCode)
    {
        $this->query->clear()
            ->select('`virtuemart_currency_id`')
            ->from('`#__virtuemart_currencies`')
            ->where('`currency_code_3` = ' . $this->db->quote($currencyCode))
            ->where('`published` = 1');
        return (int)$this->db->setQuery($this->query)->loadResult();
    }

    function getImage($id)
    {
        $return = array();
        $limit =  $this->item->get('images_limit', 1);
        $this->query->clear()
            ->select('`m`.`file_url`')
            ->from('`#__virtuemart_medias` AS m')
            ->innerJoin('`#__virtuemart_product_medias` AS pm ON `pm`.`virtuemart_media_id` = `m`.`virtuemart_media_id`')
            ->where('`m`.`published` = 1')
            ->where('`pm`.`virtuemart_product_id` = ' . (int)$id)
            ->order('`pm`.`ordering`, `pm`.`id`');

        $result = $this->db->setQuery($this->query, 0, $limit)->loadColumn();
        if(!is_array($result) || !count($result)){
            return $return;
        }

        foreach ($result as $imgName){
            $return[] = JUri::root(false, '').$imgName;
        }

        return $return;
    }

    public function loadCustomParams($params, $field)
    {
        if(!is_file(JPATH_ROOT.'/administrator/components/com_virtuemart/helpers/shopfunctions.php')){
            return array();
        }
        include_once JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/shopfunctions.php';

        $values = json_decode($field->value, true);
        $include_categories = (empty($values["include_categories"])) ? array() : $values["include_categories"];
        $categories = ShopFunctions::categoryListTree($include_categories);
        $selectAllCats = (is_array($include_categories) && in_array('-1', $include_categories)) ? ' selected="selected"' : '';
        $categories = '<option value="-1"' . $selectAllCats . '>' . JText::_('JALL') . '</option>' . $categories;
        $categoriesSelect = '<select name="' . $field->name . '[include_categories][]" class="inputbox" size="10" multiple = "multiple">';
        $categoriesSelect .= $categories;
        $categoriesSelect .= '</select>';

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

        $root_id = (empty($values["root_id"])) ? 0 : $values["root_id"];
        $enable_child = (empty($values["enable_child"])) ? 0 : $values["enable_child"];
        $attributes = (empty($values["attributes"])) ? 'none' : $values["attributes"];
        $related = (empty($values["related"])) ? 0 : $values["related"];
        $options = array();
        $options[] = JHTML::_('select.option', 'none', JText::_('JNO'));
//        $options[] = JHTML::_('select.option', 'attr', JText::_('COM_ARGENSYML_SHOP_ATTRIBUTES'));
        $options[] = JHTML::_('select.option', 'params', JText::_('COM_ARGENSYML_SHOP_PARAMS'));

        $customParams = array(
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_ROOT_ID'),
                'input' => '<input type="text" name="' . $field->name . '[root_id]" id="vm_root_id" value="' . $root_id . '"/>'
            ),
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
                'input' => JHTML::_('select.genericlist', $options, $field->name . '[attributes]', 'class="inputbox" ', 'value', 'text', $attributes)
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_SHOP_CHILD_PRODUCT_LABEL'),
                'input' => JHTML::_('select.booleanlist', $field->name . '[enable_child]', '', $enable_child)
            ),
            array(
                'label' => JText::_('COM_ARGENSYML_ENABLE_RELATED'),
                'input' => JHTML::_('select.booleanlist', $field->name.'[related]', 'class="inputbox" ', $related)
            )
        );
        $customFieldsOptions = $this->getCustomFieldsOptions();

        if (count($customFieldsOptions) && $attributes == 'params') {
            $custom_fields = (empty($values["custom_fields"])) ? array() : $values["custom_fields"];
            array_push($customParams, array(
                'label' => JText::_('COM_ARGENSYML_SHOP_CUSTOM_FIELDS_LABEL'),
                'input' => JHTML::_('select.genericlist', $customFieldsOptions, $field->name . '[custom_fields][]', 'class="inputbox" size="10" multiple = "multiple"', 'value', 'text', $custom_fields)
            ));
        }

        if ($enable_child) {
            $child_name = (empty($values["child_name"])) ? array() : $values["child_name"];
            array_push($customParams, array(
                'label' => JText::_('COM_ARGENSYML_SHOP_CHILD_NAME_LABEL'),
                'input' => JHTML::_('select.booleanlist', $field->name . '[child_name]', '', $child_name)
            ));
            $child_desc = (empty($values["child_desc"])) ? array() : $values["child_desc"];
            array_push($customParams, array(
                'label' => JText::_('COM_ARGENSYML_SHOP_CHILD_DESC_LABEL'),
                'input' => JHTML::_('select.booleanlist', $field->name . '[child_desc]', '', $child_desc)
            ));
            $child_url = (empty($values["child_url"])) ? array() : $values["child_url"];
            array_push($customParams, array(
                'label' => JText::_('COM_ARGENSYML_SHOP_CHILD_URL_LABEL'),
                'input' => JHTML::_('select.booleanlist', $field->name . '[child_url]', '', $child_url)
            ));
        }
        $enableMfEmailToCountry = (empty($values["email_country"])) ? 0 : $values["email_country"];
        array_push($customParams, array(
            'label' => JText::_('COM_ARGENSYML_SHOP_EMAIL_COUNTRY'),
            'input' => JHTML::_('select.booleanlist', $field->name . '[email_country]', '', $enableMfEmailToCountry)
        ));
        return $customParams;
    }

    private function getCustomFields()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('virtuemart_custom_id, custom_title, custom_desc')
            ->from('#__virtuemart_customs')
            ->where('custom_element = ' . $db->quote('param'));
        $result = $db->setQuery($query)->loadObjectList('virtuemart_custom_id');
        if (!is_array($result)) {
            $result = array();
        }
        return $result;
    }

    private function getCustomFieldValues()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('pr.virtuemart_product_id, pr.virtuemart_custom_id, pr.val, pv.value')
            ->from('#__virtuemart_product_custom_plg_param_values as pv')
            ->from('#__virtuemart_product_custom_plg_param_ref as pr')
            ->where('pr.val = pv.id');
        $result = $db->setQuery($query)->loadObjectList();
        if (!is_array($result) || !count($result)) {
            return array();
        }
        $return = array();
        foreach ($result as $v) {
            $return[$v->virtuemart_product_id][$v->virtuemart_custom_id][] = $v->value;
        }
        return $return;
    }

    private function getCustomFieldsOptions()
    {
        $result = $this->getCustomFields();
        $options = array();
        if (count($result)) {
            foreach ($result as $v) {
                $options[] = JHTML::_('select.option', $v->virtuemart_custom_id, JText::_($v->custom_title));
            }
        }
        return $options;
    }

    private function getProducts(array $products)
    {
        if(!count($products)){
            return array();
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('p.`virtuemart_product_id`, p.product_sku AS sku, pl.`product_name` AS name')
            ->from('`#__virtuemart_products` AS p')
            ->innerJoin('`#__virtuemart_products_' . $this->langTag . '` AS pl USING(virtuemart_product_id)')
            ->where('p.`virtuemart_product_id` IN ('.implode(', ', $products).')');
        $result = $db->setQuery($query)->loadObjectList('virtuemart_product_id');
        return $result;
    }

    public function getFilterCategory()
    {
        if(!is_file(JPATH_ROOT.'/administrator/components/com_virtuemart/helpers/shopfunctions.php')){
            return '';
        }
        include_once JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/shopfunctions.php';

        $categories = '<option value="-1">' . JText::_('JALL') . '</option>' . ShopFunctions::categoryListTree(array());
        $categoriesSelect = '<select id="filter_categories" class="inputbox" size="1">';
        $categoriesSelect .= $categories;
        $categoriesSelect .= '</select>';
        return $categoriesSelect;
    }

    public function getFilteredProducts($category=0, $dateFrom='', $dateTo='', $text='', array $excludeProducts=array(), array $includeProducts=array(), array $params=array())
    {
        $root_id = !empty($params['root_id']) ? (int)$params['root_id'] : 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT p.virtuemart_product_id AS id, p.product_sku AS sku, pl.product_name AS name')
            ->from('#__virtuemart_products AS p')
            ->leftJoin('#__virtuemart_product_categories as c ON c.virtuemart_product_id = p.virtuemart_product_id')
            ->leftJoin('#__virtuemart_products_' . $this->langTag . ' AS pl ON pl.virtuemart_product_id = p.virtuemart_product_id')
            ->where('p.product_parent_id = '.$root_id)
            ->group('p.virtuemart_product_id')
        ;

        if($category>0)
        {
            $query->where('c.virtuemart_category_id = '.(int)$category);
        }

        if($dateFrom!='')
        {
            $d = new JDate($dateFrom);
            $dateFrom = $d->format('Y-m-d').' 00:00:00';
            $query->where('p.modified_on >= '.$db->quote($dateFrom));
        }

        if($dateTo!='')
        {
            $d = new JDate($dateTo);
            $dateTo = $d->format('Y-m-d').' 23:59:59';
            $query->where('p.modified_on <= '.$db->quote($dateTo));
        }

        if(!empty($text))
        {
            $text = StringHelper1::strtolower($text);
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($text), true) . '%'));
            $query->where('pl.product_name LIKE LOWER(' . $search . ')');
        }

        if(count($excludeProducts))
        {
            $query->where('p.virtuemart_product_id NOT IN ('.implode(', ', $excludeProducts).')');
        }

        if(count($includeProducts))
        {
            $query->where('p.virtuemart_product_id NOT IN ('.implode(', ', $includeProducts).')');
        }

        $result = $db->setQuery($query)->loadObjectList();
        return $result;
    }

    function getCategoriesForParent($parentId, $type='vk')
    {
        $this->query->clear()
            ->select('`cc`.`category_parent_id` AS parent_id, `cc`.`category_child_id` AS id, `cl`.`category_name` AS name')
            ->select('(SELECT COUNT(*) FROM #__virtuemart_category_categories AS sc WHERE sc.`category_parent_id` = c.`virtuemart_category_id`) AS hasChild')
            ->from('`#__virtuemart_categories` AS c')
            ->innerJoin('`#__virtuemart_category_categories` AS cc ON `cc`.`category_child_id` = `c`.`virtuemart_category_id`')
            ->innerJoin('`#__virtuemart_categories_' . $this->langTag . '` AS cl ON `cl`.`virtuemart_category_id` = `c`.`virtuemart_category_id`')
            ->where('`c`.`published` = 1')
            ->where('`cc`.`category_parent_id` = '.(int)$parentId)
            ->order('`cc`.`category_child_id` ASC, `cl`.`category_name` ASC');

        if($type == 'ya'){
            $this->query->select('ya.*')
                ->leftJoin('#__argensyml_yacat as ya ON ya.`category_id` = c.`virtuemart_category_id`');
        }
        else{
            $this->query->select('vk_id, vk_section_id, vk_name')
                ->leftJoin('#__argensyml_cat_assoc as vk ON vk.`category_id` = `cc`.`category_child_id`');
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
        $query->select('virtuemart_custom_id')
            ->from('#__virtuemart_customs')
            ->where('custom_value = '.$db->quote('related_products'));
        $virtuemart_custom_id = $db->setQuery($query,0,1)->loadResult();
        if($virtuemart_custom_id == 0){
            return '';
        }
        $query->clear()->select('customfield_value')
            ->from('#__virtuemart_product_customfields')
            ->where('virtuemart_custom_id = '.(int)$virtuemart_custom_id)
            ->where('virtuemart_product_id = '.(int)$product_id);
        $result = $db->setQuery($query,0,30)->loadColumn();
        if(!is_array($result) || !count($result)){
            return '';
        }
        return implode(',',$result);
    }
}