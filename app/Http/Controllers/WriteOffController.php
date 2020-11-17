<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Ramsey\Uuid\Uuid;
use Auth;
use App\TabelTransaksi;
use App\Produk;
use App\ProdukDetail;
use App\ProdukWriteOff;



class WriteOffController extends Controller
{
    public function index(){

        return view('write_off.index');

    }


    public function loadData(Request $request){

        $cari = $request['query'];
        
        $data = DB::table('produk')->select('kode_produk', 'nama_produk')->where('kode_produk', 'LIKE', '%'.$cari.'%')->orWhere('nama_produk', 'LIKE', '%'.$cari.'%')->where('unit',3000)->limit('5')->groupBy('kode_produk')->get();
        $output = '<ul class="dropdown-menu" style="display:block; position:relative">';


        foreach($data as $row){

            $output .= '
            <li class="produk_list"><a href="#">'.$row->kode_produk.' - '.$row->nama_produk.'</a></li>
            ';

        }

        $output .= '</ul>';
        echo $output;

    }


    public function loadstok($kode){

        $data = DB::table('produk')->select('stok')->where('kode_produk', 'LIKE', '%'.$kode.'%')->where('unit',Auth::user()->unit)->first();
        echo $data->stok;
    }

    public function file($id){

        $data = DB::table('produk_wo')->select('file')->where('id_produk_wo',$id)->where('unit',Auth::user()->unit)->first();
        echo $data->file;
    }


    public function index_admin(){

        return view('write_off.index_admin');

    }

    public function listAdmin(){

        $produk = ProdukWriteOff::where('param_status',1)->where('unit',Auth::user()->unit)->get();

        $no = 0;
        $data = array();

        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->tanggal_input;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
            $row[] = "Rp. ".format_uang($list->harga_jual * $list->stok);
            $row[] = "Rp. ".format_uang($list->harga_beli * $list->stok);
            $row[] = '<div class="btn-group">
                    <a onclick="deleteData('.$list->id_produk_wo.')" class="btn btn-danger btn-sm">Proses WO</i></a>
                </div>';
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);


    }
    
    public function index_approve(){

        return view('write_off.index_approve');

    }

    public function listApprove(){

        $produk = ProdukWriteOff::where('param_status',2)->where('unit',Auth::user()->unit)->get();

        $no = 0;
        $data = array();

        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->tanggal_input;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
            $row[] = "Rp. ".format_uang($list->harga_jual * $list->stok);
            $row[] = "Rp. ".format_uang($list->harga_beli * $list->stok);
            $row[] = '<div class="btn-group">
                        <a onclick="openModal('.$list->id_produk_wo.')" class="btn btn-danger btn-sm">
                            Upload Bukti Approve
                        </a>
                </div>';
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function approve(Request $request){

        DB::beginTransaction();
        try{
        
            if ($request->hasFile('file')) {
                    
                $extension = array('png','jpg','jpeg','pdf');
                $file_upload = $request->file('file');
                $ext = $file_upload->getClientOriginalExtension();
                $size = $file_upload->getSize();
                

                if (!in_array($ext,$extension)) {

                    return back()->with(['error' => 'Extention Harus png,jpg,jpeg,pdf !']);    

                }elseif ($size > 2000000) {
                    
                    return back()->with(['error' => 'Ukuran File Tidak Boleh Lebih Dari 2 Mb !']);    

                }else{

                    $name = $file_upload->getClientOriginalName();
                    $newName = $name.'.'.$ext;
                    $destinationPath = public_path('/upload/approve_wo');
                    $file_upload->move($destinationPath, $name);
                    $foto = asset('public/upload/approve_wo/'.$name);
        
                }
                                
                $produk = ProdukWriteOff::find($request->id_wo);

                if ($produk) {
                    
                    $produk->file = $foto;
                    $produk->tanggal_wo = date('Y-m-d');
                    $produk->param_status = 3;
                    $produk->update();
                    $tanggal = date('Y-m-d');
                    $nominal = $produk->harga_beli * $produk->stok;
                    
                    // Biaya PPA Umum-Piutang_Istishna
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $produk->unit; 
                    $jurnal->kode_transaksi = $produk->kode_transaksi;
                    $jurnal->kode_rekening = 1518000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'PPA Umum-Penyertaan' . $produk->kode_produk . ' ' . $produk->nama_produk;
                    $jurnal->debet = $nominal;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    // PPA Umum-PYD Musawamah
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $produk->unit; 
                    $jurnal->kode_transaksi = $produk->kode_transaksi;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang ' . $produk->kode_produk . ' ' . $produk->nama_produk;
                    $jurnal->debet = 0;
                    $jurnal->kredit = $nominal;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                
                }else {

                    return back()->with(['error' => 'Produk Tidak Ada !']);    
                
                }

            }else {
                
                return back()->with(['error' => 'upload foto']);
            
            }
            
            DB::commit();
        }catch(\Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
        
        }
         
        return back()->with(['success' => 'Proses Write Off Berhasil !']);


    }

    public function index_report(){

        return view('write_off/index_report');

    }

    public function listReport(){

        $produk = ProdukWriteOff::where('unit',Auth::user()->unit)->get();

        $no = 0;
        $data = array();

        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->tanggal_input;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
            $row[] = "Rp. ".format_uang($list->harga_jual * $list->stok);
            $row[] = "Rp. ".format_uang($list->harga_beli * $list->stok);
            
            switch ($list->param_status) {
                case '1':
                    $row[]="<span class='label label-info'>Request</span>";
                    $row[] = '<div class="btn-group">
                            <a class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                            </div>';
                    break;
                case '2':
                    $row[]="<span class='label label-primary'>On Progress</span>";
                    $row[] = '<div class="btn-group">
                                <a class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                            </div>';
                    break;
                default:
                    $row[]="<span class='label label-success'>Approved</span>";
                    $row[] = "<div class='btn-group'>
                                <a class='btn btn-primary btn-sm'onclick='showDetail(".$list->id_produk_wo.")'><i class='fa fa-eye'></i></a>
                            </div>";
                    break;
            }
           
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }
    

    public function proses($id){

        try {
            
            $tanggal = date('Y-m-d');
            
            $produk = ProdukWriteOff::find($id);
            $produk->param_status = 2;
            $produk->tanggal_input = $tanggal;
            $produk->update();

            $kode_produk = $produk->kode_produk;
            $jumlah = $produk->stok;
            $unit = DB::table('branch')->where('kode_toko',Auth::user()->unit)->first();

            $nominal = $produk->stok * $produk->harga_beli;

            // PPA Umum-PYD Musawamah
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  $unit->kode_toko; 
            $jurnal->kode_transaksi = $produk->kode_transaksi;
            $jurnal->kode_rekening = 54111;
            $jurnal->tanggal_transaksi  = $tanggal;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Biaya PPA-Surat Berharga Yang Dimiliki ' . $produk->kode_produk . ' ' . $produk->nama_produk;
            $jurnal->debet = $nominal;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            // Persediaan Musawamah
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  $unit->kode_toko; 
            $jurnal->kode_transaksi = $produk->kode_transaksi;
            $jurnal->kode_rekening = 1518000;
            $jurnal->tanggal_transaksi  = $tanggal;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'PPA Umum-Penyertaan ' . $produk->kode_produk . ' ' . $produk->nama_produk;
            $jurnal->debet = 0;
            $jurnal->kredit = $nominal;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            
            
        }catch(\Exceeption $e) {

            return back()->with(['error' => $e->getmessage()]);
        
        }
         
        return back()->with(['success' => 'Proses Write Off Berhasil !']);

    }

    public function store(Request $request){

        try {
            
            $kode_produk = $request->kode;
            $jumlah = $request->jumlah;
            $unit = DB::table('branch')->where('kode_toko',Auth::user()->unit)->first();
            $expired = $request->expired_date;
            
            $uuid=Uuid::uuid4()->getHex();
            $rndm=substr($uuid,25);
            $kode_rndm="WO/-".$unit->kode_toko.$rndm;

            $tanggal = date('Y-m-d');
            // mengurangi stok
            $produk = Produk::where('kode_produk',$kode_produk)->where('unit',$unit->kode_toko)->first();

            $produk_expired = ProdukDetail::where('kode_produk',$kode_produk)
            ->where('unit',Auth::user()->unit)->where('expired_date',$expired)->first();

            if ($produk_expired == null) {
                return back()->with(['error' => 'Produk '. $produk->kode_produk .' '. $produk->nama_produk . ' Tidak Ada Expired ' . $expired]);
            }
            if ($produk_expired->stok_detail < $jumlah) {
                return back()->with(['error' => 'Stock detail '. $produk->kode_produk .' '. $produk->nama_produk . ' Kurang']);
            }
            
            if ($produk < $jumlah) {
                return back()->with(['error' => 'Stock '. $produk->kode_produk .' '. $produk->nama_produk . ' Kurang']);
            }else {
                
                $produk_expired->stok_detail -= $jumlah;
                $produk_expired->update();

                $produk->stok -= $jumlah;
                $produk->update();

            }

            $nominal = $jumlah * $produk_expired->harga_beli;

            $produk_w0 = new ProdukWriteOff;
            $produk_w0->kode_produk = $produk->kode_produk;
            $produk_w0->kode_transaksi = $kode_rndm;
            $produk_w0->nama_produk = $produk->nama_produk;
            $produk_w0->harga_beli = $produk_expired->harga_beli;
            $produk_w0->harga_jual = $produk_expired->harga_jual_umum;
            $produk_w0->stok = $jumlah;
            $produk_w0->tanggal_wo = '';
            $produk_w0->tanggal_input = date('Y-m-d');
            $produk_w0->param_status= 1;
            $produk_w0->tanggal_expired = $expired;
            $produk_w0->unit = $unit->kode_toko;
            $produk_w0->harga_jual_member_insan = $produk_expired->harga_jual_insan;
            $produk_w0->harga_jual_insan = $produk_expired->harga_jual_insan;
            $produk_w0->harga_jual_pabrik = $produk_expired->harga_jual_insan;
            $produk_w0->save();

            
        }catch(\Exception $e){
         
            return back()->with(['error' => $e->getmessage()]);

        }


        return back()->with(['success' => 'Write Off ' . $produk->kode_produk . ' ' . $produk->nama_produk . ' Berhasil !' ]);

    }
    
}






















