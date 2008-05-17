-- (c) Aaron Schulz, 2007
-- See FlaggedRevs.sql for details

BEGIN;

CREATE TABLE flaggedpages (
  fp_page_id  INTEGER NOT NULL DEFAULT 0,
  fp_reviewed INTEGER NOT NULL DEFAULT 0,
  fp_stable   INTEGER NOT NULL DEFAULT 0,
  fp_quality  INTEGER default NULL,
  PRIMARY KEY (fp_page_id)
);
CREATE INDEX fp_reviewed_page ON flaggedpages (fp_reviewed,fp_page_id),
CREATE INDEX fp_quality_page ON flaggedpages (fp_quality,fp_page_id)

CREATE TABLE flaggedrevs (
  fr_page_id       INTEGER    NOT NULL DEFAULT 0,
  fr_rev_id        INTEGER    NOT NULL DEFAULT 0,
  fr_user          INTEGER    NULL REFERENCES mwuser(user_id) ON DELETE SET NULL,
  fr_timestamp     TIMESTAMPTZ,
  fr_comment       TEXT        NOT NULL DEFAULT '',
  fr_quality       INTEGER    NOT NULL DEFAULT 0,
  fr_tags          TEXT        NOT NULL DEFAULT '',
  fr_text          TEXT        NOT NULL DEFAULT '',
  fr_flags         TEXT        NOT NULL,
  fr_img_name      TEXT        NULL DEFAULT NULL,
  fr_img_timestamp TIMESTAMPTZ NULL DEFAULT NULL,
  fr_img_sha1      TEXT        NULL DEFAULT NULL,
  PRIMARY KEY (fr_page_id,fr_rev_id)
);
CREATE INDEX fr_namespace_title ON flaggedrevs (fr_page_id,fr_quality,fr_rev_id);
CREATE INDEX key_timestamp ON flaggedrevs (fr_img_sha1,fr_img_timestamp);

CREATE TABLE flaggedpage_config (
  fpc_page_id   INTEGER     NOT NULL PRIMARY KEY DEFAULT 0,
  fpc_select    INTEGER     NOT NULL DEFAULT 0,
  fpc_override  INTEGER     NOT NULL,
  fpc_expiry    TIMESTAMPTZ  NULL
)
CREATE INDEX fpc_expiry ON flaggedpage_config (fpc_expiry);

CREATE TABLE flaggedtemplates (
  ft_rev_id      INTEGER  NOT NULL DEFAULT 0 ,
  ft_namespace   SMALLINT NOT NULL DEFAULT 0,
  ft_title       TEXT      NOT NULL DEFAULT '',
  ft_tmp_rev_id  INTEGER  NOT NULL DEFAULT 0,
  PRIMARY KEY (ft_rev_id,ft_namespace,ft_title)
);

CREATE TABLE flaggedimages (
  fi_rev_id         INTEGER   NOT NULL DEFAULT 0,
  fi_name           TEXT       NOT NULL,
  fi_img_timestamp  TIMESTAMPTZ,
  fi_img_sha1       CHAR(64),
  PRIMARY KEY (fi_rev_id,fi_name)
);

CREATE TABLE flaggedrevs_promote (
  frp_user_id INTEGER NOT NULL PRIMARY KEY default 0,
  frp_user_params TEXT NOT NULL default ''
);

COMMIT;
