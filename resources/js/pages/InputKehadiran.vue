<template>
  <div>
    <!-- State Loading Awal -->
    <LoadingState v-if="loading" text="Memuat lembar presensi pertemuan..." />

    <!-- State Error Loading -->
    <div
      v-else-if="errorLoad"
      class="p-6 rounded-xl border border-rose-200 bg-rose-50 text-center text-rose-800"
    >
      <p class="font-medium text-sm mb-3">{{ errorLoad }}</p>
      <router-link
        :to="`/kelas/${kelasId}?tab=pertemuan`"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-300 rounded-md hover:bg-rose-100"
      >
        <ArrowLeft class="w-3.5 h-3.5" />
        <span>Kembali ke Detail Kelas</span>
      </router-link>
    </div>

    <!-- Konten Utama Input Kehadiran -->
    <div v-else-if="pertemuan" class="space-y-6 pb-20">
      <!-- Breadcrumb / Tombol Kembali -->
      <div>
        <router-link
          :to="`/kelas/${kelasId}?tab=pertemuan`"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali ke Detail Kelas</span>
        </router-link>
      </div>

      <!-- Header Halaman -->
      <PageHeader
        :title="`Pertemuan ke-${pertemuan.nomor}`"
        :description="`Tanggal: ${formatTanggal(pertemuan.tanggal)}`"
      >
        <template #badge>
          <span v-if="kelas" class="px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
            {{ kelas.mata_kuliah }} ({{ kelas.nama_kelas }})
          </span>
        </template>
      </PageHeader>

      <!-- Empty State Jika Kelas Belum Punya Mahasiswa -->
      <EmptyState
        v-if="daftarKehadiran.length === 0"
        title="Belum ada mahasiswa di kelas ini"
        description="Daftarkan mahasiswa ke dalam kelas terlebih dahulu agar dapat mencatat kehadiran pada pertemuan ini."
        action-text="Kelola Mahasiswa Kelas"
        :icon="Users"
        @action="$router.push(`/kelas/${kelasId}?tab=mahasiswa`)"
      />

      <!-- Daftar Presensi Mahasiswa -->
      <div v-else class="space-y-4">
        <!-- Kotak Pencarian Cepat Mahasiswa -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="relative max-w-sm w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              <Search class="w-4 h-4" />
            </div>
            <input
              v-model="kataKunci"
              type="text"
              placeholder="Cari mahasiswa di pertemuan ini..."
              class="block w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-lg placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
            />
          </div>

          <!-- Status Indikator Perubahan -->
          <div class="text-xs">
            <span
              v-if="adaPerubahan"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-medium"
            >
              <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
              <span>Ada perubahan belum disimpan</span>
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium"
            >
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>Presensi telah disimpan</span>
            </span>
          </div>
        </div>

        <!-- Tabel Presensi -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
              <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th scope="col" class="py-3.5 px-4 w-36">NIM</th>
                  <th scope="col" class="py-3.5 px-4">Nama Mahasiswa</th>
                  <th scope="col" class="py-3.5 px-4 w-80 text-right">Status Kehadiran</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="row in daftarTampil"
                  :key="row.mahasiswa_id"
                  class="hover:bg-slate-50/75 transition-colors"
                >
                  <td class="py-3 px-4 font-mono font-medium text-slate-900 tabular-nums">
                    {{ row.nim }}
                  </td>
                  <td class="py-3 px-4 font-medium text-slate-800">
                    {{ row.nama }}
                  </td>
                  <td class="py-3 px-4 text-right">
                    <!-- Segmented Radio Button Group -->
                    <div
                      class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50/80"
                      role="radiogroup"
                      :aria-label="`Pilihan kehadiran ${row.nama}`"
                    >
                      <label
                        v-for="stKey in STATUS_LIST"
                        :key="stKey"
                        :class="[
                          'px-3 py-1.5 text-xs font-semibold rounded-md cursor-pointer transition-all flex items-center gap-1.5 select-none',
                          row.status === stKey
                            ? getStatus(stKey).buttonActiveClass
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                        ]"
                      >
                        <input
                          type="radio"
                          :name="`kehadiran-${row.mahasiswa_id}`"
                          :value="stKey"
                          v-model="row.status"
                          class="sr-only"
                        />
                        <span>{{ getStatus(stKey).label }}</span>
                      </label>
                    </div>
                  </td>
                </tr>

                <tr v-if="daftarTampil.length === 0">
                  <td colspan="3" class="py-8 text-center text-slate-500 text-sm">
                    Tidak ada mahasiswa yang cocok dengan pencarian "{{ kataKunci }}".
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sticky Summary & Action Bar -->
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-xs border border-slate-200 rounded-xl p-3.5 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <!-- Ringkasan Counter Langsung -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-xs font-medium">
            <span class="text-slate-500 mr-1">Total {{ daftarKehadiran.length }} mhs:</span>

            <span
              v-for="stKey in STATUS_LIST"
              :key="stKey"
              :class="[
                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border',
                getStatus(stKey).countBadgeClass
              ]"
            >
              <span :class="['w-1.5 h-1.5 rounded-full', getStatus(stKey).dotClass]"></span>
              <span>{{ getStatus(stKey).label }}: <strong class="tabular-nums">{{ hitungan[stKey] || 0 }}</strong></span>
            </span>
          </div>

          <!-- Tombol Aksi -->
          <div class="flex items-center gap-2.5 shrink-0 self-end md:self-auto">
            <button
              type="button"
              :disabled="saving"
              @click="setSemuaHadir"
              class="px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:opacity-50 transition-colors"
            >
              Set Semua Hadir
            </button>

            <button
              type="button"
              :disabled="saving"
              @click="simpanKehadiran"
              :class="[
                'inline-flex items-center gap-2 px-5 py-2 text-sm font-medium rounded-lg shadow-xs focus-visible:outline-none focus-visible:ring-2 disabled:opacity-50 transition-all duration-200',
                adaPerubahan
                  ? 'bg-brand-600 hover:bg-brand-700 text-white focus-visible:ring-brand-500'
                  : 'bg-emerald-600 hover:bg-emerald-700 text-white focus-visible:ring-emerald-500'
              ]"
            >
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
              <Check v-else class="w-4 h-4" />
              <span>{{ saving ? 'Menyimpan...' : (adaPerubahan ? 'Simpan Presensi' : 'Presensi Telah Disimpan') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, onBeforeRouteLeave } from 'vue-router';
import { api } from '../api.js';
import { useToast } from '../useToast.js';
import { STATUS_LIST, getStatus } from '../status.js';
import PageHeader from '../components/PageHeader.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
import { ArrowLeft, Search, Check, Users, Loader2 } from 'lucide-vue-next';

const route = useRoute();
const toast = useToast();

const kelasId = route.params.id;
const pid = route.params.pid;

const kelas = ref(null);
const pertemuan = ref(null);
const daftarKehadiran = ref([]);
const snapshotAwal = ref('');
const loading = ref(true);
const errorLoad = ref('');
const saving = ref(false);
const kataKunci = ref('');

const daftarTampil = computed(() => {
  const q = kataKunci.value.trim().toLowerCase();
  if (!q) return daftarKehadiran.value;
  return daftarKehadiran.value.filter(
    (row) => row.nim.toLowerCase().includes(q) || row.nama.toLowerCase().includes(q)
  );
});

// Hitung rekap langsung dari pilihan di layar
const hitungan = computed(() => {
  const hasil = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
  for (const row of daftarKehadiran.value) {
    if (hasil[row.status] !== undefined) {
      hasil[row.status]++;
    }
  }
  return hasil;
});

// Mengecek apakah ada perubahan data dibanding saat pertama dimuat / disimpan
const adaPerubahan = computed(() => {
  if (!snapshotAwal.value) return false;
  const currentSnapshot = JSON.stringify(
    daftarKehadiran.value.map((r) => ({ id: r.mahasiswa_id, s: r.status }))
  );
  return currentSnapshot !== snapshotAwal.value;
});

function formatTanggal(str) {
  if (!str) return '-';
  const bagian = str.split('-');
  if (bagian.length !== 3) return str;
  const d = new Date(Number(bagian[0]), Number(bagian[1]) - 1, Number(bagian[2]));
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(d);
}

function setSemuaHadir() {
  for (const row of daftarKehadiran.value) {
    row.status = 'hadir';
  }
}

async function muatData() {
  loading.value = true;
  errorLoad.value = '';
  try {
    const [resKehadiran, resKelas] = await Promise.all([
      api.get(`/api/pertemuan/${pid}/kehadiran`),
      api.get(`/api/kelas/${kelasId}`),
    ]);

    const data = resKehadiran.data || resKehadiran;
    pertemuan.value = data.pertemuan;
    kelas.value = resKelas.data || resKelas;

    // Mahasiswa yang statusnya null default ke 'hadir' di layar
    daftarKehadiran.value = (data.kehadiran || []).map((item) => ({
      mahasiswa_id: item.mahasiswa_id,
      nim: item.nim,
      nama: item.nama,
      status: item.status || 'hadir',
    }));

    snapshotAwal.value = JSON.stringify(
      daftarKehadiran.value.map((r) => ({ id: r.mahasiswa_id, s: r.status }))
    );
  } catch (err) {
    errorLoad.value = err.message || 'Gagal memuat data presensi.';
  } finally {
    loading.value = false;
  }
}

async function simpanKehadiran() {
  saving.value = true;

  // Body request: seluruh mahasiswa di kelas
  const payload = {
    kehadiran: daftarKehadiran.value.map((item) => ({
      mahasiswa_id: item.mahasiswa_id,
      status: item.status,
    })),
  };

  try {
    const res = await api.put(`/api/pertemuan/${pid}/kehadiran`, payload);
    toast.success('Kehadiran berhasil disimpan.');

    // Perbarui data dari respons server
    const data = res.data || res;
    if (data.kehadiran) {
      daftarKehadiran.value = data.kehadiran.map((item) => ({
        mahasiswa_id: item.mahasiswa_id,
        nim: item.nim,
        nama: item.nama,
        status: item.status || 'hadir',
      }));
    }

    snapshotAwal.value = JSON.stringify(
      daftarKehadiran.value.map((r) => ({ id: r.mahasiswa_id, s: r.status }))
    );
  } catch (err) {
    toast.error(err.message || 'Gagal menyimpan presensi kehadiran.');
  } finally {
    saving.value = false;
  }
}

// Peringatan sebelum meninggalkan halaman bila ada perubahan belum disimpan
onBeforeRouteLeave((to, from, next) => {
  if (adaPerubahan.value) {
    const konfirmasi = window.confirm(
      'Ada perubahan kehadiran yang belum disimpan. Yakin ingin meninggalkan halaman ini?'
    );
    if (!konfirmasi) {
      next(false);
      return;
    }
  }
  next();
});

function handleBeforeUnload(e) {
  if (adaPerubahan.value) {
    e.preventDefault();
    e.returnValue = '';
  }
}

onMounted(() => {
  muatData();
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
