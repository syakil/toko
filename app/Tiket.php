<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tiket extends Model
{
    protected $table = 'tiket';
    protected $fillable = [
        'user_id',
        'no_tiket', 
        'cif',  
        'tanggal_transaksi',    
        'kode_transaksi',   
        'jenis_transaksi',  
        'keterangan',   
        'fu_helpesk',   
        'status_tiket', 
    ];
}
