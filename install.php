<?php
/**
 * Name:         Smart Invoice System
 * Version :     1.0
 * Author:       Zitouni Bessem
 * Requirements: PHP5 or above
 *
 */

error_reporting(-1);
ini_set('display_errors', 1);
define('MYSQL_CODEPAGE', 'utf8');
define('MYSQL_COLLATE',  'utf8_unicode_ci');
define('BASEPATH', '');
$error = false;
$step = isset($_POST['step'])?$_POST['step']:0;
$step_count = 5;
$step_counter = 1;
$MSG_PROGRESS = "";
$minPHP = 5.4;
$minApache = 2.4;

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))?"https://":"http://";
$base_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$base_url  = substr($base_url, 0, strripos($base_url, "/")+1);

$_TABLES['biller'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `address2` text NOT NULL,
  `city` varchar(55) NOT NULL,
  `state` varchar(55) NOT NULL,
  `postal_code` varchar(8) NOT NULL,
  `country` varchar(55) NOT NULL,
  `company` varchar(255) NOT NULL,
  `vat_number` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `custom_field1` varchar(255) DEFAULT NULL,
  `custom_field2` varchar(255) DEFAULT NULL,
  `custom_field3` varchar(255) DEFAULT NULL,
  `custom_field4` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)";

$_TABLES['invoices'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `title` varchar(25) NOT NULL DEFAULT 'Invoice',
  `description` VARCHAR(255) NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'Draft',
  `bill_to_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `terms` text NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `discount_type` tinyint(1) NOT NULL DEFAULT '1',
  `subtotal` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `global_discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `shipping` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `total_discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total_tax` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `count` int(11) NOT NULL DEFAULT '0',
  `total_due` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `payment_date` DATE NULL DEFAULT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `recurring_id` int(11) DEFAULT NULL,
  `double_currency` BOOLEAN NOT NULL DEFAULT FALSE,
  `rate` DECIMAL(25,4) NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `custom_field1` VARCHAR(255) NOT NULL,
  `custom_field2` VARCHAR(255) NOT NULL,
  `custom_field3` VARCHAR(255) NOT NULL,
  `custom_field4` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_to_id` (`bill_to_id`),
  KEY `estimate_id` (`estimate_id`),
  KEY `user_id` (`user_id`)";

$_TABLES['invoices_items'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `unit_price` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `tax_type` tinyint(1) NOT NULL DEFAULT '1',
  `tax` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `discount_type` tinyint(1) NOT NULL DEFAULT '1',
  `discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`)";

$_TABLES['settings'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
`type` varchar(10) NOT NULL,`configuration` text NOT NULL,
`controller` varchar(255) DEFAULT NULL,
`method` varchar(255) DEFAULT NULL,
`param` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`)";

$_TABLES['users_groups'] = "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(11) unsigned NOT NULL,
`group_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
KEY `fk_users_groups_users1_idx` (`user_id`),
KEY `fk_users_groups_groups1_idx` (`group_id`)";

$_TABLES['groups'] = "`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(20) NOT NULL,
`description` varchar(100) NOT NULL,
PRIMARY KEY (`id`)";

$_TABLES['users'] = "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`ip_address` varchar(45) NOT NULL,
`username` varchar(100) DEFAULT NULL,
`password` varchar(255) NOT NULL,
`salt` varchar(255) DEFAULT NULL,
`email` varchar(100) NOT NULL,
`activation_code` varchar(40) DEFAULT NULL,
`forgotten_password_code` varchar(40) DEFAULT NULL,
`forgotten_password_time` int(11) unsigned DEFAULT NULL,
`remember_code` varchar(40) DEFAULT NULL,
`created_on` int(11) unsigned NOT NULL,
`last_login` int(11) unsigned DEFAULT NULL,
`active` tinyint(1) unsigned DEFAULT NULL,
`first_name` varchar(50) DEFAULT NULL,
`last_name` varchar(50) DEFAULT NULL,
`company` varchar(100) DEFAULT NULL,
`phone` varchar(20) DEFAULT NULL,
PRIMARY KEY (`id`)";

$_TABLES['ci_sessions'] = "`id` varchar(40) NOT NULL,
`ip_address` varchar(45) NOT NULL,
`timestamp` int(10) unsigned NOT NULL DEFAULT '0',
`data` blob NOT NULL,PRIMARY KEY (`id`),
KEY `ci_sessions_timestamp` (`timestamp`)";

$_TABLES['login_attempts'] = "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`ip_address` varchar(15) NOT NULL,
`login` varchar(100) NOT NULL,
`time` int(11) unsigned DEFAULT NULL,
PRIMARY KEY (`id`)";

$_TABLES['tax_rates'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
`label` varchar(255) NOT NULL,
`value` decimal(10,2) NOT NULL,
`type` tinyint(1) NOT NULL,
`is_default` tinyint(1) NOT NULL DEFAULT '0',
`can_delete` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`)";

$_TABLES['invoices_taxes'] = "`invoice_id` int(11) NOT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `is_conditional` BOOLEAN NOT NULL DEFAULT FALSE,
  KEY `invoice_id` (`invoice_id`),
  KEY `tax_rate_id` (`tax_rate_id`)";

$_TABLES['payments'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `method` varchar(20) DEFAULT 'cash',
  `details` text NOT NULL,
  `credit_card` text,
  `token` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'released',
  PRIMARY KEY (`id`)";

$_TABLES['estimates'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `title` varchar(25) NOT NULL DEFAULT 'Invoice',
  `description` VARCHAR(255) NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'Draft',
  `bill_to_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `terms` text NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `discount_type` tinyint(1) NOT NULL DEFAULT '1',
  `subtotal` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `global_discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `shipping` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `total_discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total_tax` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `count` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `custom_field1` VARCHAR(255) NOT NULL,
  `custom_field2` VARCHAR(255) NOT NULL,
  `custom_field3` VARCHAR(255) NOT NULL,
  `custom_field4` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_to_id` (`bill_to_id`),
  KEY `user_id` (`user_id`)";

$_TABLES['estimates_items'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `unit_price` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `tax_type` tinyint(1) NOT NULL DEFAULT '1',
  `tax` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `discount_type` tinyint(1) NOT NULL DEFAULT '1',
  `discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`)";

$_TABLES['estimates_taxes'] = "`estimate_id` int(11) NOT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `is_conditional` BOOLEAN NOT NULL DEFAULT FALSE,
  KEY `estimate_id` (`estimate_id`),
  KEY `tax_rate_id` (`tax_rate_id`)";

$_TABLES['items'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` tinyint(1) NOT NULL DEFAULT '1',
  `discount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `tax_type` tinyint(1) NOT NULL DEFAULT '1',
  `tax` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `category` INT(11) NULL DEFAULT NULL,
  `unit` VARCHAR(25) NOT NULL DEFAULT 'U',
  `custom_field1` VARCHAR(255) NOT NULL,
  `custom_field2` VARCHAR(255) NOT NULL,
  `custom_field3` VARCHAR(255) NOT NULL,
  `custom_field4` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)";

$_TABLES['log'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `controller` varchar(255) NULL DEFAULT NULL,
  `method` varchar(255) NULL DEFAULT NULL,
  `param` VARCHAR(255) NULL DEFAULT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)";

$_TABLES['recurring_invoices'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `next_date` date DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `frequency` varchar(10) NOT NULL,
  `number` varchar(255) NOT NULL,
  `occurence` int(4) NOT NULL,
  `status` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `bill_to_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_to_id` (`bill_to_id`),
  KEY `user_id` (`user_id`)";


$_TABLES['recurring_invoices_items'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `recurring_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `skip` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `recurring_id` (`recurring_id`)";

$_TABLES['files'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `realpath` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `date_upload` datetime NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `size` decimal(25,4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trash` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)";

$_TABLES['contracts'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `description` text NOT NULL,
  `reference` varchar(20) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `attachments` TEXT NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biller_id` (`biller_id`),
  KEY `user_id` (`user_id`)";

$_TABLES['receipts'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.0000',
  `method` varchar(20) DEFAULT 'cash',
  `details` text NOT NULL,
  `credit_card` text,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)";

$_TABLES['suppliers'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `address2` text NOT NULL,
  `city` varchar(55) NOT NULL,
  `state` varchar(55) NOT NULL,
  `postal_code` varchar(8) NOT NULL,
  `country` varchar(55) NOT NULL,
  `company` varchar(255) NOT NULL,
  `vat_number` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `custom_field1` varchar(255) DEFAULT NULL,
  `custom_field2` varchar(255) DEFAULT NULL,
  `custom_field3` varchar(255) DEFAULT NULL,
  `custom_field4` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)";

$_TABLES['expenses'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'unpaid',
  `amount` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `tax_id` int(11) DEFAULT NULL,
  `tax_type` tinyint(1) NOT NULL DEFAULT '0',
  `tax_value` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `tax_total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `total_due` DECIMAL(25,4) NOT NULL DEFAULT '0.0000',
  `payment_method` varchar(255) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `details` text NOT NULL,
  `attachments` text NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)";

$_TABLES['expenses_categories'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `is_default` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)";

$_TABLES['expenses_payments'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `method` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'cash',
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'released',
  PRIMARY KEY (`id`)";

$_TABLES['chat_attempts'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `time` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)";

$_TABLES['chat_messages'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `from` int(11) UNSIGNED NOT NULL,
  `to` int(11) UNSIGNED NOT NULL,
  `read` int(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `date_read` datetime DEFAULT NULL,
  `offline` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `to` (`to`),
  KEY `from` (`from`)";

$_TABLES['calendar'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `repeat_type` int(5) NOT NULL,
  `repeat_days` varchar(50) DEFAULT NULL,
  `no_end` tinyint(1) DEFAULT NULL,
  `emails` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `additional_content` text NOT NULL,
  `attachments` text NOT NULL,
  `last_send` date DEFAULT NULL,
  PRIMARY KEY (`id`)";

$_TABLES['todo'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `priority` int(1) NOT NULL,
  `complete` int(1) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `attachments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)";

$_TABLES['items_prices'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `price` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)";

$_TABLES['items_categories'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_default` INT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)";

$_TABLES['projects'] = "`id` int(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `progress` int(3) NOT NULL,
  `billing_type` varchar(255) NOT NULL,
  `rate` decimal(25,4) NOT NULL DEFAULT '0.0000',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `estimated_hours` int(11) NOT NULL DEFAULT '0',
  `status` varchar(255) NOT NULL DEFAULT 'progress',
  `date` date NOT NULL,
  `date_due` date DEFAULT NULL,
  `members` TEXT NOT NULL,
  `description` text NOT NULL,
  `user_id` int(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `biller_id` (`biller_id`)";

$_TABLES['projects_tasks'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(111) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `hour_rate` decimal(25,4) NOT NULL,
  `date` date NOT NULL,
  `date_due` date NULL DEFAULT NULL,
  `priority` int(1) NOT NULL,
  `description` text NOT NULL,
  `attachments` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`)";

$_TABLES['email_templates'] = "`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `language` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `data` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)";

$_ADDITIONAL_SQL[] = "INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'Members', 'General User'),
(3, 'customer', 'Client'),
(4, 'supplier', 'Supplier'),
(5, 'superadmin', 'Super Administrator');";

$_ADDITIONAL_SQL[] = "INSERT INTO `tax_rates` (`id`, `label`, `value`, `type`, `is_default`, `can_delete`) VALUES
(1, 'lang:no_tax', '0.0000', 0, 1, 0);";


$_ADDITIONAL_SQL[] = "ALTER TABLE `biller`
  ADD CONSTRAINT `biller_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `users_groups` ADD CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`bill_to_id`) REFERENCES `biller` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `invoices_items`
  ADD CONSTRAINT `invoices_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `invoices_taxes` ADD CONSTRAINT `invoices_taxes_ibfk_2` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL, ADD CONSTRAINT `invoices_taxes_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `estimates`
  ADD CONSTRAINT `estimates_ibfk_1` FOREIGN KEY (`bill_to_id`) REFERENCES `biller` (`id`),
  ADD CONSTRAINT `estimates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `estimates_items`
  ADD CONSTRAINT `estimates_items_ibfk_1` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `estimates_taxes`
  ADD CONSTRAINT `estimates_taxes_ibfk_1` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `estimates_taxes_ibfk_2` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
";

$_ADDITIONAL_SQL[] = "ALTER TABLE `log`
  ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `invoices_items`
  ADD FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `estimates_items`
  ADD FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `recurring_invoices`
  ADD CONSTRAINT `recurring_invoices_ibfk_1` FOREIGN KEY (`bill_to_id`) REFERENCES `biller` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recurring_invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `recurring_invoices_items`
  ADD CONSTRAINT `recurring_invoices_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `recurring_invoices_items_ibfk_2` FOREIGN KEY (`recurring_id`) REFERENCES `recurring_invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`biller_id`) REFERENCES `biller` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`from`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`to`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `chat_attempts`
  ADD CONSTRAINT `chat_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `todo`
  ADD CONSTRAINT `todo_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`biller_id`) REFERENCES `biller` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `projects_tasks`
  ADD CONSTRAINT `projects_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_tasks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `items_prices`
  ADD CONSTRAINT `items_prices_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `items` ADD FOREIGN KEY (`category`) REFERENCES `items_categories`(`id`) ON DELETE SET NULL ON UPDATE SET NULL;";

$_ADDITIONAL_SQL[] = "ALTER TABLE `expenses_payments` ADD FOREIGN KEY (`expense_id`) REFERENCES `expenses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";

$_ADDITIONAL_SQL[] = "INSERT INTO `expenses_categories` (`id`, `type`, `label`, `is_default`) VALUES
(1, 'Utilities', 'Electricity', 1),
(2, 'Utilities', 'Gas', 0),
(3, 'Utilities', 'Water', 0),
(4, 'Utilities', 'Phone', 0),
(5, 'Utilities', 'Internet', 0),
(6, 'Utilities', 'Other utilities', 0),
(7, 'Supplies', 'Stationery', 0),
(8, 'Supplies', 'Other consumables', 0),
(9, 'Maintenance and repair', 'Repair and maintenance of headquarters', 0),
(10, 'Maintenance and repair', 'Equipment repairs', 0),
(11, 'Maintenance and repair', 'Other', 0),
(12, 'Services', 'Accountancy', 0),
(13, 'Services', 'Legally', 0),
(14, 'Services', 'Consulting', 0),
(15, 'Services', 'Fees', 0),
(16, 'Services', 'Online service subscriptions', 0),
(17, 'Services', 'Transport', 0),
(18, 'Services', 'Other services', 0),
(19, 'Services', 'Courier', 0),
(20, 'Rents', 'Rental of buildings', 0),
(21, 'Rents', 'Car leasing', 0),
(22, 'Rents', 'Equipment rental', 0),
(23, 'Rents', 'Other rents', 0),
(24, 'Auto', 'Fuels', 0),
(25, 'Auto', 'Auto parts', 0),
(26, 'Auto', 'Car maintenance materials', 0),
(27, 'Auto', 'Car repair services', 0),
(28, 'Auto', 'Other', 0),
(29, 'HR', 'Wages', 0),
(30, 'HR', 'Tickets', 0),
(31, 'HR', 'Salary contributions', 0),
(32, 'HR', 'Other', 0),
(33, 'Insurance', 'Insurance and social protection', 0),
(34, 'Insurance', 'Life insurance', 0),
(35, 'Insurance', 'Private pension', 0),
(36, 'Insurance', 'RCA', 0),
(37, 'Insurance', 'Casco', 0),
(38, 'Insurance', 'Other insurance', 0),
(39, 'Taxes', 'VAT', 0),
(40, 'Taxes', 'Tax', 0),
(41, 'Taxes', 'Bank fees', 0),
(42, 'Taxes', 'Building tax', 0),
(43, 'Taxes', 'Key maps', 0),
(44, 'Taxes', 'Fines', 0),
(45, 'Taxes', 'Other taxes', 0),
(46, 'Advertisement', 'Commercials', 0),
(47, 'Advertisement', 'Promotional materials', 0),
(48, 'Advertisement', 'Announcements', 0),
(49, 'Advertisement', 'Online ads', 0),
(50, 'Advertisement', 'Other', 0),
(51, 'Protocol', 'Organized tables', 0),
(52, 'Protocol', 'Gifts', 0),
(53, 'Protocol', 'Gifts', 0),
(54, 'Protocol', 'Other protocol expenses', 0),
(55, 'Inventory items', 'Telephones', 0),
(56, 'Inventory items', 'Furniture', 0),
(57, 'Inventory items', 'IT equipment', 0),
(58, 'Inventory items', 'Other inventory items', 0),
(59, 'Fixed assets', 'Buildings', 0),
(60, 'Fixed assets', 'Lands', 0),
(61, 'Fixed assets', 'Machinery', 0),
(62, 'Fixed assets', 'Other fixed assets', 0),
(63, 'Other expenses', 'Reparation', 0),
(64, 'Other expenses', 'Raw materials', 0),
(65, 'Other expenses', 'Other raw materials', 0);";

$_ADDITIONAL_SQL[] = "INSERT INTO `email_templates` (`id`, `name`, `language`, `subject`, `content`, `data`) VALUES
(1, 'send_invoices_to_customer.tpl', 'english', 'Invoice PDF from {{company_name}}', '<p>Greetings !<br>You have received an invoice from <strong>{{company_name}}</strong>.<br>A PDF file is attached.</p>', 'invoice|customer|company'),
(2, 'send_invoices_to_customer.tpl', 'french', 'Facture PDF de {{company_name}}', '<p>Salutations !<br>Vous avez reçu une facture de <strong>{{company_name}}</strong>.<br>Un fichier PDF est attaché.</p>', 'invoice|customer|company'),
(3, 'send_invoices_to_customer.tpl', 'spanish', 'Factura PDF de {{company_name}}', '<p>Saludos !<br>Ha recibido una factura de <strong>{{company_name}}</strong>. <br> Se adjunta un archivo PDF.</p>', 'invoice|customer|company'),
(4, 'send_invoices_to_customer.tpl', 'turkish', 'dan fatura paketi {{company_name}}', '<p>Selamlar !<br> adresinden bir fatura aldınız  {{company_name}}. <br> Bir PDF dosyası eklenmiştir.</p>', 'invoice|customer|company'),
(5, 'send_invoices_to_customer.tpl', 'russian', 'Счёт-фактура из {{company_name}}', '<p>Приветствую!<br>Вы получили счет-фактуру <strong>{{company_name}}</strong>. <br> Файл PDF прилагается.</p>', 'invoice|customer|company'),
(6, 'send_invoices_to_customer.tpl', 'romanian', 'Factura PDF din {{company_name}}', '<p>Bună!<br>Ați primit o factură de la <strong>{{company_name}}</strong>.<br>Un fișier PDF este atașat.</p>', 'invoice|customer|company'),
(7, 'send_invoices_to_customer.tpl', 'german', 'Rechnung PDF ab {{company_name}}', '<p>Grüße!<br>Sie haben eine Rechnung erhalten <strong>{{company_name}}</strong>. <br> Eine PDF-Datei ist beigefügt.</p>', 'invoice|customer|company'),
(8, 'send_invoices_to_customer.tpl', 'italian', 'Fattura PDF da {{company_name}}', '<p>Saluti !<br>Hai ricevuto una fattura da <strong>{{company_name}}</strong>. <br> È allegato un file PDF.</p>', 'invoice|customer|company'),
(9, 'send_invoices_to_customer.tpl', 'arabic', 'فاتورة بي دي أف من {{company_name}}', '<p>تحية طيبة !<br>لقد تلقيت فاتورة من <strong>{{company_name}}</strong>.<br>ملف PDF مرفق.</p>', 'invoice|customer|company'),
(19, 'send_estimates_to_customer.tpl', 'english', 'Estimate PDF from {{company_name}}', 'Greetings !<br>You have received an estimate from <b>{{company_name}}</b>.<br>A PDF file is attached.', 'estimate|customer|company'),
(20, 'send_estimates_to_customer.tpl', 'french', 'Estimation PDF de{{company_name}}', 'Salutations!<br>Vous avez reçu une estimation de <b>{{company_name}}</b> . <br> Un fichier PDF est joint.', 'estimate|customer|company'),
(21, 'send_estimates_to_customer.tpl', 'spanish', 'Calcule el PDF de{{company_name}}', 'Saludos !<br>Ha recibido una estimación de <b>{{company_name}}</b> . <br> Se adjunta un archivo PDF.', 'estimate|customer|company'),
(22, 'send_estimates_to_customer.tpl', 'turkish', '&#39;den PDF tahmin edin', 'Selamlar !<br><b></b> tarafından bir tahmin aldınız. <br> Bir PDF dosyası eklenmiştir.', 'estimate|customer|company'),
(23, 'send_estimates_to_customer.tpl', 'russian', 'Оценка PDF из{{company_name}}', 'Приветствую !<br>Вы получили оценку от <b>{{company_name}}</b> . <br> Файл PDF прилагается.', 'estimate|customer|company'),
(24, 'send_estimates_to_customer.tpl', 'romanian', 'Proformă PDF din {{company_name}}', 'Bună !<br>Ați primit o proformă de la <b>{{company_name}} </ b>. <br> Un fișier PDF este atașat.', 'estimate|customer|company'),
(25, 'send_estimates_to_customer.tpl', 'german', 'Schätzen Sie PDF aus {{company_name}} ', 'Grüße!<br>Sie haben eine Schätzung erhalten {{company_name}} . <br> Eine PDF-Datei ist beigefügt.', 'estimate|customer|company'),
(26, 'send_estimates_to_customer.tpl', 'italian', 'Stima PDF da {{company_name}} ', 'Saluti !<br>Hai ricevuto una stima da {{company_name}} . <br> È allegato un file PDF.', 'estimate|customer|company'),
(27, 'send_estimates_to_customer.tpl', 'arabic', 'ملف تقدير PDF من  {{company_name}}', 'تحية طيبة !<br>لقد تلقيت تقديرا من <b>{{company_name}}</b> . <br> يتم إرفاق ملف PDF .', 'estimate|customer|company'),
(37, 'send_contracts_to_customer.tpl', 'english', 'Contract PDF from {{company_name}}', 'Greetings !<br>You have received an contract from <b>{{company_name}}</b>.<br>A PDF file is attached.', 'contract|customer|company'),
(38, 'send_contracts_to_customer.tpl', 'french', 'Contrat PDF à partir de {{company_name}} ', 'Salutations !<br>Vous avez reçu un contrat de {{company_name}} . <br> Un fichier PDF est joint.', 'contract|customer|company'),
(39, 'send_contracts_to_customer.tpl', 'spanish', 'Contrato PDF de {{company_name}} ', 'Saludos !<br>Ha recibido un contrato de {{company_name}} . <br> Se adjunta un archivo PDF.', 'contract|customer|company'),
(40, 'send_contracts_to_customer.tpl', 'turkish', 'Şuradan sözleşme PDF&#39;si {{company_name}} ', 'Selamlar !<br>Şuradan bir sözleşme aldınız: {{company_name}} . <br> Bir PDF dosyası eklenmiştir.', 'contract|customer|company'),
(41, 'send_contracts_to_customer.tpl', 'russian', 'Договор PDF от {{company_name}} ', 'Приветствую !<br>Вы получили контракт от {{company_name}} , <br> Файл PDF прилагается.', 'contract|customer|company'),
(42, 'send_contracts_to_customer.tpl', 'romanian', 'Contract PDF la {{company_name}}', 'Salutari !<br>Ați primit un contract de la {{company_name}} . <br> Un fișier PDF este atașat.', 'contract|customer|company'),
(43, 'send_contracts_to_customer.tpl', 'german', 'Vertrag PDF ab {{company_name}} ', 'Grüße!<br>Sie haben einen Vertrag von {{company_name}} . <br> Eine PDF-Datei ist angehängt.', 'contract|customer|company'),
(44, 'send_contracts_to_customer.tpl', 'italian', 'Contratto PDF da {{company_name}} ', 'Saluti !<br>Hai ricevuto un contratto da {{company_name}} . <br> È allegato un file PDF.', 'contract|customer|company'),
(45, 'send_contracts_to_customer.tpl', 'arabic', 'Pdf العقد من {{company_name}} ', 'تحية طيبة !<br>لقد تلقيت عقدا من {{company_name}} . <br> يتم إرفاق ملف بدف.', 'contract|customer|company'),
(46, 'send_receipts_to_customer.tpl', 'english', 'Payment PDF from {{company_name}}', 'Greetings !<br>You have received an payment from <b>{{company_name}}</b>.<br>A PDF file is attached.', 'receipt|customer|company'),
(47, 'send_receipts_to_customer.tpl', 'french', 'Paiement PDF de{{company_name}}', 'Salutations!<br>Vous avez reçu un paiement de <b>{{company_name}}</b> . <br> Un fichier PDF est joint.', 'receipt|customer|company'),
(48, 'send_receipts_to_customer.tpl', 'spanish', 'Pago PDF de{{company_name}}', 'Saludos !<br>Ha recibido un pago de <b>{{company_name}}</b> . <br> Se adjunta un archivo PDF.', 'receipt|customer|company'),
(49, 'send_receipts_to_customer.tpl', 'turkish', 'Ödeme{{company_name}}&#39;den PDF', 'Selamlar !<br><b>/b> S&#39;dan bir ödeme aldınız. <br> Bir PDF dosyası eklenmiştir.', 'receipt|customer|company'),
(50, 'send_receipts_to_customer.tpl', 'russian', 'Оплата PDF из{{company_name}}', 'Приветствую !<br>Вы получили платеж от <b>{{company_name}}</b> . <br> Файл PDF прилагается.', 'receipt|customer|company'),
(51, 'send_receipts_to_customer.tpl', 'romanian', 'Plata PDF din{{company_name}}', 'Bună!<br>Ați primit o plată de la <b>{{company_name}} </ b>. <br> Un fișier PDF este atașat.', 'receipt|customer|company'),
(52, 'send_receipts_to_customer.tpl', 'german', 'Zahlung PDF ab {{company_name}} ', 'Grüße!<br>Sie haben eine Zahlung erhalten von {{company_name}} . <br> Eine PDF-Datei ist beigefügt.', 'receipt|customer|company'),
(53, 'send_receipts_to_customer.tpl', 'italian', 'Pagamento PDF da {{company_name}} ', 'Saluti !<br>Hai ricevuto un pagamento da {{company_name}} . <br> È allegato un file PDF.', 'receipt|customer|company'),
(54, 'send_receipts_to_customer.tpl', 'arabic', 'ملف PDF من {{company_name}}', 'تحية طيبة !<br>لقد تلقيت دفعة من <b>{{company_name}}</b> . <br> يتم إرفاق ملف PDF .', 'receipt|customer|company'),
(64, 'send_rinvoices_to_customer.tpl', 'english', 'New Invoice from {{company_name}}', 'Greetings !<br>You have received an new unpaid invoice from <b>{{company_name}}</b>.<br><a href=\"{{invoice_link}}\" target=\"_blank\">Open</a>', 'invoice|customer|company'),
(65, 'send_rinvoices_to_customer.tpl', 'french', 'Nouvelle facture de {{company_name}} ', 'Salutations !<br>Vous avez reçu une nouvelle facture impayée de {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">Ouvrir</a>', 'invoice|customer|company'),
(66, 'send_rinvoices_to_customer.tpl', 'spanish', 'Nueva factura de {{company_name}} ', 'Saludos !<br>Ha recibido una nueva factura no pagada de {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">Abierto</a>', 'invoice|customer|company'),
(67, 'send_rinvoices_to_customer.tpl', 'turkish', 'Şuradan yeni fatura {{company_name}} ', 'Selamlar !<br>Şuradan yeni bir ödenmemiş fatura aldınız: {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">Açık</a>', 'invoice|customer|company'),
(68, 'send_rinvoices_to_customer.tpl', 'russian', 'Новый счет-фактура {{company_name}} ', 'Приветствую!<br>Вы получили новый неоплаченный счет {{company_name}} ,<br><a href=\"{{invoice_link}}\" target=\"_blank\">открыто</a>', 'invoice|customer|company'),
(69, 'send_rinvoices_to_customer.tpl', 'romanian', 'Factura nouă de la {{company_name}}', 'Bună!<br>Ați primit o nouă factură neachitată de la <b>{{company_name}}</b>.<br><a href=\"{{invoice_link}}\" target=\"_blank\">Deschis</a>', 'invoice|customer|company'),
(70, 'send_rinvoices_to_customer.tpl', 'german', 'Neue Rechnung ab {{company_name}} ', 'Grüße!<br>Sie haben eine neue, unbezahlte Rechnung erhalten {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">Öffnen</a>', 'invoice|customer|company'),
(71, 'send_rinvoices_to_customer.tpl', 'italian', 'Nuova fattura da {{company_name}} ', 'Saluti !<br>Hai ricevuto una nuova fattura non pagata da {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">Aperto</a>', 'invoice|customer|company'),
(72, 'send_rinvoices_to_customer.tpl', 'arabic', 'فاتورة جديدة من {{company_name}} ', 'تحية طيبة !<br>لقد تلقيت فاتورة جديدة غير مدفوعة من {{company_name}} .<br><a href=\"{{invoice_link}}\" target=\"_blank\">فتح</a>', 'invoice|customer|company'),
(73, 'send_customer_reminder.tpl', 'english', 'You have unpaid invoices from {{company_name}}', 'Dear {{customer_fullname}},<br>You have <b>{{count_invoices}}</b> unpaid invoices.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Invoices</a>', 'invoice_reminder|customer|company'),
(74, 'send_customer_reminder.tpl', 'french', 'Vous avez des factures impayées de {{company_name}} ', 'cher {{customer_fullname}} ,<br>Tu as {{count_invoices}} factures impayées.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Factures</a>', 'invoice_reminder|customer|company'),
(75, 'send_customer_reminder.tpl', 'spanish', 'Tiene facturas pendientes de pago de {{company_name}} ', 'querido {{customer_fullname}} ,<br>Tienes {{count_invoices}} facturas pendientes de pago.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Facturas</a>', 'invoice_reminder|customer|company'),
(76, 'send_customer_reminder.tpl', 'turkish', 'Gönderdiğiniz ödenmemiş faturanız var {{company_name}} ', 'Sayın {{customer_fullname}} ,<br>Var {{count_invoices}} ödenmemiş faturalar.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Faturalar</a>', 'invoice_reminder|customer|company'),
(77, 'send_customer_reminder.tpl', 'russian', 'У вас есть неоплаченные счета-фактуры {{company_name}} ', 'Уважаемые {{customer_fullname}} ,<br>You have <b>{{count_invoices}}</b> unpaid invoices.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Счета-фактуры</a>', 'invoice_reminder|customer|company'),
(78, 'send_customer_reminder.tpl', 'romanian', 'Aveți facturi neachitate de la {{company_name}}', 'Bună {{customer_fullname}},<br>Aveți <b>{{count_invoices}}</b> facturi neachitate.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Facturi</a>', 'invoice_reminder|customer|company'),
(79, 'send_customer_reminder.tpl', 'german', 'Sie haben unbezahlte Rechnungen aus {{company_name}} ', 'sehr geehrter {{customer_fullname}} ,<br>Du hast {{count_invoices}} unbezahlte Rechnungen.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Rechnungen</a>', 'invoice_reminder|customer|company'),
(80, 'send_customer_reminder.tpl', 'italian', 'Hai fatture non pagate da {{company_name}} ', 'caro {{customer_fullname}} ,<br>Hai {{count_invoices}} fatture non pagate.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">Fatture</a>', 'invoice_reminder|customer|company'),
(81, 'send_customer_reminder.tpl', 'arabic', 'لديك فواتير غير مدفوعة م', 'العزيز {{customer_fullname}} ،<br>عندك {{count_invoices}} فواتير غير مدفوعة.<br>{{invoices_table}}<br><a href=\"".$base_url."index.php/invoices\" target=\"_blank\">الفواتير</a>', 'invoice_reminder|customer|company'),
(82, 'send_overdue_reminder.tpl', 'english', 'You have unpaid invoices from {{company_name}}', 'Dear {{customer_fullname}},<br>You might have missed the payment date and the invoice is now overdue by <b>{{invoice_overdue_days}}</b> days.<br><br><table width=\'100%\'><tr><td>Reference : </td><th>{{invoice_reference}}</th></tr><tr><td>Date : </td><th>{{invoice_date}}</th></tr><tr><td>Due Date : </td><th>{{invoice_date_due}}</th></tr><tr><td>Total : </td><th>{{invoice_total}}</th></tr><tr><td>Payments : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Total Due : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Invoice</a>', 'invoice|customer|company'),
(83, 'send_overdue_reminder.tpl', 'french', 'Vous avez des factures impayées de {{company_name}} ', 'cher {{customer_fullname}} ,<br>Vous avez peut-être manqué la date de paiement et la facture est maintenant en retard {{invoice_overdue_days}} journées.<br><br><table width=\'100%\'><tr><td>Référence : </td><th>{{invoice_reference}}</th></tr><tr><td>Date : </td><th>{{invoice_date}}</th></tr><tr><td>Date d&#39;échéance : </td><th>{{invoice_date_due}}</th></tr><tr><td>Total : </td><th>{{invoice_total}}</th></tr><tr><td>Paiements : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Total dû : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Facture</a>', 'invoice|customer|company'),
(84, 'send_overdue_reminder.tpl', 'spanish', 'Tiene facturas pendientes de pago de {{company_name}} ', 'querido {{customer_fullname}} ,<br>Es posible que haya perdido la fecha de pago y la factura esté atrasada por {{invoice_overdue_days}} días.<br><br><table width=\'100%\'><tr><td>Referencia : </td><th>{{invoice_reference}}</th></tr><tr><td>Fecha : </td><th>{{invoice_date}}</th></tr><tr><td>Fecha de vencimiento : </td><th>{{invoice_date_due}}</th></tr><tr><td>Total : </td><th>{{invoice_total}}</th></tr><tr><td>Pagos : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Total debido : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Factura</a>', 'invoice|customer|company'),
(85, 'send_overdue_reminder.tpl', 'turkish', 'Gönderdiğiniz ödenmemiş faturanız var {{company_name}} ', 'Sayın {{customer_fullname}} ,<br>Ödeme tarihini kaçırmış olabilirsiniz ve fatura şimdi tarafından gecikmiştir. {{invoice_overdue_days}} günler.<br><br><table width=\'100%\'><tr><td>Referans : </td><th>{{invoice_reference}}</th></tr><tr><td>tarih : </td><th>{{invoice_date}}</th></tr><tr><td>Bitiş tarihi : </td><th>{{invoice_date_due}}</th></tr><tr><td>Genel Toplam : </td><th>{{invoice_total}}</th></tr><tr><td>Ödemeler : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Vadesi gereken toplam : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Fatura</a>', 'invoice|customer|company'),
(86, 'send_overdue_reminder.tpl', 'russian', 'У вас есть неоплаченные счета-фактуры {{company_name}} ', 'Уважаемые {{customer_fullname}} ,<br>Возможно, вы пропустили дату платежа, и счет теперь просрочен {{invoice_overdue_days}} дней.<br><br><table width=\'100%\'><tr><td>Справка : </td><th>{{invoice_reference}}</th></tr><tr><td>Дата : </td><th>{{invoice_date}}</th></tr><tr><td>Срок : </td><th>{{invoice_date_due}}</th></tr><tr><td>Всего : </td><th>{{invoice_total}}</th></tr><tr><td>платежи : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Общая сумма : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Выставленный счет</a>', 'invoice|customer|company'),
(87, 'send_overdue_reminder.tpl', 'romanian', 'Aveți facturi neachitate de la {{company_name}}', 'Bună {{customer_fullname}},<br>S-ar putea să fi întarziat data de plații, iar factura este acum depășită de <b>{{invoice_overdue_days}}</b> zile<br><br><table width=\'100%\'><tr><td>Referinţă : </td><th>{{invoice_reference}}</th></tr><tr><td>Data : </td><th>{{invoice_date}}</th></tr><tr><td>Data scadentă : </td><th>{{invoice_date_due}}</th></tr><tr><td>Total : </td><th>{{invoice_total}}</th></tr><tr><td>Plăţile : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Total datorat : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Factură</a>', 'invoice|customer|company'),
(88, 'send_overdue_reminder.tpl', 'german', 'Sie haben unbezahlte Rechnungen aus {{company_name}} ', 'sehr geehrter {{customer_fullname}} ,<br>Vielleicht haben Sie das Zahlungsdatum verpasst und die Rechnung ist jetzt überfällig {{invoice_overdue_days}} Tage.<br><br><table width=\'100%\'><tr><td>Referenz : </td><th>{{invoice_reference}}</th></tr><tr><td>Datum : </td><th>{{invoice_date}}</th></tr><tr><td>Geburtstermin : </td><th>{{invoice_date_due}}</th></tr><tr><td>Gesamt : </td><th>{{invoice_total}}</th></tr><tr><td>Zahlungen : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Gesamt fällig : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Rechnung</a>', 'invoice|customer|company'),
(89, 'send_overdue_reminder.tpl', 'italian', 'Hai fatture non pagate da {{company_name}} ', 'caro {{customer_fullname}} ,<br>Potresti aver perso la data di pagamento e la fattura è ormai scaduta {{invoice_overdue_days}} giorni.<br><br><table width=\'100%\'><tr><td>Riferimento : </td><th>{{invoice_reference}}</th></tr><tr><td>Data : </td><th>{{invoice_date}}</th></tr><tr><td>Scadenza : </td><th>{{invoice_date_due}}</th></tr><tr><td>Totale : </td><th>{{invoice_total}}</th></tr><tr><td>pagamenti : </td><th>{{invoice_total_payments}}</th></tr><tr><td>Totale dovuto : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">Fattura</a>', 'invoice|customer|company'),
(90, 'send_overdue_reminder.tpl', 'arabic', 'لديك فواتير غير مدفوعة م', 'العزيز {{customer_fullname}} ،<br>قد تكون قد فاتك تاريخ الدفع إلى الفاتورة متأخرة الآن {{invoice_overdue_days}} أيام.<br><br><table width=\'100%\'><tr><td>الرقم المرجعي : </td><th>{{invoice_reference}}</th></tr><tr><td>التاريخ : </td><th>{{invoice_date}}</th></tr><tr><td>تاريخ الاستحقاق : </td><th>{{invoice_date_due}}</th></tr><tr><td>المجموع : </td><th>{{invoice_total}}</th></tr><tr><td>المدفوعات : </td><th>{{invoice_total_payments}}</th></tr><tr><td>الاجمالي المستحق : </td><th>{{invoice_total_due}}</th></tr></table><br><a href=\"{{invoice_link}}\" target=\"_blank\">فاتورة</a>', 'invoice|customer|company'),
(91, 'send_forgotten_password.tpl', 'english', 'Forgotten Password Verification - {{company_name}}', 'Hi {{user_first_name}},<br>We have received a request to reset your password.<br>Your username is <b>{{user_username}}</b>.<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Reset Password</a>', 'user|company'),
(92, 'send_forgotten_password.tpl', 'french', 'Vérification du mot de passe oublié - {{company_name}}', 'Salut {{user_first_name}},<br>Nous avons reçu une demande pour réinitialiser votre mot de passe..<br>Votre nom d\'utilisateur est <b>{{user_username}}</b>.<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Réinitialiser le mot de passe</a>', 'user|company'),
(93, 'send_forgotten_password.tpl', 'spanish', 'Verificación de la contraseña olvidada - {{company_name}}', 'Su,<br>Hemos recibido una solicitud para restablecer su contraseña. <br> Su nombre de usuario es <b>{{user_username}}</b> .<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Restablecer la contraseña</a>', 'user|company'),
(94, 'send_forgotten_password.tpl', 'turkish', 'Unutulan Parola Doğrulaması - {{company_name}}', 'Merhaba{{user_first_name}},<br>Şifrenizi sıfırlama talebi aldık. <br> Kullanıcı adınız <b>{{user_username}}</b> .<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Şifreyi yenile</a>', 'user|company'),
(95, 'send_forgotten_password.tpl', 'russian', 'Проверка забытого пароля - {{company_name}}', 'Его,<br>Мы получили запрос на сброс пароля. <br> Ваше имя пользователя <b>{{user_username}}</b> .<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Сброс пароля</a>', 'user|company'),
(96, 'send_forgotten_password.tpl', 'romanian', 'Verificarea parolei uitată - {{company_name}}', 'Bună {{user_first_name}},<br>Am primit o solicitare de resetare a parolei.<br>Numele tău de utilizator este <b>{{user_username}}</b>.<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Resetează parola</a>', 'user|company'),
(97, 'send_forgotten_password.tpl', 'german', 'Passwort vergessen - {{company_name}}', 'Hallo {{user_first_name}} ,<br>Wir haben eine Anfrage erhalten, um Ihr Passwort zurückzusetzen. <br> Dein Benutzername ist {{user_username}} .<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Passwort zurücksetzen</a>', 'user|company'),
(98, 'send_forgotten_password.tpl', 'italian', 'Dimenticata la verifica della password - {{company_name}}', 'Ciao {{user_first_name}} ,<br>Abbiamo ricevuto una richiesta di reimpostazione della password. <br> Il tuo nome utente è {{user_username}} .<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">Resetta la password</a>', 'user|company'),
(99, 'send_forgotten_password.tpl', 'arabic', 'التحقق من كلمة المرور المنسية - {{company_name}}', 'مرحبا {{user_first_name}},<br>لقد تلقينا طلبا لإعادة تعيين كلمة المرور الخاصة بك.<br> اسم المستخدم الخاص بك هو <b>{{user_username}}</b>.<br><a href=\"{{user_forgotten_password_code}}\" target=\"_blank\">إعادة تعيين كلمة المرور</a>', 'user|company'),
(100, 'send_activate.tpl', 'english', 'Account Activation - {{company_name}}', 'Congratulation !<br>Hi <b>{{user_username}}</b>, you have successfully registered on the <i>{{company_name}}</i>.<br>To activate your account, please confirm your registration.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirm registration</a>', 'user|company'),
(101, 'send_activate.tpl', 'french', 'Activation du compte - {{company_name}}', 'Félicitation !<br>Salut <b>{{user_username}}</b>, Vous avez enregistré avec succès sur le <i>{{company_name}}</i>.<br>Pour activer votre compte, veuillez confirmer votre inscription.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmer l\'inscription</a>', 'user|company'),
(102, 'send_activate.tpl', 'spanish', 'activación de cuenta - {{company_name}}', 'Enhorabuena !<br>Hola <b>{{user_username}}</b> , has registrado correctamente en el <i>{{company_name}}</i> . <br> Para activar su cuenta, confirme su registro.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmar registro</a>', 'user|company'),
(103, 'send_activate.tpl', 'turkish', 'Hesap Aktivasyonu - {{company_name}}', 'Tebrikler!<br>Merhaba, <b>{{user_username}}</b> , başarıyla <i>{{company_name}}</i> kayıt yaptın. <br> Hesabınızı etkinleştirmek için lütfen kayıt işleminizi onaylayın.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Kaydı onayla</a>', 'user|company'),
(104, 'send_activate.tpl', 'russian', 'Активация аккаунта - {{company_name}}', 'Поздравляем!<br>Привет <b>{{user_username}}</b> , вы успешно зарегистрировались на <i>{{company_name}}</i> . <br> Чтобы активировать свою учетную запись, пожалуйста, подтвердите свою регистрацию.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Подтверждение регистрации</a>', 'user|company'),
(105, 'send_activate.tpl', 'romanian', 'Activare cont - {{company_name}}', 'Felicitari !<br>Bună <b>{{user_username}}</b>, v-ați înregistrat cu succes pe <i>{{company_name}}</i>.<br>Pentru a vă activa contul, vă rugăm să confirmați înregistrarea.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmați înregistrarea</a>', 'user|company'),
(106, 'send_activate.tpl', 'german', 'Account Aktivierung - {{company_name}}', 'Herzlichen Glückwunsch!<br>Hallo {{user_username}} Du hast dich erfolgreich registriert<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Registrierung bestätigen</a>', 'user|company'),
(107, 'send_activate.tpl', 'italian', 'attivazione dell&#39;account - {{company_name}}', 'Congratulazioni!<br>Ciao {{user_username}} , sei stato registrato con successo nel sito<br><a href=\"{{user_activation_code}}\" target=\"_blank\">Conferma la registrazione</a>', 'user|company'),
(108, 'send_activate.tpl', 'arabic', 'تنشيط الحساب - {{company_name}}', 'تهنئة !<br>مرحبا <b>{{user_username}}</b>, لقد سجلت بنجاح على <i>{{company_name}}</i>.<br> لتفعيل حسابك، يرجى تأكيد تسجيلك.<br><a href=\"{{user_activation_code}}\" target=\"_blank\">تأكيد التسجيل</a>', 'user|company'),
(109, 'send_activate_customer.tpl', 'english', 'Account Activation - {{company_name}}', 'Congratulation !<br>Hi <b>{{user_username}}</b>, you have successfully registered on the <i>{{company_name}}</i>.<br>To activate your account, please confirm your registration.<br><br><p>Username :<b>{{user_username}}</b><br>Password :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirm registration</a>', 'user|company'),
(110, 'send_activate_customer.tpl', 'french', 'Activation du compte - {{company_name}}', 'Félicitation !<br>Salut <b>{{user_username}}</b>, Vous avez enregistré avec succès sur le <i>{{company_name}}</i>.<br>Pour activer votre compte, veuillez confirmer votre inscription.<br><br><p>Nom d\'utilisateur :<b>{{user_username}}</b><br>Mot de passe : :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmer l\'inscription</a>', 'user|company'),
(111, 'send_activate_customer.tpl', 'spanish', 'activación de cuenta - {{company_name}}', 'Enhorabuena !<br>Hola <b>{{user_username}}</b> , has registrado correctamente en el <i>{{company_name}}</i> . <br> Para activar su cuenta, confirme su registro.<br><br><p>Nombre de usuario :<b>{{user_username}}</b><br>Contraseña :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmar registro</a>', 'user|company'),
(112, 'send_activate_customer.tpl', 'turkish', 'Hesap Aktivasyonu - {{company_name}}', 'Tebrikler!<br>Merhaba, <b>{{user_username}}</b> , başarıyla <i>{{company_name}}</i> kayıt yaptın. <br> Hesabınızı etkinleştirmek için lütfen kayıt işleminizi onaylayın.<br><br><p>Kullanıcı adı :<b>{{user_username}}</b><br>Parola :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Kaydı onayla</a>', 'user|company'),
(113, 'send_activate_customer.tpl', 'russian', 'Активация аккаунта - {{company_name}}', 'Поздравляем!<br>Привет <b>{{user_username}}</b> , вы успешно зарегистрировались на <i>{{company_name}}</i> . <br> Чтобы активировать свою учетную запись, пожалуйста, подтвердите свою регистрацию.<br><br><p>Имя пользователя :<b>{{user_username}}</b><br>пароль :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Подтверждение регистрации</a>', 'user|company'),
(114, 'send_activate_customer.tpl', 'romanian', 'Activare cont - {{company_name}}', 'Felicitari !<br>Bună <b>{{user_username}}</b>, v-ați înregistrat cu succes pe <i>{{company_name}}</i>.<br>Pentru a vă activa contul, vă rugăm să confirmați înregistrarea.<br><br><p>Nume de utilizator :<b>{{user_username}}</b><br>Parola :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Confirmați înregistrarea</a>', 'user|company'),
(115, 'send_activate_customer.tpl', 'german', 'Account Aktivierung - {{company_name}}', 'Herzlichen Glückwunsch!<br>Hallo {{user_username}} Du hast dich erfolgreich registriert<br><br><p>Benutzername :<b>{{user_username}}</b><br>Passwort :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Registrierung bestätigen</a>', 'user|company'),
(116, 'send_activate_customer.tpl', 'italian', 'attivazione dell&#39;account - {{company_name}}', 'Congratulazioni!<br>Ciao {{user_username}} , sei stato registrato con successo nel sito<br><br><p>Nome utente :<b>{{user_username}}</b><br>Parola d&#39;ordine :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">Conferma la registrazione</a>', 'user|company'),
(117, 'send_activate_customer.tpl', 'arabic', 'تنشيط الحساب - {{company_name}}', 'تهنئة !<br>مرحبا <b>{{user_username}}</b>, لقد سجلت بنجاح على <i>{{company_name}}</i>.<br> لتفعيل حسابك، يرجى تأكيد تسجيلك.<br><br><p>إسم العضوية :<b>{{user_username}}</b><br>كلمه المرور: :<b>{{user_password}}</b></p><br><a href=\"{{user_activation_code}}\" target=\"_blank\">تأكيد التسجيل</a>', 'user|company'),
(118, 'send_file.tpl', 'english', 'File from {{company_name}}', '', 'file|company'),
(119, 'send_file.tpl', 'french', 'Fichier de {{company_name}} ', '', 'file|company'),
(120, 'send_file.tpl', 'spanish', 'Archivo de {{company_name}} ', '', 'file|company'),
(121, 'send_file.tpl', 'turkish', 'Dosyasından {{company_name}} ', '', 'file|company'),
(122, 'send_file.tpl', 'russian', 'Файл из {{company_name}} ', '', 'file|company'),
(123, 'send_file.tpl', 'romanian', 'Fișier la {{company_name}}', '', 'file|company'),
(124, 'send_file.tpl', 'german', 'Datei von {{company_name}} ', '', 'file|company'),
(125, 'send_file.tpl', 'italian', 'File da {{company_name}} ', '', 'file|company'),
(126, 'send_file.tpl', 'arabic', 'ملف من {{company_name}} ', '', 'file|company');";



function checkPostData($name, $min_size, $max_size)
{
  $data = isset($_POST[$name]) ? trim($_POST[$name]) : '';
  $s = mb_strlen($data);
  if($s < $min_size || $s > $max_size)return NULL;
  return $data;
}

function htmlEntitiesEx($string)
{
  return htmlspecialchars(preg_replace('|[\x00-\x09\x0B\x0C\x0E-\x1F\x7F-\x9F]|u', ' ', $string), ENT_QUOTES, 'UTF-8');
}

function CreateTable($name)
{
  global $_TABLES;
  global $mysql_connection;
  ShowProgress("Creating table <b>'{$name}'</b>.");
  if(!@mysqli_query($mysql_connection, "DROP TABLE IF EXISTS `{$name}`") || !@mysqli_query($mysql_connection, "CREATE TABLE `{$name}` ({$_TABLES[$name]}) ENGINE=InnoDB CHARACTER SET=".MYSQL_CODEPAGE." COLLATE=".MYSQL_COLLATE))
  {
    ShowError("Failed: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
    return false;
  }
  return true;
}

function deleteTable($mysql_connection, $name)
{
  ShowProgress("Delete table <b>'{$name}'</b>.");
  @mysqli_query($mysql_connection, 'SET foreign_key_checks = 0');
  if( !@mysqli_query($mysql_connection, "DROP TABLE IF EXISTS `{$name}`") )
  {
    ShowError("Failed: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
    return false;
  }
  @mysqli_query($mysql_connection, 'SET foreign_key_checks = 1');
  return true;
}

function exec_sql($sql)
{
  global $mysql_connection;
  if( !@mysqli_query($mysql_connection, $sql) )
  {
    ShowError("Failed: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
    return false;
  }
  return true;
}

function hash_password($password)
{
  if (empty($password))
  {
    return FALSE;
  }
  require_once("application/libraries/Bcrypt.php");
  $params['rounds'] = 8;
  $params['salt_prefix'] = version_compare(PHP_VERSION, '5.3.7', '<') ? '$2a$' : '$2y$';
  $bcrypt = new Bcrypt($params);
  return $bcrypt->hash($password);
}

function updateConfigHelper($updateList, $name, $default)
{
  return isset($updateList[$name])?$updateList[$name]:$default;
}

function updateConfig($updateList)
{
  $file    = defined('FILE_CONFIG') ? FILE_CONFIG : 'config.php';
  $oldfile = $file.'.old.php';
  @chmod(@dirname($file), 0755);
  @chmod($file,           0755);
  @chmod($oldfile,        0755);
  @unlink($oldfile);
  if(is_file($file) && !@rename($file, $oldfile)){
    return false;
  }
  else
  {
    $cfgData = "<?php\n".
    "define('CONFIG_MYSQL_HOST', '".addslashes(updateConfigHelper($updateList, 'mysql_host', 'localhost'))."');\n".
    "define('CONFIG_MYSQL_USER', '".addslashes(updateConfigHelper($updateList, 'mysql_user', 'root'))."');\n".
    "define('CONFIG_MYSQL_PASS', '".addslashes(updateConfigHelper($updateList, 'mysql_pass', ''))."');\n".
    "define('CONFIG_MYSQL_DB', '".addslashes(updateConfigHelper($updateList, 'mysql_db', 'sis_database'))."');\n".
    "define('CONFIG_BASE_URL', '".addslashes(updateConfigHelper($updateList, 'base_url', ''))."');\n".
    "?>";
    if(@file_put_contents($file, $cfgData) !== strlen($cfgData))
      return false;
    //@chmod(@dirname($file), 0444);
  }
  return true;
}

function is_ok(){
    return '<span class="label label-pill label-success"><i class="fa fa-check"></i></span>';
}

function not_ok(){
    return '<span class="label label-pill label-danger"><i class="fa fa-remove"></i></span>';
}


function ShowError($text)
{
  global $MSG_PROGRESS;
  $MSG_PROGRESS .= "<p class='text-danger'>&#8226; ERROR: ".$text."</p>";
}

function ShowProgress($text)
{
  global $MSG_PROGRESS;
  $MSG_PROGRESS .= "<p class='text-success'>&#8226; ".$text."</p>";
}

if( isset($_POST['step']) && $_POST['step'] == "0" )
{
    $step++;
}

if( isset($_POST['step']) && $_POST['step'] == "1" )
{
    $pd_mysql_host      = checkPostData('mysql_host',   1, 256);
    $pd_mysql_user      = checkPostData('mysql_user',   1, 256);
    $pd_mysql_pass      = checkPostData('mysql_pass',   0, 256);
    $pd_mysql_db        = checkPostData('mysql_db',     1, 256);

  if(!$error){
    if($pd_mysql_host === NULL || $pd_mysql_user === NULL || $pd_mysql_db === NULL)
    {
      ShowError('Bad format of MySQL server data.');
      $error = true;
    }
  }
  if(!$error){
    ShowProgress("Connecting to MySQL as <b>'{$pd_mysql_user}'</b>.");
    $mysql_connection = @mysqli_connect($pd_mysql_host, $pd_mysql_user, $pd_mysql_pass);
    // Change character set to utf8
    @mysqli_set_charset($mysql_connection,"utf8");


    if (mysqli_connect_errno()) {
        ShowError("Connect failed: ". mysqli_connect_error());
      $error = true;
    }
  }

  if(!$error){
    if(!$mysql_connection || !@mysqli_query($mysql_connection, 'SET NAMES \''.MYSQL_CODEPAGE.'\' COLLATE \''.MYSQL_COLLATE.'\''))
    {
      ShowError("Failed connect to MySQL server: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
      $error = true;
    }
  }
  if(!$error){
    $db = addslashes($pd_mysql_db);
    ShowProgress("Selecting DB <b>'{$pd_mysql_db}'</b>.");

    if(!@mysqli_query($mysql_connection, "CREATE DATABASE IF NOT EXISTS `{$db}`"))
    {
      ShowError("Failed to create database: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
      $error = true;
    }
    else if(!@mysqli_select_db($mysql_connection, $pd_mysql_db))
    {
      ShowError("Failed to select database: ".htmlEntitiesEx(mysqli_error($mysql_connection)));
      $error = true;
    }
    @mysqli_query($mysql_connection, "ALTER DATABASE `{$db}` CHARACTER SET ".MYSQL_CODEPAGE." COLLATE ".MYSQL_COLLATE);
  }

  if( !$error ){
    $step++;
  }
}


if( isset($_POST['step']) && $_POST['step'] == "2" && isset($_POST['action']) && $_POST['action'] == "droptables" )
{
  $pd_mysql_host = checkPostData('mysql_host',   1, 255);
  $pd_mysql_user = checkPostData('mysql_user',   1, 255);
  $pd_mysql_pass = checkPostData('mysql_pass',   0, 255);
  $pd_mysql_db   = checkPostData('mysql_db',     1, 255);
  $mysql_connection = @mysqli_connect($pd_mysql_host, $pd_mysql_user, $pd_mysql_pass);
  if( $mysql_connection ){
    @mysqli_select_db($mysql_connection, $pd_mysql_db);
    // Change character set to utf8
    @mysqli_set_charset($mysql_connection,"utf8");
    if(!$error){
      foreach($_TABLES as $table => $v)
      {
        $error = !deleteTable($mysql_connection, $table);
        if($error)
          break;
      }
    }
  }
}

$show_delete_tables = false;

if( isset($_POST['step']) && $_POST['step'] == "2" && !isset($_POST['action']))
{
  $pd_mysql_host = checkPostData('mysql_host',   1, 255);
  $pd_mysql_user = checkPostData('mysql_user',   1, 255);
  $pd_mysql_pass = checkPostData('mysql_pass',   0, 255);
  $pd_mysql_db   = checkPostData('mysql_db',     1, 255);
  $pd_user       = checkPostData('user',         1,  20);
  $pd_pass       = checkPostData('pass',         6,  64);
  $mysql_connection = @mysqli_connect($pd_mysql_host, $pd_mysql_user, $pd_mysql_pass);
  @mysqli_select_db($mysql_connection, $pd_mysql_db);
  // Change character set to utf8
  @mysqli_set_charset($mysql_connection,"utf8");

    if( $pd_user === NULL || $pd_pass === NULL )
    {
      ShowError('Bad format of login data.');
      $error = true;
    }

  if(!$error){
    foreach($_TABLES as $table => $v)
    {
      $error = !CreateTable($table);
      if($error)
        break;
    }

    if( $error ){
      $show_delete_tables = true;
      ShowError('Error on tables creation, please drop all tables and try again !');
    }
  }

  if(!$error){
    ShowProgress("Configure application settings ...");
    foreach($_ADDITIONAL_SQL as $sql)
    {
      $error = !exec_sql($sql);
      if($error)
        break;
    }
  }

  if(!$error)
  {
    ShowProgress("Adding user <b>'{$pd_user}'</b>.");
    $pd_pass   = hash_password($pd_pass, FALSE);
    $sql = "INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `forgotten_password_time`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (1, '127.0.0.1', '{$pd_user}', '{$pd_pass}', '', 'admin@admin.com', '', NULL, NULL, '3o6rg9FOxhHe31KAtJaLG.', 1268889823, 1483117005, 1, 'Admin', 'istrator', 'ADMIN', '0');";
    $error = !exec_sql($sql);
  }

  if(!$error)
  {
    $sql = "INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (1, 1, 1),(2, 1, 2),(3, 1, 5);";
    $error = !exec_sql($sql);
  }

  if(!$error)
  {
      ShowProgress("Writing config file");
        $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))?"https://":"http://";
      $pd_path  = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
      $pd_path  = substr($pd_path, 0, strripos($pd_path, "/")+1);

      $updateList['mysql_host'] = $pd_mysql_host;
      $updateList['mysql_user'] = $pd_mysql_user;
      $updateList['mysql_pass'] = $pd_mysql_pass;
      $updateList['mysql_db']   = $pd_mysql_db;
      $updateList['base_url']   = $pd_path;

      if(!updateConfig($updateList))
      {
        ShowError("Failed write to config file.");
        $error = true;
      }
  }

  if( !$error ){
    $MSG_PROGRESS .= "<p>Installation complete!</p>";
    $step++;
  }
}

if( isset($_POST['step']) && $_POST['step'] == "3" )
{
  $step++;
}
?>
<!DOCTYPE html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
  <title>Smart Invoice System - Installer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta content="Smart Invoice System - [SIS]" name="description" />
  <meta content="bessemzitouni" name="author" />
  <link rel="SHORTCUT ICON" href="assets/img/favicon.png"/>
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/installer.css" rel="stylesheet">
  <script src="assets/js/libs/jquery.min.js" type="text/javascript"></script>
  <link rel="shortcut icon" href="assets/img/favicon.png">
  <style type="text/css">
      .wizard-steps li {
          max-width: <?php echo 100/$step_count; ?>%;
          min-width: <?php echo 100/$step_count; ?>%;
      }
  </style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="">
<!-- BEGIN CONTAINER -->
<div class="page-container row-fluid">
  <!-- BEGIN PAGE CONTAINER-->
  <div class="page-content" style="margin: 0 !important;">
    <div class="content">

<!-- INSTALLATION -->
  <div class="col-md-2"></div>
  <div class="col-md-8">
        <div class="card">
          <div class="card-header text-md-left">
            <span class="label label-danger label-pill pull-right">STEP <?php echo ($step+1)."/".$step_count; ?></span>
            <h5>
              Smart Invoice System <small class="text-muted">v 1.9.2</small> Installer
            </h5>
          </div>
          <div class="card-block">

            <div class="form-wizard-steps">
              <ul class="wizard-steps">
                <li class="<?php echo $step=="0"?"active":""; ?>" ><span class="step"><?php echo $step_counter ++; ?></span><span class="title">Requirements</span></li>
                <li class="<?php echo $step=="1"?"active":""; ?>" ><span class="step"><?php echo $step_counter ++; ?></span><span class="title">MySQL server</span></li>
                <li class="<?php echo $step=="2"?"active":""; ?>"><span class="step"><?php echo $step_counter ++; ?></span><span class="title">Root user</span></li>
                <li class="<?php echo $step=="3"?"active":""; ?>"><span class="step"><?php echo $step_counter ++; ?></span><span class="title">Install</span></li>
                <li class="<?php echo $step=="4"?"active":""; ?>"><span class="step"><?php echo $step_counter ++; ?></span><span class="title">Finish</span></li>
              </ul>
              <div class="clearfix"></div>
            </div><br /><br />

<?php $step_counter = 1; ?>

<?php if ($step == 0): ?>
    <?php
        $php = phpversion();
        $ver = explode(" ", $_SERVER["SERVER_SOFTWARE"],3);
        $apache = ($ver[0] . " " . $ver[1]);
        $v=explode("/", $ver[0]);
        $apache_version = floatval($v[1]);
        $apache = ($apache_version==0)?$apache."/".$minApache:$apache;
        $apache_version = ($apache_version==0)?$minApache:$apache_version;
        $po_requirements = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && function_exists('curl_version');
        $curl_version = function_exists('curl_version')?curl_version():array("version"=>"Disabled");
        $ssl_version = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))?OPENSSL_VERSION_TEXT:"Disabled";

        $zip = extension_loaded('zip')?           (phpversion("zip")==phpversion()?"Enable":phpversion("zip"))           :"Disable";
        $pdo = extension_loaded('pdo')?           (phpversion("pdo")==phpversion()?"Enable":phpversion("pdo"))           :"Disable";
        $mysqli = extension_loaded('mysqli')?     (phpversion("mysqli")==phpversion()?"Enable":phpversion("mysqli"))     :"Disable";
        $mbstring = extension_loaded('mbstring')? (phpversion("mbstring")==phpversion()?"Enable":phpversion("mbstring")) :"Disable";
        $mcrypt = extension_loaded('mcrypt')?     (phpversion("mcrypt")==phpversion()?"Enable":phpversion("mcrypt"))     :"Disable";
        $gd = extension_loaded('gd')?             (phpversion("gd")==phpversion()?"Enable":phpversion("gd"))             :"Disable";

        $minRequirements = phpversion() >= $minPHP && ($apache_version >= $minApache || strpos($apache, "LiteSpeed")) && is_writable("storage");
    ?>
    <form class="form-login form-horizontal" method="post">
        <hr>
        <div class="card-title text-sm-center">
            <span class="font-weight-bold">Step 1 - </span>
            <span class="font-weight-light">Minimum System Requirements</span>
        </div>
        <hr>
        <table class="table table-sm table-striped">
            <thead class="transparent">
                <tr>
                    <th width="20%">Rquirement</th>
                    <th>Version</th>
                    <th width="10%">Minumum</th>
                    <th width="16">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-inverse">
                  <th colspan="5" class="text-md-center font-weight-normal">System & Extensions</th>
                </tr>
                <!-- System -->
                <tr>
                    <th>PHP</th>
                    <td><?php echo $php; ?></td>
                    <td><span class="label label-default"><?php echo $minPHP ?></span></td>
                    <td><?php echo phpversion () < $minPHP ? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>Apache server</th>
                    <td><?php echo $apache; ?></td>
                    <td><span class="label label-default"><?php echo $minApache ?></span></td>
                    <td><?php echo ($apache_version < $minApache && trim($apache) != "LiteSpeed") ? not_ok () : is_ok () ?></td>
                </tr>
                <!-- Extensions -->
                <tr>
                    <th>Curl</th>
                    <td><?php echo ($curl_version["version"]); ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !function_exists("curl_version")? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>SSL</th>
                    <td><?php echo $ssl_version; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>ZIP Extension</th>
                    <td><?php echo $zip; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("zip"))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>PDO Extension</th>
                    <td><?php echo $pdo; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("pdo"))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>Mysqli Extension</th>
                    <td><?php echo $mysqli; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("mysqli"))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>MBString Extension</th>
                    <td><?php echo $mbstring; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("mbstring"))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>Mcrypt Extension</th>
                    <td><?php echo $mcrypt; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("mcrypt"))? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>GD Extension</th>
                    <td><?php echo $gd; ?></td>
                    <td><span class="label label-default">Enable</span></td>
                    <td><?php echo !(extension_loaded("gd"))? not_ok () : is_ok () ?></td>
                </tr>
                <!-- Files & Folders Permissions -->
                <tr class="table-inverse">
                  <th colspan="5" class="text-md-center font-weight-normal">Files & Folders Permissions</th>
                </tr>
                <tr>
                    <th>Upload Folder</th>
                    <td>"<b>Storage</b>" is Writable</td>
                    <td><span class="label label-default">Writable</span></td>
                    <td><?php echo !is_writable("storage") ? not_ok () : is_ok () ?></td>
                </tr>
                <tr>
                    <th>Backups Folder</th>
                    <td>"<b>Backups</b>" is Writable</td>
                    <td><span class="label label-default">Writable</span></td>
                    <td><?php echo !is_writable("backups") ? not_ok () : is_ok () ?></td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="text-md-right">
            <?php if ($minRequirements): ?>
                <input type="hidden" name="step" value="0">
                <input type="submit" name="next" value="Let's Begin !" class="btn btn-success">
            <?php else: ?>
                <button type="button" class="btn">Let's Begin !</button>
            <?php endif ?>
        </div>
    </form>
<?php endif ?>


<?php if ($step == 1): ?>
  <form class="form-login form-horizontal" method="post">
    <input type="hidden" name="step" value="1">
    <hr>
    <div class="card-title text-sm-center">
      <span class="font-weight-bold">Step 2 - </span>
      <span class="font-weight-light">MySQL server</span>
    </div>
    <hr>
        <?php if (isset($MSG_PROGRESS) && !empty($MSG_PROGRESS)): ?>
          <?php echo $MSG_PROGRESS ?>
        <?php endif ?>
    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="host">Host</label>
      <div class="col-md-5">
        <input type="text" name="mysql_host" id="host" class="form-control" value="<?php echo isset($_POST['mysql_host'])?$_POST['mysql_host']:"localhost" ?>" autofocus />
      </div>
    </div>
    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="user">User</label>
      <div class="col-md-5">
        <input type="text" name="mysql_user" id="user" class="form-control" value="<?php echo isset($_POST['mysql_user'])?$_POST['mysql_user']:"root" ?>" />
      </div>
    </div>
    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="pass">Password</label>
      <div class="col-md-5">
        <input type="text" name="mysql_pass" id="pass" class="form-control" value="<?php echo isset($_POST['mysql_pass'])?$_POST['mysql_pass']:"" ?>" />
      </div>
    </div>
    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="database">Database</label>
      <div class="col-md-5">
        <input type="text" name="mysql_db" id="database" class="form-control" value="<?php echo isset($_POST['mysql_db'])?$_POST['mysql_db']:"sis_database" ?>" />
      </div>
    </div>
    <hr>
    <div class="text-md-right">
      <input type="submit" name="next" value="Next" class="btn btn-primary">
    </div>
  </form>
<?php endif ?>


<?php if ($step == 2): ?>
  <form class="form-login form-horizontal" method="post">
    <input type="hidden" name="step" value="2">
    <input type="hidden" name="mysql_host" value="<?php echo $pd_mysql_host; ?>">
    <input type="hidden" name="mysql_user" value="<?php echo $pd_mysql_user; ?>">
    <input type="hidden" name="mysql_pass" value="<?php echo $pd_mysql_pass; ?>">
    <input type="hidden" name="mysql_db" value="<?php echo $pd_mysql_db; ?>">
    <hr>
    <div class="card-title text-sm-center">
      <span class="font-weight-bold">Step 3 - </span>
      <span class="font-weight-light">Root User</span>
    </div>
    <hr>
        <?php if (isset($MSG_PROGRESS) && !empty($MSG_PROGRESS)): ?>
          <?php echo $MSG_PROGRESS ?>
        <?php endif ?>

    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="user">Username</label>
      <div class="col-md-5">
        <input type="text" name="user" id="user" class="form-control" value="admin" autofocus />
      </div>
    </div>
    <div class="row form-group">
      <label class="form-control-label col-md-3 required" for="pass">Password</label>
      <div class="col-md-5">
        <input type="password" name="pass" id="pass" class="form-control" value="" />
      </div>
        <i class="help" style="margin-left: 12px;">(6-64 characters)</i>
    </div>
    <hr>
    <div class="text-md-right">
      <?php if ($show_delete_tables): ?>
        <a href="#" onclick="document.getElementById('droptable').submit();" class="btn btn-danger">Drop all tables</a>
      <?php endif ?>
      <input type="submit" name="next" value="Next" class="btn btn-primary">
    </div>
  </form>
<?php endif ?>


<?php if ($step == 3): ?>
  <form class="form-login form-horizontal" method="post">
    <input type="hidden" name="step" value="3">
    <hr>
    <div class="card-title text-sm-center">
      <span class="font-weight-bold">Step 4 - </span>
      <span class="font-weight-light">Install</span>
    </div>
    <hr>
        <?php if (isset($MSG_PROGRESS) && !empty($MSG_PROGRESS)): ?>
          <?php echo $MSG_PROGRESS ?>
        <?php endif ?>

    <hr>
    <div class="text-md-right">
      <input type="submit" name="next" value="Next" class="btn btn-primary">
    </div>
  </form>
<?php endif ?>


<?php if ($step == 4): ?>
  <form class="form-login form-horizontal" method="post">
    <hr>
    <div class="card-title text-sm-center">
      <span class="font-weight-bold">Step 5 - </span>
      <span class="font-weight-light">Finish</span>
    </div>
    <hr>
    <div class="well well-small">
      Thank you for installing Smart <span class="font-weight-bold">Invoice</span> System v 1.9.2 <br>
    </div>
    <hr>
    <div class="text-md-right">
      <a href="index.php" class="btn btn-primary">Login</a>
    </div>
  </form>
<?php endif ?>


          </div>
        </div>
      </div>
<!-- INSTALLATION END -->
    </div>
  </div>
 </div>
<!-- END CONTAINER -->

<div class="modal fade" id="player_modal" style="width: 420px;margin-left:-210px;"></div>
<!-- BEGIN CORE JS FRAMEWORK-->
<script src="assets/vendor/jquery-ui/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- END CORE JS FRAMEWORK -->
</body>
</html>
<form method="post" id="droptable">
    <input type="hidden" name="mysql_host" value="<?php echo $pd_mysql_host; ?>">
    <input type="hidden" name="mysql_user" value="<?php echo $pd_mysql_user; ?>">
    <input type="hidden" name="mysql_pass" value="<?php echo $pd_mysql_pass; ?>">
    <input type="hidden" name="mysql_db" value="<?php echo $pd_mysql_db; ?>">
    <input type="hidden" name="step" value="2">
    <input type="hidden" name="action" value="droptables">
  </form>
