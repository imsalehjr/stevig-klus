USE stevig_klus;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE quote_request_status_log;
TRUNCATE quote_request_notes;
TRUNCATE quote_request_photos;
TRUNCATE quote_requests;
TRUNCATE reviews;
TRUNCATE project_photos;
TRUNCATE projects;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO projects (id, slug, title, town, service_id, summary, description, duration, completed_on, is_featured) VALUES
(1, 'badkamer-lindenbeek', 'Badkamer met inloopdouche', 'Lindenbeek',
 (SELECT id FROM services WHERE slug = 'renovatie'),
 'Een badkamer uit de jaren 80 omgebouwd tot een lichte ruimte met inloopdouche.',
 'De oude badkamer had bruine tegels, een bad dat bijna niet werd gebruikt en een ventilatie die niet goed werkte. We hebben alles gestript tot op de muur, de leidingen verlegd en een inloopdouche met glazen wand geplaatst. De wanden kregen grote lichte tegels, de vloer antraciet tegels van 60 bij 60. Een zwevend houten badmeubel maakt de ruimte warmer.',
 '9 werkdagen', DATE_SUB(CURDATE(), INTERVAL 24 DAY), 1),
(2, 'kozijnen-eikenrode', 'Buitenschilderwerk kozijnen', 'Eikenrode',
 (SELECT id FROM services WHERE slug = 'schilderwerk'),
 'Afbladderende groene kozijnen hersteld en geschilderd in antraciet.',
 'De verf op de kozijnen bladderde los en bij twee ramen zat houtrot in de onderdorpels. We hebben het rotte hout weggefreesd en hersteld met een tweecomponenten houtvuller, alles geschuurd en in drie lagen geschilderd. De bewoners kozen voor antraciet, wat goed past bij de rode gevel.',
 '5 werkdagen', DATE_SUB(CURDATE(), INTERVAL 52 DAY), 1),
(3, 'keukenwand-brakelveld', 'Keukenwand in visgraat', 'Brakelveld',
 (SELECT id FROM services WHERE slug = 'tegelwerk'),
 'Een gedateerde tegelwand vervangen door groene tegels in visgraatpatroon.',
 'De klant wilde de keuken een nieuwe uitstraling geven zonder alles te vervangen. We hebben de oude tegels verwijderd, de wand vlak gemaakt en groene tegels in visgraatpatroon gezet. Het werkblad is vervangen door een donker composiet blad en de keukenkastjes zijn wit gespoten.',
 '3 werkdagen', DATE_SUB(CURDATE(), INTERVAL 80 DAY), 1),
(4, 'zolderkast-kempenhoven', 'Inbouwkasten onder de schuine wand', 'Kempenhoven',
 (SELECT id FROM services WHERE slug = 'timmerwerk'),
 'Een onbruikbare zolder met schuine wand omgebouwd tot slaapkamer met veel bergruimte.',
 'Onder de schuine dakwand ging veel ruimte verloren. We hebben de zolder geïsoleerd, afgetimmerd en op maat gemaakte kasten gebouwd die precies de schuine lijn volgen. De deurtjes zijn greeploos en wit gelakt. Op de kopse wand kwamen eiken planken.',
 '6 werkdagen', DATE_SUB(CURDATE(), INTERVAL 115 DAY), 0),
(5, 'woonkamer-dommelrijt', 'Woonkamer opnieuw afgewerkt', 'Dommelrijt',
 (SELECT id FROM services WHERE slug = 'schilderwerk'),
 'Grenen schrootjes en oud behang eruit, strak stucwerk en een nieuwe houten vloer erin.',
 'De woonkamer had grenen schrootjes op de wanden en een gedateerde vloer. Na het verwijderen van de schrootjes hebben we de wanden laten stucen en in een lichte kleur geschilderd. Daarna hebben we een eiken pvc-vloer met visgraatlook gelegd en nieuwe plinten geplaatst.',
 '8 werkdagen', DATE_SUB(CURDATE(), INTERVAL 150 DAY), 0),
(6, 'vlonder-veenakker', 'Hardhouten vlonder in de tuin', 'Veenakker',
 (SELECT id FROM services WHERE slug = 'timmerwerk'),
 'Een modderig stuk gras veranderd in een vlonder van 30 vierkante meter.',
 'Het stuk tuin bij de achtergevel stond in de winter vaak onder water. We hebben de grond afgegraven, drainage aangelegd en een verhoogde vlonder van hardhout gebouwd op een fundering van betonpoeren. Langs de rand loopt een smalle plantenbak.',
 '4 werkdagen', DATE_SUB(CURDATE(), INTERVAL 190 DAY), 0);

INSERT INTO project_photos (project_id, kind, file, width, height, alt, sort_order) VALUES
(1, 'before', 'demo-badkamer-lindenbeek-before.webp', 1200, 900, 'Oude badkamer met bruine tegels en een bad', 0),
(1, 'after', 'demo-badkamer-lindenbeek-after.webp', 1200, 900, 'Nieuwe lichte badkamer met inloopdouche en houten badmeubel', 0),
(2, 'before', 'demo-kozijnen-eikenrode-before.webp', 1200, 900, 'Gevel met afbladderende groene kozijnen', 0),
(2, 'after', 'demo-kozijnen-eikenrode-after.webp', 1200, 900, 'Gevel met strak geschilderde antraciet kozijnen', 0),
(3, 'before', 'demo-keukenwand-brakelveld-before.webp', 1200, 900, 'Keuken met oude beige tegelwand', 0),
(3, 'after', 'demo-keukenwand-brakelveld-after.webp', 1200, 900, 'Keuken met groene tegels in visgraatpatroon', 0),
(4, 'before', 'demo-zolderkast-kempenhoven-before.webp', 1200, 900, 'Onafgewerkte zolder met zichtbare dakspanten', 0),
(4, 'after', 'demo-zolderkast-kempenhoven-after.webp', 1200, 900, 'Zolder met witte inbouwkasten onder de schuine wand', 0),
(5, 'before', 'demo-woonkamer-dommelrijt-before.webp', 1200, 900, 'Woonkamer met grenen schrootjes op de wanden', 0),
(5, 'after', 'demo-woonkamer-dommelrijt-after.webp', 1200, 900, 'Lichte woonkamer met strakke wanden en houten vloer', 0),
(6, 'before', 'demo-vlonder-veenakker-before.webp', 1200, 900, 'Modderig gras achter het huis', 0),
(6, 'after', 'demo-vlonder-veenakker-after.webp', 1200, 900, 'Hardhouten vlonder achter het huis', 0);

INSERT INTO reviews (customer_name, town, rating, body, service_id, project_id, reviewed_on) VALUES
('Familie Verhoeven', 'Lindenbeek', 5, 'Binnen negen dagen een complete nieuwe badkamer, precies zoals afgesproken. Elke avond was alles opgeruimd, dat had ik bij een verbouwing nog nooit meegemaakt.', (SELECT id FROM services WHERE slug = 'renovatie'), 1, DATE_SUB(CURDATE(), INTERVAL 20 DAY)),
('Anouk', 'Eikenrode', 5, 'Sanne heeft onze kozijnen prachtig gedaan en liet ons eerst zien waar het houtrot zat. Eerlijk advies en geen verrassingen op de rekening.', (SELECT id FROM services WHERE slug = 'schilderwerk'), 2, DATE_SUB(CURDATE(), INTERVAL 48 DAY)),
('Jeroen B.', 'Brakelveld', 4, 'Mooi strak tegelwerk. Er was een dag vertraging door een late levering van de tegels, maar dat werd netjes van tevoren gemeld.', (SELECT id FROM services WHERE slug = 'tegelwerk'), 3, DATE_SUB(CURDATE(), INTERVAL 75 DAY)),
('Fatima', 'Kempenhoven', 5, 'De kasten passen tot op de millimeter onder de schuine wand. Onze zolder is nu echt een slaapkamer.', (SELECT id FROM services WHERE slug = 'timmerwerk'), 4, DATE_SUB(CURDATE(), INTERVAL 110 DAY)),
('Henk en Marja', 'Heiwaarde', 5, 'Een lijst met zeven kleine klusjes in één ochtend afgewerkt. Snel, vriendelijk en voor een normale prijs.', (SELECT id FROM services WHERE slug = 'kleine-reparaties'), NULL, DATE_SUB(CURDATE(), INTERVAL 132 DAY)),
('Tom', 'Dommelrijt', 4, 'Goed werk aan de woonkamer. De planning schoof een week op, maar het eindresultaat is top.', (SELECT id FROM services WHERE slug = 'schilderwerk'), 5, DATE_SUB(CURDATE(), INTERVAL 145 DAY)),
('Lisa de G.', 'Veenakker', 5, 'Onze tuin is eindelijk bruikbaar. Ruud dacht mee over de afwatering, daar hadden we zelf niet aan gedacht.', (SELECT id FROM services WHERE slug = 'timmerwerk'), 6, DATE_SUB(CURDATE(), INTERVAL 185 DAY));

INSERT INTO quote_requests (id, reference, status, name, email, phone, street, house_number, postcode, town, service_id, service_name, description, preferred_period, privacy_consent_at, created_at) VALUES
(1, CONCAT('STV-', YEAR(CURDATE()), '-0001'), 'done', 'Pieter Smits', 'pieter.smits@example.test', '06 12345678', 'Lindelaan', '8', '5492 AA', 'Lindenbeek', (SELECT id FROM services WHERE slug = 'renovatie'), 'Renovatie & verbouw', 'Wij willen onze badkamer volledig laten vernieuwen. Het bad mag eruit, liefst een inloopdouche.', '1_3_months', DATE_SUB(NOW(), INTERVAL 70 DAY), DATE_SUB(NOW(), INTERVAL 70 DAY)),
(2, CONCAT('STV-', YEAR(CURDATE()), '-0002'), 'accepted', 'Noor el Amrani', 'noor@example.test', '06 23456789', 'Molenweg', '21', '5493 BC', 'Eikenrode', (SELECT id FROM services WHERE slug = 'schilderwerk'), 'Schilderwerk', 'Al onze kozijnen aan de voorkant moeten geschilderd worden. Bij één raam zit volgens mij houtrot.', 'within_1_month', DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),
(3, CONCAT('STV-', YEAR(CURDATE()), '-0003'), 'quote_sent', 'Daan Kuijpers', 'daan.k@example.test', '06 34567890', 'Kerkstraat', '3a', '5494 CD', 'Brakelveld', (SELECT id FROM services WHERE slug = 'tegelwerk'), 'Tegelwerk', 'Toiletruimte opnieuw betegelen, ongeveer 1,5 bij 1 meter, vloer en wanden tot 1,20 m hoog.', '1_3_months', DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(4, CONCAT('STV-', YEAR(CURDATE()), '-0004'), 'contacted', 'Sophie van Dijk', 'sophie@example.test', '06 45678901', 'Beukenhof', '14', '5495 DE', 'Kempenhoven', (SELECT id FROM services WHERE slug = 'timmerwerk'), 'Timmerwerk', 'Ik zoek iemand die een trapkast kan maken met een deur en planken. Foto van de huidige situatie bijgevoegd.', 'flexible', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, CONCAT('STV-', YEAR(CURDATE()), '-0005'), 'new', 'Mark Hendriks', 'mark.hendriks@example.test', '06 56789012', 'Dorpsstraat', '102', '5496 EF', 'Dommelrijt', (SELECT id FROM services WHERE slug = 'kleine-reparaties'), 'Kleine reparaties', 'Klemmende achterdeur, lekkende kraan in de keuken en twee losse tegels in de gang.', 'asap', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, CONCAT('STV-', YEAR(CURDATE()), '-0006'), 'new', 'Emma Willems', 'emma.w@example.test', '06 67890123', 'Heideweg', '7', '5497 FG', 'Veenakker', (SELECT id FROM services WHERE slug = 'renovatie'), 'Renovatie & verbouw', 'Wij willen een muur tussen keuken en woonkamer weg laten halen. Is dat een dragende muur? Graag advies en een prijs.', '3_6_months', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(7, CONCAT('STV-', YEAR(CURDATE()), '-0007'), 'rejected', 'Bas Meulendijks', 'bas@example.test', '06 78901234', 'Stationsplein', '1', '5251 AB', 'Moerdal', (SELECT id FROM services WHERE slug = 'tegelwerk'), 'Tegelwerk', 'Bedrijfspand van 400 m2 voorzien van nieuwe vloertegels.', 'asap', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(8, CONCAT('STV-', YEAR(CURDATE()), '-0008'), 'new', 'Yara Peeters', 'yara.p@example.test', '06 89012345', 'Esdoornlaan', '33', '5498 GH', 'Heiwaarde', (SELECT id FROM services WHERE slug = 'schilderwerk'), 'Schilderwerk', 'Slaapkamer en overloop schilderen, wanden en plafond. Plafond heeft een paar vochtvlekken.', 'within_1_month', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR));

INSERT INTO quote_request_photos (quote_request_id, file, mime, bytes, width, height) VALUES
(4, 'demo-request-trapkast.webp', 'image/webp', 9296, 1200, 900),
(5, 'demo-request-deur.webp', 'image/webp', 7650, 1200, 900),
(6, 'demo-request-muur-1.webp', 'image/webp', 7358, 1200, 900),
(6, 'demo-request-muur-2.webp', 'image/webp', 7650, 1200, 900);

INSERT INTO quote_request_status_log (quote_request_id, old_status, new_status, changed_at) VALUES
(1, NULL, 'new', DATE_SUB(NOW(), INTERVAL 70 DAY)),
(1, 'new', 'quote_sent', DATE_SUB(NOW(), INTERVAL 66 DAY)),
(1, 'quote_sent', 'accepted', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 'accepted', 'done', DATE_SUB(NOW(), INTERVAL 24 DAY)),
(2, NULL, 'new', DATE_SUB(NOW(), INTERVAL 18 DAY)),
(2, 'new', 'contacted', DATE_SUB(NOW(), INTERVAL 17 DAY)),
(2, 'contacted', 'accepted', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, NULL, 'new', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(3, 'new', 'quote_sent', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(4, NULL, 'new', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 'new', 'contacted', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, NULL, 'new', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, NULL, 'new', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(7, NULL, 'new', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(7, 'new', 'rejected', DATE_SUB(NOW(), INTERVAL 29 DAY)),
(8, NULL, 'new', DATE_SUB(NOW(), INTERVAL 2 HOUR));

INSERT INTO quote_request_notes (quote_request_id, body, created_at) VALUES
(2, 'Gebeld, inmeting gepland op dinsdag 10:00. Houtrot bij keukenraam bevestigd.', DATE_SUB(NOW(), INTERVAL 17 DAY)),
(3, 'Offerte verstuurd: 2 dagen werk, tegels door klant zelf gekozen bij leverancier.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(4, 'Klant teruggebeld, wil eiken planken. Langsgaan volgende week.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 'Te groot en buiten ons werkgebied. Klant doorverwezen.', DATE_SUB(NOW(), INTERVAL 29 DAY));
