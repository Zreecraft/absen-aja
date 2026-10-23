<template>
  <div>
    <!-- Header Halaman -->
    <PageHeader
      title="Daftar Kelas"
      description="Kelola kelas perkuliahan, peserta mahasiswa, pertemuan, dan presensi."
    >
      <template #actions>
        <button
          type="button"
          @click="bukaModalTambah"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 transition-colors"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Kelas</span>
        </button>
      </template>
    </PageHeader>

    <!-- State Loading -->
    <LoadingState v-if="loading" text="Memuat daftar kelas..." />

    <!-- State Error Loading -->
    <div
      v-else-if="errorLoad"
      class="p-6 rounded-xl border border-rose-200 bg-rose-50 text-center text-rose-800"
    >
      <p class="font-medium text-sm mb-3">{{ errorLoad }}</p>
      <button
        type="button"
        @click="muatKelas"
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
        v-if="daftarKelas.length === 0"
        title="Belum ada kelas perkuliahan"
        description="Buat kelas perkuliahan pertama Anda untuk mulai mengelola presensi mahasiswa."
        action-text="Tambah Kelas"
        :icon="GraduationCap"
        @action="bukaModalTambah"
      />

      <!-- Grid Kartu Kelas -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="k in daftarKelas"
          :key="k.id"
          @click="bukaDetail(k.id)"
          class="group bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-brand-300 hover:shadow-sm transition-all cursor-pointer flex flex-col justify-between"
        >
          <div>
            <!-- Judul Mata Kuliah & Badge Kelas -->
            <div class="flex items-start justify-between gap-3 mb-2">
              <h2 class="text-base font-semibold text-slate-900 group-hover:text-brand-600 transition-colors leading-snug line-clamp-2">
                {{ k.mata_kuliah }}
              </h2>
              <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                {{ k.nama_kelas }}
              </span>
            </div>

            <!-- Info Dosen & Peserta -->
            <div class="space-y-1.5 my-4 text-xs text-slate-600">
              <div class="flex items-center gap-2">
                <User class="w-4 h-4 text-slate-400 shrink-0" />
                <span class="truncate">Dosen: <strong class="text-slate-700 font-medium">{{ k.dosen }}</strong></span>
              </div>
              <div class="flex items-center gap-2">
                <Users class="w-4 h-4 text-slate-400 shrink-0" />
                <span>{{ k.mahasiswa_count || 0 }} mahasiswa terdaftar</span>
              </div>
            </div>
          </div>

          <!-- Bagian Bawah Kartu: Tombol Aksi -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-700 group-hover:text-brand-800">
              <span>Buka Kelas</span>
              <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
            </span>

            <div class="flex items-center gap-1" @click.stop>
              <button
                type="button"
                @click="bukaModalEdit(k)"
                class="p-1.5 text-slate-400 hover:text-brand-600 rounded-md hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                title="Ubah Kelas"
              >
                <Pencil class="w-4 h-4" />
              </button>
              <button
                type="button"
                @click="konfirmasiHapus(k)"
                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                title="Hapus Kelas"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Tambah / Ubah Kelas -->
    <ModalDialog
      :show="showModal"
      :title="modEdit ? 'Ubah Data Kelas' : 'Tambah Kelas Baru'"
      @close="tutupModal"
    >
      <form id="formKelas" @submit.prevent="simpanKelas" class="space-y-4">
        <div>
          <label for="input-mata-kuliah" class="block text-sm font-medium text-slate-700 mb-1">
            Mata Kuliah
          </label>
          <input
            id="input-mata-kuliah"
            v-model="form.mata_kuliah"
            type="text"
            required
            placeholder="Contoh: Pemrograman Web Lanjut"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errors.mata_kuliah
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errors.mata_kuliah" class="text-xs text-rose-600 mt-1">
            {{ errors.mata_kuliah[0] }}
          </p>
        </div>

        <div>
          <label for="input-nama-kelas" class="block text-sm font-medium text-slate-700 mb-1">
            Nama Kelas
          </label>
          <input
            id="input-nama-kelas"
            v-model="form.nama_kelas"
            type="text"
            required
            placeholder="Contoh: TI-3A atau Reguler Pagi"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errors.nama_kelas
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errors.nama_kelas" class="text-xs text-rose-600 mt-1">
            {{ errors.nama_kelas[0] }}
          </p>
        </div>

        <div>
          <label for="input-dosen" class="block text-sm font-medium text-slate-700 mb-1">
            Dosen Pengampu
          </label>
          <input
            id="input-dosen"
            v-model="form.dosen"
            type="text"
            required
            placeholder="Contoh: Dr. Budi Setiawan, M.Kom"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errors.dosen
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errors.dosen" class="text-xs text-rose-600 mt-1">
            {{ errors.dosen[0] }}
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
          form="formKelas"
          :disabled="submitting"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50"
        >
          <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
          <span>{{ submitting ? 'Menyimpan...' : 'Simpan' }}</span>
        </button>
      </template>
    </ModalDialog>

    <!-- Dialog Konfirmasi Hapus Kelas -->
    <ConfirmDialog
      :show="showConfirmHapus"
      title="Hapus Kelas Perkuliahan"
      :message="pesanKonfirmasiHapus"
      :loading="deleting"
      @confirm="eksekusiHapus"
      @cancel="showConfirmHapus = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../api.js';
import { useToast } from '../useToast.js';
import PageHeader from '../components/PageHeader.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
import ModalDialog from '../components/ModalDialog.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { Plus, GraduationCap, User, Users, Pencil, Trash2, ArrowRight, RotateCcw, Loader2 } from 'lucide-vue-next';

const router = useRouter();
const toast = useToast();

const daftarKelas = ref([]);
const loading = ref(true);
const errorLoad = ref('');

// State modal form
const showModal = ref(false);
const modEdit = ref(false);
const editId = ref(null);
const submitting = ref(false);
const form = ref({ mata_kuliah: '', nama_kelas: '', dosen: '' });
const errors = ref({});

// State konfirmasi hapus
const showConfirmHapus = ref(false);
const kelasAkanDihapus = ref(null);
const deleting = ref(false);

const pesanKonfirmasiHapus = computed(() => {
  if (!kelasAkanDihapus.value) return '';
  return `Apakah Anda yakin ingin menghapus kelas "${kelasAkanDihapus.value.mata_kuliah} - ${kelasAkanDihapus.value.nama_kelas}"? Peringatan: Seluruh data pertemuan dan riwayat kehadiran pada kelas ini akan ikut terhapus secara permanen.`;
});

function bukaDetail(id) {
  router.push(`/kelas/${id}`);
}

async function muatKelas() {
  loading.value = true;
  errorLoad.value = '';
  try {
    const res = await api.get('/api/kelas?per_page=100');
    daftarKelas.value = res.data || [];
  } catch (err) {
    errorLoad.value = err.message || 'Gagal memuat daftar kelas.';
  } finally {
    loading.value = false;
  }
}

function bukaModalTambah() {
  modEdit.value = false;
  editId.value = null;
  form.value = { mata_kuliah: '', nama_kelas: '', dosen: '' };
  errors.value = {};
  showModal.value = true;
}

function bukaModalEdit(k) {
  modEdit.value = true;
  editId.value = k.id;
  form.value = { mata_kuliah: k.mata_kuliah, nama_kelas: k.nama_kelas, dosen: k.dosen };
  errors.value = {};
  showModal.value = true;
}

function tutupModal() {
  showModal.value = false;
  errors.value = {};
}

async function simpanKelas() {
  submitting.value = true;
  errors.value = {};

  try {
    if (modEdit.value) {
      await api.put(`/api/kelas/${editId.value}`, form.value);
      toast.success('Data kelas berhasil diperbarui.');
    } else {
      await api.post('/api/kelas', form.value);
      toast.success('Kelas baru berhasil dibuat.');
    }
    tutupModal();
    await muatKelas();
  } catch (err) {
    if (err.errors) {
      errors.value = err.errors;
    }
    toast.error(err.message || 'Gagal menyimpan kelas.');
  } finally {
    submitting.value = false;
  }
}

function konfirmasiHapus(k) {
  kelasAkanDihapus.value = k;
  showConfirmHapus.value = true;
}

async function eksekusiHapus() {
  if (!kelasAkanDihapus.value) return;

  deleting.value = true;
  try {
    await api.delete(`/api/kelas/${kelasAkanDihapus.value.id}`);
    toast.success('Kelas berhasil dihapus.');
    showConfirmHapus.value = false;
    kelasAkanDihapus.value = null;
    await muatKelas();
  } catch (err) {
    toast.error(err.message || 'Gagal menghapus kelas.');
    showConfirmHapus.value = false;
  } finally {
    deleting.value = false;
  }
}

onMounted(() => {
  muatKelas();
});
</script>
