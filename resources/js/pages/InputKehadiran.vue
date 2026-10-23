<template>
  <div v-if="loading" class="teks-muted">Memuat data kehadiran...</div>
  <template v-else>
    <!-- Header halaman -->
    <div class="hal-header">
      <h1>Pertemuan ke-{{ pertemuan.nomor }} &mdash; {{ formatTanggal(pertemuan.tanggal) }}</h1>
      <p>{{ kelas.mata_kuliah }} {{ kelas.nama_kelas }} &mdash; {{ kelas.dosen }}</p>
      <router-link :to="'/kelas/' + route.params.id">Kembali ke detail kelas</router-link>
    </div>

    <!-- Bila belum ada mahasiswa terdaftar -->
    <div v-if="!baris.length">
      <p class="teks-muted">Belum ada mahasiswa di kelas ini.</p>
      <router-link :to="'/kelas/' + route.params.id" class="btn" style="display:inline-block;margin-top:10px;">
        Ke halaman kelas untuk mendaftarkan mahasiswa
      </router-link>
    </div>

    <template v-else>
      <!-- Ringkasan hitung langsung -->
      <p class="ringkasan">
        Hadir {{ hitung.hadir }},
        Izin {{ hitung.izin }},
        Sakit {{ hitung.sakit }},
        Alpa {{ hitung.alpa }}
      </p>

      <!-- Tombol aksi global -->
      <div class="form__aksi" style="margin-bottom:12px">
        <button class="btn" @click="semuaHadir">Semua hadir</button>
        <button class="btn btn--utama" :disabled="menyimpan" @click="simpan">
          {{ menyimpan ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>

      <!-- Pesan sukses / error -->
      <div v-if="pesanSimpan" :class="pesanSimpan.tipe === 'sukses' ? 'pesan-global pesan-global--sukses' : 'pesan-global pesan-global--gagal'"
           style="padding:0;margin-bottom:12px;">
        {{ pesanSimpan.teks }}
      </div>

      <!-- Tabel input kehadiran -->
      <div class="tabel-pembungkus">
        <table>
          <thead>
            <tr>
              <th>NIM</th>
              <th>Nama</th>
              <th>Hadir</th>
              <th>Izin</th>
              <th>Sakit</th>
              <th>Alpa</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(b, idx) in baris" :key="b.mahasiswa_id">
              <td>{{ b.nim }}</td>
              <td>{{ b.nama }}</td>
              <!-- Empat radio per baris; name unik per mahasiswa agar keyboard bisa dipakai -->
              <td>
                <label>
                  <input type="radio" :name="'status-' + b.mahasiswa_id" value="hadir" v-model="b.status" />
                </label>
              </td>
              <td>
                <label>
                  <input type="radio" :name="'status-' + b.mahasiswa_id" value="izin" v-model="b.status" />
                </label>
              </td>
              <td>
                <label>
                  <input type="radio" :name="'status-' + b.mahasiswa_id" value="sakit" v-model="b.status" />
                </label>
              </td>
              <td>
                <label>
                  <input type="radio" :name="'status-' + b.mahasiswa_id" value="alpa" v-model="b.status" />
                </label>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </template>
</template>

<script setup>
import { ref, reactive, computed, inject, onMounted } from 'vue';
import { useRoute, onBeforeRouteLeave } from 'vue-router';
import { api } from '../api.js';

const route = useRoute();
const kelasId      = route.params.id;
const pertemuanId  = route.params.pid;
const tampilPesan  = inject('tampilPesan');

const loading     = ref(true);
const menyimpan   = ref(false);
const pesanSimpan = ref(null);

// Data dari API
const pertemuan = reactive({ id: null, nomor: null, tanggal: null, kelas_id: null });
const kelas     = reactive({ mata_kuliah: '', nama_kelas: '', dosen: '' });
const baris     = ref([]); // [{ mahasiswa_id, nim, nama, status }]

// Snapshot untuk deteksi perubahan belum disimpan
let statusAwal = '';

// Ringkasan hitung langsung (computed dari baris yang bisa berubah)
const hitung = computed(() => {
  const h = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
  baris.value.forEach(b => { if (b.status && h[b.status] !== undefined) h[b.status]++; });
  return h;
});

// Muat data dari GET /api/pertemuan/{pid}/kehadiran
async function muat() {
  loading.value = true;
  pesanSimpan.value = null;
  try {
    // Ambil data kehadiran
    const respAbsen = await api.get(`/api/pertemuan/${pertemuanId}/kehadiran`);
    const dataAbsen = respAbsen.data;

    // Isi pertemuan dari respons
    Object.assign(pertemuan, dataAbsen.pertemuan);

    // Isi baris; status null -> default 'hadir' di layar (belum tersimpan)
    baris.value = dataAbsen.kehadiran.map(k => ({
      mahasiswa_id: k.mahasiswa_id,
      nim: k.nim,
      nama: k.nama,
      status: k.status ?? 'hadir',
    }));

    // Ambil data kelas untuk header
    const respKelas = await api.get(`/api/kelas/${kelasId}`);
    Object.assign(kelas, respKelas.data);

    // Catat status awal untuk deteksi perubahan
    statusAwal = JSON.stringify(baris.value.map(b => ({ id: b.mahasiswa_id, s: b.status })));
  } catch (e) {
    pesanSimpan.value = { tipe: 'gagal', teks: e.message || 'Gagal memuat data.' };
  } finally {
    loading.value = false;
  }
}

function semuaHadir() {
  baris.value.forEach(b => { b.status = 'hadir'; });
}

async function simpan() {
  menyimpan.value = true;
  pesanSimpan.value = null;
  try {
    const payload = {
      kehadiran: baris.value.map(b => ({
        mahasiswa_id: b.mahasiswa_id,
        status: b.status,
      })),
    };
    const resp = await api.put(`/api/pertemuan/${pertemuanId}/kehadiran`, payload);

    // Perbarui baris dari respons supaya statusAwal ikut diperbarui
    const diperbarui = resp.data?.kehadiran ?? [];
    const peta = {};
    diperbarui.forEach(k => { peta[k.mahasiswa_id] = k.status; });
    baris.value.forEach(b => { if (peta[b.mahasiswa_id]) b.status = peta[b.mahasiswa_id]; });

    // Perbarui snapshot
    statusAwal = JSON.stringify(baris.value.map(b => ({ id: b.mahasiswa_id, s: b.status })));

    pesanSimpan.value = { tipe: 'sukses', teks: 'Kehadiran tersimpan.' };
  } catch (e) {
    // Data di layar tidak dihapus; tampilkan pesan error
    pesanSimpan.value = { tipe: 'gagal', teks: e.message || 'Gagal menyimpan kehadiran.' };
  } finally {
    menyimpan.value = false;
  }
}

// Peringatan bila ada perubahan belum disimpan
function adaPerubahan() {
  const statusSekarang = JSON.stringify(baris.value.map(b => ({ id: b.mahasiswa_id, s: b.status })));
  return statusSekarang !== statusAwal;
}

onBeforeRouteLeave(() => {
  if (adaPerubahan()) {
    return window.confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?');
  }
});

function formatTanggal(iso) {
  if (!iso) return '-';
  const [y, m, d] = iso.split('-');
  return `${d}-${m}-${y}`;
}

onMounted(muat);
</script>
