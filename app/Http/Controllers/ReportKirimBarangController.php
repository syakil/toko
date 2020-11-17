<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Kirim;
use App\KirimDetail;
use App\Produk;
use DB;


class ReportKirimBarangController extends Controller
{
    public function index(){
        
        return view('report_kirim_barang/index');
    
    }

    public function listData(){

        // if (Auth::user()->level == 1) {
            $kirim_barang = Kirim::where('status',2)
            ->leftJoin('branch','branch.kode_toko','kirim_barang.id_supplier')
            ->select('kirim_barang.*','branch.nama_toko')
            ->where('kirim_barang.kode_gudang',Auth::user()->unit)
            ->orWhere('kirim_barang.id_supplier',Auth::user()->unit)->get();
        // }else {
        //     $kirim_barang = KirimBarang::
        //     leftJoin('branch',function($join){
        //         $join->on('branch.kode_toko', '=', 'kirim_barang.id_supplier');
        //         $join->on('branch.kode_toko','=','kirim_barang.kode_gudang');    
        //     })
        //     ->where('status',2)->get();
        // }

        
        $no = 0;
        $data = array();
        foreach($kirim_barang as $list){
            $no ++;
            $row = array();
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->id_pembelian;
            $row[] = $list->nama_toko;
            $row[] = $list->total_item;
            $row[] = $list->total_terima;
            $row[] = $list->kode_gudang;
            $row[] = '<div class="btn-group">
            <a href="'.route('report_kirim.detail',$list->id_pembelian).'" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
            </div>';
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function detail($id){

        return view('report_kirim_barang/detail',compact('id'));

    }


    
    public function listDetail($id){

        $kirim_detail = KirimDetail::where('id_pembelian',$id)->leftJoin('produk','produk.kode_produk','kirim_barang_detail.kode_produk')
        ->where('produk.unit',Auth::user()->unit)->get();

        $no = 0;
        $data = array();
        foreach($kirim_detail as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->jumlah;
            $row[] = $list->jumlah_terima;
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }
}
