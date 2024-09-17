<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Pegawai;
use App\Models\DetailPegawai; // Ganti dengan model Anda

class PdfController extends Controller
{
    public function generatePdf($id)
    {
        $record = Pegawai::findOrFail($id);
        $details = DetailPegawai::where('pegawai_id', $record->id)->get();
        $details = $details->isEmpty() ? null : $details;
    
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);
    
        // Assuming $record->image contains Base64 data
        $imageData = $record->image; // Base64 encoded image data
    
        // Prepare data for view
        $data = [
            'nip' => $record->nip,
            'nama_pegawai' => $record->nama_pegawai,
            'pangkat' => $record->pangkat,
            'jabatan' => $record->jabatan,
            'unit' => $record->unit,
            'total' => $record->total,
            'image' => $imageData,
            'details' => $details
        ];
    
        // Create HTML view
        $html = view('pdf_template', compact('data'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        $fileName = $record->id . '.pdf';
    
        return $dompdf->stream($fileName);
    }
    
}
