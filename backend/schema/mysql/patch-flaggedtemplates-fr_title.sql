ALTER TABLE /*_*/flaggedtemplates
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (ft_rev_id, ft_tmp_rev_id),
  DROP COLUMN ft_title,
  DROP COLUMN ft_namespace;
