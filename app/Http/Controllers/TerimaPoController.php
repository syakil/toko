<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use Auth;
use PDF;
use App\Supplier;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use App\ProdukDetail;
use DB;
use Session;
use App\PembelianDetail;

class TerimaPoController extends Controller
{
    public function index(){
        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                            ->where('kode_gudang',Auth::user()->unit)
                            ->where('status',null)
                            ->get();
        return view('terima_po.index', compact('pembelian')); 
    }

    public function listData(){
    
        $pembelian = Pembelian::select('pembelian.*','supplier.nama')->leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian.id_supplier')
                                ->where('kode_gudang',Auth::user()->unit)
                                ->orderBy('pembelian.id_pembelian', 'desc')
                                ->get();
        $no = 0;
        $data = array();
        foreach($pembelian as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->id_pembelian_t;
        $row[] = tanggal_indonesia($list->created_at);
        $row[] = $list->nama;
        $row[] = $list->total_item;
        $row[] = $list->total_terima;
        $row[] = '<div class="btn-group">
                <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                <a onclick="deleteData('.$list->id_pembelian.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                <a href="/toko/terima_po/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                </div>';
        $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);
    }

    public function show($id){
    
        $detail = PembelianDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_detail.kode_produk')
            ->where('id_pembelian', '=', $id)
            ->where('unit',Auth::user()->unit)
            ->get();
        $no = 0;
        $data = array();
        foreach($detail as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->kode_produk;
        $row[] = $list->nama_produk;
        $row[] = "Rp. ".format_uang($list->harga_beli);
        $row[] = $list->jumlah;
        $row[] = $list->jumlah_terima;
        $row[] = $list->status_jurnal;
        $row[] = 'Rp.' . $list->sub_total;
        $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);
    }


    public function cetak($id){
        $data['produk'] = DB::table('pembelian_detail','produk')
                            ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk')
                            ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->where('id_pembelian',$id)
                            ->get();

        $data['alamat'] = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                                    ->leftJoin('branch','pembelian.kode_gudang','=','branch.kode_gudang')
                                    ->where('id_pembelian',$id)
                                    ->first();

        $data['nosurat'] = Pembelian::where('id_pembelian',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('terima_po.cetak_po', $data);
        return $pdf->stream('surat_jalan.pdf');
    }


    public function create($id){

        $temporary = PembelianTemporary::find($id);
        // dd($temporary);
        $pembelian = new Pembelian;
        $pembelian->id_pembelian_t = $temporary->id_pembelian;
        $pembelian->id_supplier = $temporary->id_supplier;     
        $pembelian->total_item = $temporary->total_item;     
        $pembelian->total_harga = 0;
        $pembelian->total_terima = 0;
        $pembelian->total_selisih = 0;
        $pembelian->total_harga_selisih = 0;
        $pembelian->total_harga_terima = 0;     
        $pembelian->diskon = 0;     
        $pembelian->bayar = 0;      
        $pembelian->jatuh_tempo = date('Y-m-d');
        $pembelian->kode_gudang = 0;    
        $pembelian->tipe_bayar = 2;    
        $pembelian->id_user = Auth::user()->id;
        $pembelian->kode_gudang = Auth::user()->unit;
            
        $pembelian->save();

        session(['idpembelian' => $pembelian->id_pembelian]);
        session(['idtemporary' => $id]);
        session(['idsupplier' => $temporary->id_supplier]);

        return Redirect::route('terima_po_detail.index');      
    }

    public function store(Request $request){
           
        $pembelian = Pembelian::find($request['idpembelian']);
        $pembelian->total_terima = $request['totalitem'];
        $pembelian->total_selisih = $pembelian->total_item - $request['totalitem'];
        $pembelian->total_harga = $request['total'];
        $pembelian->diskon = $request['diskon'];
        $pembelian->bayar = $request['bayar'];
        $pembelian->update();

        $pembelian_temporary = PembelianTemporary::find($pembelian->id_pembelian_t);
        $pembelian_temporary->total_terima = $request['totalitem'];
        $pembelian_temporary->total_selisih = $pembelian_temporary->total_item - $request['totalitem'];
        $pembelian_temporary->total_harga = $request['total'];
        $pembelian_temporary->diskon = $request['diskon'];
        $pembelian_temporary->bayar = $request['bayar'];
        $pembelian_temporary->update();


            
        $produk = DB::table('pembelian_detail','produk')
                    ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.id_kategori','produk.unit')
                    ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                    ->where('unit',Auth::user()->unit)
                    ->where('id_pembelian',$request['idpembelian'])
                    ->get();
    
                        
        foreach ($produk as $p ) {
                
            $produk_main = Produk::where('kode_produk',$p->kode_produk)
            ->where('unit', Auth::user()->unit)
            ->first();
            
            $produk_main->stok += $p->jumlah_terima;
            $produk_main->update();

            $produk_detail = new ProdukDetail;
            $produk_detail->kode_produk = $p->kode_produk;
            $produk_detail->nama_produk = $produk_main->nama_produk;
            $produk_detail->stok_detail = $p->jumlah_terima;
            $produk_detail->harga_beli = $p->harga_beli;
            $produk_detail->harga_jual_umum = 0;
            $produk_detail->harga_jual_insan = 0;
            $produk_detail->expired_date = $p->expired_date;
            $produk_detail->promo = 0;
            $produk_detail->tanggal_masuk = date('Y-m-d');
            $produk_detail->no_faktur = $pembelian->id_pembelian_t;
            $produk_detail->unit = Auth::user()->unit;
            $produk_detail->status = '1';
            $produk_detail->promo = 0;
            $produk_detail->save();
            
        }
        
        // update status_app menjadi 1
        // $pembelian=Pembelian::where('id_pembelian',$p->id_pembelian);
        // $pembelian->update(['status'=>1]);
        
        
        $pembelianT=PembelianTemporary::where('id_pembelian',$request->session()->get('idtemporary'));
        $pembelianT->update(['status'=>1]);
        
        // dd($request->session()->get('idtemporary'));

        Session::forget('idpembelian');
        return Redirect::route('terima_po.index');
    }
    
    public function destroy($id){
        
        $pembelian = Pembelian::find($id);
        $pembelian->delete();

        $detail = PembelianDetail::where('id_pembelian', '=', $id)->get();
        foreach($detail as $data){
            $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
            $produk->stok -= $data->jumlah;
            $produk->update();
            $data->delete();
        }
    }
}
