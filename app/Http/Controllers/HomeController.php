<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Setting;
use App\Kategori;
use App\Produk;
use App\Supplier;
use App\Member;
use App\Penjualan;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = Setting::find(1);

      $awal = date('Y-m-d', mktime(0,0,0, date('m'), 1, date('Y')));
      $akhir = date('Y-m-d');

      $tanggal = $awal;
      $data_tanggal = array();
      $data_pendapatan = array();

      while(strtotime($tanggal) <= strtotime($akhir)){ 
        $data_tanggal[] = (int)substr($tanggal,8,2);
        
        $pendapatan = Penjualan::where('created_at', 'LIKE', "$tanggal%")->sum('bayar');
        $data_pendapatan[] = (int) $pendapatan;

        $tanggal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal)));
      }
        
        $kategori = Kategori::count();
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();

        if(Auth::user()->level == 1) return view('home.admin', compact('kategori', 'produk', 'supplier', 'member', 'awal', 'akhir', 'data_pendapatan', 'data_tanggal'));
        elseif(Auth::user()->level == 3) return view('home.po', compact('kategori', 'produk', 'supplier'));
        elseif(Auth::user()->level == 2) return view('home.kasir', compact('kategori', 'produk', 'supplier','setting'));
        elseif(Auth::user()->level == 4) return view('home.gudang', compact('kategori', 'produk', 'supplier','setting'));
        elseif(Auth::user()->level == 5) return view('home.spvs', compact('produk', 'setting'));
        elseif(Auth::user()->level == 6) return view('home.price', compact('kategori', 'produk','setting'));
        else return view('/login', compact('setting'));        
        

    }
}
