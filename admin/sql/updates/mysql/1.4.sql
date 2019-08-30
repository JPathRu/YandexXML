CREATE TABLE `#__argensyml_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `offer_type` varchar(20) NOT NULL,
  `filename` varchar(50) NOT NULL,
  `store` tinyint(1) NOT NULL,
  `pickup` tinyint(1) NOT NULL,
  `local_delivery_cost` float NOT NULL,
  `bid` float NOT NULL,
  `cbid` float NOT NULL,
  `shop_settings` text NOT NULL,
  `type` varchar(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `site_id` int(11) NOT NULL,
  `site_pass` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `#__argensyml_cat_assoc` (
  `category_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL,
  `vk_section_id` int(11) NOT NULL,
  `vk_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Ассоциации категорий магазина с категориями ВК';