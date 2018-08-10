CREATE DATABASE php_contact_form;

USE php_contact_form;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `sent` tinyint(1) UNSIGNED DEFAULT 0 NOT NULL,
  PRIMARY KEY (`id`)
);
