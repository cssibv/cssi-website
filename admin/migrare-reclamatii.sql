-- ════════════════════════════════════════════════════════════════
-- Mutare Reclamații → Intervenții (rulează în phpMyAdmin, tab SQL)
-- Recreează fiecare reclamație ca intervenție programată LUNEA VIITOARE
-- și șterge reclamația veche. Programarea apare automat în Planificator.
-- Sigur de rulat o singură dată; pentru mai multe reclamații, re-rulează.
-- ════════════════════════════════════════════════════════════════

-- 1) Alege reclamația de mutat (implicit: cea mai veche rămasă)
SET @rid := (SELECT id FROM reclamatii ORDER BY id LIMIT 1);

SET @cli_nume := (SELECT client    FROM reclamatii WHERE id = @rid);
SET @tel      := (SELECT telefon   FROM reclamatii WHERE id = @rid);
SET @adr      := (SELECT adresa    FROM reclamatii WHERE id = @rid);
SET @serv_ui  := (SELECT serviciu  FROM reclamatii WHERE id = @rid);
SET @desc     := (SELECT descriere FROM reclamatii WHERE id = @rid);
SET @echipa   := (SELECT echipa    FROM reclamatii WHERE id = @rid);

-- 2) Serviciu: din denumirea cu diacritice (reclamatii) în valoarea folosită de proiecte
SET @serv := CASE @serv_ui
    WHEN 'Detecție Incendiu'    THEN 'Detectie Incendiu'
    WHEN 'Alarmă Antiefracție'  THEN 'Alarma'
    WHEN 'Supraveghere Video'   THEN 'Supraveghere Video'
    WHEN 'Control Acces'        THEN 'Control Acces'
    WHEN 'Videointerfonie'      THEN 'Videointerfonie'
    WHEN 'Automatizări'         THEN 'Automatizari'
    ELSE 'Supraveghere Video'
END;

-- 3) Client: folosește unul existent (după nume) sau creează-l
SET @cli_id := (SELECT id FROM clienti WHERE LOWER(nume) = LOWER(@cli_nume) LIMIT 1);
INSERT INTO clienti (client_id, nume, telefon, oras, tip, note)
SELECT CONCAT('CLI-REC', @rid), @cli_nume, @tel, '', 'Persoana fizica', CONCAT('Din reclamatie #', @rid)
WHERE @cli_id IS NULL;
SET @cli_id := COALESCE(@cli_id, LAST_INSERT_ID());

-- 4) Proiect cu status Interventie (apare în modulul Intervenții)
INSERT INTO proiecte (proiect_id, client_id, serviciu, obiectiv, status, valoare_estimata, responsabil, adresa_obiectiv, note, istoric_status, preluat_de)
VALUES (
    CONCAT('CSSI-', YEAR(CURDATE()), '-R', @rid),
    @cli_id, @serv,
    COALESCE(NULLIF(@desc, ''), 'Intervenție'),
    'Interventie', 0, 'Admin', @adr,
    CONCAT('Recreat din reclamatie #', @rid),
    '[]', 'Admin'
);
SET @proj := LAST_INSERT_ID();

-- 5) Programare LUNEA VIITOARE (apare în Planificator pe acea săptămână)
SET @next_monday := DATE_ADD(CURDATE(), INTERVAL ((8 - DAYOFWEEK(CURDATE())) % 7 + 1) DAY);
INSERT INTO executie_programari (proiect_id, data_programata, ora_start, durata_ore, status, obiectiv, created_by)
VALUES (@proj, @next_monday, '09:00:00', 2, 'Programat', COALESCE(NULLIF(@desc, ''), 'Intervenție'), 'Admin');
SET @prg := LAST_INSERT_ID();

-- 6) Atribuie tehnicianul (dacă reclamația avea echipă)
INSERT INTO executie_atribuiri (programare_id, user_id)
SELECT @prg, @echipa WHERE @echipa IS NOT NULL AND @echipa <> '';

-- 7) Șterge reclamația veche
DELETE FROM reclamatii WHERE id = @rid;

-- Verificare:
SELECT @proj AS proiect_db_id, @next_monday AS programat_pe;
