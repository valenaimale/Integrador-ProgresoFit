import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { pool } from '../database/connection.js';

const SALT_ROUNDS = 10;

export async function register(email, password, rol = 'ALUMNO', gymData = {}) {
    const { rows: existing } = await pool.query('SELECT id FROM usuarios WHERE email = $1', [email]);

    if (existing.length > 0) {
        throw new Error('El correo ya está registrado');
    }

    const hashedPassword = await bcrypt.hash(password, SALT_ROUNDS);

    const { rows: [{ id }] } = await pool.query(
        'INSERT INTO usuarios (email, password, rol) VALUES ($1, $2, $3) RETURNING id',
        [email, hashedPassword, rol]
    );

    const userId = id;
    let gimnasio_id = null;

    if (rol === 'GIMNASIO') {
        const { nombre, direccion, horarios, telefono, descripcion, servicios } = gymData;
        if (!nombre) throw new Error('El nombre del gimnasio es obligatorio');

        const { rows: [{ id: gymId }] } = await pool.query(
            'INSERT INTO gimnasios (usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios) VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id',
            [userId, nombre, direccion || null, horarios || null, telefono || null, email, descripcion || null, servicios || null]
        );
        gimnasio_id = gymId;
    }

    return { id: userId, email, rol, gimnasio_id };
}

export async function login(email, password) {
    const { rows: users } = await pool.query(
        'SELECT id, email, password, rol FROM usuarios WHERE email = $1',
        [email]
    );

    if (users.length === 0) {
        throw new Error('Credenciales inválidas');
    }

    const user = users[0];
    const isValid = await bcrypt.compare(password, user.password);

    if (!isValid) {
        throw new Error('Credenciales inválidas');
    }

    let gimnasio_id = null;
    if (user.rol === 'GIMNASIO') {
        const { rows: gyms } = await pool.query(
            'SELECT id FROM gimnasios WHERE usuario_id = $1 AND activo = TRUE',
            [user.id]
        );
        if (gyms.length > 0) gimnasio_id = gyms[0].id;
    }

    const payload = {
        id: user.id,
        email: user.email,
        rol: user.rol,
        ...(gimnasio_id !== null && { gimnasio_id }),
    };

    const token = jwt.sign(payload, process.env.JWT_SECRET, { expiresIn: '24h' });

    return {
        token,
        user: {
            id: user.id,
            email: user.email,
            rol: user.rol,
            ...(gimnasio_id !== null && { gimnasio_id }),
        },
    };
}
