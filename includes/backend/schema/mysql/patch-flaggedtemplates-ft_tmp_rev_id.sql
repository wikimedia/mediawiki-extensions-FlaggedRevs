-- Make ft_tmp_rev_id in flaggedtemplates NOT NULL
ALTER TABLE /*_*/flaggedtemplates
  CHANGE ft_tmp_rev_id ft_tmp_rev_id integer unsigned NOT NULL;
