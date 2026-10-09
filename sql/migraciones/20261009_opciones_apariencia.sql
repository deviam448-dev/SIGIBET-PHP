USE sigibet_db;

ALTER TABLE configuracion
    ADD COLUMN color_botones VARCHAR(7) NOT NULL DEFAULT '#A85F6D',
    ADD COLUMN color_menu VARCHAR(7) NOT NULL DEFAULT '#1C191B',
    ADD COLUMN color_acento VARCHAR(7) NOT NULL DEFAULT '#B9974E',
    ADD COLUMN tipografia VARCHAR(30) NOT NULL DEFAULT 'Inter';

UPDATE configuracion
SET color_botones = CASE
        WHEN tema_colores REGEXP '^#[0-9A-Fa-f]{6}$' THEN UPPER(tema_colores)
        ELSE color_botones
    END,
    color_menu = '#1C191B',
    color_acento = '#B9974E',
    tipografia = 'Inter'
WHERE id = 1;
