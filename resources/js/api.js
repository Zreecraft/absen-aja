/**
 * api.js - Helper fetch sederhana untuk semua panggilan ke /api
 *
 * Setiap fungsi mengembalikan data JSON (sudah di-unwrap satu lapisan "data"
 * bila ada) atau null untuk 204 No Content.
 * Bila server membalas error, dilempar objek { status, message, errors }.
 */

const HEADERS = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
};

// Fungsi inti - semua metode HTTP lewat sini
async function kirim(method, path, body) {
  const opsi = { method, headers: HEADERS };
  if (body !== undefined) opsi.body = JSON.stringify(body);

  const resp = await fetch(path, opsi);

  // 204 No Content: tidak ada body
  if (resp.status === 204) return null;

  const json = await resp.json();

  if (!resp.ok) {
    // Backend Laravel meletakkan pesan di json.message dan per-field di json.errors
    throw { status: resp.status, message: json.message || 'Terjadi kesalahan.', errors: json.errors || {} };
  }

  return json;
}

export const api = {
  get:    (path)         => kirim('GET', path),
  post:   (path, body)   => kirim('POST', path, body),
  put:    (path, body)   => kirim('PUT', path, body),
  delete: (path)         => kirim('DELETE', path),
};
