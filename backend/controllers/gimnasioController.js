import * as gimnasioService from '../services/gimnasioService.js';

export async function getAll(req, res) {
    try {
        const search = req.query.search;
        const gimnasios = await gimnasioService.getAllGimnasios(req.user, search);
        res.json(gimnasios);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
}

export async function getById(req, res) {
    try {
        const gimnasio = await gimnasioService.getGimnasioById(req.user, req.params.id);
        res.json(gimnasio);
    } catch (error) {
        res.status(404).json({ error: error.message });
    }
}

export async function getMe(req, res) {
    try {
        const gimnasio = await gimnasioService.getMyGimnasio(req.user);
        res.json(gimnasio);
    } catch (error) {
        res.status(403).json({ error: error.message });
    }
}

export async function create(req, res) {
    try {
        const { nombre, direccion, horarios, telefono, email, descripcion, servicios } = req.body;
        if (!nombre) return res.status(400).json({ error: 'Nombre es requerido' });

        const nuevo = await gimnasioService.createGimnasio(req.user, { nombre, direccion, horarios, telefono, email, descripcion, servicios });
        res.status(201).json(nuevo);
    } catch (error) {
        res.status(403).json({ error: error.message });
    }
}
//usuario_id, nombre, direccion, horarios, telefono, email, descripcion, servicios
export async function registrar(req, res) {
  try {
    const {nombre, email, password, direccion, horarios, telefono, descripcion, servicios, foto_url} = req.body;
    const user = await gimnasioService.registrarGimnasio({nombre, email, password, direccion, horarios, telefono, descripcion, servicios, foto_url});
    res.status(201).json({ message: 'Gimnasio registrado exitosamente', user });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
}

export async function update(req, res) {
    try {
        const { nombre, direccion, horarios, telefono, descripcion, servicios } = req.body;
        const actualizado = await gimnasioService.updateGimnasio(req.user, req.params.id, { nombre, direccion, horarios, telefono, descripcion, servicios });
        res.json(actualizado);
    } catch (error) {
        res.status(403).json({ error: error.message });
    }
}

export async function updateMe(req, res) {
    try {
        const { nombre, direccion, horarios, telefono, descripcion, servicios, foto_url } = req.body;
        const gimnasio_id = req.user.gimnasio_id;
        if (!gimnasio_id) return res.status(403).json({ error: 'No tenés un perfil de gimnasio asociado' });

        const actualizado = await gimnasioService.updateGimnasio(req.user, gimnasio_id, { nombre, direccion, horarios, telefono, descripcion, servicios, foto_url });
        res.json(actualizado);
    } catch (error) {
        res.status(403).json({ error: error.message });
    }
}

export async function remove(req, res) {
    try {
        await gimnasioService.deleteGimnasio(req.user, req.params.id);
        res.json({ message: 'Gimnasio dado de baja lógicamente' });
    } catch (error) {
        res.status(403).json({ error: error.message });
    }
}
