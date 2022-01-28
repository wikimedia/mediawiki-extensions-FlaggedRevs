DROP INDEX fr_img_sha1 ON /*_*/flaggedrevs;

ALTER TABLE /*_*/flaggedrevs
  DROP COLUMN fr_img_name,
  DROP COLUMN fr_img_timestamp,
  DROP COLUMN fr_img_sha1;
