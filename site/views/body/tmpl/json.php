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

$enableKeys = array('price', 'categoryId', 'picture', 'name', 'description');

if(is_array($this->offers) && count($this->offers)){
    $offers = array();
    $i=0;
    echo "\n";
    foreach ($this->offers as $offer)
    {
        if(empty($offer->product_id))
        {
            $this->setError('Product '.$offer->name .' skipped/ ID is empty');
            continue;
        }
        if(empty($offer->price))
        {
            $this->setError('Product '.$offer->attribute->id.' '.$offer->name .' skipped. Price is empty');
            continue;
        }
        if(empty($offer->categoryId))
        {
            $this->setError('Product '.$offer->attribute->id.' '.$offer->name .' skipped. Category is empty');
            continue;
        }
        if(empty($offer->picture))
        {
            $this->setError('Product '.$offer->attribute->id.' '.$offer->name .' skipped. Picture is empty');
            continue;
        }
        if(empty($offer->name))
        {
            $this->setError('Product '.$offer->attribute->id.' skipped. Name is empty');
            continue;
        }
        if(empty($offer->description))
        {
            $this->setError('Product '.$offer->attribute->id.' '.$offer->name .' skipped. Description is empty');
            continue;
        }

        $data = array();
        $data['id'] = $offer->product_id;
        $data['categoryId'] = $offer->categoryId;
        $data['name'] = $offer->name;
        $data['price'] = $offer->price;
        $data['picture'] = $offer->picture;
        $data['description'] = $offer->description;

        if($i > 0) echo ",\n";
        echo json_encode($data, JSON_PRETTY_PRINT);
        $i++;
    }
    echo "\n";
}
