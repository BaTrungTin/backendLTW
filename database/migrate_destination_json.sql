-- Chuyển destination từ 1 ID sang mảng JSON (nhiều điểm đến)
USE tour_db;

ALTER TABLE tours ADD COLUMN destination_json JSON DEFAULT NULL;

UPDATE tours
SET destination_json = JSON_ARRAY(CAST(destination AS CHAR))
WHERE destination IS NOT NULL;

ALTER TABLE tours DROP COLUMN destination;
ALTER TABLE tours CHANGE destination_json destination JSON DEFAULT NULL;
