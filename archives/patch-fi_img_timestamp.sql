-- Fix bad fi_img_timestamp definition
ALTER TABLE /*$wgDBprefix*/flaggedimages
    CHANGE fi_img_timestamp fi_img_timestamp char(14) NULL;
-- Move bad values over to NULL
UPDATE /*$wgDBprefix*/flaggedimages
    SET fi_img_timestamp = NULL WHERE fi_img_timestamp = '0\0\0\0\0\0\0\0\0\0\0\0\0\0';
