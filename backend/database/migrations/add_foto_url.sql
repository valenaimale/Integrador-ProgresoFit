-- Add foto_url column to usuarios and gimnasios tables
ALTER TABLE usuarios  ADD COLUMN IF NOT EXISTS foto_url TEXT DEFAULT NULL;
ALTER TABLE gimnasios ADD COLUMN IF NOT EXISTS foto_url TEXT DEFAULT NULL;
