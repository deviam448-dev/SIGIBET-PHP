USE sigibet_db;

ALTER TABLE configuracion
    ALTER COLUMN color_acento SET DEFAULT '#B9BF66';

UPDATE configuracion
SET color_acento = '#B9BF66'
WHERE id = 1;
