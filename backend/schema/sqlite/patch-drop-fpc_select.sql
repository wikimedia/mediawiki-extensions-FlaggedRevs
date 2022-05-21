DROP  INDEX fpc_expiry;
CREATE TEMPORARY TABLE /*_*/__temp__flaggedpage_config AS
  SELECT fpc_page_id, fpc_select, fpc_override, fpc_level, fpc_expiry
  FROM  /*_*/flaggedpage_config;
DROP  TABLE  /*_*/flaggedpage_config;
CREATE TABLE /*_*/flaggedpage_config (
  fpc_page_id integer unsigned NOT NULL PRIMARY KEY,
  fpc_override bool NOT NULL,
  fpc_level varbinary(60) NULL,
  fpc_expiry varbinary(14) NOT NULL default 'infinity'
) /*$wgDBTableOptions*/;
INSERT OR IGNORE INTO  /*_*/flaggedpage_config (fpc_page_id, fpc_override, fpc_level, fpc_expiry)
  SELECT fpc_page_id, fpc_override, fpc_level, fpc_expiry
  FROM  /*_*/__temp__flaggedpage_config;
DROP TABLE  /*_*/__temp__flaggedpage_config;
