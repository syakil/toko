<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use Auth;
use PDF;
use App\Supplier;
use App\KirimDetail;
use App\Produk;
use App\ProdukDetail;
use App\Branch;
use App\TabelTransaksi;


class ReturSupplierController extends Controller{

   public function index(){
      $supplier = Supplier::all();
      $branch = Branch::all();
      return view('retur_supplier.index', compact('supplier','branch')); 
   }

   public function listData(){
      $pembelian = Kirim::leftJoin('supplier', 'supplier.id_supplier', '=', 'kirim_barang.id_supplier')
->select('kirim_barang.*','supplier.nama')
                        ->orderBy('kirim_barang.id_pembelian', 'desc')
                        ->where('tujuan','supplier') 
->where('kode_gudang',Auth::user()->unit)
                        ->get();
   
      $no = 0;
      $data = array();
      foreach($pembelian as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->created_at;
         $row[] = $list->nama;
         $row[] = $list->total_item;
         $row[] = "Rp. ".format_uang($list->total_harga);
         $row[] = '<div class="btn-group">
                  <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                  <a href="/toko/retur_supplier/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
               </div>';
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
   }

   public function show($id){

      $detail = KirimDetail::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail.kode_produk')
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
         $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
   }



   public function cetak($id){
      $data['produk'] = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                       ->where('id_pembelian',$id)
                                       ->where('produk.unit',Auth::user()->unit)
                                       ->get();

      $data['alamat']= Kirim::leftJoin('supplier','kirim_barang.id_supplier','=','supplier.id_supplier')
      ->leftJoin('branch','kirim_barang.kode_gudang','=','branch.kode_gudang')
      ->select('supplier.*','branch.kode_gudang','branch.nama_gudang')
      ->where('id_pembelian',$id)
      ->first();
      $data['nosurat'] = Kirim::where('id_pembelian',$id)->get();
      $data['no'] =1;
      $pdf = PDF::loadView('retur_supplier.cetak_sj', $data);
      return $pdf->stream('surat_jalan.pdf');
   }

   public function create($id){
      $pembelian = new Kirim;
      $pembelian->id_supplier = $id;     
      $pembelian->total_item = 0;     
      $pembelian->total_harga = 0;     
      //total_terima = 0;
      $pembelian->total_terima = 0;
      // tambah field total_harga_terima di table kirim_barang not null
      $pembelian->total_harga_terima = 0;
      $pembelian->id_user = Auth::user()->id;
      $pembelian->kode_gudang = Auth::user()->unit;
      $pembelian->tujuan = 'supplier';
      $pembelian->status_kirim = 'retur'; 
      $pembelian->save();    

      session(['idpembelian' => $pembelian->id_pembelian]);
      session(['idsupplier' => $id]);
      session(['kode_gudang' => $pembelian->kode_gudang]);
      // dd($pembeian);
      return Redirect::route('retur_supplier_detail.index');      
   }

   public function store(Request $request){
      // dd($request);
      $pembelian = Kirim::find($request->idpembelian);
      $pembelian->total_item = $request->totalitem;
      $pembelian->total_harga = $request->total;
      $pembelian->update();

      $details = KirimDetail::where('id_pembelian', '=', $request->idpembelian)->get();
      // kode syakil

      foreach($details as $list){
         $cek_sum_penjualan = KirimDetail::where('id_pembelian', '=', $request->idpembelian)->where('kode_produk',$list->kode_produk)->sum('jumlah');
         $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
       
         if($cek_sum_penjualan > $produk->stok){
          return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
         }
       }


       foreach($details as $list){
         $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
         $produk->stok -= $list->jumlah;

         $produk->update();
       }
       // --- //

      //$now = \Carbon\Carbon::now();
      $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

      
      // insert jurnal 
      $data = Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                  ->where('id_pembelian',$request['idpembelian'])
                  ->get();
                  
      
      foreach($data as $d){
         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $d->id_pembelian;
         $jurnal->kode_rekening = 2500000;
         $jurnal->tanggal_transaksi  = $tanggal;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama_toko;
         $jurnal->debet =$d->total_harga;
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $d->id_pembelian;
         $jurnal->kode_rekening = 1482000;
         $jurnal->tanggal_transaksi  = $tanggal;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama_toko;
         $jurnal->debet =0;
         $jurnal->kredit =$d->total_harga;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();
      }
      // --- /kode syakil ---
      
      $supplier = Supplier::all();
      $branch = Branch::all();
      // dd($branch);
      return view('retur_supplier.index', compact('supplier','branch')); 
      
   }

   public function destroy($id){
      $pembelian = Kirim::find($id);
      $pembelian->delete();

      $detail = KirimDetail::where('id_pembelian', '=', $id)->get();
      foreach($detail as $data){
         $produk = ProdukDetail::where('kode_produk', '=', $data->kode_produk)
                                 ->where('expired_date',$data->expired_date)                      
                                 ->first();
         $produk->stok += $data->jumlah;
         $produk->update();
         $data->delete();
      }
   }
}

