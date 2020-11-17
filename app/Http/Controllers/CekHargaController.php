<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Produk;
class CekHargaController extends Controller
{
    public function index(){
        return view('cek_harga/index');
    }

    public function listData(){

        $produk = Produk::where('unit',Auth::user()->unit)->get();
        
        $data = array();
        foreach($produk as $list){
          $row = array();
          $row[] = $list->kode_produk;
          $row[] = $list->nama_produk;
          $row[] = $list->stok;
          $row[] = $list->harga_jual_pabrik;
          $row[] = $list->harga_jual_insan;
          $data[] = $row;
        }
   
        $output = array("data" => $data);
        return response()->json($output);

    }
}
