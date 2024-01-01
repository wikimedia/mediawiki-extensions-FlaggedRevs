DROP TABLE IF EXISTS /*_*/flaggedrevs_tmp;
CREATE TABLE /*_*/flaggedrevs_tmp (
  -- Foreign key to revision.rev_id
  fr_rev_id integer unsigned NOT NULL PRIMARY KEY,
  -- Timestamp of revision reviewed (revision.rev_timestamp)
  fr_rev_timestamp varbinary(14) NOT NULL default '',
  -- Foreign key to page.page_id
  fr_page_id integer unsigned NOT NULL,
  -- Foreign key to user.user_id
  fr_user integer unsigned NOT NULL,
  -- Timestamp of review
  fr_timestamp varbinary(14) NOT NULL,
  -- Store the precedence level
  fr_quality tinyint(1) NOT NULL default 0,
  -- Store tag metadata as newline separated,
  -- colon separated tag:value pairs
  fr_tags mediumblob NOT NULL,
  -- Comma-separated list of flags:
  -- dynamic: conversion marker for inclusion handling (legacy schema had fr_text with PST text)
  -- auto: revision reviewed automatically
  fr_flags tinyblob NOT NULL
);
CREATE INDEX /*i*/fr_page_rev ON /*_*/flaggedrevs (fr_page_id,fr_rev_id);
CREATE INDEX /*i*/fr_page_time ON /*_*/flaggedrevs (fr_page_id,fr_rev_timestamp);
CREATE INDEX /*i*/fr_page_qal_rev ON /*_*/flaggedrevs (fr_page_id,fr_quality,fr_rev_id);
CREATE INDEX /*i*/fr_page_qal_time ON /*_*/flaggedrevs (fr_page_id,fr_quality,fr_rev_timestamp);
CREATE INDEX /*i*/fr_user ON /*_*/flaggedrevs (fr_user);

INSERT OR IGNORE INTO /*_*/flaggedrevs_tmp (fr_rev_id, fr_rev_timestamp, fr_page_id, fr_user, fr_timestamp, fr_quality, fr_tags, fr_flags)
  SELECT fr_rev_id, fr_rev_timestamp, fr_page_id, fr_user, fr_timestamp, fr_quality, fr_tags, fr_flags
  FROM /*_*/flaggedrevs;

DROP TABLE /*_*/flaggedrevs;

ALTER TABLE /*_*/flaggedrevs_tmp RENAME TO /*_*/flaggedrevs;
