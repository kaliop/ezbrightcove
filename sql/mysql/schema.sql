CREATE TABLE `ezx_klpbc_video` (
  `contentobject_attribute_id` int(11) NOT NULL COMMENT 'Related content object attribute ID',
  `version` int(11) NOT NULL default '0' COMMENT 'Related content version',
  `input_type_identifier` varchar(255) NOT NULL COMMENT 'Video input type identifier',
  `state` int(1) NOT NULL default '0' COMMENT 'Media workflow status',
  `brightcove_id` varchar(255) NOT NULL COMMENT 'Brightcove media ID',
  `need_meta_update` int(11) NOT NULL COMMENT 'Whether we need to update this videos meta data',
  `error_log` varchar(255) NOT NULL default '' COMMENT 'Last error message',
  `created` int(11) NOT NULL COMMENT 'Creation date',
  `modified` int(11) NOT NULL COMMENT 'Modification date',
  PRIMARY KEY (`contentobject_attribute_id`,`version`),
  KEY `state_index` (`state`),
  KEY `meta_index` (`need_meta_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ezx_klpbc_serverfile` (
  `contentobject_attribute_id` int(11) NOT NULL COMMENT 'Related content object attribute ID',
  `version` int(11) NOT NULL default '0' COMMENT 'Related content version',
  `filepath` varchar(4096) NOT NULL COMMENT 'File path to video on server',
  PRIMARY KEY (`contentobject_attribute_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
