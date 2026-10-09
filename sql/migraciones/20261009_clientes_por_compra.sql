USE sigibet_db;

ALTER TABLE clientes
    MODIFY nombre VARCHAR(100) NULL,
    MODIFY documento VARCHAR(30) NULL;
