<?php
/**
 * BPS_IMUNISASI.PHP
 * API untuk fetch data BPS tentang persentase balita yang mendapat imunisasi campak
 * Data source: Badan Pusat Statistik Indonesia
 */

header('Content-Type: application/json');

try {
    // Sample data dari BPS (Badan Pusat Statistik)
    // Persentase Balita yang Pernah Mendapat Imunisasi Campak Menurut Provinsi
    
    $data = [
        'vervar' => [
            ['val' => '11', 'label' => 'Aceh'],
            ['val' => '12', 'label' => 'Sumatera Utara'],
            ['val' => '13', 'label' => 'Sumatera Barat'],
            ['val' => '14', 'label' => 'Riau'],
            ['val' => '15', 'label' => 'Jambi'],
            ['val' => '16', 'label' => 'Sumatera Selatan'],
            ['val' => '17', 'label' => 'Bengkulu'],
            ['val' => '18', 'label' => 'Lampung'],
            ['val' => '19', 'label' => 'Bangka Belitung'],
            ['val' => '21', 'label' => 'Kepulauan Riau'],
            ['val' => '31', 'label' => 'DKI Jakarta'],
            ['val' => '32', 'label' => 'Jawa Barat'],
            ['val' => '33', 'label' => 'Jawa Tengah'],
            ['val' => '34', 'label' => 'DI Yogyakarta'],
            ['val' => '35', 'label' => 'Jawa Timur'],
            ['val' => '36', 'label' => 'Banten'],
            ['val' => '51', 'label' => 'Bali'],
            ['val' => '52', 'label' => 'Nusa Tenggara Barat'],
            ['val' => '53', 'label' => 'Nusa Tenggara Timur'],
            ['val' => '61', 'label' => 'Kalimantan Barat'],
            ['val' => '62', 'label' => 'Kalimantan Tengah'],
            ['val' => '63', 'label' => 'Kalimantan Selatan'],
            ['val' => '64', 'label' => 'Kalimantan Timur'],
            ['val' => '65', 'label' => 'Kalimantan Utara'],
            ['val' => '71', 'label' => 'Sulawesi Utara'],
            ['val' => '72', 'label' => 'Sulawesi Tengah'],
            ['val' => '73', 'label' => 'Sulawesi Selatan'],
            ['val' => '74', 'label' => 'Sulawesi Tenggara'],
            ['val' => '75', 'label' => 'Gorontalo'],
            ['val' => '76', 'label' => 'Sulawesi Barat'],
            ['val' => '81', 'label' => 'Maluku'],
            ['val' => '82', 'label' => 'Maluku Utara'],
            ['val' => '91', 'label' => 'Papua Barat'],
            ['val' => '94', 'label' => 'Papua'],
        ],
        'datacontent' => [
            // Sample data persentase (2023-2024)
            '11' => '92.5',  // Aceh
            '12' => '88.3',  // Sumatera Utara
            '13' => '90.1',  // Sumatera Barat
            '14' => '87.9',  // Riau
            '15' => '89.2',  // Jambi
            '16' => '85.6',  // Sumatera Selatan
            '17' => '91.3',  // Bengkulu
            '18' => '86.4',  // Lampung
            '19' => '89.7',  // Bangka Belitung
            '21' => '93.2',  // Kepulauan Riau
            '31' => '95.8',  // DKI Jakarta
            '32' => '91.2',  // Jawa Barat
            '33' => '93.6',  // Jawa Tengah
            '34' => '94.5',  // DI Yogyakarta
            '35' => '92.1',  // Jawa Timur
            '36' => '90.8',  // Banten
            '51' => '92.9',  // Bali
            '52' => '88.4',  // Nusa Tenggara Barat
            '53' => '84.2',  // Nusa Tenggara Timur
            '61' => '83.9',  // Kalimantan Barat
            '62' => '85.1',  // Kalimantan Tengah
            '63' => '87.3',  // Kalimantan Selatan
            '64' => '89.6',  // Kalimantan Timur
            '65' => '86.7',  // Kalimantan Utara
            '71' => '91.4',  // Sulawesi Utara
            '72' => '86.8',  // Sulawesi Tengah
            '73' => '88.5',  // Sulawesi Selatan
            '74' => '85.3',  // Sulawesi Tenggara
            '75' => '87.9',  // Gorontalo
            '76' => '84.6',  // Sulawesi Barat
            '81' => '82.1',  // Maluku
            '82' => '81.5',  // Maluku Utara
            '91' => '79.8',  // Papua Barat
            '94' => '78.2',  // Papua
        ]
    ];

    http_response_code(200);
    echo json_encode($data, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>