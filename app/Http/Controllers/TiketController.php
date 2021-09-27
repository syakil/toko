<?php

namespace App\Http\Controllers;

use App\JenisTransaksi;
use App\Tiket;
use Auth;
use Ramsey\Uuid\Uuid;
use DB;
use Illuminate\Http\Request;

class TiketController extends Controller
{    
    /**
     * index
     *
     * @return void
     */
    public function index(){

        $jenis_transaksi = JenisTransaksi::where('kode_transaksi','like','B%')->get();
        return view('tiket.index',compact('jenis_transaksi'));
    }

    public function data(){

        $tiket = Tiket::select('tiket.*','jenis_transaksi.keterangan_transaksi','users.name')->leftJoin('users','users.id','tiket.user_id')->leftJoin('jenis_transaksi','jenis_transaksi.kode_transaksi','tiket.jenis_transaksi')->get();

        $no = 0;
        $data = array();
        foreach($tiket as $list){

            $row = array();
            $row[] = $list->no_tiket;
            $row[] = $list->name;
            $row[] = $list->cif;
            $row[] = $list->tanggal_transaksi;
            $row[] = $list->keterangan_transaksi;
            $row[] = $list->kode_transaksi;
            $row[] = $list->keterangan;
            $row[] = $list->fu_helpdesk == 'done'? '<span class="label label-success">Done</span>' : '<span class="label label-danger">Pending</span>';
            $row[] = $list->status_tiket == 'done'? '<span class="label label-success">Done</span>' : '<span class="label label-danger">Pending</span>';
            $row[] = $list->status_tiket == 'done'? '<div class="btn-group">
            <a class="btn btn-success" disabled role="button"><i class="fa fa-check"></i>&ensp; Done</a>
            </div>' :  '<div class="btn-group">
            <a class="btn btn-success" onClick="done('.$list->id.')" role="button"><i class="fa fa-check"></i>&ensp; Done</a>
            </div>';
            $data[] = $row;
            
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function store(Request $request){

        $kode = Uuid::uuid4()->getHex();
        $kode_t = substr($kode,25);
        $unit = Auth::user()->unit;
        $kode_t="NI/-".$unit.$kode_t;
        $this->validate($request, [
            'kode_transaksi' => 'required',
        ]);
        
        try {
            
            DB::beginTransaction();

            $tiket = Tiket::create([
                'no_tiket' => $kode_t,
                'user_id' => Auth::user()->id,
                'cif' => $request->cif,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'kode_transaksi' => $request->kode_transaksi,
                'jenis_transaksi' => $request->jenis_transaksi,
                'keterangan' => $request->keterangan,
                'fu_helpdesk' => 'pending',
                'status_tiket' => 'pending'
            ]);

            DB::commit();
            return back()->with(["success" => "Tiket Berhasil Di Buat!"]);

        } catch (\QueryException $e) {
        
            DB::rollback();
            return back()->with(["error" => $e->getMessage()]);
        
        }
        

    }

    public function done($id){
        
        $tiket = Tiket::findOrFail($id);
        
        if($tiket->fu_helpdesk === "pending"){
            
            return back()->with(["error" => "Tiket Anda Sedang Di Proses Helpdesk!"]);
        
        }else{

            $tiket->update(['status_tiket' => 'done']);
            return back()->with(["success" => "Tiket Anda Berhasil Diselesaikan!"]);
        }

    }

}
