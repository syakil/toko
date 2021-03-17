@extends('layouts.app')

@section('title')
  Daftar Parameter
@endsection

@section('breadcrumb')
   @parent
   <li>user</li>
@endsection

@section('content')     

@if ($message = Session::get('error'))
    <script>
    var pesan = "{{$message}}"
    swal("Maaf !", pesan, "error"); 
    </script>
@elseif ($message = Session::get('success'))
    <script>
    var pesan = "{{$message}}"
    swal("Selamat !", pesan, "success"); 
    </script>
@endif

<div class="row">
  <div class="col-xs-6">
    <div class="box">
      
      <div class="box-header">
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Tambah</a>
      </div>

      <div class="box-body">  
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Tenor</th>
              <th>Kenaikan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </div>
</div>
@include('param_kenaikan.form')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
   table = $('.table').DataTable({
     "processing" : true,
     "ajax" : {
       "url" : "{{ route('param_kenaikan.data') }}",
       "type" : "GET"
     }
   }); 
   
   $('#modal-form form').validator().on('submit', function(e){
      if(!e.isDefaultPrevented()){
         var id = $('#id').val();
         if(save_method == "add") url = "{{ route('param_kenaikan.store') }}";
         else url = "{{route('param_kenaikan.edit',':id')}}"
              url = url.replace(':id',id);
         
         $.ajax({
           url : url,
           type : "POST",
           data : $('#modal-form form').serialize(),
           success : function(data){
             $('#modal-form').modal('hide');
             table.ajax.reload();
           },
           error : function(){
             alert("Tidak dapat menyimpan data!");
           }   
         });
         return false;
     }
   });
});
function addForm(){
   save_method = "add";
   url = "{{ route('param_kenaikan.store') }}";
   $('#form-param').attr('action', url);
   $('input[name=_method]').val('POST');
   $('#modal-form').modal('show');
   
   $('#pekan').prop("disabled", false);
   $('#modal-form form')[0].reset();            
   $('.modal-title').text('Tambah Parameter');
   $('#password, #password1').attr('required', true);
}
function editForm(id){
  save_method = "edit";
  $('input[name=_method]').val('POST');
  $('#modal-form form')[0].reset();
  url = "{{route('param_kenaikan.edit',':id')}}"
  url = url.replace(':id',id);
  $.ajax({
    url : url,
    type : "GET",
    dataType : "JSON",
    success : function(data){
      $('#modal-form').modal('show');
      $('.modal-title').text('Edit Parameter');
      url_edit = "{{route('param_kenaikan.update',':id')}}"
      url_edit = url_edit.replace(':id',id);  
       
      $('#id').val(data.id_param);
      $('#form-param').attr('action', url_edit);
      $('#pekan').val(data.pekan);
      $('#pekan').prop("disabled", true);
      $('#kenaikan').val(data.kenaikan);
       
    },
    error : function(){
      alert("Tidak dapat menampilkan data!");
    }
  });
}
function deleteData(id){
  if(confirm("Apakah yakin data akan dihapus?")){
    url = "{{route('param_kenaikan.delete',':id')}}"
    url = url.replace(':id',id);
  
    $.ajax({
      url : url,
      type : "GET",
      success : function(data){
        swal("Selamat !", 'Paramater Berhasil Di Hapus!', "success"); 
        table.ajax.reload();
      },
      error : function(){
        swal("Maaf !", "Transaksi Error!", "error"); 
      }
    });
  }
}
</script>
@endsection