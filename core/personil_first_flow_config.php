<?php
declare(strict_types=1);
/**
 * Personil-First Flow Implementation Guide
 * Regulation-Compliant Workflow for SPRIN v2.0
 * 
 * Based on:
 * - PERKAP No. 23/2010 (Pembentukan dan Susunan Organisasi Kepolisian)
 * - Perpol No. 3/2024 (Organisasi dan Tata Kerja Kepolisian)
 * - PP No. 100/2000 (Jabatan Pejabat Pemerintah Sipil)
 */

class PersonilFirstFlow {
    
    /**
     * FLOW 1: PERSONIL MANAGEMENT (Foundation)
     * ----------------------------------------
     * All operations start with personil data
     */
    const FLOW_PERSONIL = [
        'step' => 1,
        'title' => 'Personil Management',
        'icon' => 'fa-users',
        'color' => '#1a237e',
        'description' => 'Data master personil sebagai foundation sistem',
        'operations' => [
            'create' => [
                'title' => 'Tambah Personil Baru',
                'validation' => [
                    'nrp' => '8 digit angka (PERKAP standard)',
                    'nama' => 'Minimal 3 karakter',
                    'tanggal_lahir' => 'Umur minimal 18 tahun',
                    'tempat_lahir' => 'Wajib diisi',
                    'jenis_kelamin' => 'L/P',
                    'pangkat' => 'Sesuai jenjang karir'
                ],
                'workflow' => [
                    '1. Input data personil (NRP, Nama, TTL, Pangkat awal)',
                    '2. Validasi NRP format (8 digit)',
                    '3. Validasi umur (≥18 tahun)',
                    '4. Simpan ke tabel personil',
                    '5. Buat riwayat pangkat awal',
                    '6. Assign jabatan (jika ada)'
                ]
            ],
            'read' => [
                'title' => 'Lihat Data Personil',
                'features' => [
                    'Filter by unsur, bagian, pangkat',
                    'Search by NRP atau nama',
                    'Sorting by nama, pangkat, jabatan',
                    'Pagination 20 items/page'
                ]
            ],
            'update' => [
                'title' => 'Update Data Personil',
                'allowed_updates' => [
                    'Data pribadi (alamat, telepon, email)',
                    'Pendidikan (pendidikan_terakhir, gelar)',
                    'Status (nikah, jumlah_anak)'
                ],
                'restricted_updates' => [
                    'NRP (immutable - unique identifier)',
                    'Nama (requires approval)',
                    'Pangkat (via kepegawaian module)',
                    'Jabatan (via penugasan module)'
                ]
            ],
            'delete' => [
                'title' => 'Non-aktifkan Personil',
                'process' => 'Soft delete dengan alasan',
                'validation' => 'Tidak boleh ada penugasan aktif'
            ]
        ]
    ];
    
    /**
     * FLOW 2: KEPEGAWAIAN (Career Management)
     * ----------------------------------------
     * Career progression and mutations
     */
    const FLOW_KEPEGAWAIAN = [
        'step' => 2,
        'title' => 'Kepegawaian Management',
        'icon' => 'fa-user-tie',
        'color' => '#198754',
        'description' => 'Kenaikan pangkat dan mutasi jabatan',
        'operations' => [
            'kenaikan_pangkat' => [
                'title' => 'Kenaikan Pangkat',
                'types' => [
                    'reguler' => 'Sesuai masa kerja (PERKAP)',
                    'luar_biasa' => 'Penghargaan khusus',
                    'prestasi' => 'Prestasi luar biasa',
                    'penghargaan' => 'Penghargaan pemerintah'
                ],
                'eligibility_criteria' => [
                    'Masa kerja minimal (sesuai jenjang)',
                    'Tidak ada sanksi sedang/berat',
                    'Nilai kinerja minimal "Baik"',
                    'Rekomendasi atasan'
                ],
                'workflow' => [
                    '1. Pilih personil dari data master',
                    '2. Check eligibility (automated)',
                    '3. Pilih pangkat berikutnya',
                    '4. Input nomor SK kenaikan',
                    '5. Validasi jenjang karir',
                    '6. Update data personil',
                    '7. Create riwayat kenaikan pangkat',
                    '8. Notify personil'
                ]
            ],
            'mutasi_jabatan' => [
                'title' => 'Mutasi Jabatan',
                'types' => [
                    'promosi' => 'Naik eselon',
                    'mutasi' => 'Pindah jabatan selevel',
                    'rotasi' => 'Rotasi internal',
                    'demosi' => 'Turun eselon (sanksi)'
                ],
                'eligibility_criteria' => [
                    'Pangkat sesuai jabatan tujuan (PP 100/2000)',
                    'Jabatan tujuan kosong',
                    'Masa kerja minimal di jabatan lama',
                    'Eselon sesuai jenjang'
                ],
                'workflow' => [
                    '1. Pilih personil',
                    '2. Check jabatan available',
                    '3. Validate pangkat vs eselon (PP 100/2000)',
                    '4. Input SK mutasi',
                    '5. End penugasan lama',
                    '6. Create penugasan baru',
                    '7. Update riwayat jabatan',
                    '8. Update struktur organisasi'
                ]
            ],
            'jenjang_karir' => [
                'title' => 'Jenjang Karir',
                'description' => 'Tracking career progression',
                'components' => [
                    'riwayat_pangkat' => 'History semua kenaikan',
                    'riwayat_jabatan' => 'History semua mutasi',
                    'masa_kerja' => 'Otomatis terhitung',
                    'prediksi_kenaikan' => 'Next eligible date'
                ]
            ]
        ]
    ];
    
    /**
     * FLOW 3: PENUGASAN (Assignment Management)
     * ----------------------------------------
     * Assign personil to positions
     */
    const FLOW_PENUGASAN = [
        'step' => 3,
        'title' => 'Penugasan Management',
        'icon' => 'fa-briefcase',
        'color' => '#fd7e14',
        'description' => 'Assignment ke jabatan dengan compliance monitoring',
        'jenis_penugasan' => [
            'definitif' => [
                'code' => 'Definitif',
                'description' => 'Penugasan tetap/tanpa batas waktu',
                'requirements' => 'Jabatan struktural definitif',
                'max_duration' => 'Tidak terbatas',
                'compliance' => 'Standard'
            ],
            'ps' => [
                'code' => 'PS',
                'full_name' => 'Pejabat Sementara',
                'description' => 'Menggantikan pejabat yang berhalangan',
                'requirements' => 'Pangkat minimal sama dengan jabatan',
                'max_duration' => '6 bulan (bisa diperpanjang)',
                'compliance' => 'Max 15% total jabatan (Perpol 3/2024)'
            ],
            'plt' => [
                'code' => 'Plt',
                'full_name' => 'Pelaksana Tugas',
                'description' => 'Melaksanakan tugas jabatan yang kosong',
                'requirements' => 'Pangkat bisa lebih rendah 1 tingkat',
                'max_duration' => '3 bulan (bisa diperpanjang)',
                'compliance' => 'Monitoring required'
            ],
            'pjs' => [
                'code' => 'Pjs',
                'full_name' => 'Pejabat Sementara',
                'description' => 'Menggantikan pejabat untuk sementara',
                'requirements' => 'Pangkat minimal sama',
                'max_duration' => 'Sesuai kebutuhan',
                'compliance' => 'Standard'
            ],
            'plh' => [
                'code' => 'Plh',
                'full_name' => 'Pelaksana Harian',
                'description' => 'Pelaksanaan tugas harian',
                'requirements' => 'Pangkat sesuai jabatan',
                'max_duration' => 'Maksimal 1 bulan',
                'compliance' => 'Short-term only'
            ],
            'pj' => [
                'code' => 'Pj',
                'full_name' => 'Penjabat',
                'description' => 'Menjabat sementara untuk belajar',
                'requirements' => 'Calon untuk promosi',
                'max_duration' => 'Sesuai program',
                'compliance' => 'Development purpose'
            ]
        ],
        'operations' => [
            'assign' => [
                'title' => 'Assign Penugasan',
                'workflow' => [
                    '1. Pilih personil (dari data master)',
                    '2. Pilih jenis penugasan',
                    '3. Check eligibility (pangkat, status)',
                    '4. Check PS percentage (if applicable)',
                    '5. Pilih jabatan (available only)',
                    '6. Input periode penugasan',
                    '7. Input nomor SK',
                    '8. Validasi compliance',
                    '9. Simpan penugasan',
                    '10. Update jabatan status'
                ]
            ],
            'extend' => [
                'title' => 'Perpanjang Penugasan',
                'condition' => 'Penugasan masih aktif',
                'validation' => 'Tidak melebihi max duration',
                'approval' => 'Requires atasan approval'
            ],
            'end' => [
                'title' => 'Akhiri Penugasan',
                'condition' => 'Bisa sebelum atau setelah periode',
                'reasons' => [
                    'Penugasan definitif selesai',
                    'Mutasi ke jabatan lain',
                    'Kenaikan pangkat',
                    'Pensiun',
                    'Sanksi',
                    'Lainnya'
                ],
                'workflow' => [
                    '1. Pilih penugasan aktif',
                    '2. Pilih alasan pengakhiran',
                    '3. Input tanggal akhir',
                    '4. Input SK pengakhiran (jika ada)',
                    '5. Update status penugasan',
                    '6. Update status jabatan (jadi available)',
                    '7. Create riwayat penugasan'
                ]
            ]
        ],
        'compliance_monitoring' => [
            'ps_percentage' => [
                'rule' => 'Maximum 15% dari total jabatan struktural',
                'source' => 'Perpol No. 3/2024',
                'monitoring' => 'Real-time dashboard',
                'alert' => 'Warning jika >12%, Critical jika >15%'
            ],
            'expiration' => [
                'monitoring' => 'Auto-check daily',
                'alert_7_days' => 'Warning 7 hari sebelum expired',
                'alert_expired' => 'Critical saat expired',
                'action' => 'Wajib perpanjang atau akhiri'
            ],
            'pangkat_jabatan' => [
                'rule' => 'Pangkat harus sesuai jabatan (PP 100/2000)',
                'validation' => 'Automated saat assign',
                'warning' => 'Alert jika tidak match'
            ]
        ]
    ];
    
    /**
     * FLOW 4: MONITORING & REPORTING
     * -------------------------------
     * Dashboard and compliance reporting
     */
    const FLOW_MONITORING = [
        'step' => 4,
        'title' => 'Monitoring & Reporting',
        'icon' => 'fa-chart-pie',
        'color' => '#dc3545',
        'description' => 'Executive dashboard dan compliance reporting',
        'components' => [
            'dashboard' => [
                'title' => 'Executive Dashboard',
                'metrics' => [
                    'total_personil' => 'Aktif vs non-aktif',
                    'personil_by_unsur' => 'Distribusi per unsur',
                    'personil_by_pangkat' => 'Distribusi per pangkat',
                    'jabatan_kosong' => 'Positions available',
                    'ps_percentage' => 'PS compliance status',
                    'kenaikan_tahun_ini' => 'Promotions this year',
                    'mutasi_tahun_ini' => 'Transfers this year'
                ],
                'charts' => [
                    'pie_chart' => 'Distribusi unsur',
                    'bar_chart' => 'Distribusi pangkat',
                    'line_chart' => 'Trend kepegawaian',
                    'gauge' => 'PS percentage'
                ]
            ],
            'compliance_report' => [
                'title' => 'Laporan Compliance',
                'reports' => [
                    'ps_compliance' => 'PS percentage per unit',
                    'eselon_validation' => 'Pangkat vs Eselon match',
                    'expiration_report' => 'Penugasan expiring/expired',
                    'regulation_audit' => 'Audit trail perubahan'
                ],
                'export_formats' => ['PDF', 'Excel', 'CSV']
            ],
            'analytics' => [
                'title' => 'Advanced Analytics',
                'features' => [
                    'prediksi_karir' => 'AI-powered career prediction',
                    ' succession_planning' => 'Talent pool analysis',
                    'performance_trend' => 'Performance over time',
                    'age_distribution' => 'Demographics analysis'
                ]
            ]
        ]
    ];
    
    /**
     * POLRI ORGANIZATIONAL STRUCTURE
     * -------------------------------
     * Based on PERKAP No. 23/2010
     */
    const STRUKTUR_POLRI = [
        'unsur_pimpinan' => [
            'name' => 'Unsur Pimpinan',
            'level' => 'Eselon I - II',
            'components' => [
                'Kapolres' => 'Eselon II',
                'Wakapolres' => 'Eselon II'
            ],
            'kode' => 'UNSUR_PIMPINAN'
        ],
        'unsur_pembantu' => [
            'name' => 'Unsur Pembantu Pimpinan',
            'level' => 'Eselon III',
            'components' => [
                'Kabag Ops' => 'Eselon III',
                'Kabag SDM' => 'Eselon III',
                'Kabag Intel' => 'Eselon III',
                'Kabag Log' => 'Eselon III',
                'Kasat Reskrim' => 'Eselon III',
                'Kasat Resnarkoba' => 'Eselon III',
                'Kasat Lantas' => 'Eselon III',
                'Kasat Intel' => 'Eselon III',
                'Kasat Samapta' => 'Eselon III',
                'Kasat Binmas' => 'Eselon III'
            ],
            'kode' => 'UNSUR_PEMBANTU'
        ],
        'unsur_pelaksana_tugas' => [
            'name' => 'Unsur Pelaksana Tugas Pokok',
            'level' => 'Eselon IV',
            'components' => [
                'Unit Reskrim' => 'Eselon IV',
                'Unit Resnarkoba' => 'Eselon IV',
                'Unit Lantas' => 'Eselon IV',
                'Unit Intel' => 'Eselon IV',
                'Unit Samapta' => 'Eselon IV',
                'Unit Binmas' => 'Eselon IV',
                'Unit Sabhara' => 'Eselon IV',
                'Unit Tamsil' => 'Eselon IV',
                'Unit Labbak' => 'Eselon IV',
                'Unit SPKT' => 'Eselon IV'
            ],
            'kode' => 'UNSUR_PELAKSANA'
        ],
        'unsur_kewilayahan' => [
            'name' => 'Unsur Pelaksana Kewilayahan',
            'level' => 'Polsek',
            'components' => [
                'Polsek Pangururan' => 'Kecamatan',
                'Polsek Harian' => 'Kecamatan',
                'Polsek Palipi' => 'Kecamatan',
                'Polsek Simanindo' => 'Kecamatan',
                'Polsek Onan Runggu' => 'Kecamatan',
                'Polsek Ronggur Nihuta' => 'Kecamatan',
                'Polsek Sitio-tio' => 'Kecamatan',
                'Polsek Nainggolan' => 'Kecamatan'
            ],
            'kode' => 'UNSUR_KEWILAYAHAN'
        ],
        'unsur_pendukung' => [
            'name' => 'Unsur Pendukung',
            'level' => 'Unit Administrasi',
            'components' => [
                'Unit Keuangan' => 'Administrasi',
                'Unit Umum' => 'Administrasi',
                'Unit BMN' => 'Administrasi',
                'Unit Kepegawaian' => 'Administrasi',
                'Unit TIU' => 'Administrasi',
                'Unit TIK' => 'Administrasi',
                'Unit Dokkes' => 'Administrasi',
                'Unit Provos' => 'Administrasi'
            ],
            'kode' => 'UNSUR_PENDUKUNG'
        ],
        'unsur_lainnya' => [
            'name' => 'Unsur Lainnya',
            'level' => 'Khusus',
            'components' => [
                'Unit Dikyasa' => 'Pendidikan',
                'Unit Wabprof' => 'Pengawasan',
                'Unit Yanma' => 'Khusus'
            ],
            'kode' => 'UNSUR_LAINNYA'
        ]
    ];
    
    /**
     * VALIDATION RULES
     * ----------------
     * System-wide validation per regulations
     */
    const VALIDATION_RULES = [
        'nrp' => [
            'format' => '/^[0-9]{8}$/',
            'message' => 'NRP harus 8 digit angka',
            'source' => 'PERKAP'
        ],
        'umur_minimal' => [
            'value' => 18,
            'message' => 'Umur minimal 18 tahun',
            'source' => 'UU No. 11/2002'
        ],
        'ps_percentage' => [
            'max' => 15,
            'warning' => 12,
            'message' => 'PS percentage maksimal 15%',
            'source' => 'Perpol No. 3/2024'
        ],
        'kenaikan_pangkat' => [
            'masa_kerja_minimal' => [
                'Bharada → Brigadir' => 2,
                'Brigadir → Bripka' => 2,
                'Bripka → Aiptu' => 4,
                'Aiptu → Aipda' => 4,
                'Aipda → Briptu' => 4,
                'Briptu → Bripda' => 4
            ],
            'source' => 'PERKAP & PP'
        ],
        'pangkat_eselon' => [
            'Eselon II' => ['AKBP', 'KOMPOL'],
            'Eselon III' => ['AKP', 'IPTU', 'IPDA'],
            'Eselon IV' => ['AIPTU', 'AIPDA', 'BRIPKA', 'BRIGADIR'],
            'source' => 'PP No. 100/2000'
        ]
    ];
    
    /**
     * NAVIGATION STRUCTURE
     * --------------------
     * Regulation-compliant menu structure
     */
    const NAV_STRUCTURE = [
        [
            'title' => 'Dashboard',
            'icon' => 'fa-gauge-high',
            'url' => 'dashboard_v2.php',
            'permission' => 'all'
        ],
        [
            'title' => 'Manajemen Personil',
            'icon' => 'fa-users',
            'url' => 'personil_management_v2.php',
            'permission' => 'all',
            'submenu' => [
                ['title' => 'Data Personil', 'url' => 'personil_management_v2.php', 'icon' => 'fa-list'],
                ['title' => 'Tambah Personil', 'url' => '#add', 'icon' => 'fa-plus'],
                ['title' => 'Import Data', 'url' => '#import', 'icon' => 'fa-file-import']
            ]
        ],
        [
            'title' => 'Kepegawaian',
            'icon' => 'fa-user-tie',
            'url' => 'kepegawaian_management_v2.php',
            'permission' => 'admin',
            'submenu' => [
                ['title' => 'Kenaikan Pangkat', 'url' => '#kenaikan', 'icon' => 'fa-arrow-up'],
                ['title' => 'Mutasi Jabatan', 'url' => '#mutasi', 'icon' => 'fa-exchange-alt'],
                ['title' => 'Jenjang Karir', 'url' => '#jenjang', 'icon' => 'fa-stairs']
            ]
        ],
        [
            'title' => 'Penugasan',
            'icon' => 'fa-briefcase',
            'url' => 'penugasan_management_v2.php',
            'permission' => 'admin',
            'submenu' => [
                ['title' => 'Definitif', 'url' => '#definitif', 'icon' => 'fa-id-card'],
                ['title' => 'Pejabat Sementara (PS)', 'url' => '#ps', 'icon' => 'fa-user-clock'],
                ['title' => 'Pelaksana Tugas (Plt)', 'url' => '#plt', 'icon' => 'fa-user-cog'],
                ['title' => 'Pejabat Sementara (Pjs)', 'url' => '#pjs', 'icon' => 'fa-user-shield'],
                ['title' => 'Pelaksana Harian (Plh)', 'url' => '#plh', 'icon' => 'fa-calendar-day'],
                ['title' => 'Penjabat (Pj)', 'url' => '#pj', 'icon' => 'fa-user-tag']
            ]
        ],
        [
            'title' => 'Struktur Organisasi',
            'icon' => 'fa-sitemap',
            'url' => '#',
            'permission' => 'admin',
            'submenu' => [
                ['title' => 'Unsur Pimpinan', 'url' => '#pimpinan', 'icon' => 'fa-crown'],
                ['title' => 'Unsur Pembantu', 'url' => '#pembantu', 'icon' => 'fa-hands-helping'],
                ['title' => 'Satuan Fungsi', 'url' => '#fungsi', 'icon' => 'fa-tasks'],
                ['title' => 'Polsek', 'url' => '#polsek', 'icon' => 'fa-map-marked-alt']
            ]
        ],
        [
            'title' => 'Compliance & Regulasi',
            'icon' => 'fa-balance-scale',
            'url' => '#',
            'permission' => 'admin',
            'submenu' => [
                ['title' => 'Monitoring PS %', 'url' => '#monitor-ps', 'icon' => 'fa-percentage'],
                ['title' => 'Validasi Eselon', 'url' => '#validasi', 'icon' => 'fa-check-double'],
                ['title' => 'Laporan Compliance', 'url' => '#laporan', 'icon' => 'fa-file-contract']
            ]
        ],
        [
            'title' => 'Laporan & Analisis',
            'icon' => 'fa-chart-bar',
            'url' => 'dashboard_v2.php',
            'permission' => 'all'
        ]
    ];
    
}

// Return flow configuration
return PersonilFirstFlow::class;
?>
