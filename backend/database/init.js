import { readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import pg from 'pg';
import dotenv from 'dotenv';

dotenv.config();

const __dirname = dirname(fileURLToPath(import.meta.url));

const files = [
  join(__dirname, 'init', '01-schema.sql'),
  join(__dirname, 'init', '02-pagos.sql'),
  join(__dirname, 'init', '02-seed.sql'),
];

const poolConfig = process.env.DATABASE_URL
  ? { connectionString: process.env.DATABASE_URL, ssl: { rejectUnauthorized: false } }
  : { host: process.env.DB_HOST, user: process.env.DB_USER, password: process.env.DB_PASSWORD, database: process.env.DB_NAME };

const client = new pg.Client(poolConfig);

try {
  await client.connect();
  for (const file of files) {
    const sql = readFileSync(file, 'utf-8');
    await client.query(sql);
    console.log(`OK: ${file.split('/').pop()}`);
  }
  console.log('Database initialized successfully');
} catch (err) {
  console.error('Database init failed:', err.message);
  process.exit(1);
} finally {
  await client.end();
}
