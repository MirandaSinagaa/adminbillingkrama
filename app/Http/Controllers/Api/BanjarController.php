<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banjar; // Import Model Banjar
use Illuminate\Http\Request;

class BanjarController extends Controller
{
    /**
     * Method 'index'
     * Mengambil SEMUA data banjar untuk dropdown di React.
     * Tidak menggunakan paginasi.
     */
    public function index()
    {
        // Ambil hanya kolom yang kita butuhkan, urutkan berdasarkan nama
        $banjars = Banjar::orderBy('nama_banjar', 'asc')
                         ->get(['banjar_id', 'nama_banjar']);
        
        // Langsung kembalikan sebagai JSON
        return response()->json($banjars);
    }
}