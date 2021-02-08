<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\KartuStok;
use DB;
use Auth;


class KartuStokController extends Controller{

    public function index(){

        return view('kartu_stok.index');

    }

    public function listData(){

        $kartu = DB::table('kartu_stok')->select(DB::raw('
            produk.kode_produk,
            nama_produk,
            SUM(IF(status="stok_awal",masuk,0)) AS stok_awal,
            SUM(IF(status="pembelian",masuk,0)) AS pembelian,
            SUM(IF(status="terima_barang",masuk,0)) AS terima_barang_retur,
            SUM(IF(status="kirim_barang",keluar,0)) AS kirim_barang,
            SUM(IF(status="kirim_barang_retur",keluar,0)) AS kirim_barang_retur
        '))
        ->groupBy('kartu_stok.kode_produk')
        ->leftJoin('produk','produk.kode_produk','kartu_stok.kode_produk')
        ->where('kartu_stok.unit',Auth::user()->unit)
        ->where('produk.unit',Auth::user()->unit)
        ->get();

        
        $no = 0;
        $data = array();
        
        foreach($kartu as $list){
            
            $stok_akhir = $list->stok_awal + $list->pembelian + $list->terima_barang_retur - $list->kirim_barang - $list->kirim_barang_retur;

            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->stok_awal;
            $row[] = $list->pembelian;
            $row[] = $list->terima_barang_retur;
            $row[] = $list->kirim_barang;
            $row[] = $list->kirim_barang_retur;
            $row[] = $stok_akhir;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    

    }

}
