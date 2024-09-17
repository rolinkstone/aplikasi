<div>
    <h2>Detail Pegawai</h2>

    @if($record)
        <p><strong>NIP:</strong> {{ $record->nip }}</p>
        <p><strong>Nama Pegawai:</strong> {{ $record->nama_pegawai }}</p>
        <p><strong>Pangkat:</strong> {{ $record->pangkat }}</p>
        <p><strong>Jabatan:</strong> {{ $record->jabatan }}</p>
        <p><strong>Unit:</strong> {{ $record->unit }}</p>
        <p><strong>Unit:</strong> {{ $record->user_id }}</p>

         <!-- Assuming $record has an image_url property -->
         @if($record->image)
            <div>
                <h3>Gambar Pegawai</h3>
                <img src="{{ asset('storage/' . $record->image) }}" alt="Gambar Pegawai" style="max-width: 35%; height: auto;">
            </div>
        @else
            <p><strong>Gambar:</strong> Tidak ada gambar pegawai.</p>
        @endif

        <!-- Check if the 'gambar' field is an array and loop through if needed -->
        @if(is_array($record->gambar) && !empty($record->gambar))
            <h3>Gambar Pegawai</h3>
            @foreach($record->gambar as $image)
                <div>
                    <img src="{{ asset('storage/' . $image) }}" alt="Gambar Pegawai" style="max-width: 35%; height: auto;">
                </div>
            @endforeach
        @elseif(!is_array($record->gambar) && !empty($record->gambar))
            <h3>Gambar Pegawai</h3>
            <img src="{{ asset('storage/' . $record->gambar) }}" alt="Gambar Pegawai" style="max-width: 35%; height: auto;">
        @else
            <p><strong>Gambar:</strong> Tidak ada gambar pegawai.</p>
        @endif

        @if($record->department)
            <p><strong>Provinsi:</strong> {{ $record->department->provinsi }}</p>
            <p><strong>Alamat:</strong> {{ $record->department->alamat }}</p>
            <p><strong>Kode Pos:</strong> {{ $record->department->kode_pos }}</p>
        @else
            <p><strong>Departemen:</strong> Data departemen tidak tersedia.</p>
        @endif

        @if($record->apayolah->isNotEmpty())
            <h3>Daftar Belanja</h3>
            @foreach($record->apayolah as $penerbit)
                <div>
                    <p><strong>Nama Penerbit:</strong> {{ $penerbit->name }}</p>
                    <p><strong>Jumlah:</strong> {{ $penerbit->jumlah }}</p>
                    <p><strong>Harga:</strong> {{ $penerbit->harga }}</p>
                </div>
                <hr>
            @endforeach
        @else
            <p><strong>Data Penerbit:</strong> Tidak ada data penerbit.</p>
        @endif

       

    @else
        <p>Record belum diisi.</p>
    @endif
    <p><strong>Total Belanja:</strong> {{ $record->total }}</p>
</div>
