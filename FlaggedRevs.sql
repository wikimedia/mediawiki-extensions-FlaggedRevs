-- (c) Joerg Baach, Aaron Schulz, 2007

-- Table structure for table `Flagged Revisions`
-- Replace /*$wgDBprefix*/ with the proper prefix

-- This stores all of our reviews, 
-- the corresponding tags are stored in the tag table
CREATE TABLE /*$wgDBprefix*/flaggedrevs (
  fr_id int(10) NOT NULL auto_increment,
  fr_namespace int NOT NULL default '0',
  fr_title varchar(255) binary NOT NULL default '',
  fr_rev_id int(10) NOT NULL,
  fr_user int(5) NOT NULL,
  fr_timestamp char(14) NOT NULL,
  fr_comment mediumblob NOT NULL default '',
  -- Store the text with all transclusions resolved
  -- This will trade space for more speed and reliability
  fr_text mediumblob NOT NULL default '',
  -- Store the precedence level
  fr_quality tinyint(1) default 0,
  
  PRIMARY KEY (fr_namespace,fr_title,fr_rev_id),
  UNIQUE KEY (fr_id)
) TYPE=InnoDB;

-- This stores all of our tag data
-- These are attached to specific flagged revisions
CREATE TABLE /*$wgDBprefix*/flaggedrevtags (
  frt_rev_id int(10) NOT NULL,
  frt_dimension varchar(255) NOT NULL,
  frt_value tinyint(2) NOT NULL,
  
  PRIMARY KEY (frt_rev_id,frt_dimension),
  INDEX frt_rev_dim_val (frt_rev_id,frt_dimension,frt_value)
) TYPE=InnoDB;