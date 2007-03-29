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

  PRIMARY KEY fr_rev_id (fr_rev_id),
  UNIQUE KEY (fr_id),
  INDEX fr_page_rev (fr_page_id,fr_rev_id)
) TYPE=InnoDB;

-- This stores all of our tag data
-- These are attached to specific flagged revisions
CREATE TABLE /*$wgDBprefix*/flaggedrevtags (
  frt_rev_id int(10) NOT NULL,
  frt_dimension int(2) NOT NULL,
  frt_value int(2) NOT NULL,

  PRIMARY KEY ftr_dimension_value (frt_rev_id,frt_dimension)
) TYPE=InnoDB;

-- This stores expanded (transclusions resolved) revision text
CREATE TABLE /*$wgDBprefix*/flaggedtext (
  ft_id int(10) NOT NULL auto_increment,
  ft_rev_id int(10) NOT NULL,
  ft_text mediumblob NOT NULL default '',

  PRIMARY KEY ft_id (ft_id),
  UNIQUE KEY ft_rev_id (ft_rev_id)
) TYPE=InnoDB;

-- This stores image usage for the stable image directory
-- Used for scripts that clear out unused images
-- Can also be used to list out all images approved in articles
CREATE TABLE /*$wgDBprefix*/flaggedimages (
  fi_name varchar(255) NOT NULL,
  fi_rev_id int(10) NOT NULL,
  
  PRIMARY KEY (fi_name,fi_rev_id),
  INDEX fi_name (fi_name)
) TYPE=InnoDB;

-- This stores cached text for page view
CREATE TABLE /*$wgDBprefix*/flaggedcache (
  fc_key char(255) binary NOT NULL default '',
  fc_cache mediumblob NOT NULL default '',
  -- This timestamp is compared against page_touched
  fc_timestamp char(14) NOT NULL,

  PRIMARY KEY fc_key (fc_key)
) TYPE=InnoDB;