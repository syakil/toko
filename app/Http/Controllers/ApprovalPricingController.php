<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\PembelianTemporary;
use DB;
use Auth;
use App\ProdukDetail;

class ApprovalPricingController extends Controller{

    public function index(){

        return view('approve_pricing.index');

    }

    public function listData(){

        $produk_detail = ProdukDetail::where('status',3)->get();

        $no = 1;
        $data = array();
        
        foreach($produk_detail as $list){
            
            $row = array();
            
            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
            $harga_pasar = array($produk->harga_indo,$produk->harga_alfa,$produk->harga_grosir,$produk->harga_olshop);

            $row [] = $no++;
            $row [] = $list->kode_produk;
            $row [] = $list->nama_produk ;
            $row [] = $list->harga_beli;
            $row [] = number_format($produk->harga_jual);
            $row [] = number_format($list->harga_jual_insan);
            $row [] = number_format(min($harga_pasar));
            $row[] = "<div class='btn-group'>
            <a href='".route('approve_pricing.approve',$list->id_produk_detail)."' class='btn btn-success btn-sm'>Approve</a>
            <a href='".route('approve_pricing.reject',$list->id_produk_detail)."' class='btn btn-danger btn-sm'>Tolak</a>
            </div>";
            $data [] = $row; 

        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function approve($id){

        ProdukDetail::where('id_produk_detail',$id)->update(['status'=>2]);

        return back()->with(['success' => 'Harga Berhasil Di Setujui !']);

    }

    public function reject($id){

        $produk = ProdukDetail::where('id_produk_detail',$id)->first();
        $produk->status = 2;
        $produk->update();

        $pembelian_tempo = PembelianTemporary::where('id_pembelian',$produk->no_invoice)->update(['status'=>2]);
        
        return back()->with(['success' => 'Harga Berhasil Di Tolak !']);

    }
}
