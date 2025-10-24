BEGIN;
CREATE TABLE IF NOT EXISTS coupons (
  id               INTEGER PRIMARY KEY,
  company_id       INTEGER NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
  code             TEXT    NOT NULL,                
  discount_percent INTEGER NOT NULL CHECK(discount_percent BETWEEN 1 AND 100),
  is_active        INTEGER NOT NULL DEFAULT 1,      
  starts_at        TEXT    NULL,                    
  ends_at          TEXT    NULL,
  max_uses         INTEGER NULL,                    
  used_count       INTEGER NOT NULL DEFAULT 0,
  created_at       TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE UNIQUE INDEX IF NOT EXISTS ux_coupons_company_code ON coupons(company_id, code);
COMMIT;
