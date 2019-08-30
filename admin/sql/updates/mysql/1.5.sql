CREATE TABLE `#__argensyml_yacat` (
  `category_id` int(11) NOT NULL,
  `offer_type` varchar(20) NOT NULL,
  `store` int(1) NOT NULL DEFAULT '0',
  `pickup` int(1) NOT NULL DEFAULT '0',
  `local_delivery_cost` varchar(20) NOT NULL,
  `delivery` tinyint(1) NOT NULL,
  `bid` int(1) NOT NULL,
  `cbid` int(1) NOT NULL,
  `sales_notes` varchar(255) NOT NULL,
  `adult` tinyint(1) NOT NULL DEFAULT '0',
  `age` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `#__argensyml_items` ADD `delivery` TINYINT(1) NOT NULL;
ALTER TABLE `#__argensyml_items` ADD `adult` TINYINT(1) NOT NULL;
ALTER TABLE `#__argensyml_items` ADD `age` TINYINT(2) NOT NULL;