<template>
  <div class="hal-header">
    <h1>Kelas</h1>
  </div>

  <!-- Form tambah / ubah kelas -->
  <div class="form">
    <h2>{{ modEdit ? 'Ubah Kelas' : 'Tambah Kelas' }}</h2>
    <form @submit.prevent="simpan">
      <div class="form__baris">
        <label for="mata_kuliah">Mata Kuliah</label>
        <input id="mata_kuliah" v-model="form.mata_kuliah" type="text" maxlength="255" />
        <div v-if="errors.mata_kuliah" class="form__error">{{ errors.mata_kuliah[0] }}</div>
      </div>
      <div class="form__baris">
        <label for="nama_kelas">Nama Kelas</label>
        <input id="nama_kelas" v-model="form.nama_kelas" type="text" maxlength="255" />
        <div v-if="errors.nama_kelas" class="form__error">{{ errors.nama_kelas[0] }}</div>
      </div>
      <div class="form__baris">
        <label for="dosen">Dosen</label>
        <input id="dosen" v-model="form.dosen" type="text" maxlength="255" />
        <div v-if="errors.dosen" class="form__error">{{ errors.dosen[0] }}</div>
      </div>
      <div class="form__aksi">
        <button type="submit" class="btn btn--utama" :disabled="loading">Simpan</button>
        <button v-if="modEdit" type="button" class="btn" @click="batalEdit">Batal</button>
      </div>
    </form>
  </div>

  <!-- Pesan error lokal -->
  <div v-if="pesanGlobal" class="pesan-global pesan-global--gagal" style="padding:0;margin-bottom:12px;">
    {{ pesanGlobal }}
  </div>

  <!-- Tabel daftar kelas -->
  <div v-if="loading && !kelas.length" class="teks-muted">Memuat data...</div>
  <div v-else-if="!kelas.length" class="teks-muted">Belum ada data kelas.</div>
  <div v-else class="tabel-pembungkus">
    <table>
      <thead>
        <tr>
          <th>Mata Kuliah</th>
          <th>Kelas</th>
          <th>Dosen</th>
          <th>Jumlah Mahasiswa</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="k in kelas" :key="k.id">
          <td>{{ k.mata_kuliah }}</td>
          <td>{{ k.nama_kelas }}</td>
          <td>{{ k.dosen }}</td>
          <td>{{ k.jumlah_mahasiswa }}</td>
          <td>
            <div class="aksi">
              <router-link :to="'/kelas/' + k.id" class="btn">Buka</router-link>
              <button class="btn" @click="mulaEdit(k)">Ubah</button>
              <button class="btn--hapus" @click="hapus(k)">Hapus</button>
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

const kelas      = ref([]);
const loading    = ref(false);
const pesanGlobal = ref('');

const form    = reactive({ mata_kuliah: '', nama_kelas: '', dosen: '' });
const errors  = reactive({});
const modEdit = ref(null);

async function muat() {
  loading.value = true;
  pesanGlobal.value = '';
  try {
    const resp = await api.get('/api/kelas?per_page=200');
    kelas.value = resp.data ?? [];
  } catch {
    pesanGlobal.value = 'Gagal memuat data kelas.';
  } finally {
    loading.value = false;
  }
}

function resetForm() {
  form.mata_kuliah = '';
  form.nama_kelas  = '';
  form.dosen       = '';
  Object.keys(errors).forEach(k => delete errors[k]);
}

function mulaEdit(k) {
  modEdit.value = k;
  form.mata_kuliah = k.mata_kuliah;
  form.nama_kelas  = k.nama_kelas;
  form.dosen       = k.dosen;
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
    const body = { mata_kuliah: form.mata_kuliah, nama_kelas: form.nama_kelas, dosen: form.dosen };
    if (modEdit.value) {
      await api.put(`/api/kelas/${modEdit.value.id}`, body);
      tampilPesan({ teks: 'Data kelas berhasil diperbarui.' });
    } else {
      await api.post('/api/kelas', body);
      tampilPesan({ teks: 'Kelas berhasil ditambahkan.' });
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

async function hapus(k) {
  if (!window.confirm(
    `Hapus kelas "${k.mata_kuliah} ${k.nama_kelas}"?\n\nPeringatan: semua pertemuan dan data kehadiran kelas ini juga akan terhapus.`
  )) return;
  pesanGlobal.value = '';
  try {
    await api.delete(`/api/kelas/${k.id}`);
    tampilPesan({ teks: 'Kelas berhasil dihapus.' });
    await muat();
  } catch (e) {
    pesanGlobal.value = e.message || 'Gagal menghapus kelas.';
  }
}

onMounted(muat);
</script>
