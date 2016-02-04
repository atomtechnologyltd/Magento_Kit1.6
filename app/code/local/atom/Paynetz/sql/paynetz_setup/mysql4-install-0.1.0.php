<?php

$installer = $this;

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('paynetz_api_debug')};
CREATE TABLE `{$this->getTable('paynetz_api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `checkout_method` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `checkout_method` VARCHAR( 255 ) NOT NULL ;");
$installer->endSetup();