<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/home', 'HomeController@index')->name('home');

Route::post('/home/store', 'HomeController@store')->name('home.store');
Route::post('/home/update/{id}', 'HomeController@update')->name('home.update');
Route::get('/logout', 'Auth\LoginController@logout');

Route::get('/ganti_password/index', 'GantiPasswordController@index')->name('ganti_password.index');
Route::post('/ganti_password/update', 'GantiPasswordController@update')->name('ganti_password.update');
Route::get('/ganti_password/reset/{id}', 'GantiPasswordController@reset')->name('ganti_password.reset');

Auth::routes();

Route::group(['middleware' => ['web', 'cekuser:2']], function(){
// saldo simpanan
   Route::get('saldo_titipan/index', 'SaldoTitipanController@index')->name('saldo_titipan.index');
   Route::get('saldo_titipan/getData/{member}', 'SaldoTitipanController@getData')->name('saldo_titipan.getData');
   Route::get('saldo_titipan/getTitipan/{member}', 'SaldoTitipanController@getTitipan')->name('saldo_titipan.getTitipan');
   Route::get('saldo_titipan/listDetail/{member}', 'SaldoTitipanController@listDetail')->name('saldo_titipan.listDetail');
   Route::resource('saldo_titipan', 'SaldoTitipanController');

   // angsuran
   Route::get('angsuran/index', 'AngsuranController@index')->name('angsuran.index');
   Route::get('angsuran/member/{id}', 'AngsuranController@getMember')->name('angsuran.getMember');
   Route::get('angsuran/listTransaksi/{id}', 'AngsuranController@listTransaksi')->name('angsuran.listTransaksi');
   Route::get('angsuran/listTransaksiKelompok/{id}', 'AngsuranController@listTransaksiKelompok')->name('angsuran.listTransaksiKelompok');
   Route::post('angsuran/addTransaksi/', 'AngsuranController@addTransaksi')->name('angsuran.addTransaksi');
   Route::post('angsuran/store_kelompok/','AngsuranController@store_kelompok')->name('angsuran.store_kelompok');

   // cek harga
   Route::get('cek_harga/index', 'CekHargaController@index')->name('cek_harga.index');
   Route::get('cek_harga/data', 'CekHargaController@listData')->name('cek_harga.data');
   Route::resource('cek_harga', 'CekHargaController');

   // // sotck toko
   // Route::get('stock_toko/index','StockTokoController@index')->name('stockToko.index');
   // Route::get('stock_toko/detail/{id}','StockTokoController@detail')->name('stockToko.detail');
   // Route::get('stock_toko/listData', 'StockTokoController@listData')->name('stockToko.data');
   // Route::put('stock_toko/store/', 'StockTokoController@store')->name('stockToko.store');
   // Route::get('stock_toko/listData', 'StockTokoController@listData')->name('stockToko.data');
   // Route::get('stock_toko/delete/{id}','StockTokoController@delete')->name('stockToko.delete');
   
   // Route::resource('stockToko', 'StockTokoController');



   Route::get('user/profil', 'UserController@profil')->name('user.profil');
   Route::patch('user/{id}/change', 'UserController@changeProfil');

   Route::get('transaksi/menu', 'PenjualanDetailController@NewMenu')->name('transaksi.menu');
   Route::get('transaksi/baru', 'PenjualanDetailController@newSession')->name('transaksi.new');
   Route::get('transaksi/{id}/data', 'PenjualanDetailController@listData')->name('transaksi.data');
   Route::get('transaksi/cetaknota', 'PenjualanDetailController@printNota')->name('transaksi.cetak');
   Route::get('transaksi/notapdf', 'PenjualanDetailController@notaPDF')->name('transaksi.pdf');
   Route::post('transaksi/simpan', 'PenjualanDetailController@saveData');
   Route::get('transaksi/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailController@loadForm');
   Route::resource('transaksi', 'PenjualanDetailController');

   
   //harga member insan
   Route::get('memberinsan/menu', 'PenjualanDetailMemberInsanController@NewMenu')->name('memberinsan.menu');
   
   Route::post('memberinsan/baru', 'PenjualanDetailMemberInsanController@newSession')->name('memberinsan.new');
   Route::post('memberinsan/pin_baru', 'PenjualanDetailMemberInsanController@newPin')->name('memberinsan.new_pin');

   Route::get('memberinsan/{id}/data', 'PenjualanDetailMemberInsanController@listData')->name('memberinsan.data');
   Route::get('memberinsan/cetaknota', 'PenjualanDetailMemberInsanController@printNota')->name('memberinsan.cetak');
   Route::get('memberinsan/notapdf', 'PenjualanDetailMemberInsanController@notaPDF')->name('memberinsan.pdf');
   Route::post('memberinsan/simpan', 'PenjualanDetailMemberInsanController@saveData');
   Route::get('memberinsan/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberInsanController@loadForm');
route::get('member/check/{id}','PenjualanDetailMemberInsanController@checkPin')->name('member.check');
   
   Route::resource('memberinsan', 'PenjualanDetailMemberInsanController');

   //harga member pbarik
   Route::get('memberpabrik/menu', 'PenjualanDetailMemberPabrikController@NewMenu')->name('memberpabrik.menu');
   
   Route::post('memberpabrik/baru', 'PenjualanDetailMemberPabrikController@newSession')->name('memberpabrik.new');
   Route::post('memberpabrik/pin_baru', 'PenjualanDetailMemberPabrikController@newPin')->name('memberpabrik.new_pin');
   Route::get('memberpabrik/{id}/data', 'PenjualanDetailMemberPabrikController@listData')->name('memberpabrik.data');
   Route::get('memberpabrik/cetaknota', 'PenjualanDetailMemberPabrikController@printNota')->name('memberpabrik.cetak');
   Route::get('memberpabrik/notapdf', 'PenjualanDetailMemberPabrikController@notaPDF')->name('memberpabrik.pdf');
   Route::post('memberpabrik/simpan', 'PenjualanDetailMemberPabrikController@saveData');
   Route::get('memberpabrik/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberPabrikController@loadForm');
   Route::resource('memberpabrik', 'PenjualanDetailMemberPabrikController');

    //harga cash insan
    Route::get('cashinsan/menu', 'PenjualanDetailCashInsanController@NewMenu')->name('cashinsan.menu');
     Route::get('cashinsan/baru', 'PenjualanDetailCashInsanController@newSession')->name('cashinsan.new');
     Route::get('cashinsan/{id}/data', 'PenjualanDetailCashInsanController@listData')->name('cashinsan.data');
     Route::get('cashinsan/cetaknota', 'PenjualanDetailCashInsanController@printNota')->name('cashinsan.cetak');
     Route::get('cashinsan/notapdf', 'PenjualanDetailCashInsanController@notaPDF')->name('cashinsan.pdf');
     Route::post('cashinsan/simpan', 'PenjualanDetailCashInsanController@saveData');
     Route::get('cashinsan/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailCashInsanController@loadForm');
     Route::resource('cashinsan', 'PenjualanDetailCashInsanController');

    //harga cash Pabrik
  
   Route::get('umum/baru', 'PenjualanDetailMemberPabrikController@newSession')->name('umum.new');
   Route::get('umum/{id}/data', 'PenjualanDetailMemberPabrikController@listData')->name('umum.data');
   Route::get('umum/cetaknota', 'PenjualanDetailMemberPabrikController@printNota')->name('umum.cetak');
   Route::get('umum/notapdf', 'PenjualanDetailMemberPabrikController@notaPDF')->name('umum.pdf');
   Route::post('umum/simpan', 'PenjualanDetailMemberPabrikController@saveData');
   Route::get('umum/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberPabrikController@loadForm');
   Route::resource('umum', 'PenjualanDetailMemberPabrikController');

   //kasa
   Route::get('kasa/data', 'KasaController@listData')->name('kasa.data');
   Route::post('kasa/printeod', 'KasaController@printKasa')->name('kasa.cetak');
   Route::resource('kasa', 'KasaController');

   Route::get('pengeluaran/data', 'PengeluaranController@listData')->name('pengeluaran.data');
   Route::resource('pengeluaran', 'PengeluaranController');

   Route::get('musawamahdetail/data', 'MusawamahDetailController@listData')->name('musawamahdetail.data');
   Route::post('musawamahdetail/cetak', 'MusawamahDetailController@printCard');
   Route::resource('musawamahdetail', 'MusawamahDetailController');


   
});

   
   Route::group(['middleware' => ['web', 'cekuser:1' ]], function(){
      Route::get('kategori/data', 'KategoriController@listData')->name('kategori.data');
      Route::resource('kategori', 'KategoriController');
      Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
      Route::post('produk/hapus', 'ProdukController@deleteSelected');
      Route::post('produk/cetak', 'ProdukController@printBarcode');
      Route::resource('produk', 'ProdukController');
      Route::get('supplier/data', 'SupplierController@listData')->name('supplier.data');
      Route::resource('supplier', 'SupplierController');
      Route::get('member/data', 'MemberController@listData')->name('member.data');
      Route::post('member/cetak', 'MemberController@printCard');
      Route::resource('member', 'MemberController');

Route::get('write_off/index_admin', 'WriteOffController@index_admin')->name('write_off.index_admin');
      Route::get('write_off/proses/{id}', 'WriteOffController@proses')->name('write_off.proses');
      Route::get('write_off/list_admin', 'WriteOffController@listAdmin')->name('write_off.listAdmin');

      Route::get('write_off/index_approve/', 'WriteOffController@index_approve')->name('write_off.index_approve');
      Route::get('write_off/approve_list/', 'WriteOffController@listApprove')->name('write_off.listApprove');
      Route::post('write_off/approve', 'WriteOffController@approve')->name('write_off.approve_proses');
      
      
      Route::get('write_off/index_report', 'WriteOffController@index_report')->name('write_off.index_report');
      Route::get('write_off/print_surat/{id}', 'WriteOffController@print_surat')->name('write_off.print_surat');
      Route::get('write_off/list_report', 'WriteOffController@listReport')->name('write_off.listReport');
      Route::get('write_off/file/{id}', 'WriteOffController@file')->name('write_off.file');
      Route::get('write_off/detail_report/{id}', 'WriteOffController@detailReport')->name('write_off.detailReport');


// report pembelian
      Route::get('report_pembelian/index', 'ReportPembelianController@index')->name('report_pembelian.index');
      Route::get('report_pembelian/data', 'ReportPembelianController@listData')->name('report_pembelian.data');
      Route::get('report_pembelian/detail/{id}', 'ReportPembelianController@detail')->name('report_pembelian.detail');
      Route::get('report_pembelian/detail/data/{id}','ReportPembelianController@listDetail')->name('report_pembelian.data_detail');
      Route::resource('report_pembelian', 'ReportPembelianController');

   
      Route::get('penjualan/index', 'PenjualanController@index')->name('penjualan.index');
      route::get('penjualan/data/{awal}/{akhir}','PenjualanController@listData')->name('penjualan.data');
      Route::get('penjualan/detail', 'PenjualanController@detail')->name('penjualan.detail');
      route::get('penjualan/data_detail/{awal}/{akhir}','PenjualanController@listDetail')->name('penjualan.data_detail');
      Route::resource('penjualan', 'PenjualanController');      


      Route::get('laporan', 'LaporanController@index')->name('laporan.index');
      Route::post('laporan', 'LaporanController@refresh')->name('laporan.refresh');
      Route::get('laporan/data/{awal}/{akhir}', 'LaporanController@listData')->name('laporan.data'); 
      Route::get('laporan/pdf/{awal}/{akhir}', 'LaporanController@exportPDF');
   
      Route::resource('setting', 'SettingController');

      // controller menu pembelian di user admin
      Route::get('pembelian_admin/index','PembelianAdminController@index')->name('pembelian.admin');
      Route::get('pembelian_admin/detail/{id}','PembelianAdminController@detail')->name('pembelian.admin_detail');
      Route::post('pembelian_admin/store_jurnal','PembelianAdminController@store_jurnal')->name('pembelian.update_jurnal');
      Route::get('pembelian_admin/jurnal/{id}','PembelianAdminController@jurnal')->name('pembelian.jurnal');
      Route::get('pembelian_admin/cetak/{id}','PembelianAdminController@cetak_po')->name('pembelian.cetak_po');
      Route::get('pembelian_admin/fpd/{id}','PembelianAdminController@cetak_fpd')->name('pembelian.cetak_fpd');
      Route::post('pembelian_admin/simpan','PembelianAdminController@simpan')->name('pembelian.simpan');

      // controller menu jurnal di user admin
      Route::get('jurnal_umum_admin/index', 'JurnalUmumAdminController@index')->name('jurnal_umum_admin.index');
      Route::post('jurnal_umum_admin/create','JurnalUmumAdminController@create')->name('jurnal_umum_admin.create');
      Route::get('jurnal_umum_admin/destroy/{id}', 'JurnalUmumAdminController@destroy')->name('jurnal_umum_admin.destroy');
      Route::get('jurnal_umum_admin/approve', 'JurnalUmumAdminController@approve')->name('jurnal_umum_admin.approve');
      Route::post('jurnal_umum_admin/autocomplete', 'JurnalUmumAdminController@autocomplete')->name('jurnal_umum_admin.autocomplete');
      
      // pricing
      Route::get('pricing/tambah', 'PricingController@tambah')->name('pricing.tambah');
      Route::post('pricing/add', 'PricingController@add')->name('pricing.add');
      Route::get('pricing/index', 'PricingController@index')->name('pricing.index');
      Route::get('pricing/data', 'PricingController@listData')->name('pricing.data');
      Route::get('pricing/edit/{id}', 'PricingController@edit')->name('pricing.edit');
      Route::post('pricing/update/{id}', 'PricingController@update')->name('pricing.update');
Route::get('pricing/promo/{id}', 'PricingController@tambah_promo')->name('pricing.promo');
      Route::post('pricing/update_promo', 'PricingController@update_promo')->name('pricing.update_promo');
      Route::put('pricing/update_margin/', 'PricingController@show')->name('pricing.margin');
      
      Route::resource('pricing', 'PricingController');


      // laporan Musawamah
      Route::get('laporan/muswamah','LaporanMusawamahController@index')->name('muswamah.index');
      Route::get('muswamah/listData','LaporanMusawamahController@listData')->name('musawamah.listData');
      Route::resource('musawamah','LaporanMusawamahController');

  // approval
      Route::get('approve_admin/index', 'ApprovalAdminController@index')->name('approve_admin.index');
      Route::put('approve_admin/store', 'ApprovalAdminController@store')->name('approve_admin.store');
      Route::get('approve_admin/data', 'ApprovalAdminController@listData')->name('approve_admin.data');
      Route::resource('approve_admin', 'ApprovalAdminController');

      //invoice
      Route::get('invoice/index', 'InvoiceController@index')->name('invoice.index');
      Route::get('invoice/data', 'InvoiceController@listData')->name('invoice.data');
      Route::get('invoice/detail/{id}', 'InvoiceController@detail')->name('invoice.detail');
      Route::get('invoice/data/diskon/{id}', 'InvoiceController@listDiskonLainya')->name('invoice.listDiskonLainya');
      Route::get('invoice/data/detail/{id}', 'InvoiceController@listDetail')->name('invoice.listDetail');
      Route::get('invoice/data/spesial_diskon/{id}', 'InvoiceController@listSpesialDiskon')->name('invoice.listSpesial');
      Route::post('invoice/data/addspesial', 'InvoiceController@add_spesial_diskon')->name('invoice.addSpesial');
      Route::get('invoice/delete/spesial/{id}', 'InvoiceController@delete_spesial_diskon')->name('invoice.deleteSpesial');
      Route::post('invoice/update/spesial/{id}','InvoiceController@update_spesial_diskon')->name('invoice.updatespesial');
      Route::post('invoice/update/spesial_2/{id}','InvoiceController@update_spesial_diskon')->name('invoice.updatespesial_2');
      
      Route::post('invoice/update/invoice/{id}','InvoiceController@update_invoice')->name('invoice.updateinvoice');
      Route::post('invoice/update/regular_ppn/{id}','InvoiceController@update_regular_diskon_ppn')->name('invoice.updateregularppn');
      Route::post('invoice/update/regular/{id}','InvoiceController@update_regular_diskon')->name('invoice.updateregular');
      
      Route::post('invoice/add/diskon-lainya', 'InvoiceController@add_diskon_lainya')->name('invoice.addDiskonLainya');
      Route::get('invoice/delete/diskon/{id}', 'InvoiceController@delete_diskon_lainya')->name('invoice.deleteDiskonLainya');
      Route::get('invoice/proses/diskon/{id}', 'InvoiceController@perhitungan_diskon')->name('invoice.perhitungan');
      Route::post('invoice/simpan', 'InvoiceController@simpan')->name('invoice.simpan');
      Route::post('invoice/hitung/{id}', 'InvoiceController@hitung')->name('invoice.hitung');
      Route::resource('invoice', 'InvoiceController');
   
      // report kirim barang
      Route::get('report_kirim/index', 'ReportKirimBarangController@index')->name('report_kirim.index');
      Route::get('report_kirim/data', 'ReportKirimBarangController@listData')->name('report_kirim.data');
      Route::get('report_kirim/detail/{id}', 'ReportKirimBarangController@detail')->name('report_kirim.detail');
      Route::get('report_kirim/data/detail/{id}', 'ReportKirimBarangController@listDetail')->name('report_kirim.data_detail');
      Route::resource('report_kirim', 'ReportKirimBarangController');


Route::get('kasa_eod/eod', 'KasaController@eod')->name('kasa_eod.eod');
      Route::resource('kasa_eod', 'KasaController');
     
      
   });


   Route::group(['middleware' => ['web', 'cekuser:3' ]], function(){
   Route::get('kategori/data', 'KategoriController@listData')->name('kategori.data');
   Route::resource('kategori', 'KategoriController');

   Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
   Route::post('produk/hapus', 'ProdukController@deleteSelected');
   Route::post('produk/cetak', 'ProdukController@printBarcode');
   Route::resource('produk', 'ProdukController');

   Route::get('pembelian/data', 'PembelianController@listData')->name('pembelian.data');
Route::get('pembelian_detail/{id}/update_harga', 'PembelianDetailController@update_harga')->name('pembelian_detail.update_harga');
   
   Route::get('pembelian/{id}/tambah', 'PembelianController@create');
   Route::get('pembelian/{id}/lihat', 'PembelianController@show');
   Route::get('pembelian/{id}/poPDF', 'PembelianController@cetak');
   Route::resource('pembelian', 'PembelianController');   

   Route::get('pembelian_detail/{id}/data', 'PembelianDetailController@listData')->name('pembelian_detail.data');
   Route::get('pembelian_detail/loadform/{diskon}/{total}', 'PembelianDetailController@loadForm');
   Route::resource('pembelian_detail', 'PembelianDetailController');

   // riwayat pembelian
   Route::get('riwayat_stok/index', 'RiwayatStokController@index')->name('riwayat_stok.index');
   Route::get('riwayat_stok/data', 'RiwayatStokController@listData')->name('riwayat_stok.data');
   Route::resource('riwayat_stok', 'RiwayatStokController');   

   Route::get('supplier/data', 'SupplierController@listData')->name('supplier.data');
   Route::resource('supplier', 'SupplierController');

   // controller menu jurnal di user admin
   Route::get('jurnal_umum_po/index', 'JurnalUmumPoController@index')->name('jurnal_umum_po.index');
   Route::post('jurnal_umum_po/create','JurnalUmumPoController@create')->name('jurnal_umum_po.create');
   Route::get('jurnal_umum_po/destroy/{id}', 'JurnalUmumPoController@destroy')->name('jurnal_umum_po.destroy');
   Route::get('jurnal_umum_po/approve', 'JurnalUmumPoController@approve')->name('jurnal_umum_po.approve');
   Route::post('jurnal_umum_po/autocomplete', 'JurnalUmumPoController@autocomplete')->name('jurnal_umum_po.autocomplete');

 Route::get('koreksi/store', 'KoreksiPembelianController@store')->name('koreksi.store');
});


Route::group(['middleware' => ['web', 'cekuser:4' ]], function(){

// stock opname
   Route::get('stock_opname/index', 'StockOpnameGudangController@index')->name('stock_opname.index');
   Route::get('stock_opname/data/{id}', 'StockOpnameGudangController@listData')->name('stock_opname.data');
   route::get('stock_opname/get/{id}', 'StockOpnameGudangController@getData')->name('stock_opname.get');
   Route::post('stock_opname/tambah', 'StockOpnameGudangController@tambah')->name('stock_opname.tambah');
   Route::get('stock_opname/simpan_/{id}', 'StockOpnameGudangController@simpan_')->name('stock_opname.simpan');

   Route::resource('stock_opname', 'StockOpnameGudangController');

// approval
   Route::get('approve/index', 'ApprovalGudangController@index')->name('approve.index');
   Route::get('approve/listData', 'ApprovalGudangController@listData')->name('approve.data');
   Route::put('approve/store', 'ApprovalGudangController@store')->name('approve.store');
   Route::resource('approve', 'ApprovalGudangController');
   

   // retur ke supplier
   Route::get('retur_supplier/data', 'ReturSupplierController@listData')->name('retur_supplier.data');
   Route::get('retur_supplier/{id}/tambah', 'ReturSupplierController@create');
   Route::get('retur_supplier/{id}/lihat', 'ReturSupplierController@show');
   Route::get('retur_supplier/{id}/poPDF', 'ReturSupplierController@cetak');
   Route::resource('retur_supplier', 'ReturSupplierController');   

   Route::get('retur_supplier_detail/{id}/data', 'ReturSupplierDetailController@listData')->name('retur_supplier_detail.data');
   Route::get('retur_supplier_detail/loadform/{diskon}/{total}', 'ReturSupplierDetailController@loadForm');
   Route::resource('retur_supplier_detail', 'ReturSupplierDetailController');   









// merubah resource TranferController menjadi ReturGudang Controller untuk controller terima barang retur
   Route::get('retur/gudang', 'ReturGudangController@index')->name('retur.index');
   Route::get('retur/detail/{id}', 'ReturGudangController@show')->name('retur.detail');   
   Route::post('retur/create', 'ReturGudangController@update_status')->name('retur.update_status');
   Route::post('retur/create_stok', 'ReturGudangController@input_stok')->name('retur.input_stok');
   Route::resource('retur', 'ReturGudangController');
   // sotck gudang
   Route::get('stock/index','StockController@index')->name('stock.index');
   Route::get('stock/detail/{id}','StockController@detail')->name('stock.detail');
   Route::get('stock/listData', 'StockController@listData')->name('stock.data');
   Route::get('stock/delete/{id}','StockController@delete')->name('stock.delete');
   // so
   Route::put('stock/store/', 'StockController@store')->name('stock.store');
   Route::resource('stock', 'StockController');
   // 

   // laporan so
   Route::get('laporan/Gudang', 'LaporanSoGudangController@index')->name('laporanGudang.index');
   Route::get('laporan/Gudang/listData', 'LaporanSoGudangController@listData')->name('laporanGudang.data');

   // terima barang dari PO
   Route::get('terima/index', 'TerimaController@index')->name('terima.index');
   Route::get('terima/detail/{id}', 'TerimaController@detail')->name('terima.detail');
   Route::post('terima/create', 'TerimaController@update_status')->name('terima.update_status');
   Route::post('terima/create_stok', 'TerimaController@input_stok')->name('terima.input_stok');
   Route::resource('terima', 'TerimaController');
   
   // controller terima barang terbaru
   Route::get('terima_po/data', 'TerimaPoController@listData')->name('terima_po.data');
   Route::get('terima_po/{id}/tambah', 'TerimaPoController@create');
   Route::get('terima_po/{id}/lihat', 'TerimaPoController@show');
   Route::get('terima_po/{id}/poPDF', 'TerimaPoController@cetak');
   Route::resource('terima_po', 'TerimaPoController');   

   Route::get('terima_po_detail/{id}/data', 'TerimaPoDetailController@listData')->name('terima_po_detail.data');
   Route::get('terima_po_detail/loadform/{diskon}/{total}', 'TerimaPoDetailController@loadForm');
   Route::resource('terima_po_detail', 'TerimaPoDetailController');   


   // approval
   Route::get('approve/index', 'ApprovalGudangController@index')->name('approve.index');
   Route::get('approve/listData', 'ApprovalGudangController@listData')->name('approve.data');
   Route::put('approve/store', 'ApprovalGudangController@store')->name('approve.store');
   Route::resource('approve', 'ApprovalGudangController');

   // ----//
   
   Route::get('transfer/gudang', 'TransferController@gudang')->name('kirim.index');
   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('/transfer/gudang/{id}', 'TransferController@print_gudang');
   
   
//
Route::get('kirim_barang_hold/index', 'KirimBarangHoldController@index')->name('kirim_hold.index');
   Route::get('kirim_barang_hold/data', 'KirimBarangHoldController@listData')->name('kirim_hold.data');

   Route::get('kirim_barang/data', 'KirimBarangController@listData')->name('kirim_barang.data');
   Route::get('kirim_barang/{id}/tambah', 'KirimBarangController@create');
   Route::get('kirim_barang/{id}/lihat', 'KirimBarangController@show');
   Route::get('kirim_barang/{id}/poPDF', 'KirimBarangController@cetak')->name('kirim_barang.cetak');
   Route::resource('kirim_barang', 'KirimBarangController');   

   Route::get('kirim_barang_detail/{id}/data', 'KirimBarangDetailController@listData')->name('barang_detail.data');
   Route::get('kirim_barang_detail/continued/{id}', 'KirimBarangDetailController@continued_hold')->name('barang_detail.continued');
   Route::get('kirim_barang_detail/update/{id}', 'KirimBarangDetailController@update')->name('barang_detail.update');
   Route::get('kirim_barang_detail/expired/{id}', 'KirimBarangDetailController@expired')->name('barang_detail.update_expired');
   Route::delete('kirim_barang_detail/destroy/{id}', 'KirimBarangDetailController@destroy')->name('barang_detail.destroy');
   Route::get('kirim_barang_detail/loadform/{id}', 'KirimBarangDetailController@loadForm')->name('barang_detail.loadForm');
   Route::resource('kirim_barang_detail', 'KirimBarangDetailController');  
//

   Route::get('kirim_barang_detail/{id}/data', 'KirimBarangDetailController@listData')->name('barang_detail.data');
   Route::get('kirim_barang_detail/loadform/{diskon}/{total}', 'KirimBarangDetailController@loadForm');
   Route::resource('kirim_barang_detail', 'KirimBarangDetailController');  
// antar_gudang
   Route::get('kirim_antar_gudang/data', 'KirimAntarGudangController@listData')->name('kirim_antar_gudang.data');
   Route::get('kirim_antar_gudang/{id}/tambah', 'KirimAntarGudangController@create');
   Route::get('kirim_antar_gudang/{id}/lihat', 'KirimAntarGudangController@show');
   Route::get('kirim_antar_gudang/{id}/poPDF', 'KirimAntarGudangController@cetak')->name('kirim_antar_gudang.cetak');
   Route::resource('kirim_antar_gudang', 'KirimAntarGudangController');   

   
   Route::get('kirim_antar_gudang_detail/{id}/data', 'KirimAntarGudangDetailController@listData')->name('kirim_antar_gudang_detail.data');
   Route::get('kirim_antar_gudang_detail/continued/{id}', 'KirimAntarGudangDetailController@continued_hold')->name('kirim_antar_gudang_detail.continued');
   Route::get('kirim_antar_gudang_detail/update/{id}', 'KirimAntarGudangDetailController@update')->name('kirim_antar_gudang_detail.update');
   Route::get('kirim_antar_gudang_detail/expired/{id}', 'KirimAntarGudangDetailController@expired')->name('kirim_antar_gudang_detail.update_expired');
   Route::delete('kirim_antar_gudang_detail/destroy/{id}', 'KirimAntarGudangDetailController@destroy')->name('kirim_antar_gudang_detail.destroy');
   Route::get('kirim_antar_gudang_detail/loadform/{id}', 'KirimAntarGudangDetailController@loadForm')->name('kirim_antar_gudang_detail.loadForm');
   Route::resource('kirim_antar_gudang_detail', 'KirimAntarGudangDetailController');

// terima gudang
      Route::get('terima_gudang/index', 'TerimaGudangController@index')->name('terimaGudang.index');
      Route::get('terima_gudang/detail/{id}', 'TerimaGudangController@detail')->name('terimaGudang.detail');
      Route::post('terima_gudang/create', 'TerimaGudangController@create_jurnal')->name('terimaGudang.create_jurnal');
      Route::resource('terimatoko', 'TerimaGudangController');

// approval_baru
   Route::get('approve_gudang/index', 'ApprovalGudangController@index')->name('approve_gudang.index');
   Route::put('approve_gudang/store', 'ApprovalGudangController@store')->name('approve_gudang.store');
   Route::get('approve_gudang/data/{id}', 'ApprovalGudangController@listData')->name('approve_gudang.data');
   Route::resource('approve_gudang', 'ApprovalGudangController');
   // ----//
   
  Route::get('write_off/index', 'WriteOffController@index')->name('write_off.index');
   Route::get('write_off/load_data', 'WriteOffController@loadData')->name('write_off.loadData');
   Route::get('write_off/load_stok/{id}', 'WriteOffController@loadstok')->name('write_off.loadstok');
   Route::post('write_off/store', 'WriteOffController@store')->name('write_off.store');
});

Route::group(['middleware' => ['web', 'cekuser:5' ]], function(){
   // sotck toko
   Route::get('stock_toko/index','StockTokoController@index')->name('stockToko.index');
   Route::get('stock_toko/detail/{id}','StockTokoController@detail')->name('stockToko.detail');
   Route::get('stock_toko/delete/{id}','StockTokoController@delete')->name('stockToko.delete');
   
   Route::put('stock_toko/store/', 'StockTokoController@store')->name('stockToko.store');
   
   Route::get('stock_toko/listData', 'StockTokoController@listData')->name('stockToko.data');
   
   Route::resource('stockToko', 'StockTokoController');

   // laporan so
   Route::get('laporan/toko', 'LaporanSoTokoController@index')->name('laporanToko.index');
   Route::get('laporan/toko/listData', 'LaporanSoTokoController@listData')->name('laporanToko.data');

   // 
   Route::get('terima_toko/index', 'TerimaTokoController@index')->name('terimaToko.index');
   Route::get('terima_toko/detail/{id}', 'TerimaTokoController@detail')->name('terimatoko.detail');
   Route::post('terima_toko/create', 'TerimaTokoController@create_jurnal')->name('terimatoko.create_jurnal');
   Route::resource('terimatoko', 'TerimaTokoController');


   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('transfer/toko', 'TransferController@toko')->name('terimatoko.index');
   Route::get('transfer/toko/{id}', 'TransferController@print_toko');
   Route::post('transfer/update/{id}','TransferController@api');

   Route::resource('transfer', 'transferController');  
 ////////////////
   Route::get('kirim_barang_toko_hold/index', 'KirimBarangHoldController@index')->name('kirim_hold.index');
   Route::get('kirim_barang_toko_hold/data', 'KirimBarangHoldController@listData')->name('kirim_hold.data');

   Route::get('kirim_barang_toko/data', 'KirimBarangTokoController@listData')->name('kirim_barang_toko.data');
   Route::get('kirim_barang_toko/{id}/tambah', 'KirimBarangTokoController@create');
   Route::get('kirim_barang_toko/{id}/lihat', 'KirimBarangTokoController@show');
   Route::get('kirim_barang_toko/{id}/poPDF', 'KirimBarangTokoController@cetak')->name('kirim_barang_toko.cetak');
   Route::resource('kirim_barang_toko', 'KirimBarangTokoController');   

   Route::get('kirim_barang_toko_detail/{id}/data', 'KirimBarangTokoDetailController@listData')->name('barang_toko_detail.data');
   Route::get('kirim_barang_toko_detail/continued/{id}', 'KirimBarangTokoDetailController@continued_hold')->name('barang_toko_detail.continued');
   Route::get('kirim_barang_toko_detail/update/{id}', 'KirimBarangTokoDetailController@update')->name('barang_toko_detail.update');
   Route::get('kirim_barang_toko_detail/expired/{id}', 'KirimBarangTokoDetailController@expired')->name('barang_toko_detail.update_expired');
   Route::delete('kirim_barang_toko_detail/destroy/{id}', 'KirimBarangTokoDetailController@destroy')->name('barang_toko_detail.destroy');
   Route::get('kirim_barang_toko_detail/loadform/{id}', 'KirimBarangTokoDetailController@loadForm')->name('barang_toko_detail.loadForm');
   Route::resource('kirim_barang_toko_detail', 'KirimBarangTokoDetailController');  
   ///////////////////////////////

Route::get('stock_opname_toko/index', 'StockOpnameTokoController@index')->name('stock_opname_toko.index');
   Route::get('stock_opname_toko/data/{id}', 'StockOpnameTokoController@listData')->name('stock_opname_toko.data');
   route::get('stock_opname_toko/get/{id}', 'StockOpnameTokoController@getData')->name('stock_opname_toko.get');
   Route::post('stock_opname_toko/tambah', 'StockOpnameTokoController@tambah')->name('stock_opname_toko.tambah');
   Route::get('stock_opname_toko/simpan_/{id}', 'StockOpnameTokoController@simpan_')->name('stock_opname_toko.simpan');

   Route::resource('stock_opname_toko', 'StockOpnameTokoController');
});

Route::group(['middleware' => ['web', 'cekuser:6' ]], function(){
   Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
   Route::post('produk/hapus', 'ProdukController@deleteSelected');
   Route::get('produk/update/{id}', 'ProdukController@edit')->name('produk.edit');
   Route::post('produk/ubah/{id}', 'ProdukController@update')->name('produk.harga_jual');
   Route::post('produk/cetak', 'ProdukController@printBarcode');
   Route::resource('produk', 'ProdukController');
   
   // controller menu jurnal di user kp
   Route::get('jurnal_umum_kp/index', 'JurnalUmumKpController@index')->name('jurnal_umum_kp.index');
   Route::post('jurnal_umum_kp/create','JurnalUmumKpController@create')->name('jurnal_umum_kp.create');
   Route::get('jurnal_umum_kp/destroy/{id}', 'JurnalUmumKpController@destroy')->name('jurnal_umum_kp.destroy');
   Route::get('jurnal_umum_kp/approve', 'JurnalUmumKpController@approve')->name('jurnal_umum_kp.approve');
   Route::post('jurnal_umum_kp/autocomplete', 'JurnalUmumKpController@autocomplete')->name('jurnal_umum_kp.autocomplete');

   // approval
   Route::get('approve_kp/index', 'ApprovalKpController@index')->name('approve_kp.index');
   Route::put('approve_kp/store', 'ApprovalKpController@store')->name('approve_kp.store');
   Route::resource('approve_kp', 'ApprovalKpController');

    // report jatpo
    Route::get('report_jatpo/index','ReportJatuhTempoController@index')->name('report_jatpo.index');
    Route::get('report_jatpo/data','ReportJatuhTempoController@listData')->name('report_jatpo.data');
    Route::get('report_jatpo/{id}/update','ReportJatuhTempoController@update')->name('report_jatpo.update');

 //pricing_kp
   Route::get('pricing_kp/index', 'PricingKPController@index')->name('pricing_kp.index');
   Route::get('pricing_kp/data', 'PricingKPController@listData')->name('pricing_kp.data');
   Route::get('pricing_kp/detail/{id}', 'PricingKPController@detail')->name('pricing_kp.detail');
   Route::get('pricing_kp/data_detail/{id}', 'PricingKPController@listDetail')->name('pricing_kp.data_detail');
   Route::post('pricing_kp/update_invoice/{id}', 'PricingKPController@update_invoice')->name('pricing_kp.update_invoice');
   Route::post('pricing_kp/harga_jual/{id}', 'PricingKPController@update_harga_jual')->name('pricing_kp.harga_jual');
   Route::post('pricing_kp/harga_ni/{id}', 'PricingKPController@update_harga_jual_ni')->name('pricing_kp.harga_ni');
   Route::post('pricing_kp/simpan/{id}', 'PricingKPController@simpan')->name('pricing_kp.simpan');
   Route::resource('pricing_ko', 'PricingKPController');
    //koreksi pembelian
   Route::get('koreksi_pembelian/index', 'KoreksiPembelianController@index')->name('koreksi_pembelian.index');
   Route::get('koreksi_pembelian/listData', 'KoreksiPembelianController@listData')->name('koreksi_pembelian.listData');
   Route::get('koreksi_pembelian/show/{id}', 'KoreksiPembelianController@show')->name('koreksi_pembelian.show');
   Route::post('koreksi_pembelian/update', 'KoreksiPembelianController@update')->name('koreksi_pembelian.update');
   Route::get('koreksi_pembelian/delete/{id}', 'KoreksiPembelianController@delete')->name('koreksi_pembelian.delete');

});

Route::group(['middleware' => ['web', 'cekuser:7' ]], function(){

   Route::get('eod/index', 'EODController@index')->name('eod.index'); 
   Route::get('eod/store', 'EODController@store')->name('eod.store');   


   Route::get('user/data', 'UserController@listData')->name('user.data');
   Route::resource('user', 'UserController');

   
   Route::get('all_stok/index','AllStokController@index')->name('all_stok.index');
   Route::get('all_stok/data/{unit}', 'AllStokController@listData')->name('all_stok.data');
   route::get('all_stok/detail/{id}','AllStokController@detail')->name('all_stok.detail');
   Route::get('all_stok/delete/{id}', 'AllStokController@delete')->name('all_stok.delete');
   Route::post('all_stok/store', 'AllStokController@store')->name('all_stok.store');

});
