import { pool } from '../database/connection.js';

export async function findAll(search) {
    let query = 'SELECT id, nombre, direccion, horarios, activo FROM gimnasios WHERE activo = TRUE';
    const params = [];

    if (search) {
        query += ' AND nombre LIKE ?';
        params.push(`%${search}%`);
    }

    const [rows] = await pool.query(query, params);
    return rows;
}

export async function findById(id) {
    const [rows] = await pool.query(
        'SELECT id, usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios, activo FROM gimnasios WHERE id = ? AND activo = TRUE',
        [id]
    );
    return rows[0];
}

export async function findByUsuarioId(usuario_id) {
    const [rows] = await pool.query(
        'SELECT id, usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios, activo FROM gimnasios WHERE usuario_id = ? AND activo = TRUE',
        [usuario_id]
    );
    return rows[0];
}

export async function create({ usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios }) {
    const [result] = await pool.query(
        'INSERT INTO gimnasios (usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [usuario_id || null, nombre, direccion || null, horarios || null, telefono || null, email || null, descripcion || null, servicios || null]
    );
    return { id: result.insertId, usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios, activo: true };
}

export async function update(id, { nombre, direccion, horarios, telefono, descripcion, servicios }) {
    await pool.query(
        'UPDATE gimnasios SET nombre = ?, direccion = ?, horarios = ?, telefono = ?, descripcion = ?, servicios = ? WHERE id = ?',
        [nombre, direccion, horarios, telefono, descripcion, servicios, id]
    );
    return findById(id);
}

export async function logicalDelete(id) {
    await pool.query('UPDATE gimnasios SET activo = FALSE WHERE id = ?', [id]);
}
