<template>
  <div class="hal-header">
    <h1>Mahasiswa</h1>
  </div>

  <!-- Form tambah / ubah -->
  <div class="form">
    <h2>{{ modEdit ? 'Ubah Mahasiswa' : 'Tambah Mahasiswa' }}</h2>
    <form @submit.prevent="simpan">
      <div class="form__baris">
        <label for="nim">NIM</label>
        <input id="nim" v-model="form.nim" type="text" maxlength="30" />
        <div v-if="errors.nim" class="form__error">{{ errors.nim[0] }}</div>
      </div>
      <div class="form__baris">
        <label for="nama">Nama</label>
        <input id="nama" v-model="form.nama" type="text" maxlength="255" />
        <div v-if="errors.nama" class="form__error">{{ errors.nama[0] }}</div>
      </div>
      <div class="form__aksi">
        <button type="submit" class="btn btn--utama" :disabled="loading">Simpan</button>
        <button v-if="modEdit" type="button" class="btn" @click="batalEdit">Batal</button>
      </div>
    </form>
  </div>

  <!-- Pesan error global (misal penolakan hapus) -->
  <div v-if="pesanGlobal" class="pesan-global pesan-global--gagal" style="padding: 0; margin-bottom: 12px;">
    {{ pesanGlobal }}
  </div>

  <!-- Tabel daftar mahasiswa -->
  <div v-if="loading && !mahasiswa.length" class="teks-muted">Memuat data...</div>
  <div v-else-if="!mahasiswa.length" class="teks-muted">Belum ada data mahasiswa.</div>
  <div v-else class="tabel-pembungkus">
    <table>
      <thead>
        <tr>
          <th>NIM</th>
          <th>Nama</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="m in mahasiswa" :key="m.id">
          <td>{{ m.nim }}</td>
          <td>{{ m.nama }}</td>
          <td>
            <div class="aksi">
              <button class="btn" @click="mulaEdit(m)">Ubah</button>
              <button class="btn--hapus" @click="hapus(m)">Hapus</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, reactive, inject, onMounted } from 'vue';
import { api } from '../api.js';

const tampilPesan = inject('tampilPesan');

const mahasiswa  = ref([]);
const loading    = ref(false);
const pesanGlobal = ref('');

const form   = reactive({ nim: '', nama: '' });
const errors = reactive({});
const modEdit = ref(null); // null = tambah, objek mahasiswa = ubah

// Ambil semua mahasiswa dari API (urut NIM dari backend)
async function muat() {
  loading.value = true;
  pesanGlobal.value = '';
  try {
    const resp = await api.get('/api/mahasiswa?per_page=200');
    mahasiswa.value = resp.data ?? [];
  } catch {
    pesanGlobal.value = 'Gagal memuat data mahasiswa.';
  } finally {
    loading.value = false;
  }
}

function resetForm() {
  form.nim  = '';
  form.nama = '';
  Object.keys(errors).forEach(k => delete errors[k]);
}

function mulaEdit(m) {
  modEdit.value = m;
  form.nim  = m.nim;
  form.nama = m.nama;
  Object.keys(errors).forEach(k => delete errors[k]);
  pesanGlobal.value = '';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function batalEdit() {
  modEdit.value = null;
  resetForm();
}

async function simpan() {
  Object.keys(errors).forEach(k => delete errors[k]);
  pesanGlobal.value = '';
  loading.value = true;
  try {
    if (modEdit.value) {
      // PUT /api/mahasiswa/{id}
      await api.put(`/api/mahasiswa/${modEdit.value.id}`, { nim: form.nim, nama: form.nama });
      tampilPesan({ teks: 'Data mahasiswa berhasil diperbarui.' });
    } else {
      // POST /api/mahasiswa
      await api.post('/api/mahasiswa', { nim: form.nim, nama: form.nama });
      tampilPesan({ teks: 'Mahasiswa berhasil ditambahkan.' });
    }
    modEdit.value = null;
    resetForm();
    await muat();
  } catch (e) {
    if (e.errors) Object.assign(errors, e.errors);
    else pesanGlobal.value = e.message || 'Gagal menyimpan data.';
  } finally {
    loading.value = false;
  }
}

async function hapus(m) {
  if (!window.confirm(`Hapus mahasiswa "${m.nama}" (${m.nim})?`)) return;
  pesanGlobal.value = '';
  try {
    await api.delete(`/api/mahasiswa/${m.id}`);
    tampilPesan({ teks: 'Mahasiswa berhasil dihapus.' });
    await muat();
  } catch (e) {
    // Tampilkan pesan dari backend apa adanya (mis. masih terdaftar di kelas)
    pesanGlobal.value = e.message || 'Gagal menghapus mahasiswa.';
  }
}

onMounted(muat);
</script>
