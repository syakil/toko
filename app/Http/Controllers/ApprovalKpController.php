<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ProdukSo;
use Auth;
use App\Branch;
use App\ParamTgl;

class ApprovalKpController extends Controller
{
    public function index(){
        $unit = Branch::groupBy('region')
                        ->get();

        
        return view('approve_kp/index',compact('unit'));
    }


    public function listData($unit){

        $produk_so = ProdukSo:: select('produk_so.*','produk.nama_produk','produk.stok')
                                ->leftJoin('produk','produk.kode_produk','produk_so.kode_produk')
                                ->where('produk_so.unit',$unit)
                                ->where('produk.unit',$unit)
                                ->where('status',1)
                                ->get();
        
        $no = 0;
        $data = array();
        foreach ($produk_so as $list) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->qty;
            $row[] = $list->stok_system;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
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


    public function approval(){

        



    }
}

