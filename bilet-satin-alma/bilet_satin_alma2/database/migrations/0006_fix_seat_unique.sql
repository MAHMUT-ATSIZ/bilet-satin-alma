BEGIN;


DROP INDEX IF EXISTS ux_tickets_trip_seat;


CREATE UNIQUE INDEX IF NOT EXISTS ux_tickets_trip_seat_paid
ON tickets(trip_id, seat_no)
WHERE status = 'PAID';

COMMIT;
