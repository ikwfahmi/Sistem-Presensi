<?php

class Laporan extends Controller
{
    public function index()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen', 'mahasiswa']);
        $data['rows'] = $this->model('Laporan_model')->getRekap($pengguna);
        $data['classes'] = $this->model('Kelas_model')->getKelasPengguna($pengguna);
        $this->halaman('reports', $data);
    }

    public function excel()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen']);
        $rows = $this->model('Laporan_model')->getRekap($pengguna);
        $this->downloadExcel($rows);
    }

    public function cetak()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen']);
        $data['rows'] = $this->model('Laporan_model')->getRekap($pengguna);
        $data['u'] = $pengguna;
        $this->view('print', $data);
    }

    private function downloadExcel(array $rows): void {
        $matrix=[['Kode kelas','Mata kuliah','Pertemuan','Jadwal (WIB)','NIM','Nama','Status','Waktu presensi (WIB)','Jarak (m)','Akurasi (m)']];
        foreach ($rows as $r) $matrix[]=[$r['code'],$r['class_name'],$r['title'],$r['starts_at'],$r['identifier'],$r['name'],$r['status'],$r['recorded_at'] ?? '',$r['distance']===null?'':round($r['distance'],1),$r['accuracy'] ?? ''];
        $xml='<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($matrix as $i=>$row) { $xml.='<row r="'.($i+1).'">'; foreach ($row as $j=>$v) $xml.='<c r="'.chr(65+$j).($i+1).'" t="inlineStr"><is><t>'.htmlspecialchars((string)$v,ENT_XML1|ENT_QUOTES,'UTF-8').'</t></is></c>'; $xml.='</row>'; } $xml.='</sheetData></worksheet>';
        $file=tempnam(sys_get_temp_dir(),'presensi'); $zip=new ZipArchive(); $zip->open($file,ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml','<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels','<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml','<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Presensi" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels','<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>'); $zip->addFromString('xl/worksheets/sheet1.xml',$xml); $zip->close();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment; filename="rekap-presensi-'.date('Ymd').'.xlsx"'); readfile($file); unlink($file); exit;
    }
}
