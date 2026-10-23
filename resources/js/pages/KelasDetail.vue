<template>
  <div>
    <!-- Loading State Awal -->
    <LoadingState v-if="loadingKelas" text="Memuat detail kelas..." />

    <!-- Error State Jika Kelas Tidak Ditemukan -->
    <div
      v-else-if="errorKelas"
      class="p-6 rounded-xl border border-rose-200 bg-rose-50 text-center text-rose-800"
    >
      <p class="font-medium text-sm mb-3">{{ errorKelas }}</p>
      <router-link
        to="/kelas"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-300 rounded-md hover:bg-rose-100"
      >
        <ArrowLeft class="w-3.5 h-3.5" />
        <span>Kembali ke Daftar Kelas</span>
      </router-link>
    </div>

    <!-- Konten Utama Kelas -->
    <template v-else-if="kelas">
      <!-- Breadcrumb / Tombol Kembali -->
      <div class="mb-3">
        <router-link
          to="/kelas"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali ke daftar kelas</span>
        </router-link>
      </div>

      <!-- Header Halaman -->
      <PageHeader
        :title="kelas.mata_kuliah"
        :description="`Dosen: ${kelas.dosen}`"
      >
        <template #badge>
          <span class="px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
            {{ kelas.nama_kelas }}
          </span>
        </template>
        <template #actions>
          <button
            v-if="tabAktif === 'pertemuan'"
            type="button"
            @click="bukaModalTambahPertemuan"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
          >
            <Plus class="w-4 h-4" />
            <span>Buat Pertemuan</span>
          </button>
        </template>
      </PageHeader>

      <!-- Baris Ringkasan (3 Kartu Statistik) -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 mb-1">
            <span class="text-xs font-medium">Jumlah Mahasiswa</span>
            <Users class="w-4 h-4 text-slate-400" />
          </div>
          <div class="text-2xl font-bold text-slate-900 tabular-nums">
            {{ kelas.jumlah_mahasiswa ?? kelas.mahasiswa_count ?? mahasiswaKelas.length ?? 0 }}
          </div>
          <p class="text-xs text-slate-400 mt-1">Mahasiswa terdaftar di kelas</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 mb-1">
            <span class="text-xs font-medium">Pertemuan Dicatat</span>
            <Calendar class="w-4 h-4 text-slate-400" />
          </div>
          <div class="text-2xl font-bold text-slate-900 tabular-nums">
            {{ jumlahPertemuanTercatat }} <span class="text-sm font-normal text-slate-400">dari {{ daftarPertemuan.length }}</span>
          </div>
          <p class="text-xs text-slate-400 mt-1">Presensi sudah diisi</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 mb-1">
            <span class="text-xs font-medium">Rata-rata Kehadiran</span>
            <BarChart2 class="w-4 h-4 text-slate-400" />
          </div>
          <div class="text-2xl font-bold text-slate-900 tabular-nums">
            {{ rataRataKehadiran }}%
          </div>
          <p class="text-xs text-slate-400 mt-1">Dari rekap pertemuan tercatat</p>
        </div>
      </div>

      <!-- Navigasi Tab -->
      <div class="border-b border-slate-200 mb-6 overflow-x-auto">
        <nav class="flex gap-2 min-w-max pb-px" aria-label="Tab Informasi Kelas">
          <button
            type="button"
            @click="gantiTab('pertemuan')"
            :class="[
              'inline-flex items-center gap-2 py-3 px-4 border-b-2 text-sm font-medium transition-colors',
              tabAktif === 'pertemuan'
                ? 'border-brand-600 text-brand-600 font-semibold'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
            ]"
          >
            <Calendar class="w-4 h-4" />
            <span>Pertemuan</span>
            <span
              class="ml-1 px-2 py-0.5 rounded-full text-xs"
              :class="tabAktif === 'pertemuan' ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-600'"
            >
              {{ daftarPertemuan.length }}
            </span>
          </button>

          <button
            type="button"
            @click="gantiTab('mahasiswa')"
            :class="[
              'inline-flex items-center gap-2 py-3 px-4 border-b-2 text-sm font-medium transition-colors',
              tabAktif === 'mahasiswa'
                ? 'border-brand-600 text-brand-600 font-semibold'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
            ]"
          >
            <Users class="w-4 h-4" />
            <span>Mahasiswa</span>
            <span
              class="ml-1 px-2 py-0.5 rounded-full text-xs"
              :class="tabAktif === 'mahasiswa' ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-600'"
            >
              {{ mahasiswaKelas.length }}
            </span>
          </button>

          <button
            type="button"
            @click="gantiTab('rekap')"
            :class="[
              'inline-flex items-center gap-2 py-3 px-4 border-b-2 text-sm font-medium transition-colors',
              tabAktif === 'rekap'
                ? 'border-brand-600 text-brand-600 font-semibold'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
            ]"
          >
            <BarChart2 class="w-4 h-4" />
            <span>Rekap Kehadiran</span>
          </button>
        </nav>
      </div>

      <!-- ============================================== -->
      <!-- TAB 1: PERTEMUAN -->
      <!-- ============================================== -->
      <div v-if="tabAktif === 'pertemuan'">
        <LoadingState v-if="loadingPertemuan" text="Memuat daftar pertemuan..." />

        <EmptyState
          v-else-if="daftarPertemuan.length === 0"
          title="Belum ada pertemuan"
          description="Buat pertemuan untuk mulai mencatat presensi mahasiswa pada kelas ini."
          action-text="Buat Pertemuan Pertama"
          :icon="Calendar"
          @action="bukaModalTambahPertemuan"
        />

        <div v-else class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
              <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th scope="col" class="py-3.5 px-4 w-24">Nomor</th>
                  <th scope="col" class="py-3.5 px-4">Tanggal Pertemuan</th>
                  <th scope="col" class="py-3.5 px-4">Status Presensi</th>
                  <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="p in daftarPertemuan"
                  :key="p.id"
                  class="hover:bg-slate-50/75 transition-colors"
                >
                  <td class="py-3.5 px-4 font-semibold text-slate-900 tabular-nums">
                    Ke-{{ p.nomor }}
                  </td>
                  <td class="py-3.5 px-4 font-medium text-slate-800">
                    {{ formatTanggal(p.tanggal) }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      v-if="Number(p.jumlah_tercatat ?? p.kehadiran_count ?? 0) > 0"
                      class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                      Sudah dicatat ({{ p.jumlah_tercatat ?? p.kehadiran_count }} mhs)
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                      Belum dicatat
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="inline-flex items-center gap-2">
                      <router-link
                        :to="`/kelas/${kelasId}/pertemuan/${p.id}`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors border border-brand-200"
                      >
                        <CheckSquare class="w-3.5 h-3.5" />
                        <span>Input Kehadiran</span>
                      </router-link>

                      <button
                        type="button"
                        @click="bukaModalEditPertemuan(p)"
                        class="p-1.5 text-slate-400 hover:text-brand-600 rounded-md hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                        title="Ubah Pertemuan"
                      >
                        <Pencil class="w-4 h-4" />
                      </button>

                      <button
                        type="button"
                        @click="konfirmasiHapusPertemuan(p)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                        title="Hapus Pertemuan"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 2: MAHASISWA -->
      <!-- ============================================== -->
      <div v-else-if="tabAktif === 'mahasiswa'" class="space-y-6">
        <!-- Form Pendaftaran Mahasiswa ke Kelas -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
          <h3 class="text-sm font-semibold text-slate-800 mb-3">
            Daftarkan Mahasiswa ke Kelas Ini
          </h3>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
              <select
                v-model="mahasiswaDipilih"
                class="block w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
              >
                <option :value="null" disabled>
                  -- Pilih mahasiswa yang belum terdaftar ({{ kandidatMahasiswa.length }} tersedia) --
                </option>
                <option
                  v-for="mhs in kandidatMahasiswa"
                  :key="mhs.id"
                  :value="mhs.id"
                >
                  {{ mhs.nim }} - {{ mhs.nama }}
                </option>
              </select>
            </div>
            <button
              type="button"
              :disabled="!mahasiswaDipilih || mendaftar"
              @click="daftarkanMahasiswa"
              class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50"
            >
              <Loader2 v-if="mendaftar" class="w-4 h-4 animate-spin" />
              <UserPlus v-else class="w-4 h-4" />
              <span>Daftarkan</span>
            </button>
          </div>
        </div>

        <!-- Daftar Mahasiswa Terdaftar -->
        <LoadingState v-if="loadingMahasiswa" text="Memuat daftar mahasiswa kelas..." />

        <EmptyState
          v-else-if="mahasiswaKelas.length === 0"
          title="Belum ada mahasiswa di kelas ini"
          description="Gunakan dropdown di atas untuk mendaftarkan mahasiswa ke dalam kelas."
          :icon="Users"
        />

        <div v-else class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
              <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th scope="col" class="py-3.5 px-4 w-44">NIM</th>
                  <th scope="col" class="py-3.5 px-4">Nama Mahasiswa</th>
                  <th scope="col" class="py-3.5 px-4 w-32 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="mhs in mahasiswaKelas"
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
                    <button
                      type="button"
                      @click="konfirmasiKeluarkanMahasiswa(mhs)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition-colors"
                      title="Keluarkan dari kelas"
                    >
                      <UserMinus class="w-3.5 h-3.5" />
                      <span>Keluarkan</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-500">
            Total {{ mahasiswaKelas.length }} mahasiswa terdaftar
          </div>
        </div>
      </div>

      <!-- ============================================== -->
      <!-- TAB 3: REKAP KEHADIRAN -->
      <!-- ============================================== -->
      <div v-else-if="tabAktif === 'rekap'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <p class="text-sm text-slate-600">
            Dihitung dari <strong class="text-slate-900">{{ dataRekap.pertemuan_tercatat }}</strong> pertemuan yang sudah dicatat.
          </p>
          <button
            type="button"
            @click="muatRekap"
            class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-800 self-start"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            <span>Segarkan Rekap</span>
          </button>
        </div>

        <LoadingState v-if="loadingRekap" text="Menghitung rekap kehadiran..." />

        <EmptyState
          v-else-if="!dataRekap.rekap || dataRekap.rekap.length === 0"
          title="Belum ada data rekap"
          description="Data rekap akan otomatis dihitung setelah mahasiswa didaftarkan dan presensi pertemuan dicatat."
          :icon="BarChart2"
        />

        <div v-else class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
              <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th scope="col" class="py-3.5 px-4 w-36">NIM</th>
                  <th scope="col" class="py-3.5 px-4">Nama</th>
                  <th scope="col" class="py-3.5 px-3 text-center w-20">
                    <span class="inline-flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                      <span>Hadir</span>
                    </span>
                  </th>
                  <th scope="col" class="py-3.5 px-3 text-center w-20">
                    <span class="inline-flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                      <span>Izin</span>
                    </span>
                  </th>
                  <th scope="col" class="py-3.5 px-3 text-center w-20">
                    <span class="inline-flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                      <span>Sakit</span>
                    </span>
                  </th>
                  <th scope="col" class="py-3.5 px-3 text-center w-20">
                    <span class="inline-flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                      <span>Alpa</span>
                    </span>
                  </th>
                  <th scope="col" class="py-3.5 px-4 w-48 text-right">Persentase</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="r in dataRekap.rekap"
                  :key="r.mahasiswa_id"
                  class="hover:bg-slate-50/75 transition-colors"
                >
                  <td class="py-3.5 px-4 font-mono font-medium text-slate-900 tabular-nums">
                    {{ r.nim }}
                  </td>
                  <td class="py-3.5 px-4 font-medium text-slate-800">
                    {{ r.nama }}
                  </td>
                  <td class="py-3.5 px-3 text-center tabular-nums font-semibold text-emerald-700">
                    {{ r.hadir }}
                  </td>
                  <td class="py-3.5 px-3 text-center tabular-nums font-semibold text-sky-700">
                    {{ r.izin }}
                  </td>
                  <td class="py-3.5 px-3 text-center tabular-nums font-semibold text-amber-700">
                    {{ r.sakit }}
                  </td>
                  <td class="py-3.5 px-3 text-center tabular-nums font-semibold text-rose-700">
                    {{ r.alpa }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-3">
                      <!-- Bar Progress Tipis -->
                      <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden hidden sm:block">
                        <div
                          :class="['h-2 rounded-full transition-all', warnaPersentase(r.persentase).bar]"
                          :style="{ width: `${Math.min(100, Math.max(0, r.persentase))}%` }"
                        ></div>
                      </div>
                      <span :class="['font-semibold tabular-nums text-sm', warnaPersentase(r.persentase).text]">
                        {{ Number(r.persentase).toFixed(1) }}%
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Legenda di Bawah Tabel -->
          <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
              <strong>Catatan aturan presensi:</strong> Izin dan sakit tidak dihitung sebagai hadir.
            </div>
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>&ge; 75% Baik</span>
              </span>
              <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>50 - 74% Cukup</span>
              </span>
              <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>&lt; 50% Kurang</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal Form Tambah / Ubah Pertemuan -->
    <ModalDialog
      :show="showModalPertemuan"
      :title="modEditPertemuan ? 'Ubah Pertemuan' : 'Buat Pertemuan Baru'"
      @close="showModalPertemuan = false"
    >
      <form id="formPertemuan" @submit.prevent="simpanPertemuan" class="space-y-4">
        <div>
          <label for="input-tanggal-pertemuan" class="block text-sm font-medium text-slate-700 mb-1">
            Tanggal Pertemuan
          </label>
          <input
            id="input-tanggal-pertemuan"
            v-model="formPertemuan.tanggal"
            type="date"
            required
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errorsPertemuan.tanggal
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p v-if="errorsPertemuan.tanggal" class="text-xs text-rose-600 mt-1">
            {{ errorsPertemuan.tanggal[0] }}
          </p>
        </div>

        <div>
          <label for="input-nomor-pertemuan" class="block text-sm font-medium text-slate-700 mb-1">
            Nomor Pertemuan (Opsional)
          </label>
          <input
            id="input-nomor-pertemuan"
            v-model="formPertemuan.nomor"
            type="number"
            min="1"
            placeholder="Kosongkan untuk otomatis nomor berikutnya"
            :class="[
              'block w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none transition-colors',
              errorsPertemuan.nomor
                ? 'border-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                : 'border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500'
            ]"
          />
          <p class="text-xs text-slate-500 mt-1">
            Bila dikosongkan, sistem akan memberikan nomor pertemuan selanjutnya secara otomatis.
          </p>
          <p v-if="errorsPertemuan.nomor" class="text-xs text-rose-600 mt-1">
            {{ errorsPertemuan.nomor[0] }}
          </p>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          :disabled="submittingPertemuan"
          @click="showModalPertemuan = false"
          class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:opacity-50"
        >
          Batal
        </button>
        <button
          type="submit"
          form="formPertemuan"
          :disabled="submittingPertemuan"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50"
        >
          <Loader2 v-if="submittingPertemuan" class="w-4 h-4 animate-spin" />
          <span>{{ submittingPertemuan ? 'Menyimpan...' : 'Simpan' }}</span>
        </button>
      </template>
    </ModalDialog>

    <!-- Dialog Konfirmasi Hapus Pertemuan -->
    <ConfirmDialog
      :show="showConfirmHapusPertemuan"
      title="Hapus Pertemuan"
      :message="pesanHapusPertemuan"
      :loading="deletingPertemuan"
      @confirm="eksekusiHapusPertemuan"
      @cancel="showConfirmHapusPertemuan = false"
    />

    <!-- Dialog Konfirmasi Keluarkan Mahasiswa -->
    <ConfirmDialog
      :show="showConfirmKeluarkan"
      title="Keluarkan Mahasiswa dari Kelas"
      :message="pesanKeluarkanMahasiswa"
      :loading="removingMahasiswa"
      @confirm="eksekusiKeluarkanMahasiswa"
      @cancel="showConfirmKeluarkan = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../api.js';
import { useToast } from '../useToast.js';
import PageHeader from '../components/PageHeader.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
import ModalDialog from '../components/ModalDialog.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import {
  Calendar,
  Users,
  BarChart2,
  ArrowLeft,
  Plus,
  Pencil,
  Trash2,
  CheckSquare,
  UserPlus,
  UserMinus,
  RotateCcw,
  Loader2
} from 'lucide-vue-next';

// Ambang batas persentase kehadiran
const AMBANG_PERSENTASE_TINGGI = 75;
const AMBANG_PERSENTASE_SEDANG = 50;

function warnaPersentase(persen) {
  const p = Number(persen) || 0;
  if (p >= AMBANG_PERSENTASE_TINGGI) {
    return { bar: 'bg-emerald-500', text: 'text-emerald-700' };
  }
  if (p >= AMBANG_PERSENTASE_SEDANG) {
    return { bar: 'bg-amber-500', text: 'text-amber-700' };
  }
  return { bar: 'bg-rose-500', text: 'text-rose-700' };
}

const route = useRoute();
const router = useRouter();
const toast = useToast();

const kelasId = computed(() => route.params.id);

// Data kelas
const kelas = ref(null);
const loadingKelas = ref(true);
const errorKelas = ref('');

// Tab aktif (sinkron dengan query string ?tab=...)
const tabAktif = ref(route.query.tab === 'mahasiswa' || route.query.tab === 'rekap' ? route.query.tab : 'pertemuan');

// Data Tab Pertemuan
const daftarPertemuan = ref([]);
const loadingPertemuan = ref(false);
const showModalPertemuan = ref(false);
const modEditPertemuan = ref(false);
const editPertemuanId = ref(null);
const submittingPertemuan = ref(false);
const formPertemuan = ref({ tanggal: '', nomor: '' });
const errorsPertemuan = ref({});
const showConfirmHapusPertemuan = ref(false);
const pertemuanAkanDihapus = ref(null);
const deletingPertemuan = ref(false);

// Data Tab Mahasiswa
const mahasiswaKelas = ref([]);
const semuaMasterMahasiswa = ref([]);
const loadingMahasiswa = ref(false);
const mahasiswaDipilih = ref(null);
const mendaftar = ref(false);
const showConfirmKeluarkan = ref(false);
const mahasiswaAkanDikeluarkan = ref(null);
const removingMahasiswa = ref(false);

// Data Tab Rekap
const dataRekap = ref({ pertemuan_tercatat: 0, rekap: [] });
const loadingRekap = ref(false);

// Hitungan statistik
const jumlahPertemuanTercatat = computed(() => {
  return daftarPertemuan.value.filter((p) => Number(p.jumlah_tercatat ?? p.kehadiran_count ?? 0) > 0).length;
});

const rataRataKehadiran = computed(() => {
  const list = dataRekap.value.rekap || [];
  if (!list.length) return '0.0';
  const total = list.reduce((acc, curr) => acc + (Number(curr.persentase) || 0), 0);
  return (total / list.length).toFixed(1);
});

const kandidatMahasiswa = computed(() => {
  const idsTerdaftar = new Set(mahasiswaKelas.value.map((m) => m.id));
  return semuaMasterMahasiswa.value.filter((m) => !idsTerdaftar.has(m.id));
});

const pesanHapusPertemuan = computed(() => {
  if (!pertemuanAkanDihapus.value) return '';
  return `Apakah Anda yakin ingin menghapus Pertemuan ke-${pertemuanAkanDihapus.value.nomor}? Seluruh data absensi pada pertemuan ini akan ikut terhapus.`;
});

const pesanKeluarkanMahasiswa = computed(() => {
  if (!mahasiswaAkanDikeluarkan.value) return '';
  return `Apakah Anda yakin ingin mengeluarkan ${mahasiswaAkanDikeluarkan.value.nama} dari kelas ini?`;
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

function getTanggalHariIni() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

function gantiTab(tabKey) {
  tabAktif.value = tabKey;
  router.replace({ query: { ...route.query, tab: tabKey } });
  if (tabKey === 'pertemuan') muatPertemuan();
  if (tabKey === 'mahasiswa') muatMahasiswaKelas();
  if (tabKey === 'rekap') muatRekap();
}

async function muatKelas() {
  loadingKelas.value = true;
  errorKelas.value = '';
  try {
    const res = await api.get(`/api/kelas/${kelasId.value}`);
    kelas.value = res.data || res;
  } catch (err) {
    errorKelas.value = err.message || 'Kelas tidak ditemukan.';
  } finally {
    loadingKelas.value = false;
  }
}

async function muatPertemuan() {
  loadingPertemuan.value = true;
  try {
    const res = await api.get(`/api/kelas/${kelasId.value}/pertemuan`);
    daftarPertemuan.value = res.data || [];
  } catch (err) {
    toast.error('Gagal memuat pertemuan: ' + err.message);
  } finally {
    loadingPertemuan.value = false;
  }
}

async function muatMahasiswaKelas() {
  loadingMahasiswa.value = true;
  try {
    const [resKelas, resMaster] = await Promise.all([
      api.get(`/api/kelas/${kelasId.value}/mahasiswa`),
      api.get('/api/mahasiswa?per_page=1000'),
    ]);
    mahasiswaKelas.value = resKelas.data || [];
    semuaMasterMahasiswa.value = resMaster.data || [];
  } catch (err) {
    toast.error('Gagal memuat mahasiswa: ' + err.message);
  } finally {
    loadingMahasiswa.value = false;
  }
}

async function muatRekap() {
  loadingRekap.value = true;
  try {
    const res = await api.get(`/api/kelas/${kelasId.value}/rekap`);
    dataRekap.value = res.data || res;
  } catch (err) {
    toast.error('Gagal memuat rekap: ' + err.message);
  } finally {
    loadingRekap.value = false;
  }
}

// Handler Pertemuan
function bukaModalTambahPertemuan() {
  modEditPertemuan.value = false;
  editPertemuanId.value = null;
  formPertemuan.value = { tanggal: getTanggalHariIni(), nomor: '' };
  errorsPertemuan.value = {};
  showModalPertemuan.value = true;
}

function bukaModalEditPertemuan(p) {
  modEditPertemuan.value = true;
  editPertemuanId.value = p.id;
  formPertemuan.value = { tanggal: p.tanggal, nomor: p.nomor };
  errorsPertemuan.value = {};
  showModalPertemuan.value = true;
}

async function simpanPertemuan() {
  submittingPertemuan.value = true;
  errorsPertemuan.value = {};

  const payload = { tanggal: formPertemuan.value.tanggal };
  if (formPertemuan.value.nomor) {
    payload.nomor = Number(formPertemuan.value.nomor);
  }

  try {
    if (modEditPertemuan.value) {
      await api.put(`/api/pertemuan/${editPertemuanId.value}`, payload);
      toast.success('Pertemuan berhasil diperbarui.');
    } else {
      await api.post(`/api/kelas/${kelasId.value}/pertemuan`, payload);
      toast.success('Pertemuan berhasil dibuat.');
    }
    showModalPertemuan.value = false;
    await muatPertemuan();
    muatRekap();
  } catch (err) {
    if (err.errors) {
      errorsPertemuan.value = err.errors;
    }
    toast.error(err.message || 'Gagal menyimpan pertemuan.');
  } finally {
    submittingPertemuan.value = false;
  }
}

function konfirmasiHapusPertemuan(p) {
  pertemuanAkanDihapus.value = p;
  showConfirmHapusPertemuan.value = true;
}

async function eksekusiHapusPertemuan() {
  if (!pertemuanAkanDihapus.value) return;

  deletingPertemuan.value = true;
  try {
    await api.delete(`/api/pertemuan/${pertemuanAkanDihapus.value.id}`);
    toast.success('Pertemuan berhasil dihapus.');
    showConfirmHapusPertemuan.value = false;
    pertemuanAkanDihapus.value = null;
    await muatPertemuan();
    muatRekap();
  } catch (err) {
    toast.error(err.message || 'Gagal menghapus pertemuan.');
    showConfirmHapusPertemuan.value = false;
  } finally {
    deletingPertemuan.value = false;
  }
}

// Handler Pendaftaran Mahasiswa
async function daftarkanMahasiswa() {
  if (!mahasiswaDipilih.value) return;

  mendaftar.value = true;
  try {
    await api.post(`/api/kelas/${kelasId.value}/mahasiswa`, {
      mahasiswa_id: mahasiswaDipilih.value,
    });
    toast.success('Mahasiswa berhasil didaftarkan ke kelas.');
    mahasiswaDipilih.value = null;
    await muatMahasiswaKelas();
    muatRekap();
  } catch (err) {
    toast.error(err.message || 'Gagal mendaftarkan mahasiswa.');
  } finally {
    mendaftar.value = false;
  }
}

function konfirmasiKeluarkanMahasiswa(mhs) {
  mahasiswaAkanDikeluarkan.value = mhs;
  showConfirmKeluarkan.value = true;
}

async function eksekusiKeluarkanMahasiswa() {
  if (!mahasiswaAkanDikeluarkan.value) return;

  removingMahasiswa.value = true;
  try {
    await api.delete(`/api/kelas/${kelasId.value}/mahasiswa/${mahasiswaAkanDikeluarkan.value.id}`);
    toast.success('Mahasiswa berhasil dikeluarkan dari kelas.');
    showConfirmKeluarkan.value = false;
    mahasiswaAkanDikeluarkan.value = null;
    await muatMahasiswaKelas();
    muatRekap();
  } catch (err) {
    toast.error(err.message || 'Gagal mengeluarkan mahasiswa.');
    showConfirmKeluarkan.value = false;
  } finally {
    removingMahasiswa.value = false;
  }
}

watch(
  () => route.query.tab,
  (newTab) => {
    if (newTab && ['pertemuan', 'mahasiswa', 'rekap'].includes(newTab)) {
      tabAktif.value = newTab;
    }
  }
);

watch(
  () => route.params.id,
  async (newId) => {
    if (newId) {
      await muatKelas();
      muatPertemuan();
      muatMahasiswaKelas();
      muatRekap();
    }
  }
);

onMounted(async () => {
  await muatKelas();
  muatPertemuan();
  muatMahasiswaKelas();
  muatRekap();
});
</script>
