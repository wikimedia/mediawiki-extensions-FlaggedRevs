-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: backend/schema/tables.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE flaggedpages (
  fp_page_id INT NOT NULL,
  fp_reviewed SMALLINT DEFAULT 0 NOT NULL,
  fp_pending_since TIMESTAMPTZ DEFAULT NULL,
  fp_stable INT NOT NULL,
  fp_quality SMALLINT DEFAULT NULL,
  PRIMARY KEY(fp_page_id)
);

CREATE INDEX fp_reviewed_page ON flaggedpages (fp_reviewed, fp_page_id);

CREATE INDEX fp_quality_page ON flaggedpages (fp_quality, fp_page_id);

CREATE INDEX fp_pending_since ON flaggedpages (fp_pending_since);


CREATE TABLE flaggedrevs (
  fr_rev_id INT NOT NULL,
  fr_rev_timestamp TIMESTAMPTZ NOT NULL,
  fr_page_id INT NOT NULL,
  fr_user INT NOT NULL,
  fr_timestamp TIMESTAMPTZ NOT NULL,
  fr_quality SMALLINT DEFAULT 0 NOT NULL,
  fr_tags TEXT NOT NULL,
  fr_flags TEXT NOT NULL,
  PRIMARY KEY(fr_rev_id)
);

CREATE INDEX fr_page_rev ON flaggedrevs (fr_page_id, fr_rev_id);

CREATE INDEX fr_page_time ON flaggedrevs (fr_page_id, fr_rev_timestamp);

CREATE INDEX fr_page_qal_rev ON flaggedrevs (
  fr_page_id, fr_quality, fr_rev_id
);

CREATE INDEX fr_page_qal_time ON flaggedrevs (
  fr_page_id, fr_quality, fr_rev_timestamp
);

CREATE INDEX fr_user ON flaggedrevs (fr_user);


CREATE TABLE flaggedpage_config (
  fpc_page_id INT NOT NULL,
  fpc_override SMALLINT NOT NULL,
  fpc_level TEXT DEFAULT NULL,
  fpc_expiry TIMESTAMPTZ NOT NULL,
  PRIMARY KEY(fpc_page_id)
);

CREATE INDEX fpc_expiry ON flaggedpage_config (fpc_expiry);


CREATE TABLE flaggedrevs_tracking (
  ftr_from INT DEFAULT 0 NOT NULL,
  ftr_namespace INT DEFAULT 0 NOT NULL,
  ftr_title TEXT DEFAULT '' NOT NULL,
  PRIMARY KEY(
    ftr_from, ftr_namespace, ftr_title
  )
);

CREATE INDEX frt_namespace_title_from ON flaggedrevs_tracking (
  ftr_namespace, ftr_title, ftr_from
);


CREATE TABLE flaggedrevs_promote (
  frp_user_id INT NOT NULL,
  frp_user_params TEXT NOT NULL,
  PRIMARY KEY(frp_user_id)
);


CREATE TABLE flaggedrevs_statistics (
  frs_timestamp TIMESTAMPTZ NOT NULL,
  frs_stat_key VARCHAR(255) NOT NULL,
  frs_stat_val BIGINT NOT NULL,
  PRIMARY KEY(frs_stat_key, frs_timestamp)
);

CREATE INDEX frs_timestamp ON flaggedrevs_statistics (frs_timestamp);
