ALTER TABLE  /*_*/flaggedrevs
CHANGE  fr_rev_timestamp fr_rev_timestamp BINARY(14) NOT NULL,
CHANGE  fr_timestamp fr_timestamp BINARY(14) NOT NULL;
