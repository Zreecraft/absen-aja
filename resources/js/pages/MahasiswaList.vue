<template>
  <div>
    <!-- Header Halaman -->
    <PageHeader
      title="Data Mahasiswa"
      description="Kelola data induk mahasiswa untuk pendaftaran kelas dan presensi."
    >
      <template #actions>
        <button
          type="button"
          @click="bukaModalTambah"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 transition-colors"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Mahasiswa</span>
        </button>
      </template>
    </PageHeader>

    <!-- State Loading -->
    <LoadingState v-if="loading" text="Memuat daftar mahasiswa..." />

    <!-- State Error Loading -->
    <div
      v-else-if="errorLoad"
      class="p-6 rounded-xl border border-rose-200 bg-rose-50 text-center text-rose-800"
    >
      <p class="font-medium text-sm mb-3">{{ errorLoad }}</p>
      <button
        type="button"
        @click="muatMahasiswa"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-300 rounded-md hover:bg-rose-100"
      >
        <RotateCcw class="w-3.5 h-3.5" />
        <span>Coba Lagi</span>
      </button>
    </div>

    <!-- Konten Utama -->
    <div v-else>
      <!-- Empty State Jika Database Kosong -->
      <EmptyState
        v-if="semuaMahasiswa.length === 0"
        title="Belum ada data mahasiswa"
        description="Mulai dengan menambahkan data mahasiswa pertama untuk didaftarkan ke kelas."
        action-text="Tambah Mahasiswa"
        :icon="Users"
        @action="bukaModalTambah"
      />

      <div v-else class="space-y-4">
        <!-- Kotak Pencarian -->
        <div class="relative max-w-sm">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <Search class="w-4 h-4" />
          </div>
          <input
            v-model="kataKunci"
            type="text"
            placeholder="Cari berdasarkan NIM atau nama..."
            class="block w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-lg placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
          />
        </div>

        <!-- Tabel Mahasiswa -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
              <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th scope="col" class="py-3.5 px-4 w-44">NIM</th>
                  <th scope="col" class="py-3.5 px-4">Nama Mahasiswa</th>
                  <th scope="col" class="py-3.5 px-4 w-28 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="mhs in daftarTampil"
                  :key="mhs.id"
                  class="hover:bg-slate-50/75 transition-colors"
                >
                  <td class="py-3.5 px-4 font-mono font-medium text-slate-900 tabular-nums">
                    {{ mhs.nim }}
                  </td>
                  <td class="py-3.5 px-4 font-medium text-slate-800">
                    {{ mhs.nama }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="inline-flex items-center gap-1">
                      <button
                        type="button"
                        @click="bukaModalEdit(mhs)"
                        class="p-1.5 text-slate-500 hover:text-brand-600 rounded-md hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                        title="Ubah Data Mahasiswa"
                      >
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button
                        type="button"
                        @click="konfirmasiHapus(mhs)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                        title="Hapus Mahasiswa"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Baris jika pencarian tidak menemukan hasil -->
                <tr v-if="daftarTampil.length === 0">
                  <td colspan="3" class="py-8 text-center text-slate-500 text-sm">
                    Tidak ada mahasiswa yang cocok dengan kata kunci "{{ kataKunci }}".
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Footer Ringkasan -->
          <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            <span>Menampilkan {{ daftarTampil.length }} dari {{ semuaMahasiswa.length }} mahasiswa</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Tambah / Ubah Mahasiswa -->
    <ModalDialog
      :show="showModal"
      :title="modEdit ? 'Ubah Data Mahasiswa' : 'Tambah Mahasiswa Baru'"
      @close="tutupModal"
    >
      <form id="formMahasiswa" @submit.prevent="simpanMahasiswa" class="space-y-4">
        <div>
          <label for="input-nim" class="block text-sm font-medium text-slate-700 mb-1">
            NIM (Nomor Induk Mahasiswa)
          </label>
          <input
            id="input-nim"
            v-model="form.nim"
            type="text"
            required
            placeholder="Contoh: 220101001"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg font-mono focus:outline-none transition-colors',
              errors.nim
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errors.nim" class="text-xs text-rose-600 mt-1">
            {{ errors.nim[0] }}
          </p>
        </div>

        <div>
          <label for="input-nama" class="block text-sm font-medium text-slate-700 mb-1">
            Nama Lengkap
          </label>
          <input
            id="input-nama"
            v-model="form.nama"
            type="text"
            required
            placeholder="Contoh: Budi Santoso"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errors.nama
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errors.nama" class="text-xs text-rose-600 mt-1">
            {{ errors.nama[0] }}
          </p>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          :disabled="submitting"
          @click="tutupModal"
          class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:opacity-50"
        >
          Batal
        </button>
        <button
          type="submit"
          form="formMahasiswa"
          :disabled="submitting"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50"
        >
          <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
          <span>{{ submitting ? 'Menyimpan...' : 'Simpan' }}</span>
        </button>
      </template>
    </ModalDialog>

    <!-- Dialog Konfirmasi Hapus -->
    <ConfirmDialog
      :show="showConfirmHapus"
      title="Hapus Mahasiswa"
      :message="pesanKonfirmasiHapus"
      :loading="deleting"
      @confirm="eksekusiHapus"
      @cancel="showConfirmHapus = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api.js';
import { useToast } from '../useToast.js';
import PageHeader from '../components/PageHeader.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
import ModalDialog from '../components/ModalDialog.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { Plus, Search, Pencil, Trash2, Users, RotateCcw, Loader2 } from 'lucide-vue-next';

const toast = useToast();

const semuaMahasiswa = ref([]);
const loading = ref(true);
const errorLoad = ref('');
const kataKunci = ref('');

// State modal form
const showModal = ref(false);
const modEdit = ref(false);
const editId = ref(null);
const submitting = ref(false);
const form = ref({ nim: '', nama: '' });
const errors = ref({});

// State konfirmasi hapus
const showConfirmHapus = ref(false);
const mhsAkanDihapus = ref(null);
const deleting = ref(false);

const pesanKonfirmasiHapus = computed(() => {
  if (!mhsAkanDihapus.value) return '';
  return `Apakah Anda yakin ingin menghapus data mahasiswa ${mhsAkanDihapus.value.nama} (NIM: ${mhsAkanDihapus.value.nim})? Tindakan ini tidak dapat dibatalkan.`;
});

const daftarTampil = computed(() => {
  const q = kataKunci.value.trim().toLowerCase();
  if (!q) return semuaMahasiswa.value;
  return semuaMahasiswa.value.filter(
    (m) => m.nim.toLowerCase().includes(q) || m.nama.toLowerCase().includes(q)
  );
});

async function muatMahasiswa() {
  loading.value = true;
  errorLoad.value = '';
  try {
    const res = await api.get('/api/mahasiswa?per_page=1000');
    semuaMahasiswa.value = res.data || [];
  } catch (err) {
    errorLoad.value = err.message || 'Gagal memuat data mahasiswa.';
  } finally {
    loading.value = false;
  }
}

function bukaModalTambah() {
  modEdit.value = false;
  editId.value = null;
  form.value = { nim: '', nama: '' };
  errors.value = {};
  showModal.value = true;
}

function bukaModalEdit(mhs) {
  modEdit.value = true;
  editId.value = mhs.id;
  form.value = { nim: mhs.nim, nama: mhs.nama };
  errors.value = {};
  showModal.value = true;
}

function tutupModal() {
  showModal.value = false;
  errors.value = {};
}

async function simpanMahasiswa() {
  submitting.value = true;
  errors.value = {};

  try {
    if (modEdit.value) {
      await api.put(`/api/mahasiswa/${editId.value}`, form.value);
      toast.success('Data mahasiswa berhasil diperbarui.');
    } else {
      await api.post('/api/mahasiswa', form.value);
      toast.success('Mahasiswa baru berhasil ditambahkan.');
    }
    tutupModal();
    await muatMahasiswa();
  } catch (err) {
    if (err.errors) {
      errors.value = err.errors;
    }
    toast.error(err.message || 'Gagal menyimpan data mahasiswa.');
  } finally {
    submitting.value = false;
  }
}

function konfirmasiHapus(mhs) {
  mhsAkanDihapus.value = mhs;
  showConfirmHapus.value = true;
}

async function eksekusiHapus() {
  if (!mhsAkanDihapus.value) return;

  deleting.value = true;
  try {
    await api.delete(`/api/mahasiswa/${mhsAkanDihapus.value.id}`);
    toast.success('Data mahasiswa berhasil dihapus.');
    showConfirmHapus.value = false;
    mhsAkanDihapus.value = null;
    await muatMahasiswa();
  } catch (err) {
    toast.error(err.message || 'Gagal menghapus mahasiswa.');
    showConfirmHapus.value = false;
  } finally {
    deleting.value = false;
  }
}

onMounted(() => {
  muatMahasiswa();
});
</script>
