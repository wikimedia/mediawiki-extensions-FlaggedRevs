-- (c) Joerg Baach, Aaron Schulz, 2007

-- Table structure for table `Flagged Revisions`
-- Replace /*$wgDBprefix*/ with the proper prefix

-- This stores all of our reviews, 
-- the corresponding tags are stored in the tag table
CREATE TABLE /*$wgDBprefix*/flaggedrevs (
  fr_namespace int NOT NULL default '0',
  fr_title varchar(255) binary NOT NULL default '',
  fr_rev_id int(10) NOT NULL,
  fr_user int(5) NOT NULL,
  fr_timestamp char(14) NOT NULL,
  fr_comment mediumblob NOT NULL default '',
  -- Store the text with all transclusions resolved
  -- This will trade space for speed
  fr_text mediumblob NOT NULL default '',
  -- Store the precedence level
  fr_quality tinyint(1) default 0,
  
  PRIMARY KEY (fr_namespace,fr_title,fr_rev_id),
  UNIQUE KEY (fr_rev_id),
  INDEX (fr_namespace,fr_title,fr_quality,fr_rev_id)
) TYPE=InnoDB;

-- This stores all of our tag data
-- These are attached to specific flagged revisions
CREATE TABLE /*$wgDBprefix*/flaggedrevtags (
  frt_rev_id int(10) NOT NULL,
  frt_dimension varchar(255) NOT NULL,
  frt_value tinyint(2) NOT NULL,
  
  PRIMARY KEY (frt_rev_id,frt_dimension),
  INDEX (frt_rev_id,frt_dimension,frt_value)
) TYPE=InnoDB;

-- This stores all of our transclusion revision pointers
CREATE TABLE /*$wgDBprefix*/flaggedtemplates (
  ft_rev_id int(10) NOT NULL,
  -- Namespace and title of included page
  ft_namespace int NOT NULL default '0',
  ft_title varchar(255) binary NOT NULL default '',
  -- Revisions ID used when reviewed
  ft_tmp_rev_id int(10) NULL,
  
  PRIMARY KEY (ft_rev_id,ft_namespace,ft_title),
  INDEX (ft_rev_id,ft_namespace,ft_title,ft_tmp_rev_id)
) TYPE=InnoDB;

-- This stores all of our image revision pointers
CREATE TABLE /*$wgDBprefix*/flaggedimages (
  fi_rev_id int(10) NOT NULL,
  -- Name of included image
  fi_name varchar(255) binary NOT NULL default '',
  -- Timestamp of image used when reviewed
  fi_img_timestamp char(14) NULL,
  -- Statistically unique SHA-1 key
  fi_img_sha1 varbinary(32) NOT NULL default '',
  
  PRIMARY KEY (fi_rev_id,fi_name),
  INDEX (fi_rev_id,fi_name,fi_img_timestamp),
  INDEX (fi_rev_id,fi_name,fi_img_sha1)
) TYPE=InnoDB;

-- Add page_ext_stable column, similar to page_latest
-- Add page_ext_upd columns, a boolean for up to date stable versions
ALTER TABLE /*$wgDBprefix*/page 
  ADD page_ext_reviewed bool NULL,
  ADD page_ext_stable int(10) NULL,
  ADD INDEX ext_namespace_reviewed (page_namespace,page_is_redirect,page_ext_reviewed,page_id);
