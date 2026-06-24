import { readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { pool } from './connection.js';

const __dirname = dirname(fileURLToPath(import.meta.url));

const files = [
  join(__dirname, 'init', '01-schema.sql'),
  join(__dirname, 'init', '02-pagos.sql'),
  join(__dirname, 'init', '02-seed.sql'),
];

export async function initDatabase() {
  const client = await pool.connect();
  try {
    for (const file of files) {
      const sql = readFileSync(file, 'utf-8');
      await client.query(sql);
      console.log(`DB init OK: ${file.split('/').pop()}`);
    }
    console.log('Database initialized successfully');
  } catch (err) {
    console.error('Database init failed:', err.message);
    throw err;
  } finally {
    client.release();
  }
}
