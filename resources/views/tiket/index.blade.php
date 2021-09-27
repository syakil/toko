@extends('layouts.app')

@section('content-header')
    Tiket
@endsection


@section('content')
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#opentiket">
  Open Tiket
</button>

<div class="box">    
    <div class="box-body">
        <table class="table table-tiket">
            <thead>
                <tr>
                    <th scope="col">No Tiket</th>
                    <th scope="col">UserId</th>
                    <th scope="col">CIF</th>
                    <th scope="col">Tanggal Transaksi</th>
                    <th scope="col">Jenis Transaksi</th>
                    <th scope="col">Kode Transaksi</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">FU Helpesk</th>
                    <th scope="col">Status Tiket</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                
             </tbody>
        </table>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->

@include('tiket.form')
@if ($errors->any())
  <script>
    var pesan = "{{$errors->first()}}"
    swal("Maaf !", pesan, "error"); 
  </script>
@elseif ($message = Session::get('success'))
<script>
  var pesan = "{{$message}}"
  swal("Selamat !", pesan, "success"); 
</script>
@elseif ($message = Session::get('error'))
<script>
  var pesan = "{{$message}}"
  swal("Maaf !", pesan, "error"); 
</script>
@endif
@endsection


@section('script')
<script>
    $('body').addClass('sidebar-collapse');
</script>
<script type="text/javascript">
var table, save_method, table1;

  var url = "{{ route('tiket.data') }}";
   table = $('.table-tiket').DataTable({
     "serverside" : true,
     dom: 'Bfrtip',
        buttons: [
        ],
     "ajax" : {
       "url" : url,
       "type" : "GET"
     }

   });
   $('div.dataTables_filter input').focus(); 

  function done(id){
    
    swal({
    title: "Anda Yakin?",
    text: "Tiket Yang sudah diselesaikan tidak akan dapat dirubah kembali!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          url = "{{route('tiket.done',':id')}}"
          url = url.replace(':id',id);
          window.location.href = url;
        } else {
          swal("Tiket Anda Masih Dalam Proses!");
        }
      });
  }
</script>
@endsection