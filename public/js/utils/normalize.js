/**
 * Utilidades de normalización de texto para búsquedas insensibles a mayúsculas y tildes
 */

export function normalizeText(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim();
}
