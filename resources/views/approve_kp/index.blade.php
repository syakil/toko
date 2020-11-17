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
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body"> 
                <form action="{{ route('approve_kp.store') }}" method="post">
                {{ csrf_field() }}
            <table class="table table-striped" id="eample">
            <input type="checkbox" name="select-all" id="select-all" class="checkbox"> Pilih Semua
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
                @php $no =1 ; @endphp
                    @foreach ($produk as $p)
                    <tr>
                        <td><input type="checkbox" name="kode[]" id="kode" value="{{$p->id_produk_so}}" ></td>
                        <td>{{$no++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->tanggal_so}}</td>
                        <td>{{$p->stok}}</td>
                        <td>{{$p->so}}</td>
                        <td>{{$p->nama_toko}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
                <button type="submit" class="btn btn-danger">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- /.content -->

@endsection

@section('script')


<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>


<script>
$(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
} );
</script>

<script language="JavaScript">
$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;                        
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;                       
        });
    }
});
</script>

@endsection
