ALTER TABLE flaggedrevs_tracking DROP CONSTRAINT flaggedrevs_tracking_pkey;
CREATE UNIQUE INDEX frt_from_namespace_title
  ON flaggedrevs_tracking (ftr_from, ftr_namespace, ftr_title);
