// Proxies the wger.de REST API — no API key required
const WGER_BASE = 'https://wger.de/api/v2';

function stripHtml(html = '') {
  return html.replace(/<[^>]*>/g, '').trim();
}

function transformExercise(raw) {
  // Prefer Spanish (language id = 4), fallback to English (id = 2), fallback to first
  const translation =
    raw.translations?.find(t => (t.language?.id ?? t.language) === 4) ||
    raw.translations?.find(t => (t.language?.id ?? t.language) === 2) ||
    raw.translations?.[0];

  const name = translation?.name?.trim();
  if (!name) return null;

  // Prefer Spanish muscle names, fallback to English
  const musculos = raw.muscles?.map(m => m.name || m.name_en).join(', ') ||
                   raw.category?.name || '';

  const equipamiento = raw.equipment?.map(e => e.name).join(', ') || 'Sin equipamiento';

  return {
    api_id: raw.id,
    nombre: name,
    descripcion: stripHtml(translation?.description || ''),
    musculos,
    equipamiento,
    dificultad: 'media',
    video_url: null,
    categoria: raw.category?.name || ''
  };
}

export async function listarEjercicios({ limit = 20, offset = 0, q = '' } = {}) {
  let url = `${WGER_BASE}/exerciseinfo/?format=json&language=2&limit=${limit}&offset=${offset}`;
  if (q) url += `&name=${encodeURIComponent(q)}`;
  const response = await fetch(url);
  if (!response.ok) throw new Error('Error al contactar la API de ejercicios');

  const data = await response.json();
  const ejercicios = data.results.map(transformExercise).filter(Boolean);

  return { total: data.count, ejercicios };
}

export async function obtenerEjercicioExterno(apiId) {
  const url = `${WGER_BASE}/exerciseinfo/${apiId}/?format=json`;
  const response = await fetch(url);
  if (!response.ok) throw new Error('Ejercicio no encontrado en la API externa');

  const raw = await response.json();
  const ejercicio = transformExercise(raw);
  if (!ejercicio) throw new Error('Ejercicio sin traducción disponible');
  return ejercicio;
}
