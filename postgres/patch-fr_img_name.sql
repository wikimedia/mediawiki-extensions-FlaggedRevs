BEGIN;

ALTER TABLE flaggedrevs 
  -- Name of included image
  ADD fr_img_name TEXT NULL default NULL,
  -- Timestamp of file (when uploaded)
  ADD fr_img_timestamp TIMESTAMPTZ NULL default NULL,
  -- Statistically unique SHA-1 key
  ADD fr_img_sha1 TEXT NULL default NULL;
  
CREATE INDEX key_timestamp ON flaggedrevs (fr_img_sha1,fr_img_timestamp);
  
COMMIT;
