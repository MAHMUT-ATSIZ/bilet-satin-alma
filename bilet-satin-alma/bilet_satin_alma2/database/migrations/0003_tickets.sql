BEGIN;


CREATE TABLE IF NOT EXISTS tickets (
id INTEGER PRIMARY KEY,
user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
trip_id INTEGER NOT NULL REFERENCES trips(id) ON DELETE CASCADE,
seat_no INTEGER NOT NULL,
price REAL NOT NULL CHECK(price >= 0),
status TEXT NOT NULL CHECK(status IN ('PAID','CANCELLED')),
pnr TEXT NOT NULL UNIQUE,
created_at TEXT NOT NULL DEFAULT (datetime('now')),
paid_at TEXT,
canceled_at TEXT
);


CREATE UNIQUE INDEX IF NOT EXISTS ux_tickets_trip_seat ON tickets(trip_id, seat_no);
CREATE INDEX IF NOT EXISTS ix_tickets_user ON tickets(user_id);


COMMIT;