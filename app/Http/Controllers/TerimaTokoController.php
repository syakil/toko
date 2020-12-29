<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use App\TabelTransaksi;
use App\ProdukDetail;

use App\Produk;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;
use App\Branch;

class TerimaTokoController extends Controller
{
    public function index(){

        $terima = Kirim::where('id_supplier',Auth::user()->unit)
                        ->where('status_kirim','transfer')
                        ->where('status',1)
                        ->get();
        $no = 1;
        return view ('terima_toko.index',['terima'=>$terima,'no'=>$no]);
    
    }

    public function listDetail(){
        
    }

    public function detail($id){

        $detail = KirimDetail::where('id_pembelian',$id)
                            ->join('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->get();
        $nopo = Kirim::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('terima_toko.detail',['kirim'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo]);
    }

    public function update_jumlah_terima(Request $request,$id){

        $kirim_detail = KirimDetail::where('id_pembelian_detail',$id)->first();
        $kirim_detail->update(['jumlah_terima'=>$request->value]);
        
        $total = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('jumlah_terima');

        $kirim = Kirim::where('id_pembelian',$kirim_detail->id_pembelian)->first();
        $kirim->total_terima = $total;
        $kirim->update();


        
        $produk_detail = KirimDetail::where('id_pembelian_detail',$id)
                                        ->get();

        // ubah sub_total
        foreach($produk_detail as $detail){

            // harga sub total kirim_barang_detail
            $sub_total = $detail->harga_beli * $request->value;
            $sub_total_margin = $detail->harga_jual * $request->value;
            $produk_sub_total = KirimDetail::where('id_pembelian_detail',$id)->first();
            $produk_sub_total->sub_total_terima = $sub_total;
            $produk_sub_total->sub_total_margin_terima = $sub_total_margin;
            $produk_sub_total->update();

        }

        
        $total_harga_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('sub_total_terima');
        $total_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('jumlah_terima');
        $total_margin_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('sub_total_margin_terima');

        $kirim = Kirim::where('id_pembelian',$kirim_detail->id_pembelian)->first();
        $kirim->total_harga_terima = $total_harga_terima;
        $kirim->total_terima = $total_terima;
        $kirim->total_margin_terima = $total_margin_terima;
        $kirim->update();

    }

    
    public function update_expired_date(Request $request,$id){

        $detail = KirimDetail::where('id_pembelian_detail',$id);
        $detail->update(['expired_date'=>$request->value]);

    }

    public function create_jurnal(Request $request){
        // menampung id_pembelian yang di checklist
        $data = $request->check;

        try{

            DB::beginTransaction();        
            // cek terima 
            foreach($data as $id){
            
                $cek_terima = Kirim::where('id_pembelian',$id)->first();

                if ($cek_terima->total_terima > $cek_terima->total_item) {
                    
                    return back()->with(['error' => 'Total Terima Surat Jalan '. $id . ' lebih besar !']);
                
                }
            
            }

            // update_stok
            foreach($data as $id){
                

                // update_stok
                $produk = DB::table('kirim_barang_detail')
                            ->where('id_pembelian',$id)
                            ->get();
                
                foreach ($produk as $p ) {

                    $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                        ->where('unit',Auth::user()->unit)
                                        ->first();

                    $produk_main->stok = $produk_main->stok + $p->jumlah_terima;
                    $produk_main->update();

                    $produk_detail = new ProdukDetail;
                    $produk_detail->kode_produk = $p->kode_produk;
                    $produk_detail->nama_produk = $produk_main->nama_produk;
                    $produk_detail->stok_detail = $p->jumlah_terima;
                    $produk_detail->harga_beli = $p->harga_beli;
                    $produk_detail->harga_jual_umum = $p->harga_jual;
                    $produk_detail->harga_jual_insan = $p->harga_jual;
                    $produk_detail->tanggal_masuk = date('Y-m-d');
                    $produk_detail->unit = Auth::user()->unit;
                    $produk_detail->status = null;
                    $produk_detail->expired_date = $p->expired_date;
                    $produk_detail->no_faktur = $p->no_faktur;
                    $produk_detail->save();

                }

            }
                
            // insert_jurnal

            foreach($data as $id){

                $data_jurnal = Kirim::where('id_pembelian',$id)->first();
                $tanggal = date('Y-m-d',strtotime($data_jurnal->created_at));

                $pengirim = Branch::where('kode_toko',$data_jurnal->kode_gudang)->first();
                $penerima = Branch::where('kode_toko',$data_jurnal->id_supplier)->first();

                
                $kode_penerima = $penerima->kode_toko;
                $nama_penerima = $penerima->nama_toko;
                $aktiva_penerima = $penerima->aktiva;
                
                $kode_pengirim = $pengirim->kode_toko;
                $nama_pengirim = $pengirim->nama_toko;
                $aktiva_pengirim = $pengirim->aktiva;

                $harga_beli = $data_jurnal->total_harga_terima;
                $harga_jual = $data_jurnal->total_margin_terima;

                $harga_terima = $data_jurnal->total_harga_terima;
                $harga_kirim = $data_jurnal->total_harga;

                $margin = $harga_jual - $harga_beli;

                $selisih = $harga_kirim - $harga_terima;

                $terima = $data_jurnal->total_terima;
                $kirim = $data_jurnal->total_item;
                
                $unit_kp = '1010';
                // jika yang diterima toko selisih dengan yang dikirim
                if ($kirim > $terima) {               

                    // toko
                    // Persediaan Musawamah/Barang Dagang
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = $harga_jual;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // RAK PASIVA - KP
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 2500000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $harga_beli;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // PMYD-PYD Musawamah
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 1483000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $margin;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // gudang
                    // Persediaan Musawamah/Barang Dagang
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_pengirim; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = abs($selisih);
                    $jurnal->kredit =0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    // RAK PASIVA - KP
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_pengirim; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 2500000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = abs($selisih);
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                
                    //KP
                    // RAK - AKTIVA UNIT PENERIMA
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_kp; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = $aktiva_penerima;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = $harga_terima;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // RAK - AKTIVA UNIT PENGIRIM
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_kp; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = $aktiva_pengirim;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = abs($selisih);
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // RAK - AKTIVA UNIT PENGIRIM
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_kp; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = $aktiva_pengirim;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Kirim Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $harga_kirim;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    $barang_kurang = KirimDetail::where('id_pembelian',$id)->where('jumlah','<','jumlah_terima')->get();

                    foreach ($barang_kurang as $value) {
                        
                        $selisih = $value->jumlah - $value->jumlah_terima;

                        $stok_gudang = Produk::where('kode_produk',$value->kode_produk)->where('unit',$data_jurnal->kode_gudang)->first();
                        // dd($stok_gudang);
                        $stok_gudang->stok += $selisih;
                        $stok_gudang->update();
                            
                        $stok_gudang_detail = new ProdukDetail;
                        $stok_gudang_detail->kode_produk = $value->kode_produk;
                        $stok_gudang_detail->nama_produk = $stok_gudang->nama_produk;
                        $stok_gudang_detail->stok_detail = $selisih;
                        $stok_gudang_detail->harga_beli = $value->harga_beli;
                        $stok_gudang_detail->harga_jual_umum = $value->harga_jual;
                        $stok_gudang_detail->harga_jual_insan = $value->harga_jual;
                        $stok_gudang_detail->tanggal_masuk = date('Y-m-d');
                        $stok_gudang_detail->unit = $data_jurnal->kode_gudang;
                        $stok_gudang_detail->status = null;
                        $stok_gudang_detail->expired_date = $value->expired_date;
                        $stok_gudang_detail->no_faktur = $value->no_faktur;
                        $stok_gudang_detail->save();

                    }

                    
                }else {

                    // toko
                    // Persediaan Musawamah/Barang Dagang
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = $harga_jual;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // RAK PASIVA - KP
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 2500000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $harga_beli;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // PMYD-PYD Musawamah
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $kode_penerima; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = 1483000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $margin;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // KP
                    // RAK Aktiva Penerima
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_kp; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = $aktiva_penerima;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = $harga_beli;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    // RAK Aktuiva Pengirim
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_kp; 
                    $jurnal->kode_transaksi = $id;
                    $jurnal->kode_rekening = $aktiva_pengirim;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Kirim Toko' . ' ' . $id . ' ' . $nama_penerima;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $harga_beli;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                }

                $kirim_status = Kirim::where('id_pembelian',$id)->update(['status'=>2]);
            }

            DB::commit();

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }
            
        return redirect('terima_toko/index')->with(['success' => 'Transaksi Berhasil']);
    }
}


