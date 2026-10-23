/**
 * api.js
 * Helper fetch sederhana untuk semua panggilan ke endpoint /api
 *
 * Mengembalikan JSON atau null untuk 204 No Content.
 * Bila server membalas status selain 2xx, melempar error terstruktur:
 * { status, message, errors }
 */

const HEADERS = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
};

async function kirim(method, path, body) {
  const opsi = { method, headers: { ...HEADERS } };
  if (body !== undefined) opsi.body = JSON.stringify(body);

  const resp = await fetch(path, opsi);

  if (resp.status === 204) return null;

  let json = null;
  try {
    json = await resp.json();
  } catch {
    // Abaikan parse error bila body kosong atau teks
  }

  if (!resp.ok) {
    throw {
      status: resp.status,
      message: json?.message || 'Terjadi kesalahan pada server.',
      errors: json?.errors || {},
    };
  }

  return json;
}

export const api = {
  get:    (path)       => kirim('GET', path),
  post:   (path, body) => kirim('POST', path, body),
  put:    (path, body) => kirim('PUT', path, body),
  delete: (path)       => kirim('DELETE', path),
};
