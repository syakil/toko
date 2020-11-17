@extends('layouts.app')

@section('title')
  Daftar Produk
@endsection

@section('header')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection


@section('breadcrumb')
   @parent
   <li>produk</li>
@endsection

@section('content')

<div class="row">
<div class="col-md-3">
          <div class="box box-default collapsed-box">
            <div class="box-header with-border">

            <label for="exampleFormControlSelect1">Pilih Toko</label>
                <select class="form-control" id="toko" onclick="pilihUnit();">
                    <option value="">pilih toko</option>
            @foreach($unit as $id)
                    <option value="{{$id->kode_toko}}">{{$id->nama_toko}}</option>
            @endforeach
                </select>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>

  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
            <input type="checkbox" name="select-all" id="select-all" class="checkbox"> Pilih Semua
      </div>
      <div class="box-body"> 
                <form action="{{ route('approve_gudang.store') }}" method="post">
                {{ csrf_field() }}
            <table class="table table-striped tabel-so">
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Tanggal SO</th>
                        <th>Stock Sistem</th>
                        <th>Stock Sebenarnya</th>
                        <th>Nama Gudang</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
                <button type="submit" class="btn btn-danger pull-right approve" disabled>Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- /.content -->

@endsection

@section('script')



<script language="JavaScript">
var table = $('table-so');
$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;
            $(".approve").attr("disabled",false);                        
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;
            $(".approve").attr("disabled",true);                       
        });
    }
});



function pilihUnit(){
    $('.tabel-so').DataTable().destroy();
}

$(document).ready(function(){
    $("select#toko").change(function(){
        
        var unit = $(this).children("option:selected").val();
        getData(unit);
    });
});



function getData(unit) {
var table;
$(function(){
    table = $('.tabel-so').DataTable({
        "processing" : true,
        "paging" : true,
dom: 'Bfrtip',
        "serverside" : true,
        "reload":true,
        "ajax" : {
        "url" : "approve_gudang/data/" + unit,
        "type" : "GET"
            }
    });
});
}
var n;

function check(){
    n = $("input:checked").length;

    if (n >= 1) {
        $(".approve").attr("disabled",false);
    }else{
        $(".approve").attr("disabled",true);
    }
}


</script>



@endsection
