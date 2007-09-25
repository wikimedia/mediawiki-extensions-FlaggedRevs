-- (c) Joerg Baach, Aaron Schulz, 2007
-- See FlaggedRevs.sql for details

BEGIN;

CREATE TABLE flaggedrevs (
  fr_namespace    SMALLINT NOT NULL DEFAULT 0,
  fr_title        TEXT     NOT NULL DEFAULT '',
  fr_rev_id       INTEGER  NOT NULL PRIMARY KEY DEFAULT 0 ,
  fr_user int(5)  INTEGER      NULL REFERENCES mwuser(user_id) ON DELETE SET NULL,
  fr_timestamp    TIMESTAMPTZ,
  fr_comment      TEXT     NOT NULL DEFAULT '',
  fr_quality      SMALLINT NOT NULL DEFAULT 0,
  fr_text         TEXT     NOT NULL DEFAULT '',
  fr_flags        TEXT     NOT NULL
);
CREATE INDEX fr_namespace_title ON flaggedrevs (fr_namespace,fr_title,fr_quality,fr_rev_id);

CREATE TABLE flaggedpages (
  fp_page_id INTEGER NOT NULL PRIMARY KEY DEFAULT 0,
  fp_select INTEGER NOT NULL DEFAULT 0,
  fp_override bool NOT NULL
)

CREATE TABLE flaggedrevtags (
  frt_rev_id     INTEGER  NOT NULL DEFAULT 0 ,
  frt_dimension  TEXT     NOT NULL DEFAULT '',
  frt_value      SMALLINT NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX frt_rev_dimension ON flaggedrevtags (frt_rev_id,frt_dimension);
CREATE INDEX        frt_rev_dim_val   ON flaggedrevtags (frt_rev_id,frt_dimension,frt_value);

CREATE TABLE flaggedtemplates (
  ft_rev_id      INTEGER  NOT NULL DEFAULT 0 ,
  ft_namespace   SMALLINT NOT NULL DEFAULT 0,
  ft_title       TEXT     NOT NULL DEFAULT '',
  ft_tmp_rev_id  INTEGER  NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX ft_rev_namespace_title ON flaggedtemplates (ft_rev_id,ft_namespace,ft_title);
CREATE INDEX        rev_namespace_title_id ON flaggedtemplates (ft_rev_id,ft_namespace,ft_title,ft_tmp_rev_id);

CREATE TABLE flaggedimages (
  fi_rev_id         INTEGER   NOT NULL DEFAULT 0 ,
  fi_name           TEXT      NOT NULL PRIMARY KEY,
  fi_img_timestamp  TIMESTAMPTZ,
  fi_img_sha1       CHAR(64)
);
CREATE UNIQUE INDEX fi_rev_name        ON flaggedimages (fi_rev_id,fi_name);
CREATE INDEX        fi_rev_name_time   ON flaggedimages (fi_rev_id,fi_name,fi_img_timestamp);
CREATE INDEX        fi_rev_img_key     ON flaggedimages (fi_rev_id,fi_name,fi_img_sha1);

ALTER TABLE page 
	ADD page_ext_reviewed bool NULL,
	ADD page_ext_stable int(10) NULL;
CREATE INDEX ext_namespace_reviewed ON page (page_namespace,page_is_redirect,page_ext_reviewed,page_id);

COMMIT;
