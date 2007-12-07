-- (c) Joerg Baach, Aaron Schulz, 2007
-- See FlaggedRevs.sql for details

BEGIN;

CREATE TABLE flaggedrevs (
  fr_page_id      INTEGER  NOT NULL DEFAULT 0 ,
  fr_rev_id       INTEGER  NOT NULL DEFAULT 0 ,
  fr_user int(5)  INTEGER      NULL REFERENCES mwuser(user_id) ON DELETE SET NULL,
  fr_timestamp    TIMESTAMPTZ,
  fr_comment      TEXT     NOT NULL DEFAULT '',
  fr_quality      SMALLINT NOT NULL DEFAULT 0,
  fr_text         TEXT     NOT NULL DEFAULT '',
  fr_flags        TEXT     NOT NULL,
  PRIMARY KEY (fr_page_id,fr_rev_id)
);
CREATE INDEX fr_namespace_title ON flaggedrevs (fr_page_id,fr_quality,fr_rev_id);

CREATE TABLE flaggedpage_config (
  fpc_page_id   INTEGER NOT NULL PRIMARY KEY DEFAULT 0,
  fpc_select    INTEGER NOT NULL DEFAULT 0,
  fpc_override  bool NOT NULL
)

CREATE TABLE flaggedrevtags (
  frt_rev_id     INTEGER  NOT NULL DEFAULT 0 ,
  frt_dimension  TEXT     NOT NULL DEFAULT '',
  frt_value      SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (frt_rev_id,frt_dimension)
);

CREATE TABLE flaggedtemplates (
  ft_rev_id      INTEGER  NOT NULL DEFAULT 0 ,
  ft_namespace   SMALLINT NOT NULL DEFAULT 0,
  ft_title       TEXT     NOT NULL DEFAULT '',
  ft_tmp_rev_id  INTEGER  NOT NULL DEFAULT 0,
  PRIMARY KEY (ft_rev_id,ft_namespace,ft_title)
);

CREATE TABLE flaggedimages (
  fi_rev_id         INTEGER   NOT NULL DEFAULT 0 ,
  fi_name           TEXT      NOT NULL PRIMARY KEY,
  fi_img_timestamp  TIMESTAMPTZ,
  fi_img_sha1       CHAR(64),
  PRIMARY KEY (fi_rev_id,fi_name)
);

ALTER TABLE page 
	ADD page_ext_reviewed bool NULL,
	ADD page_ext_stable INTEGER NULL,
	ADD page_ext_quality SMALLINT DEFAULT NULL;

CREATE INDEX ext_namespace_reviewed ON page (page_namespace,page_is_redirect,page_ext_reviewed,page_id);
CREATE INDEX ext_namespace_quality ON page (page_namespace,page_ext_quality,page_title);

COMMIT;
