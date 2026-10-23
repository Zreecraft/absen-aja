/**
 * status.js
 * Definisi status kehadiran tunggal untuk seluruh aplikasi.
 * Selalu memuat label teks, kelas warna badge, dan kelas tombol.
 * Hadir = emerald, Izin = sky, Sakit = amber, Alpa = rose.
 */

export const STATUS_LIST = ['hadir', 'izin', 'sakit', 'alpa'];

export const STATUS_CONFIG = {
  hadir: {
    key: 'hadir',
    label: 'Hadir',
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    dotClass: 'bg-emerald-500',
    buttonActiveClass: 'bg-emerald-600 text-white border-emerald-600 shadow-xs ring-2 ring-emerald-600/30',
    buttonInactiveClass: 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
    countBadgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  },
  izin: {
    key: 'izin',
    label: 'Izin',
    badgeClass: 'bg-sky-50 text-sky-700 border-sky-200',
    dotClass: 'bg-sky-500',
    buttonActiveClass: 'bg-sky-600 text-white border-sky-600 shadow-xs ring-2 ring-sky-600/30',
    buttonInactiveClass: 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
    countBadgeClass: 'bg-sky-100 text-sky-800 border-sky-200',
  },
  sakit: {
    key: 'sakit',
    label: 'Sakit',
    badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
    dotClass: 'bg-amber-500',
    buttonActiveClass: 'bg-amber-600 text-white border-amber-600 shadow-xs ring-2 ring-amber-600/30',
    buttonInactiveClass: 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
    countBadgeClass: 'bg-amber-100 text-amber-800 border-amber-200',
  },
  alpa: {
    key: 'alpa',
    label: 'Alpa',
    badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
    dotClass: 'bg-rose-500',
    buttonActiveClass: 'bg-rose-600 text-white border-rose-600 shadow-xs ring-2 ring-rose-600/30',
    buttonInactiveClass: 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
    countBadgeClass: 'bg-rose-100 text-rose-800 border-rose-200',
  },
};

export function getStatus(statusKey) {
  if (statusKey && STATUS_CONFIG[statusKey]) {
    return STATUS_CONFIG[statusKey];
  }
  return {
    key: statusKey || 'unknown',
    label: statusKey ? String(statusKey) : 'Belum diisi',
    badgeClass: 'bg-slate-100 text-slate-600 border-slate-200',
    dotClass: 'bg-slate-400',
    buttonActiveClass: 'bg-slate-600 text-white border-slate-600',
    buttonInactiveClass: 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
    countBadgeClass: 'bg-slate-100 text-slate-700 border-slate-200',
  };
}
