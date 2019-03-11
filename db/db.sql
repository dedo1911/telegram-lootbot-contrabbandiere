
CREATE TABLE IF NOT EXISTS `contrabbandi` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `time` bigint(20) NOT NULL DEFAULT '0',
  `test` varchar(100) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `owner_id` bigint(20) NOT NULL DEFAULT '0',
  `item` varchar(100) NOT NULL,
  `prezzo` varchar(100) NOT NULL,
  `chat_id` bigint(20) NOT NULL DEFAULT '0',
  `message_id` bigint(20) NOT NULL DEFAULT '0',
  `creation` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(6) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rarity` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `value` int(10) NOT NULL,
  `estimate` int(10) NOT NULL,
  `craftable` int(1) NOT NULL,
  `reborn` int(1) NOT NULL,
  `power` int(4) NOT NULL,
  `power_armor` int(4) NOT NULL,
  `power_shield` int(4) NOT NULL,
  `dragon_power` int(4) NOT NULL,
  `critical` int(4) NOT NULL,
  `allow_sell` int(1) NOT NULL,
  `craft_pnt` int(4) NOT NULL DEFAULT '0',
  `material_1` int(5) NOT NULL DEFAULT '0',
  `material_2` int(5) NOT NULL DEFAULT '0',
  `material_3` int(5) NOT NULL DEFAULT '0',
  `secret_price` int(20) NOT NULL DEFAULT '0',
  `time_check` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `time_ins` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) DEFAULT CHARSET=latin1;