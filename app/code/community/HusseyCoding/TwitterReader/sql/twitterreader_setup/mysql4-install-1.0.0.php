<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE {$this->getTable('twitterreader/twitter_reader')} (
        `id` int unsigned NOT NULL auto_increment,
        `twitter_id` tinytext NOT NULL,
        `content` text NOT NULL,
        `store` tinytext NOT NULL,
        `screen_name` tinytext NOT NULL,
        `timestamp` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();