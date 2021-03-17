<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParamKenaikan;

class ParamKenaikanController extends Controller
{
    public function index(){

        return view('param_kenaikan/index');

    }

    public function edit($id){

        $data = ParamKenaikan::find($id);
        echo json_encode($data);

    }



    public function listData(){

        $data_kenaikan = ParamKenaikan::get();

        $data = array();

        foreach ($data_kenaikan as $list) {

            $row = array();
            $row[] = $list->pekan;
            $row[] = $list->kenaikan . '%';
            $row[] = '<div class="btn-group">
                <a onclick="editForm('.$list->id_param.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>
                <a onclick="deleteData('.$list->id_param.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                </div>';;
            $data[] = $row;

        }
  
        $output = array("data" => $data);
        return response()->json($output);     

    }


    public function update(Request $request,$id){

        $data_kenaikan = ParamKenaikan::find($id);

        $data_kenaikan->kenaikan = $request->kenaikan;
        $data_kenaikan->update();
        
        return back()->with(['success' => 'Parameter Berhasil Di Ubah !']);
    }

    public function store(Request $request){

        $data_kenaikan = new ParamKenaikan;
        $data_kenaikan->pekan = $request->pekan;
        $data_kenaikan->kenaikan = $request->kenaikan;
        $data_kenaikan->save();

        return back()->with(['success' => 'Parameter Berhasil Di Tambah !']);

    }

    public function delete($id){

        $param = ParamKenaikan::find($id);
        $param->delete();

        
    }   

}
