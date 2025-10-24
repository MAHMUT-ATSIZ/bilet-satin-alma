BEGIN;


CREATE TABLE IF NOT EXISTS cities (
id INTEGER PRIMARY KEY,
name TEXT NOT NULL UNIQUE
);


CREATE TABLE IF NOT EXISTS trips (
id INTEGER PRIMARY KEY,
company_id INTEGER NOT NULL REFERENCES companies(id),
departure_city_id INTEGER NOT NULL REFERENCES cities(id),
arrival_city_id INTEGER NOT NULL REFERENCES cities(id),
departure_time TEXT NOT NULL, 
arrival_time TEXT NOT NULL,
price REAL NOT NULL CHECK(price >= 0),
seat_capacity INTEGER NOT NULL CHECK(seat_capacity > 0),
created_at TEXT NOT NULL DEFAULT (datetime('now'))
);


-- örnek şehirler
INSERT OR IGNORE INTO cities (id, name) VALUES
(1,'İstanbul'),
(2,'Ankara'),
(3,'İzmir'),
(4,'Bursa'),
(5,'Antalya');


COMMIT;