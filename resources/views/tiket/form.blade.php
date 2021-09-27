<!-- Modal -->
<div class="modal fade" id="opentiket" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Open Tiket</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{route('tiket.store')}}" method="post">
          {{csrf_field()}}
            <div class="form-group">
                <label for="user">User</label>
                <input type="text" class="form-control" id="user" value="{{Auth::user()->name}}" disabled>
            </div>
            
            <div class="form-group">
                <label for="cif">CIF/Kode Member</label>
                <input type="text" class="form-control" id="cif" name="cif">
            </div>

            <div class="form-group">
                <label for="kode_transaksi">Kode Transaksi</label>
                <input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi">
            </div>

            <div class="form-group">
                <label for="tanggal_transaksi">Tanggal Transaksi</label>
                <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi">
            </div>

            <div class="form-group">
                <label for="jenis_transaksi">Jenis Transaksi</label>
                <select required class="form-control" id="jenis_transaksi" name="jenis_transaksi">
                    <option value="" disabled selected>-Pilih Salah Satu-</option>
                    @foreach($jenis_transaksi as $list)
                    <option value="{{$list->kode_transaksi}}">{{$list->keterangan_transaksi}}</option>
                    @endforeach
                </select>
            </div>
           
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
        <button type="submit" class="btn btn-primary">Proses</button>
        
        </form>
      </div>
    </div>
  </div>
</div>