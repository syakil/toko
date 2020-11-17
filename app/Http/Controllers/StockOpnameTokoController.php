<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use Auth;
use DB;
use Session;
use Redirect;
use App\ProdukSo;
use App\ParamTgl;

class StockOpnameTokoController extends Controller{

    public function __construct(){
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
    }



    public function index(){
        
        $param = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $tanggalSo = $param->param_tgl;
        $tanggalNow = date('Y-m-d');

        if ($tanggalSo == $tanggalNow) {
            
        $produk = Produk::where('unit',Auth::user()->unit)
                        ->orderBy('stok','desc')
                        ->get();

        return view('stock_opname_toko/index',compact('produk'));

        }else {
            
            return Redirect::back()->withErrors(['Belum Waktunya Stok Opname']);;
        
        }
        
    }

    public function listData($id){
        
        $produk_detail = ProdukDetail::where('kode_produk',$id)
                                        ->where('unit',Auth::user()->unit)
                                        ->get();
        
         
        // dd($id);
        $no = 0;
        $data = array();
        foreach ($produk_detail as $produk) {
        $no++;
        $row = array();
            $row[] = $no;
            $row[] = $produk->kode_produk;
            $row[] = $produk->nama_produk;
            $row[] = "<input type='number' class='jumlah' style='border:none; background:transparent;' name='jumlah_$produk->id_produk_detail' value='$produk->stok_detail' onChange='changeCount($produk->id_produk_detail)'>";
            $row[] = "<input type='date' style='border:none; background:transparent;' name='expired_$produk->id_produk_detail' value='$produk->expired_date' onChange='changeCount($produk->id_produk_detail)'>";;
            $row[] = '<div class="btn-group">
            <a onclick="deleteItem('.$produk->id_produk_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
    }


    public function getData($id){
        $produk = Produk::where('kode_produk',$id)
                        ->where('unit',Auth::user()->unit)
                        ->first();
        echo json_encode($produk);
    }

    public function update(Request $request,$id){



        $nama_input = "jumlah_".$id;
        $exp_input = "expired_".$id;
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;

        $detail = ProdukDetail::find($id);
        

        $produk_so = ProdukSo::where('id_produk_detail',$id)
                            ->where('tanggal_so',$now)
                            ->count();

        if ($produk_so > 0) {

            $ubah_produk_so = ProdukSo::where('id_produk_detail',$id)
                                ->where('tanggal_so',$now)
                                ->first();

            $ubah_produk_so->stok_opname = $request[$nama_input];
            $ubah_produk_so->expired_date = $request[$exp_input];
            $ubah_produk_so->keterangan = 'ubah';
            $ubah_produk_so->update();

        }else{
            
            $produk = Produk::where('kode_produk',$detail->kode_produk)
                            ->where('unit',Auth::user()->unit)
                            ->first();


            $tambah_produk_so = new ProdukSo;
            $tambah_produk_so->id_produk_detail = $id;
            $tambah_produk_so->kode_produk = $detail->kode_produk;
            $tambah_produk_so->tanggal_so = $now;
            $tambah_produk_so->stok = $produk->stok;
            $tambah_produk_so->stok_opname = $request[$nama_input];
            $tambah_produk_so->expired_date = $request[$exp_input];
            $tambah_produk_so->unit = Auth::user()->unit;
            $tambah_produk_so->keterangan = 'ubah';
            $tambah_produk_so->user_id = Auth::user()->id;
            $tambah_produk_so->save();

        }

        
        $detail->stok_detail = $request[$nama_input];
        $detail->expired_date = $request[$exp_input];
        $detail->status = 'stok_opname';
        $detail->update();
        
    }


    public function store(Request $request){

        $kode_produk = $request->kode_produk;
        $nama_produk = $request->nama_produk;
        $stok = $request->stok;
        $exp_date = $request->exp_date;
        
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;


        $produk = Produk::where('kode_produk',$kode_produk)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        
        // cek agar exp date tidak ada yang sama

        $cek_exp = ProdukDetail::where('kode_produk',$kode_produk)
                                ->where('unit',Auth::user()->unit)
                                ->where('expired_date',$exp_date)
                                ->count();
        if ($cek_exp > 0) {
        
            $produk_detail = ProdukDetail::where('kode_produk',$kode_produk)
                                ->where('unit',Auth::user()->unit)
                                ->where('expired_date',$exp_date)
                                ->first();
            
            $produk_detail->stok_detail += $stok;
            $produk_detail->status = 'stock_opname';
            $produk_detail->update();

            $cek_produk_so = ProdukSo::where('id_produk_detail',$produk_detail->id_produk_detail)->where('tanggal_so',$now)->count();

            if ($cek_produk_so > 0) {

                $ubah_produk_so = ProdukSo::where('id_produk_detail',$produk_detail->id_produk_detail)->first();
                $ubah_produk_so->stok_opname = $produk_detail->stok_detail;
                $ubah_produk_so->expired_date = $produk_detail->expired_date;
                $ubah_produk_so->update();

                
            }else{

                $tambah_produk_so = new ProdukSo;
                $tambah_produk_so->id_produk_detail = $produk_detail->id_produk_detail;
                $tambah_produk_so->kode_produk = $kode_produk;
                $tambah_produk_so->tanggal_so = $now;
                $tambah_produk_so->stok = $produk->stok;
                $tambah_produk_so->stok_opname = $stok;
                $tambah_produk_so->expired_date = $exp_date;
                $tambah_produk_so->unit = Auth::user()->unit;
                $tambah_produk_so->keterangan = 'tambah';
                $tambah_produk_so->user_id = Auth::user()->id;
                $tambah_produk_so->save();    

            }

                
        }else{

            $harga_beli = Produk::where('kode_produk',$kode_produk)
                                ->where('unit',Auth::user()->unit)
                                ->first();

            $produk_detail = new ProdukDetail;
            $produk_detail->kode_produk = $kode_produk;
            $produk_detail->id_kategori = '';
            $produk_detail->nama_produk = $nama_produk;
            $produk_detail->stok_detail = $stok;
            $produk_detail->satuan = '';
            $produk_detail->harga_beli = $harga_beli->harga_beli;
            $produk_detail->expired_date = $exp_date;
            $produk_detail->unit = Auth::user()->unit;
            $produk_detail->status = 'stok_opname';
            $produk_detail->save();

            $tambah_produk_so = new ProdukSo;
            $tambah_produk_so->id_produk_detail = $produk_detail->id_produk_detail;
            $tambah_produk_so->kode_produk = $kode_produk;
            $tambah_produk_so->tanggal_so = $now;
            $tambah_produk_so->stok = $produk->stok;
            $tambah_produk_so->stok_opname = $stok;
            $tambah_produk_so->expired_date = $exp_date;
            $tambah_produk_so->unit = Auth::user()->unit;
            $tambah_produk_so->keterangan = 'tambah';
            $tambah_produk_so->user_id = Auth::user()->id;
            $tambah_produk_so->save();
        }
    }

    public function simpan_($id){
        
        
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;


        // ngambil produk
        $produk = Produk::where('kode_produk',$id)
                        ->where('unit',Auth::user()->unit)
                        ->first();
        // sum detail produk
        $stok_detail = ProdukDetail::where('kode_produk',$id)
                                     ->where('unit',Auth::user()->unit)
                                     ->sum('stok_detail');

        // selisih ? 
        if ($produk->stok != $stok_detail) {
            // get produk_detail 
            $detail_so = ProdukDetail::where('kode_produk',$id)
                                ->where('unit',Auth::user()->unit)
                                ->get();
            

            foreach ($detail_so as $detail ) {

                //cek data yang tersimpan di produk_so
                $cek_produk_so = ProdukSo::where('id_produk_detail',$detail->id_produk_detail)->where('tanggal_so',$now)->count();

                if ($cek_produk_so > 0) {
                    
                    $get_produk_so = ProdukSo::where('id_produk_detail',$detail->id_produk_detail)->where('tanggal_so',$now)->first();
                    $get_produk_so->status = 'selisih';
                    $get_produk_so->update();

                    
                    // tambah status di produk_detail = stok_opname
                    $ubah_status_produk_detail = ProdukDetail::find($detail->id_produk_detail);
                    $ubah_status_produk_detail->status = 'stok_opname';
                    $ubah_status_produk_detail->update();

                }else{

                    // simpan data produk_detail menggunakan id_produk_detail
                    $produk_so = new ProdukSo;
                    $produk_so->id_produk_detail = $detail->id_produk_detail;
                    $produk_so->kode_produk = $detail->kode_produk;
                    $produk_so->tanggal_so = $now;
                    $produk_so->stok = $produk->stok;
                    $produk_so->stok_opname = $detail->stok_detail;
                    $produk_so->unit = Auth::user()->unit;
                    $produk_so->keterangan = 'selisih';
                    $produk_so->user_id = Auth::user()->id;
                    $produk_so->expired_date = $detail->expired_date;
                    $produk_so->save();
                    
                    // tambah status di produk_detail = stok_opname
                    $ubah_status_produk_detail = ProdukDetail::find($detail->id_produk_detail);
                    $ubah_status_produk_detail->status = 'stok_opname';
                    $ubah_status_produk_detail->update();

                }


            }
            

                $update_status_produkso = ProdukSo::where('kode_produk',$id)
                                                    ->where('unit',Auth::user()->unit)
                                                    ->where('tanggal_so',$now)
                                                    ->get();

                foreach ($update_status_produkso as $detail ) {
                    $detail->status = 'selisih';
                    $detail->update();
                }

        }else{        
            
            $produk_detail = ProdukDetail::where('kode_produk',$id)
                                    ->where('unit',Auth::user()->unit)
                                    ->where('status',null)
                                    ->get();

            foreach ($produk_detail as $detail ) {
            
                
                    $produk_so = new ProdukSo;
                    $produk_so->id_produk_detail = $detail->id_produk_detail;
                    $produk_so->kode_produk = $detail->kode_produk;
                    $produk_so->tanggal_so = $now;
                    $produk_so->stok = $produk->stok;
                    $produk_so->stok_opname = $detail->stok_detail;
                    $produk_so->unit = Auth::user()->unit;
                    $produk_so->keterangan = 'sama';
                    $produk_so->user_id = Auth::user()->id;
                    $produk_so->expired_date = $detail->expired_date;
                    $produk_so->save();
                
            }

            
            $produk->stok = $stok_detail;
            $produk->update();

            $produk_detail = ProdukDetail::where('kode_produk',$id)
                                        ->where('unit',Auth::user()->unit)
                                        ->where('status','stok_opname')
                                        ->get();
        
            foreach ($produk_detail as $detail ) {
                $detail->status = null;
                $detail->update();
            }

            $update_status_produkso = ProdukSo::where('kode_produk',$id)
                                            ->where('unit',Auth::user()->unit)
                                            ->where('tanggal_so',$now)
                                            ->get();

            foreach ($update_status_produkso as $detail ) {
                $detail->status = 'sama';
                $detail->update();
            }
        }


    }

   
    public function destroy($id){
        
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;

        $detail = ProdukDetail::find($id);

        $produk = Produk::where('kode_produk',$detail->kode_produk)
        ->where('unit',Auth::user()->unit)
        ->first();
        
        $produk_so = ProdukSo::where('id_produk_detail',$id)
                            ->count();

        if ($produk_so > 0) {

            $ubah_produk_so = ProdukSo::where('id_produk_detail',$id)->first();
            $ubah_produk_so->keterangan = 'dihapus';
            $ubah_produk_so->update();

        }else{

            $produk_so = new ProdukSo;
            $produk_so->id_produk_detail = $id;
            $produk_so->kode_produk = $detail->kode_produk;
            $produk_so->tanggal_so = $now;
            $produk_so->stok = $produk->stok;
            $produk_so->stok_opname = $detail->stok_detail;
            $produk_so->unit = Auth::user()->unit;
            $produk_so->keterangan = 'dihapus';
            $produk_so->user_id = Auth::user()->id;
            $produk_so->expired_date = $detail->expired_date;
            $produk_so->save();

        }

        $detail->delete();

    }
}

