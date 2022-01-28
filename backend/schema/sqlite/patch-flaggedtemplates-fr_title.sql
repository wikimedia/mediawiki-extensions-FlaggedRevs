DROP TABLE IF EXISTS /*_*/flaggedtemplates_tmp;
CREATE TABLE /*_*/flaggedtemplates_tmp (
  ft_rev_id      BIGINT   NOT NULL DEFAULT 0 ,
  ft_tmp_rev_id  BIGINT   NOT NULL DEFAULT 0,
  PRIMARY KEY (ft_rev_id, ft_tmp_rev_id)
);

INSERT OR IGNORE INTO /*_*/flaggedtemplates_tmp (ft_rev_id, ft_tmp_rev_id)
  SELECT ft_rev_id, ft_tmp_rev_id
  FROM /*_*/flaggedtemplates;

DROP TABLE /*_*/flaggedtemplates;

ALTER TABLE /*_*/flaggedtemplates_tmp RENAME TO /*_*/flaggedtemplates;
