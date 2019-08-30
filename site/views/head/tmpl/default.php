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
$jversion = new JVersion;
if (version_compare(JVERSION, '3.4.8', '>'))
{
    $version = $jversion::PRODUCT;
}
else
{
    $version = $jversion->PRODUCT;
}
?>
<!DOCTYPE yml_catalog SYSTEM "http://partner.market.yandex.ru/pages/help/shops.dtd">
<yml_catalog date="<?php echo $this->date; ?>">
    <shop>
        <name><?php echo $this->shop_name; ?></name>
        <company><?php echo $this->company_name; ?></company>
        <url><?php echo JUri::root(); ?></url>
        <platform><?php echo $version; ?>, Joomline Yandex.Market XML</platform>
        <version><?php echo $jversion->getShortVersion(); ?></version>
        <currencies>
            <currency id="<?php echo $this->currency ?>" rate="1" plus="0"/>
        </currencies>
        <?php if(is_array($this->categories) && count($this->categories)) : ?>
            <categories>
                <?php foreach ($this->categories as $cat): ?>
                    <category id="<?php echo $cat->id; ?>"<?php if($cat->parentId > 1) : ?> parentId="<?php echo $cat->parentId; ?>"<?php endif; ?>><?php echo $cat->name; ?></category>
                <?php endforeach; ?>
            </categories>
        <?php endif; ?>
        <?php if($this->cpa != -1) : ?>
            <cpa><?php echo $this->cpa; ?></cpa>
        <?php endif; ?>
        <?php if($this->delivery == 1) :
            $order_before = !empty($this->order_before) ? ' order-before="'.$this->order_before.'"' : '';
            ?>
            <delivery-options>
                <option cost="<?php echo (int)$this->local_delivery_cost; ?>" days="<?php echo (int)$this->delivery_time; ?>"<?php echo $order_before; ?>/>
            </delivery-options>
        <?php endif; ?>
        <offers>
