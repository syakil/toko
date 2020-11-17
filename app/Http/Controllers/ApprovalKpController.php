<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ProdukTemporary;
use App\ProdukDetailTemporary;
use App\ProdukSo;
use App\ParamTgl;

class ApprovalKpController extends Controller
{
    public function index(){
$unit = '3000';
        $produk = ProdukSo::where('produk_so.unit', $unit)
                        ->where('produk_so.keterangan','!=','dihapus')
                        ->where('produk.unit',$unit)
                        ->where('approval',null)
                        ->leftJoin('produk','produk.kode_produk','produk_so.kode_produk')
                        ->leftJoin('branch','branch.kode_toko','produk_so.unit')
                        ->select(\DB::raw('SUM(stok_opname) as so,produk_so.*,branch.nama_toko,produk.kode_produk,produk.nama_produk'))
                        ->groupBy('produk_so.kode_produk')
                        ->groupBy('produk_so.tanggal_so')
                        ->groupBy('produk_so.unit')
                        ->get();

        $no=1;
        return view('approve_kp/index',['produk'=>$produk,'no'=>$no]);
    }

    
    public function store(Request $request){

        
        $data = $request->kode;
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;

        foreach ($data as $id ) {
            $data_produk = ProdukSo::find($id);
            $get_produk_so = ProdukSo::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->where('tanggal_so',$now)->get();
  
            $master_produk = Produk::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->first();
            $sum_detail = ProdukDetail::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->sum('stok_detail');
            
            $master_produk->stok = $sum_detail;
            $master_produk->update();

            foreach ($get_produk_so as $produk_so ) {
                
                $produk_so->approval = 'A';
                $produk_so->update();

            }

            $get_produk_detail = ProdukDetail::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->get();
            foreach ($get_produk_detail as $produk_detail ) {
                
                $produk_detail->status = null;
                $produk_detail->update();

            }
            
        }


        return back();
    }

}
