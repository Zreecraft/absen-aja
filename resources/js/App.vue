<template>
  <header class="header">
    <a href="/" class="header__nama-app">Absen Aja</a>
    <nav class="header__nav">
      <router-link to="/kelas" :class="{ aktif: $route.path.startsWith('/kelas') }">Kelas</router-link>
      <router-link to="/mahasiswa" :class="{ aktif: $route.path.startsWith('/mahasiswa') }">Mahasiswa</router-link>
    </nav>
  </header>

  <!-- Pesan global sukses/gagal yang muncul sebentar -->
  <div v-if="pesan.teks" class="pesan-global" :class="'pesan-global--' + pesan.tipe">
    {{ pesan.teks }}
  </div>

  <main class="konten">
    <router-view :key="$route.fullPath" @pesan="tampilPesan" />
  </main>
</template>

<script setup>
import { reactive, provide } from 'vue';

const pesan = reactive({ teks: '', tipe: '' });

let timer = null;

function tampilPesan({ teks, tipe = 'sukses' }) {
  clearTimeout(timer);
  pesan.teks = teks;
  pesan.tipe = tipe;
  // Pesan hilang otomatis setelah 4 detik
  timer = setTimeout(() => { pesan.teks = ''; }, 4000);
}

// Expose ke child lewat provide supaya child bisa memanggil tanpa prop drilling
provide('tampilPesan', tampilPesan);
</script>
