BEGIN;

CREATE TABLE IF NOT EXISTS roles (
  id   INTEGER PRIMARY KEY,
  name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS companies (
  id   INTEGER PRIMARY KEY,
  name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY,
  fullname      TEXT NOT NULL,
  email         TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role_id       INTEGER NOT NULL REFERENCES roles(id),
  company_id    INTEGER REFERENCES companies(id),
  created_at    TEXT NOT NULL DEFAULT (datetime('now'))
);

-- örnek roller
INSERT OR IGNORE INTO roles (id, name) VALUES
  (1,'Admin'), (2,'Firma Admin'), (3,'User');

COMMIT;
