<template>
  <div v-if="loadingKelas" class="teks-muted">Memuat...</div>
  <div v-else-if="!kelas" class="teks-muted">Kelas tidak ditemukan.</div>
  <template v-else>
    <!-- Header halaman -->
    <div class="hal-header">
      <h1>{{ kelas.mata_kuliah }} — {{ kelas.nama_kelas }}</h1>
      <p>Dosen: {{ kelas.dosen }}</p>
      <router-link to="/kelas">Kembali ke daftar kelas</router-link>
    </div>

    <!-- Tab navigasi -->
    <div class="tab-nav">
      <button @click="tabAktif = 'pertemuan'" :class="{ aktif: tabAktif === 'pertemuan' }">Pertemuan</button>
      <button @click="tabAktif = 'mahasiswa';  muatMahasiswa()" :class="{ aktif: tabAktif === 'mahasiswa' }">Mahasiswa</button>
      <button @click="tabAktif = 'rekap';      muatRekap()"     :class="{ aktif: tabAktif === 'rekap' }">Rekap</button>
    </div>

    <!-- ================= TAB PERTEMUAN ================= -->
    <div v-show="tabAktif === 'pertemuan'">
      <div class="form">
        <h2>Tambah Pertemuan</h2>
        <form @submit.prevent="simpanPertemuan">
          <div class="form__baris">
            <label for="nomor-pertemuan">Nomor (opsional)</label>
            <input id="nomor-pertemuan" v-model="formPertemuan.nomor" type="number" min="1" style="max-width:120px" />
            <div class="form__petunjuk">Kosongkan untuk nomor berikutnya secara otomatis.</div>
            <div v-if="errPertemuan.nomor" class="form__error">{{ errPertemuan.nomor[0] }}</div>
          </div>
          <div class="form__baris">
            <label for="tanggal-pertemuan">Tanggal</label>
            <input id="tanggal-pertemuan" v-model="formPertemuan.tanggal" type="date" style="max-width:200px" />
            <div v-if="errPertemuan.tanggal" class="form__error">{{ errPertemuan.tanggal[0] }}</div>
          </div>
          <div class="form__aksi">
            <button type="submit" class="btn btn--utama" :disabled="loadingPertemuan">Tambah</button>
          </div>
        </form>
      </div>

      <div v-if="pesanPertemuan" class="pesan-global pesan-global--gagal" style="padding:0;margin-bottom:12px;">
        {{ pesanPertemuan }}
      </div>

      <!-- Form ubah pertemuan (tampil bila ada yang dipilih untuk diubah) -->
      <div v-if="editPertemuan" class="form" style="margin-top:0;padding:12px;border:1px solid var(--warna-border);">
        <h2>Ubah Pertemuan {{ editPertemuan.nomor }}</h2>
        <form @submit.prevent="perbaruiPertemuan">
          <div class="form__baris">
            <label for="edit-tanggal">Tanggal</label>
            <input id="edit-tanggal" v-model="formUbahPertemuan.tanggal" type="date" style="max-width:200px" />
          </div>
          <div class="form__aksi">
            <button type="submit" class="btn btn--utama" :disabled="loadingPertemuan">Simpan</button>
            <button type="button" class="btn" @click="editPertemuan = null">Batal</button>
          </div>
        </form>
      </div>

      <div v-if="loadingPertemuan && !pertemuan.length" class="teks-muted">Memuat pertemuan...</div>
      <div v-else-if="!pertemuan.length" class="teks-muted">Belum ada pertemuan.</div>
      <div v-else class="tabel-pembungkus" style="margin-top:16px">
        <table>
          <thead>
            <tr>
              <th>Nomor</th>
              <th>Tanggal</th>
              <th>Kehadiran</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in pertemuan" :key="p.id">
              <td>{{ p.nomor }}</td>
              <td>{{ formatTanggal(p.tanggal) }}</td>
              <td>
                <span v-if="p.jumlah_tercatat > 0">Sudah dicatat ({{ p.jumlah_tercatat }})</span>
                <span v-else class="teks-muted">Belum dicatat</span>
              </td>
              <td>
                <div class="aksi">
                  <router-link :to="`/kelas/${kelas.id}/pertemuan/${p.id}`" class="btn">Input Kehadiran</router-link>
                  <button class="btn" @click="mulaEditPertemuan(p)">Ubah</button>
                  <button class="btn--hapus" @click="hapusPertemuan(p)">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================= TAB MAHASISWA ================= -->
    <div v-show="tabAktif === 'mahasiswa'">
      <!-- Pilih dan daftarkan mahasiswa -->
      <div class="form">
        <h2>Daftarkan Mahasiswa</h2>
        <div v-if="!mahasiswaBelumDaftar.length" class="teks-muted" style="margin-bottom:8px">
          Semua mahasiswa sudah terdaftar di kelas ini.
        </div>
        <template v-else>
          <div class="form__baris">
            <label for="pilih-mahasiswa">Pilih Mahasiswa</label>
            <select id="pilih-mahasiswa" v-model="pilihMahasiswaId" style="max-width:300px">
              <option value="">-- pilih mahasiswa --</option>
              <option v-for="m in mahasiswaBelumDaftar" :key="m.id" :value="m.id">
                {{ m.nim }} - {{ m.nama }}
              </option>
            </select>
          </div>
          <div class="form__aksi">
            <button class="btn btn--utama" :disabled="!pilihMahasiswaId || loadingMhs" @click="daftarkan">Daftarkan</button>
          </div>
        </template>
        <div v-if="pesanMhs" class="pesan-global pesan-global--gagal" style="padding:0;margin-top:10px;">{{ pesanMhs }}</div>
      </div>

      <!-- Tabel mahasiswa terdaftar -->
      <div v-if="loadingMhs && !mahasiswaTerdaftar.length" class="teks-muted">Memuat...</div>
      <div v-else-if="!mahasiswaTerdaftar.length" class="teks-muted">Belum ada mahasiswa terdaftar.</div>
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
            <tr v-for="m in mahasiswaTerdaftar" :key="m.id">
              <td>{{ m.nim }}</td>
              <td>{{ m.nama }}</td>
              <td>
                <button class="btn--hapus" @click="keluarkan(m)">Keluarkan</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================= TAB REKAP ================= -->
    <div v-show="tabAktif === 'rekap'">
      <div v-if="loadingRekap" class="teks-muted">Memuat rekap...</div>
      <template v-else>
        <p class="teks-muted" style="margin-bottom:10px;">
          Dihitung dari {{ rekap.pertemuan_tercatat ?? 0 }} pertemuan yang sudah dicatat.
        </p>
        <div v-if="!rekap.data?.length" class="teks-muted">Belum ada data rekap.</div>
        <div v-else class="tabel-pembungkus">
          <table>
            <thead>
              <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
                <th>Persentase</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in rekap.data" :key="r.mahasiswa_id">
                <td>{{ r.nim }}</td>
                <td>{{ r.nama }}</td>
                <td>{{ r.hadir }}</td>
                <td>{{ r.izin }}</td>
                <td>{{ r.sakit }}</td>
                <td>{{ r.alpa }}</td>
                <td>
                  <span :class="r.persentase < AMBANG_RENDAH ? 'teks-rendah' : ''">
                    {{ r.persentase.toFixed(1) }}%
                    <span v-if="r.persentase < AMBANG_RENDAH"> rendah</span>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
          <p class="teks-keterangan">Izin dan sakit tidak dihitung hadir.</p>
        </div>
      </template>
    </div>
  </template>
</template>

<script setup>
import { ref, reactive, computed, inject, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api.js';

// Ambang persentase rendah — mudah diubah di satu tempat
const AMBANG_RENDAH = 75;

const route = useRoute();
const kelasId = route.params.id;
const tampilPesan = inject('tampilPesan');

// ──────────────────────────────────────────
//  State utama
// ──────────────────────────────────────────
const kelas        = ref(null);
const loadingKelas = ref(true);
const tabAktif     = ref('pertemuan');

// Tab Pertemuan
const pertemuan       = ref([]);
const loadingPertemuan = ref(false);
const pesanPertemuan  = ref('');
const editPertemuan   = ref(null);
const formPertemuan   = reactive({ nomor: '', tanggal: hariIni() });
const formUbahPertemuan = reactive({ tanggal: '' });
const errPertemuan    = reactive({});

// Tab Mahasiswa
const mahasiswaTerdaftar   = ref([]);
const semuaMahasiswa      = ref([]);
const pilihMahasiswaId     = ref('');
const loadingMhs           = ref(false);
const pesanMhs             = ref('');

// Tab Rekap
const rekap        = reactive({ pertemuan_tercatat: 0, data: [] });
const loadingRekap = ref(false);

// Mahasiswa yang belum terdaftar = semua mahasiswa dikurangi yang sudah terdaftar
const mahasiswaBelumDaftar = computed(() => {
  const terdaftarIds = new Set(mahasiswaTerdaftar.value.map(m => m.id));
  return semuaMahasiswa.value.filter(m => !terdaftarIds.has(m.id));
});

// ──────────────────────────────────────────
//  Inisialisasi
// ──────────────────────────────────────────
onMounted(async () => {
  try {
    const resp = await api.get(`/api/kelas/${kelasId}`);
    kelas.value = resp.data;
  } catch {
    kelas.value = null;
  } finally {
    loadingKelas.value = false;
  }
  await Promise.all([muatPertemuan(), muatMahasiswa(), muatSemuaMahasiswa()]);
});

// ──────────────────────────────────────────
//  Pertemuan
// ──────────────────────────────────────────
async function muatPertemuan() {
  loadingPertemuan.value = true;
  try {
    const resp = await api.get(`/api/kelas/${kelasId}/pertemuan`);
    pertemuan.value = resp.data ?? [];
  } catch {
    pesanPertemuan.value = 'Gagal memuat pertemuan.';
  } finally {
    loadingPertemuan.value = false;
  }
}

async function simpanPertemuan() {
  Object.keys(errPertemuan).forEach(k => delete errPertemuan[k]);
  pesanPertemuan.value = '';
  loadingPertemuan.value = true;
  try {
    const body = { tanggal: formPertemuan.tanggal };
    // Kirim nomor hanya bila diisi
    if (formPertemuan.nomor !== '' && formPertemuan.nomor !== null) {
      body.nomor = Number(formPertemuan.nomor);
    }
    await api.post(`/api/kelas/${kelasId}/pertemuan`, body);
    formPertemuan.nomor  = '';
    formPertemuan.tanggal = hariIni();
    tampilPesan({ teks: 'Pertemuan berhasil ditambahkan.' });
    await muatPertemuan();
  } catch (e) {
    if (e.errors) Object.assign(errPertemuan, e.errors);
    else pesanPertemuan.value = e.message || 'Gagal menambah pertemuan.';
  } finally {
    loadingPertemuan.value = false;
  }
}

function mulaEditPertemuan(p) {
  editPertemuan.value = p;
  formUbahPertemuan.tanggal = p.tanggal;
}

async function perbaruiPertemuan() {
  loadingPertemuan.value = true;
  try {
    await api.put(`/api/pertemuan/${editPertemuan.value.id}`, { tanggal: formUbahPertemuan.tanggal });
    editPertemuan.value = null;
    tampilPesan({ teks: 'Pertemuan berhasil diperbarui.' });
    await muatPertemuan();
  } catch (e) {
    pesanPertemuan.value = e.message || 'Gagal memperbarui pertemuan.';
  } finally {
    loadingPertemuan.value = false;
  }
}

async function hapusPertemuan(p) {
  if (!window.confirm(`Hapus pertemuan ke-${p.nomor} (${formatTanggal(p.tanggal)})? Data kehadiran pertemuan ini juga akan terhapus.`)) return;
  try {
    await api.delete(`/api/pertemuan/${p.id}`);
    tampilPesan({ teks: 'Pertemuan berhasil dihapus.' });
    await muatPertemuan();
  } catch (e) {
    pesanPertemuan.value = e.message || 'Gagal menghapus pertemuan.';
  }
}

// ──────────────────────────────────────────
//  Mahasiswa
// ──────────────────────────────────────────
async function muatMahasiswa() {
  loadingMhs.value = true;
  pesanMhs.value = '';
  try {
    const resp = await api.get(`/api/kelas/${kelasId}/mahasiswa`);
    mahasiswaTerdaftar.value = resp.data ?? [];
  } catch {
    pesanMhs.value = 'Gagal memuat mahasiswa.';
  } finally {
    loadingMhs.value = false;
  }
}

async function muatSemuaMahasiswa() {
  try {
    const resp = await api.get('/api/mahasiswa?per_page=200');
    semuaMahasiswa.value = resp.data ?? [];
  } catch { /* abaikan */ }
}

async function daftarkan() {
  if (!pilihMahasiswaId.value) return;
  pesanMhs.value = '';
  loadingMhs.value = true;
  try {
    await api.post(`/api/kelas/${kelasId}/mahasiswa`, { mahasiswa_id: pilihMahasiswaId.value });
    pilihMahasiswaId.value = '';
    tampilPesan({ teks: 'Mahasiswa berhasil didaftarkan.' });
    await muatMahasiswa();
  } catch (e) {
    pesanMhs.value = e.message || 'Gagal mendaftarkan mahasiswa.';
  } finally {
    loadingMhs.value = false;
  }
}

async function keluarkan(m) {
  if (!window.confirm(`Keluarkan ${m.nama} dari kelas ini?`)) return;
  pesanMhs.value = '';
  try {
    await api.delete(`/api/kelas/${kelasId}/mahasiswa/${m.id}`);
    tampilPesan({ teks: `${m.nama} berhasil dikeluarkan dari kelas.` });
    await muatMahasiswa();
  } catch (e) {
    pesanMhs.value = e.message || 'Gagal mengeluarkan mahasiswa.';
  }
}

// ──────────────────────────────────────────
//  Rekap
// ──────────────────────────────────────────
async function muatRekap() {
  loadingRekap.value = true;
  try {
    const resp = await api.get(`/api/kelas/${kelasId}/rekap`);
    rekap.pertemuan_tercatat = resp.data?.pertemuan_tercatat ?? 0;
    rekap.data = resp.data?.rekap ?? [];
  } catch {
    rekap.data = [];
  } finally {
    loadingRekap.value = false;
  }
}

// ──────────────────────────────────────────
//  Helpers
// ──────────────────────────────────────────
function hariIni() {
  return new Date().toISOString().slice(0, 10);
}

function formatTanggal(iso) {
  if (!iso) return '-';
  const [y, m, d] = iso.split('-');
  return `${d}-${m}-${y}`;
}
</script>
