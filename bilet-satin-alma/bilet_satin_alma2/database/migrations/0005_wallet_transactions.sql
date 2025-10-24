BEGIN;

CREATE TABLE IF NOT EXISTS wallet_transactions (
  id         INTEGER PRIMARY KEY,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  amount     REAL    NOT NULL, 
  type       TEXT    NOT NULL CHECK (type IN ('DEPOSIT','SPEND','REFUND')),
  ticket_id  INTEGER NULL REFERENCES tickets(id) ON DELETE SET NULL,
  note       TEXT,
  created_at TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS ix_wallet_user ON wallet_transactions(user_id);

COMMIT;
