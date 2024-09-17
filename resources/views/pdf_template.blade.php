<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Template</title>
</head>
<body>
    <h1>Detail Pegawai</h1>
    <p><strong>NIP:</strong> {{ $data['nip'] }}</p>
    <p><strong>Nama Pegawai:</strong> {{ $data['nama_pegawai'] }}</p>
    <p><strong>Pangkat:</strong> {{ $data['pangkat'] }}</p>
    <p><strong>Jabatan:</strong> {{ $data['jabatan'] }}</p>
    <p><strong>Unit:</strong> {{ $data['unit'] }}</p>
    <p><strong>Total:</strong> {{ $data['total'] }}</p>
    <p><strong>Foto:</strong> 
    <img src="{{ public_path('storage/' . $data['image']) }}" style="max-width: 200px;"/>
    </p>
    <h2>Details</h2>
    @if($data['details'])
        <ul>
            @foreach($data['details'] as $detail)
                <li><strong>Jenis Kelamin:</strong> {{ $detail->jk }}</li>
                <!-- Add more fields from DetailPegawai if necessary -->
            @endforeach
        </ul>
    @else
        <p>Detail Pegawai Belum Diinput</p>
    @endif
</body>
</html>

