-- (c) Joerg Baach, Aaron Schulz, 2007

-- Table structure for table `Flagged Revisions`
-- Replace /*$wgDBprefix*/ with the proper prefix

-- This stores all of our reviews, 
-- the corresponding tags are stored in the tag table
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
  frt_value tinyint(2) NOT NULL,
  
  PRIMARY KEY frt_rev_dimension (frt_rev_id,frt_dimension),
  INDEX frt_page_rev_val (frt_page_id,frt_rev_id,frt_dimension,frt_value)
) TYPE=InnoDB;

-- This reduces query load by making some complex info available
CREATE TABLE /*$wgDBprefix*/flaggedpages (
  fp_page_id int(10) NOT NULL,
  -- What are the latest reviewed and "quality" revisions
  fp_latest int(10) NOT NULL,
  fp_latest_q int(10) NOT NULL,
  -- minimum quality levels for some pages, csv
  fp_restrictions blob NOT NULL,

  PRIMARY KEY fp_page_id (fp_page_id),
  INDEX frt_page_latest (fp_page_id,fp_latest,fp_latest_q)
) TYPE=InnoDB;