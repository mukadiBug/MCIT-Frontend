ALTER TABLE `invoices`
ADD `payment_date` DATE NULL DEFAULT NULL AFTER `total_due`,
ADD `double_currency` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recurring_id`,
ADD `rate` DECIMAL(25,4) NOT NULL DEFAULT '0' AFTER `double_currency`;

CREATE TABLE IF NOT EXISTS `expenses_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `method` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'cash',
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'released',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `expenses` ADD `total_due` DECIMAL(25,4) NOT NULL DEFAULT '0' AFTER `total`;

ALTER TABLE `expenses_payments` ADD FOREIGN KEY (`expense_id`) REFERENCES `expenses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `expenses_payments` (`expense_id`,`number`,`date`,`amount`,`method`,`details`,`status`)
SELECT `id`,`number`,`date`,`amount`,`payment_method`,'','released'
FROM `expenses`;


UPDATE `invoices`
LEFT JOIN `payments` ON `payments`.`invoice_id`=`invoices`.`id` AND `invoices`.`status`='paid'
SET `invoices`.`payment_date` = `payments`.`date` ;
