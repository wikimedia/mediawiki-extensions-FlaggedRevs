-- Add fr_user column index

ALTER TABLE /*$wgDBprefix*/flaggedrevs
  ADD INDEX (fr_user);
