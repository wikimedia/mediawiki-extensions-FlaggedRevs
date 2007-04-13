-- (c) Joerg Baach, Aaron Schulz, 2007

-- Table structure for table `revisiontags`
-- Replace /*$wgDBprefix*/ with the proper prefix

-- This stores which revision where flagged
-- The page and rev id are stored as is the user/comment/time
CREATE TABLE /*$wgDBprefix*/flaggedrevs (
  fr_id int(10) NOT NULL auto_increment,
  fr_page_id int(10) NOT NULL,
  fr_rev_id int(10) NOT NULL,
  fr_user int(5) NOT NULL,
  fr_timestamp char(14) NOT NULL,
  fr_comment mediumblob default NULL,
  -- Store the text with all transclusions resolved
  -- This will trade space for more speed and reliability
  fr_text mediumblob default NULL,

  PRIMARY KEY fr_rev_id (fr_rev_id),
  UNIQUE KEY (fr_id),
  INDEX fr_page_rev (fr_page_id,fr_rev_id)
) TYPE=InnoDB;

-- This stores all of our tag data
-- These are attached to specific flagged revisions
CREATE TABLE /*$wgDBprefix*/flaggedrevtags (
  frt_page_id int(10) NOT NULL,
  frt_rev_id int(10) NOT NULL,
  frt_dimension varchar(255) NOT NULL,
  frt_value int(2) NOT NULL,

  PRIMARY KEY frt_rev_dimension (frt_rev_id,frt_dimension),
  INDEX frt_page_rev_val (frt_page_id,frt_rev_id,frt_dimension,frt_value)
) TYPE=InnoDB;

-- This stores cached text for page view
CREATE TABLE /*$wgDBprefix*/flaggedcache (
  fc_key char(255) binary NOT NULL default '',
  fc_cache mediumblob NOT NULL default '',
  -- This timestamp is compared against page_touched
  -- Not all the same links may be used in the current version,
  -- So it may fall a bit out of date sometimes, since cache
  -- clearing is based on the current WLH information
  fc_timestamp char(14) NOT NULL,

  PRIMARY KEY fc_key (fc_key)
) TYPE=InnoDB;