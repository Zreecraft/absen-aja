<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case Hadir = 'hadir';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alpa = 'alpa';
}
