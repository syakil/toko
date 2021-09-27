<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportStokOpnameController extends Controller
{
    public function index(){
        
        return view('report_so/index');

    }


    public function listData(){

        $data_so = DB::select(
        'SELECT * FROM produk_selisih
         LEFT JOIN 
         produk 
         ON 
         produk.kode_produk = produk_selisih.kode_produk
         LEFT JOIN 
         branch 
         ON 
         produk_selisih.unit = branch.kode_toko
         WHERE
         produk.unit = '.Auth::user()->unit.'
         AND
         produk_selisih.unit IN (SELECT kode_toko FROM branch WHERE kode_gudang = '.Auth::user()->unit.')'
        );


        $data = array();
        foreach ($data_so as $key => $value) {
            
            $row = array();
            $row[] = tanggal_indonesia($value->tanggal_so);
            $row[] = $value->nama_toko;
            $row[] = $value->kode_produk;
            $row[] = $value->nama_produk;
            $row[] = $value->jumlah;
            $row[] = $value->harga_beli;
            $row[] = $value->harga_jual;

            if ($value->ket == 'lebih') {
                
                $row[] = '<span class="label label-warning">Fisik '.$value->ket.'</span>';
            
            }else{

                $row[] = '<span class="label label-danger">Fisik '.$value->ket.'</span>';
            }


            $data[] = $row;
        
        }

        $output = array("data" => $data);
        return response()->json($output);
    }
}
