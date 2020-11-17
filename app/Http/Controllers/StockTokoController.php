<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\ProdukDetail;
use App\Produk;

class StockTokoController extends Controller
{
    public function index(){
        return view('toko/stock');
    }


    public function listData(){
        $produk = Produk::where('unit', '=',  Auth::user()->unit)
        // ->take(100)                
        ->get();
        $no = 0;
        $data = array();
        foreach($produk as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->kode_produk;
        //$row[] = '<a href="'. route('stockToko.detail',$list->kode_produk) .'">'.$list->nama_produk.'</a>';
$row[] = $list->nama_produk;       
$row[] = $list->stok;
        $data[] = $row;
        }
        //   dd($data);
        $output = array("data" => $data);
        return response()->json($output);
    }

    public function detail($id){
        $produk = ProdukDetail::where('kode_produk',$id)
                        ->where('unit', '=',  Auth::user()->unit)
                        ->get();
                        
        $nama = Produk::where('kode_produk',$id)->first();
        return view('toko/detail_stock',['produk'=>$produk,'nama'=>$nama]);
    }


    public function delete($id){
        
        // dd($id);
        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $produk = Produk::where('kode_produk',$detail->kode_produk)
        ->where('unit',Auth::user()->unit)
        ->first();
        // dd($produk);
        $produk->stok = $produk->stok - $detail->stok_detail;
        $produk->update(); 
        
        
        $detail->delete();

        return back();

    }



    public function store(Request $request){
        $unit = Auth::user()->unit;
        // dd($unit);
        $produk_detail = new ProdukDetail;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = Auth::user()->unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetail::where('kode_produk',$request->barcode)
                        ->where('unit',Auth::user()->unit)
                        ->sum('stok_detail');

        $update_stok = Produk::where('kode_produk',$request->barcode)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        $update_stok->stok= $stok;
        $update_stok->update();
    
        return back();
    }
}
