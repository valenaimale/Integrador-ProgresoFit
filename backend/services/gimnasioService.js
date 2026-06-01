import * as gimnasioRepository from '../repositories/gimnasioRepository.js';

export async function getAllGimnasios(user, search) {
    return await gimnasioRepository.findAll(search);
}

export async function getGimnasioById(user, id) {
    const gimnasio = await gimnasioRepository.findById(id);
    if (!gimnasio) throw new Error('Gimnasio no encontrado');
    return gimnasio;
}

export async function getMyGimnasio(user) {
    if (user.rol !== 'GIMNASIO') throw new Error('No tenés un perfil de gimnasio asociado');
    const gimnasio = await gimnasioRepository.findByUsuarioId(user.id);
    if (!gimnasio) throw new Error('Perfil de gimnasio no encontrado');
    return gimnasio;
}

export async function createGimnasio(user, data) {
    if (user.rol !== 'ADMIN') {
        throw new Error('No tenés permiso para crear gimnasios');
    }
    return await gimnasioRepository.create(data);
}

export async function updateGimnasio(user, id, data) {
    if (user.rol === 'GIMNASIO') {
        if (String(user.gimnasio_id) !== String(id)) {
            throw new Error('No tenés permiso para editar este gimnasio');
        }
    } else if (user.rol !== 'ADMIN') {
        throw new Error('No tenés permiso para editar gimnasios');
    }

    const actualizado = await gimnasioRepository.update(id, data);
    if (!actualizado) throw new Error('Gimnasio no encontrado');
    return actualizado;
}

export async function deleteGimnasio(user, id) {
    if (user.rol !== 'ADMIN') {
        throw new Error('No tenés permiso para eliminar gimnasios');
    }
    await gimnasioRepository.logicalDelete(id);
}
