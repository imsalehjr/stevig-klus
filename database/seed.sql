USE stevig_klus;

INSERT INTO services (slug, name, icon, intro, description, sort_order) VALUES
('renovatie', 'Renovatie & verbouw', 'house',
 'Van een nieuwe badkamer tot een complete verbouwing: één aanspreekpunt van sloop tot oplevering.',
 'Wij nemen de hele verbouwing uit handen. We beginnen met een gratis inmeting bij u thuis, maken een duidelijke offerte met een vaste prijs en plannen samen met u de werkzaamheden. Denk aan badkamers, toiletten, keukens, zolders, het plaatsen of verwijderen van wanden en het afwerken van wanden en plafonds. Tijdens de klus heeft u één vast aanspreekpunt en houden we de werkplek elke dag netjes.',
 10),
('schilderwerk', 'Schilderwerk', 'brush',
 'Binnen- en buitenschilderwerk met verf die lang meegaat, inclusief houtrot-herstel.',
 'Goed schilderwerk begint met goede voorbereiding. We schuren, ontvetten en repareren eerst alle ondergronden, en herstellen houtrot voordat er verf op komt. Binnen werken we met reukarme, watergedragen verf. Buiten gebruiken we systemen die zijn gemaakt voor het Nederlandse weer, zodat u jaren vooruit kunt.',
 20),
('tegelwerk', 'Tegelwerk', 'grid',
 'Strak tegelwerk voor badkamer, keuken en vloer, ook in groot formaat.',
 'Of het nu gaat om een wandje achter het aanrecht of een complete badkamer met grootformaat tegels: wij zorgen voor een vlakke ondergrond, een goede waterdichting en strakke voegen. We denken mee over tegelkeuze, legpatroon en afwerking, en leveren de ruimte schoon op.',
 30),
('timmerwerk', 'Timmerwerk', 'saw',
 'Maatwerk in hout: kasten, trappen, kozijnen, deuren en vlonders.',
 'Onze timmerman maakt en plaatst maatwerk zoals inbouwkasten, trapbekleding, binnendeuren, kozijnen, plinten en vlonders. We werken met duurzaam hout en meten alles zelf in, zodat het precies past, ook in oudere woningen waar niets recht is.',
 40),
('kleine-reparaties', 'Kleine reparaties', 'wrench',
 'Lekkende kraan, klemmende deur of een losse tegel? Geen klus is te klein.',
 'Heeft u een lijstje met klusjes die al een tijd blijven liggen? Wij komen langs en lossen ze in één bezoek op. Denk aan het afstellen van deuren en ramen, het vervangen van kitvoegen, het ophangen van lampen en planken of het repareren van een losse tegel. U betaalt een eerlijk uurtarief zonder voorrijkosten binnen ons werkgebied.',
 50);

INSERT INTO service_areas (town, distance_km, sort_order) VALUES
('Eikenrode', 0, 10),
('Lindenbeek', 6, 20),
('Brakelveld', 9, 30),
('Kempenhoven', 12, 40),
('Dommelrijt', 15, 50),
('Veenakker', 18, 60),
('Heiwaarde', 22, 70),
('Moerdal', 25, 80);
