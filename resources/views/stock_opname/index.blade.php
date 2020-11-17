@extends('layouts.app')

@section('title')
  Stok Opname
@endsection

@section('breadcrumb')
   @parent
   <li>stok_opname</li>
@endsection

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>

<style>

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type=number] {
    -moz-appearance:textfield; /* Firefox */
}

</style>

@endsection

@section('content')     
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-body">
                
            <form class="form form-horizontal form-produk">
                <div class="form-group">
                    <label class="col-md-2 control-label" for="kode">Kode Produk</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" class="form-control" id="kode" name="kode" autofocus required>
                            <span class="input-group-btn">
                                <button onclick="showProduct()" type="button" class="btn btn-info show-produk">...</button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="kode_produk">Kode Produk</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="kode_produk" name="kode_produk" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="nama">Nama Produk</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="nama" name="nama" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="stok">Total Stock</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control no-border" id="stok" name="stok" readonly>
                        </div>
                    </div>
                </div>
                
            </form>


            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
        <div class="box-header">
        
        <button type="button" class="btn btn-primary tambah-detail" data-toggle="modal" data-target="#modal-tambah" disabled>
            Tambah Detail Produk
        </button>
        
        </div>
        
        <div class="box-body box-detail">
        <form class="form-keranjang">
        {{ csrf_field() }} {{ method_field('PATCH') }}
        <table class="table table-striped tabel-detail" >
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Stock</th>
                        <th>Expired</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                </tbody>
            </table>
            </form>
        </div>

        <div class="box-footer">
            <button class="btn btn-danger pull-right simpan" disabled onclick="simpanData()">Simpan</button>
        </div>

        </div>
    </div>
</div>


@include('stock_opname.produk')
@include('stock_opname.tambah')
@endsection

@section('script')

<script type="text/javascript">
// listdata detail produk
var table;
function getDetail(kode){

};

// tambah produk
function addItem(){
   $.ajax({
       url:"{{ route('stock_opname.store')}}",
       type: "POST",
       data: $('.form-tambah').serialize(),
       success: function(data){
           $('#modal-tambah').modal('hide');
           alert('data berhasil ditambahkan');
           table.ajax.reload();
            $('.simpan').attr('disabled',false)        
            $('.show-produk').attr("disabled", true);            
            $('#kode').attr('readonly',true);  
       },
       error : function(){
           alert("Tidak dapa menyimpad data!");
           $('#modal-tambah').modal('hide');
           table.ajax.reload();
       }
   }); 
}

// ketika produk dipilih
function selectItem(kode){
    $('#so-modal').modal('hide');
    $('.tambah-detail').attr('disabled',false);
}

// datatable di produk.blade.php
$(document).ready(function() {
    $('.tabel-produk').DataTable();
} );

// nampilin data 
function showProduct(){
    $('#so-modal').modal('show');
    $('.tabel-detail').DataTable().destroy();
    $('#nama').val('');
    $('#kode_produk').val('');
    $('#stok').val('');
    $('#kode').val('');
}

//menghapus detail produk
function deleteItem(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "stock_opname/"+id,
       type : "POST",
       data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
       success : function(data){
            alert("Data berhasil di hapus");
            table.ajax.reload();
            $('.simpan').attr('disabled',false);
            $('.show-produk').attr("disabled", true);            
            $('#kode').attr('readonly',true);
       },
       error : function(){
            alert("Tidak dapat menghapus data!");
       }
     });
   }
}


// pilih item

$('.kode').on('click',function(){
    const id = $(this).data('id');
    
    $.ajax({
        url: 'stock_opname/get/'+id,
        data:{id : id},
        method: 'get',
        dataType: 'json',
        success: function(data){
            $('#nama').val(data.nama_produk);
            $('#kode_produk').val(data.kode_produk);
            $('#kode').val(id);
            $('#stok').val(data.stok);
            $('#kode_produk_create').val(data.kode_produk);
            $('#nama_produk_create').val(data.nama_produk);            
            $('.tambah-detail').attr('disabled',false);
        },
        error: function(){
            alert("Produk Tidak Ada !");
        }
    });
    
    $(function(){
        table = $('.tabel-detail').DataTable({
            "dom" : 'Brt',
            "bSort" : false,
            "processing" : true,
            "servrSide" : true,
            "paging": false,
            "ajax" : {
            "url" : "stock_opname/data/"+ id,
            "type" : "GET"
            }
        })    
    });
})


$('#kode').on('keypress',function(e){
    if(e.which == 13) {
        const id = $('#kode').val();
        // table.destroy();
        $.ajax({
            url: 'stock_opname/get/'+id,
            data:{id : id},
            method: 'get',
            dataType: 'json',
            success: function(data){
                table.destroy();
                $('#nama').val(data.nama_produk);
                $('#kode_produk').val(data.kode_produk);
                $('#stok').val(data.stok);
                $('#kode_produk_create').val(data.kode_produk);
                $('#nama_produk_create').val(data.nama_produk);
                $('.tambah-detail').attr('disabled',false);
            },
            error: function(){
                alert("Produk Tidak Ada !");
            }
        });
        
        $(function(){
            table = $('.tabel-detail').DataTable({
                "dom" : 'Brt',
                "bSort" : false,
                "processing" : true,
                "servrSide" : true,
                "paging": false,
                "ajax" : {
                "url" : "stock_opname/data/"+ id,
                "type" : "GET"
                }
            })    
        });
    }
})


// ubah data stok_detail dan expired date
function changeCount(id){
     $.ajax({
        url : "stock_opname/"+id,
        type : "POST",
        data : $('.form-keranjang').serialize(),
        success : function(data){
            alert("Data berhasil ubah");
            $('.simpan').attr('disabled',false);
            $('.show-produk').attr("disabled", true);            
            $('#kode').attr('readonly',true);  
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
        }   
     });

     $('.form-keranjang').submit(function(){
     return false;
   });
}

// simpan data
function simpanData(){
    kode = $('#kode').val();
    if(confirm("Data Akan Di Simpan?")){
        

        var jumlah_detail = 0;
        // mengambil data jumlah detail produk
        $('.jumlah').each(function(){
            jumlah_detail += parseFloat(this.value);
        })

        var jumlah_stok = $('#stok').val();
        
        if(jumlah_detail != jumlah_stok){
                if(confirm("Data tidak sama, Lanjutkan?")){
                    $.ajax({
                        url: "stock_opname/simpan_/"+kode,
                        type: "GET",
                        data: kode,
                        success: function(data){
                            alert('Data Berhasil Disimpan');
                            $('.simpan').attr('disabled',true);
                            $('.show-produk').attr("disabled", false);
                            $('#kode').attr('readonly',false);      
                            $('.tambah-detail').attr('disabled',true);
                            $('#nama').val('');
                            $('#kode_produk').val('');
                            $('#stok').val('');
                            $('#kode').val('');
                            table.clear();
                            table.destroy();
                        },
                        error: function(){
                            alert('Data Gagal Di Simpan')
                        }
                    }) 
                }   
            }else{

                $.ajax({
                        url: "stock_opname/simpan_/"+kode,
                        type: "GET",
                        data: kode,
                        success: function(data){
                            alert('Data Berhasil Di Simpan');  
                            $('.simpan').attr('disabled',true);
                            $('.show-produk').attr("disabled", false);
                            $('#kode').attr('readonly',false);
                            $('.tambah-detail').attr('disabled',true);
                            $('#nama').val('');
                            $('#kode_produk').val('');
                            $('#stok').val('');
                            $('#kode').val(''); 
                            table.clear();
                            table.destroy();
                        },
                        error: function(){
                            alert('Data Gagal Di Simpan')
                        }
                })
            }
    }
    
}
</script>





@endsection
