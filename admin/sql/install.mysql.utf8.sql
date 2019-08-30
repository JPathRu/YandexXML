CREATE TABLE IF NOT EXISTS `#__argensyml_errors` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `errors` text NOT NULL,
   UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE `#__argensyml_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `offer_type` varchar(20) NOT NULL,
  `filename` varchar(50) NOT NULL,
  `store` tinyint(1) NOT NULL,
  `pickup` tinyint(1) NOT NULL,
  `delivery` TINYINT(1) NOT NULL,
  `delivery_time` int(5) NOT NULL,
  `order_before` varchar(10) NOT NULL,
  `local_delivery_cost` float NOT NULL,
  `cpa` int(1) NOT NULL DEFAULT '-1',
  `bid` float NOT NULL,
  `cbid` float NOT NULL,
  `sales_notes` varchar(255) NOT NULL,
  `shop_settings` text NOT NULL,
  `type` varchar(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `site_id` int(11) NOT NULL,
  `site_pass` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `album_id` int(11) NOT NULL,
  `adult` TINYINT(1) NOT NULL,
  `age` TINYINT(2) NOT NULL,
  `images_limit` int(3) NOT NULL DEFAULT '1',
  `free_shipping` int(10) NOT NULL DEFAULT '0',
  `delete_missing` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `#__argensyml_cat_assoc` (
  `category_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL,
  `vk_section_id` int(11) NOT NULL,
  `vk_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ассоциации категорий магазина с категориями ВК';

CREATE TABLE `#__argensyml_yacat` (
  `category_id` int(11) NOT NULL,
  `offer_type` varchar(20) NOT NULL,
  `type_prefix` text NOT NULL,
  `store` int(1) NOT NULL DEFAULT '0',
  `pickup` int(1) NOT NULL DEFAULT '0',
  `local_delivery_cost` varchar(20) NOT NULL,
  `delivery` tinyint(1) NOT NULL,
  `delivery_time` int(5) NOT NULL,
  `order_before` varchar(10) NOT NULL,
  `cpa` int(1) NOT NULL DEFAULT '-1',
  `bid` int(1) NOT NULL,
  `cbid` int(1) NOT NULL,
  `sales_notes` varchar(255) NOT NULL,
  `adult` tinyint(1) NOT NULL DEFAULT '0',
  `age` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
