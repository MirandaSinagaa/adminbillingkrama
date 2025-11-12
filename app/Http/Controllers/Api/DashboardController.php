<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Krama;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Import DB

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik untuk dashboard admin.
     * (Method ini sudah ada)
     */
    public function getStats(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // 1. Total Krama (Warga) Terdaftar
        $total_krama = Krama::count();

        // 2. Total Tagihan Bulan Ini (Rupiah)
        $tagihan_bulan_ini = Tagihan::whereYear('tanggal', $currentYear)
                            ->whereMonth('tanggal', $currentMonth)
                            ->sum(DB::raw('iuran + dedosan + peturuhan'));

        // 3. Tagihan Lunas Bulan Ini (Jumlah transaksi)
        $lunas_bulan_ini = Pembayaran::whereYear('tgl_bayar', $currentYear)
                            ->whereMonth('tgl_bayar', $currentMonth)
                            ->where('status', 'selesai')
                            ->count();

        // 4. Total Tagihan Belum Lunas (Semua waktu)
        $belum_lunas_total = Tagihan::whereDoesntHave('pembayaran')
                               ->count();

        return response()->json([
            'total_krama' => $total_krama,
            'tagihan_bulan_ini' => (float) $tagihan_bulan_ini,
            'lunas_bulan_ini' => $lunas_bulan_ini,
            'belum_lunas_total' => $belum_lunas_total,
        ]);
    }

    /**
     * ==========================================================
     * (METHOD BARU)
     * Mengambil data untuk chart tagihan 6 bulan terakhir.
     * ==========================================================
     */
    public function getChartData(Request $request)
    {
        // 1. Ambil data tagihan 6 bulan terakhir, group per bulan
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        
        $tagihanData = Tagihan::where('tanggal', '>=', $startDate)
            ->select(
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('SUM(iuran + dedosan + peturuhan) as total_tagihan')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 2. Siapkan array untuk 6 bulan (termasuk bulan kosong)
        $labels = [];
        $data = [];
        $currentDate = Carbon::now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            // Label (e.g., "Nov 2025")
            $labels[] = $currentDate->format('M Y');
            
            // Cari data tagihan untuk bulan ini
            $foundData = $tagihanData->first(function($item) use ($currentDate) {
                return $item->year == $currentDate->year && $item->month == $currentDate->month;
            });

            // Isi data, 0 jika tidak ada
            $data[] = $foundData ? (float)$foundData->total_tagihan : 0;
            
            // Lanjut ke bulan berikutnya
            $currentDate->addMonth();
        }

        // 3. Kembalikan data
        return response()->json([
            'labels' => $labels, // e.g., ["Jun 2025", "Jul 2025", ...]
            'data'   => $data,   // e.g., [500000, 750000, ...]
        ]);
    }
}