<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';
if (!AuthHelper::validateSession()) { header('Location: ' . url('login.php')); exit; }

AuthHelper::requireRole('admin', 'operator');

$page_title = 'Manajemen Tim Piket - BAGOPS POLRES Samosir';

// Unsur yang relevan untuk piket: 3=Tugas Pokok, 4=Kewilayahan
$PIKET_UNSUR = [3, 4];
$PIKET_EXTRA = [20]; // SPKT (Unsur Pendukung tapi piket 24 jam)

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Unsur piket saja
    $uph = implode(',', array_fill(0, count($PIKET_UNSUR), '?'));
    $unsur_piket = $pdo->prepare("SELECT id, nama_unsur FROM unsur WHERE id IN ($uph) AND is_active=1 ORDER BY urutan");
    $unsur_piket->execute($PIKET_UNSUR);
    $unsur_piket = $unsur_piket->fetchAll(PDO::FETCH_ASSOC);

    // Bagian yang relevan untuk piket
    $eph = implode(',', array_fill(0, count($PIKET_EXTRA), '?'));
    $stmtB = $pdo->prepare("
        SELECT b.id, b.nama_bagian, b.id_unsur, u.nama_unsur
        FROM bagian b
        LEFT JOIN unsur u ON u.id = b.id_unsur
        WHERE (b.id_unsur IN ($uph) OR b.id IN ($eph))
          AND b.is_active = 1
        ORDER BY u.id, b.urutan, b.nama_bagian
    ");
    $stmtB->execute(array_merge($PIKET_UNSUR, $PIKET_EXTRA));
    $bagian_piket = $stmtB->fetchAll(PDO::FETCH_ASSOC);
    $bagian_map   = array_column($bagian_piket, null, 'id');
    $bagian_ids   = array_column($bagian_piket, 'id');

    // Tim piket (hanya dari bagian relevan)
    $tim_list = [];
    if ($bagian_ids) {
        $bph  = implode(',', array_fill(0, count($bagian_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT t.*, b.nama_bagian, u.nama_unsur,
                   COUNT(DISTINCT a.id) AS jml_anggota,
                   f.nama_fase, f.urutan AS fase_urutan,
                   f.durasi_jam AS fase_durasi,
                   f.jam_mulai_default AS fase_jam_mulai
            FROM tim_piket t
            LEFT JOIN bagian b ON b.id = t.id_bagian
            LEFT JOIN unsur  u ON u.id = t.id_unsur
            LEFT JOIN tim_piket_anggota a ON a.tim_id = t.id
            LEFT JOIN siklus_piket_fase f ON f.id = t.fase_siklus_id
            WHERE t.id_bagian IN ($bph)
            GROUP BY t.id
            ORDER BY t.is_active DESC, b.nama_bagian, t.nama_tim
        ");
        $stmt->execute($bagian_ids);
        $tim_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Siklus per bagian (tidak ada fallback otomatis ke siklus umum)
    $siklus_raw = $pdo->query("
        SELECT s.* FROM siklus_piket_fase s ORDER BY s.id_bagian IS NULL, s.id_bagian, s.urutan
    ")->fetchAll(PDO::FETCH_ASSOC);
    $siklus_by_bagian = [];
    $siklus_umum = [];
    foreach ($siklus_raw as $s) {
        if ($s['id_bagian'] === null) {
            $siklus_umum[] = $s;
        } else {
            $siklus_by_bagian[$s['id_bagian']][] = $s;
        }
    }

    // Group tim per bagian
    $tim_by_bagian = [];
    foreach ($tim_list as $t) if ($t['id_bagian']) $tim_by_bagian[$t['id_bagian']][] = $t;

} catch (Exception $e) {
    $dbError = $e->getMessage();
    $bagian_piket = $unsur_piket = $tim_list = [];
    $siklus_by_bagian = $tim_by_bagian = $bagian_map = [];
}

$labelJenis = ['piket'=>'Piket Harian','satuan_tugas'=>'Satuan Tugas','kegiatan'=>'Rencana Kegiatan'];
$badgeJenis = ['piket'=>'bg-primary','satuan_tugas'=>'bg-warning text-dark','kegiatan'=>'bg-info text-dark'];
$colorFase  = ['#dc3545','#198754','#fd7e14','#0dcaf0','#6f42c1','#6c757d'];

function jamSelesai(string $mulai, $durasi): string {
    $dt = DateTime::createFromFormat('H:i:s', $mulai) ?: DateTime::createFromFormat('H:i', $mulai);
    if (!$dt) return '-';
    $dt->modify('+'.((float)$durasi).' hours');
    return $dt->format('H:i');
}

include __DIR__ . '/../includes/components/header.php';
?>
<style>
.papan-col{min-height:130px;border-radius:0 0 8px 8px;border:2px dashed #dee2e6;padding:8px;transition:background .15s}
.papan-col.dragover{border-color:#0d6efd;background:#dbeafe}
.tim-chip{border-radius:7px;font-size:.8rem;cursor:grab;transition:box-shadow .15s}
.tim-chip:active{cursor:grabbing;box-shadow:0 4px 14px rgba(0,0,0,.18)}
.fase-hdr{border-radius:7px 7px 0 0;padding:7px 10px;font-weight:700;font-size:.83rem;color:#fff}
.fase-hdr .jam-info{font-size:.72rem;opacity:.88;font-weight:400}
.arrow-siklus{font-size:1.6rem;color:#adb5bd;display:flex;align-items:center;padding:18px 2px 0}
.no-tim-msg{color:#adb5bd;font-size:.77rem;text-align:center;padding:16px 0}
</style>

<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-users-cog me-2 text-primary"></i>Manajemen Tim / Regu Piket</h4>
            <small class="text-muted">Unsur Pelaksana Tugas Pokok &amp; Kewilayahan + SPKT</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="openSiklusModal(0)">
                <i class="fas fa-sync-alt me-1"></i> Atur Siklus
            </button>
            <button class="btn btn-primary" onclick="openTambahTim(0)">
                <i class="fas fa-plus me-1"></i> Tambah Tim
            </button>
        </div>
    </div>

    <?php if (!empty($dbError)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($dbError); ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php
        $totalTim  = count($tim_list);
        $timAktif  = count(array_filter($tim_list, fn($t)=>$t['is_active']));
        $totalAngg = array_sum(array_column($tim_list,'jml_anggota'));
        ?>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-primary"><?php echo $totalTim; ?></div>
            <div class="text-muted small">Total Tim</div>
        </div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-success"><?php echo $timAktif; ?></div>
            <div class="text-muted small">Tim Aktif</div>
        </div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-info"><?php echo $totalAngg; ?></div>
            <div class="text-muted small">Total Anggota</div>
        </div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-warning"><?php echo count($bagian_piket); ?></div>
            <div class="text-muted small">Satuan Piket</div>
        </div></div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-3" id="piketTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPapan" role="tab"><i class="fas fa-project-diagram me-1"></i>Papan Siklus</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabDashboard" role="tab"><i class="fas fa-tachometer-alt me-1"></i>Dashboard Hari Ini</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabKalender" role="tab"><i class="fas fa-calendar-alt me-1"></i>Kalender</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabStatistik" role="tab"><i class="fas fa-chart-bar me-1"></i>Statistik</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabLogRotasi" role="tab"><i class="fas fa-history me-1"></i>Log Rotasi</a></li>
    </ul>

    <div class="tab-content" id="piketTabContent">

    <!-- ═══ TAB: PAPAN SIKLUS ═══ -->
    <div class="tab-pane fade show active" id="tabPapan" role="tabpanel">

    <h5 class="fw-bold mb-3"><i class="fas fa-project-diagram me-2 text-success"></i>Papan Siklus Piket per Satuan</h5>

    <!-- Kartu Siklus Umum (Template) -->
    <?php if (!empty($siklus_umum)): ?>
    <div class="card border-success shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
            <span class="fw-bold text-success">
                <i class="fas fa-globe me-1"></i>Siklus Umum (Template)
                <small class="text-muted fw-normal ms-1">Bisa dicopy ke satuan lain</small>
            </span>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-success py-0" onclick="openSiklusModal(null,'Siklus Umum');document.getElementById('siklusUmum').checked=true;onSiklusTypeChange()" title="Edit Siklus Umum">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Fase</th>
                            <th>Durasi (jam)</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Mode</th>
                            <th>Wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($siklus_umum as $fi => $fase):
                        $jamMul = substr($fase['jam_mulai_default'],0,5);
                        $jamSel = jamSelesai($fase['jam_mulai_default'], $fase['durasi_jam']);
                        $modeText = $fase['jam_mulai_mode'] === 'manual' ? 'Manual' : 'Auto';
                        $wajibText = $fase['is_wajib'] ? 'Ya' : 'Tidak';
                        $wajibBadge = $fase['is_wajib'] ? 'bg-success' : 'bg-secondary';
                    ?>
                        <tr>
                            <td class="text-muted"><?php echo $fi + 1; ?></td>
                            <td><?php echo htmlspecialchars($fase['nama_fase']); ?></td>
                            <td><?php echo (float)$fase['durasi_jam']; ?></td>
                            <td><?php echo $jamMul; ?></td>
                            <td><?php echo $jamSel; ?></td>
                            <td><?php echo $modeText; ?></td>
                            <td><span class="badge <?php echo $wajibBadge; ?>"><?php echo $wajibText; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-dashed shadow-sm mb-3" style="border:2px dashed #dee2e6">
        <div class="card-body py-2 text-center">
            <button class="btn btn-sm btn-outline-success" onclick="openSiklusModal(null,'Siklus Umum');document.getElementById('siklusUmum').checked=true;onSiklusTypeChange()">
                <i class="fas fa-plus me-1"></i>Buat Siklus Umum (Template)
            </button>
            <p class="text-muted small mb-0 mt-2">Siklus umum berlaku untuk semua satuan piket</p>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($bagian_piket as $bag):
        $bid   = $bag['id'];
        $fases = $siklus_by_bagian[$bid] ?? [];
        $tims  = $tim_by_bagian[$bid]    ?? [];
        $timPerFase   = [];
        $timTanpaFase = [];
        foreach ($tims as $t) {
            if ($t['fase_siklus_id']) $timPerFase[$t['fase_siklus_id']][] = $t;
            else $timTanpaFase[] = $t;
        }
    ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-bold">
                <i class="fas fa-building me-1 text-primary"></i>
                <?php echo htmlspecialchars($bag['nama_bagian']); ?>
                <small class="text-muted fw-normal ms-1 d-none d-md-inline"><?php echo htmlspecialchars($bag['nama_unsur'] ?? ''); ?></small>
            </span>
            <div class="d-flex gap-1">
                <?php if (!empty($siklus_umum)): ?>
                <button class="btn btn-sm btn-outline-success py-0" onclick="copySiklusUmum(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Copy siklus umum ke bagian ini">
                    <i class="fas fa-copy"></i> Copy Umum
                </button>
                <?php endif; ?>
                <?php if (!empty($fases)): ?>
                <button class="btn btn-sm btn-outline-warning py-0" onclick="openSiklusModal(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Edit siklus bagian ini">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger py-0" onclick="hapusSiklusBagian(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Hapus siklus bagian ini">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary py-0" onclick="openSiklusModal(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Buat siklus baru">
                    <i class="fas fa-plus"></i> Buat
                </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-info py-0" onclick="rotasiFase(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Rotasi semua tim ke fase berikutnya">
                    <i class="fa-solid fa-rotate"></i> Rotasi
                </button>
            </div>
        </div>
        <div class="card-body py-3">

        <?php if (empty($fases) && empty($tims)): ?>
            <div class="text-center text-muted py-2 small">
                Belum ada siklus maupun tim.
                <a href="#" onclick="openSiklusModal(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>');return false">Buat siklus</a>
                atau
                <a href="#" onclick="openTambahTim(<?php echo $bid; ?>);return false">tambah tim</a>.
            </div>

        <?php elseif (empty($fases)): ?>
            <div class="alert alert-warning py-2 small mb-2">
                <i class="fas fa-exclamation-triangle me-1"></i>Belum ada siklus fase.
                <a href="#" onclick="openSiklusModal(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>');return false">Buat siklus sekarang</a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tims as $t): ?>
                    <?php echo timChipHtml($t, $labelJenis, $badgeJenis); ?>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Toolbar untuk fase siklus -->
            <div class="d-flex justify-content-between align-items-center mb-2 py-1 px-2 bg-light rounded">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAllFases_<?php echo $bid; ?>" onchange="toggleAllFasesInBagian(<?php echo $bid; ?>, this)">
                    <label class="form-check-label small fw-semibold" for="selectAllFases_<?php echo $bid; ?>">
                        Pilih Semua Fase
                    </label>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-warning py-0" onclick="editFaseTerpilih(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Edit fase yang dipilih">
                        <i class="fas fa-edit"></i> Edit Terpilih
                    </button>
                    <button class="btn btn-sm btn-outline-danger py-0" onclick="hapusFaseTerpilih(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Hapus fase yang dipilih">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                </div>
            </div>

            <div class="d-flex gap-0 overflow-auto pb-1">
            <?php foreach ($fases as $fi => $fase):
                $fc     = $colorFase[$fi % count($colorFase)];
                $jamMul = substr($fase['jam_mulai_default'],0,5);
                $jamSel = jamSelesai($fase['jam_mulai_default'], $fase['durasi_jam']);
                $tsFase = $timPerFase[$fase['id']] ?? [];
            ?>
                <?php if ($fi > 0): ?><div class="arrow-siklus">&#8594;</div><?php endif; ?>
                <div style="min-width:175px;max-width:250px;flex:1">
                    <div class="fase-hdr d-flex justify-content-between align-items-center" style="background:<?php echo $fc; ?>">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input fase-checkbox-<?php echo $bid; ?>" type="checkbox" 
                                   data-fase-id="<?php echo $fase['id']; ?>" value="<?php echo $fase['id']; ?>">
                            <span><?php echo htmlspecialchars($fase['nama_fase']); ?></span>
                            <?php if (!$fase['is_wajib']): ?><span class="badge bg-light text-dark ms-1" style="font-size:.6rem">Opsional</span><?php endif; ?>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-light py-0 px-1" onclick="editSatuFase(<?php echo $fase['id']; ?>,<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Edit fase ini">
                                <i class="fas fa-edit text-primary"></i>
                            </button>
                            <button class="btn btn-sm btn-light py-0 px-1" onclick="hapusSatuFase(<?php echo $fase['id']; ?>,'<?php echo htmlspecialchars(addslashes($fase['nama_fase'])); ?>')" title="Hapus fase ini">
                                <i class="fas fa-times text-danger"></i>
                            </button>
                        </div>
                    </div>
                    <div class="jam-info px-2 py-1 bg-light border-top" style="font-size:.72rem;opacity:.88;font-weight:400">
                        <?php echo $jamMul; ?>&#8211;<?php echo $jamSel; ?> &nbsp;(<?php echo (float)$fase['durasi_jam']; ?> jam)
                    </div>
                    <div class="papan-col"
                         id="col_<?php echo $fase['id']; ?>"
                         data-fase-id="<?php echo $fase['id']; ?>"
                         ondragover="event.preventDefault();this.classList.add('dragover')"
                         ondragleave="this.classList.remove('dragover')"
                         ondrop="dropTim(event,<?php echo $fase['id']; ?>)">
                        <?php if (empty($tsFase)): ?>
                            <div class="no-tim-msg">Drag tim ke sini</div>
                        <?php else: ?>
                            <?php foreach ($tsFase as $t): ?>
                                <?php echo timChipHtml($t, $labelJenis, $badgeJenis); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($timTanpaFase)): ?>
                <div class="arrow-siklus" style="color:#dee2e6;font-size:1rem;padding-top:18px">|</div>
                <div style="min-width:160px;max-width:210px;flex:1">
                    <div class="fase-hdr" style="background:#6c757d">
                        Belum Ditempatkan
                        <div class="jam-info">drag ke kolom fase</div>
                    </div>
                    <div class="papan-col"
                         id="col_tanpa_<?php echo $bid; ?>"
                         ondragover="event.preventDefault();this.classList.add('dragover')"
                         ondragleave="this.classList.remove('dragover')"
                         ondrop="dropTim(event,0)">
                        <?php foreach ($timTanpaFase as $t): ?>
                            <?php echo timChipHtml($t, $labelJenis, $badgeJenis); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($tim_list) && empty($siklus_raw)): ?>
    <div class="text-center text-muted py-5">
        <i class="fas fa-users fa-3x mb-3 d-block"></i>
        Belum ada tim. Klik <strong>Tambah Tim</strong> untuk mulai.
    </div>
    <?php endif; ?>

    </div><!-- /tabPapan -->

    <!-- ═══ TAB: DASHBOARD HARI INI ═══ -->
    <div class="tab-pane fade" id="tabDashboard" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard Piket Hari Ini</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-warning" onclick="openFatigueCheckModal()" title="Cek jeda istirahat personil"><i class="fas fa-bed me-1"></i>Fatigue Check</button>
                <button class="btn btn-sm btn-outline-info" onclick="openSwapShiftModal()" title="Tukar jadwal antar personil"><i class="fas fa-exchange-alt me-1"></i>Swap Shift</button>
                <button class="btn btn-sm btn-outline-primary" onclick="loadDashboard()"><i class="fas fa-sync me-1"></i>Refresh</button>
            </div>
        </div>
        <!-- Notifikasi Rotasi -->
        <div id="notifRotasiContainer" class="mb-3" style="display:none"></div>
        <div class="row g-3 mb-3" id="dashboardStats">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3"><div class="fs-2 fw-bold text-primary" id="dsTotalJadwal">-</div><div class="text-muted small">Total Jadwal</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3"><div class="fs-2 fw-bold text-success" id="dsHadir">-</div><div class="text-muted small">Hadir</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3"><div class="fs-2 fw-bold text-warning" id="dsBelumCheckin">-</div><div class="text-muted small">Belum Check-in</div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center py-3"><div class="fs-2 fw-bold text-danger" id="dsTidakHadir">-</div><div class="text-muted small">Tidak Hadir</div></div></div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header py-2"><strong>Jadwal Piket Hari Ini</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Personil</th><th>Pangkat</th><th>Tim</th><th>Bagian</th><th>Shift</th><th>Jam</th><th>Status</th></tr>
                        </thead>
                        <tbody id="dashboardTableBody">
                            <tr><td colspan="8" class="text-center text-muted py-3">Klik tab untuk memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ TAB: KALENDER ═══ -->
    <div class="tab-pane fade" id="tabKalender" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-info"></i>Kalender Piket</h5>
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-sm btn-outline-secondary" onclick="changeCalendarMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                <span class="fw-bold" id="calendarTitle">-</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="changeCalendarMonth(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div id="calendarGrid" class="mb-3"></div>
    </div>

    <!-- ═══ TAB: STATISTIK ═══ -->
    <div class="tab-pane fade" id="tabStatistik" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-warning"></i>Statistik Jam Piket per Personil</h5>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" id="statBulan" style="width:auto" onchange="loadStatistik()">
                    <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m==(int)date('m')?'selected':''; ?>><?php echo DateTime::createFromFormat('!m',$m)->format('F'); ?></option>
                    <?php endfor; ?>
                </select>
                <select class="form-select form-select-sm" id="statTahun" style="width:auto" onchange="loadStatistik()">
                    <?php for($y=(int)date('Y')-1;$y<=(int)date('Y')+1;$y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y==(int)date('Y')?'selected':''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button class="btn btn-sm btn-outline-success" onclick="exportStatistikCSV()" title="Export ke CSV"><i class="fas fa-file-csv me-1"></i>Export</button>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Personil</th><th>Bagian</th><th>Jml Jadwal</th><th>Total Jam</th><th>Hadir</th><th>Absen</th><th>%</th></tr>
                        </thead>
                        <tbody id="statistikTableBody">
                            <tr><td colspan="8" class="text-center text-muted py-3">Klik tab untuk memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ TAB: LOG ROTASI ═══ -->
    <div class="tab-pane fade" id="tabLogRotasi" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-history me-2 text-secondary"></i>Riwayat Rotasi</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadLogRotasi()"><i class="fas fa-sync me-1"></i>Refresh</button>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Waktu</th><th>Bagian</th><th>Dari Fase</th><th>Ke Fase</th><th>Jumlah Tim</th><th>Tipe</th><th>Oleh</th></tr>
                        </thead>
                        <tbody id="logRotasiTableBody">
                            <tr><td colspan="8" class="text-center text-muted py-3">Klik tab untuk memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-content -->

    <!-- Cetak SPRIN buttons per bagian -->
    <div class="mt-3">
        <h5 class="fw-bold mb-3"><i class="fas fa-print me-2 text-dark"></i>Cetak SPRIN Piket</h5>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($bagian_piket as $b): ?>
            <button class="btn btn-sm btn-outline-dark" onclick="cetakSprin(<?php echo $b['id']; ?>,'<?php echo htmlspecialchars(addslashes($b['nama_bagian'])); ?>')">
                <i class="fas fa-print me-1"></i><?php echo htmlspecialchars($b['nama_bagian']); ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
function timChipHtml(array $t, array $lj, array $bj): string {
    $jb  = $bj[$t['jenis']] ?? 'bg-secondary';
    $jbl = $lj[$t['jenis']] ?? $t['jenis'];
    $tj  = htmlspecialchars(json_encode($t), ENT_QUOTES);
    $id  = (int)$t['id'];
    $nm  = htmlspecialchars($t['nama_tim']);
    $nj  = htmlspecialchars(addslashes($t['nama_tim']));
    $an  = (int)$t['jml_anggota'];
    $op  = $t['is_active'] ? '' : ' opacity-50';
    return <<<HTML
<div class="card tim-chip mb-2 border-0 shadow-sm{$op}"
     draggable="true" id="tc_{$id}" data-tim-id="{$id}"
     ondragstart="dragStart(event,{$id})">
  <div class="card-body py-2 px-2">
    <div class="d-flex justify-content-between align-items-start gap-1">
      <div>
        <div class="fw-semibold" style="font-size:.81rem">{$nm}</div>
        <div class="text-muted" style="font-size:.69rem">
          <span class="badge {$jb}" style="font-size:.59rem">{$jbl}</span> {$an} anggota
        </div>
      </div>
      <div class="dropdown">
        <button class="btn btn-link btn-sm text-muted p-0 lh-1" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="font-size:.81rem;min-width:160px">
          <li><a class="dropdown-item" href="#" onclick="kelolaAnggota({$id},'{$nj}');return false"><i class="fas fa-users me-1"></i>Anggota</a></li>
          <li><a class="dropdown-item" href="#" onclick="buatJadwalDariTim({$tj});return false"><i class="fas fa-calendar-plus me-1"></i>Buat Jadwal</a></li>
          <li><a class="dropdown-item" href="#" onclick="editTim({$tj});return false"><i class="fas fa-edit me-1"></i>Edit</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="#" onclick="hapusTim({$id},'{$nj}');return false"><i class="fas fa-trash me-1"></i>Hapus</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
HTML;
}
?>

<!-- ══ MODAL TAMBAH/EDIT TIM ══ -->
<div class="modal fade" id="timModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="timModalTitle"><i class="fas fa-users-cog me-2"></i>Tambah Tim</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="timForm">
          <input type="hidden" id="tim_id" name="id">

          <!-- 1. Unsur -->
          <div class="mb-3">
            <label class="form-label fw-semibold">1. Unsur</label>
            <select class="form-select" id="tim_unsur" name="id_unsur" onchange="onUnsurChange(this.value)">
              <option value="">-- Semua Unsur Piket --</option>
              <?php foreach ($unsur_piket as $u): ?>
              <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nama_unsur']); ?></option>
              <?php endforeach; ?>
              <option value="spkt">SPKT</option>
            </select>
          </div>

          <!-- 2. Bagian -->
          <div class="mb-3">
            <label class="form-label fw-semibold">2. Bagian / Satuan <span class="text-danger">*</span></label>
            <select class="form-select" id="tim_bagian" name="id_bagian" onchange="onBagianChange(this.value)" required>
              <option value="">-- Pilih Bagian --</option>
              <?php foreach ($bagian_piket as $b): ?>
              <option value="<?php echo $b['id']; ?>"
                      data-unsur="<?php echo $b['id_unsur']; ?>"
                      data-spkt="<?php echo ($b['id']==20)?'1':'0'; ?>">
                <?php echo htmlspecialchars($b['nama_bagian']); ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div id="siklusBagianInfo" class="mt-2"></div>
          </div>

          <!-- 3. Nama Tim -->
          <div class="mb-3">
            <label class="form-label fw-semibold">3. Nama Tim / Regu <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="tim_nama" name="nama_tim"
                   placeholder="Contoh: Regu A, Tim Piket Lantas 1" required>
          </div>

          <!-- 4. Jenis -->
          <div class="mb-3">
            <label class="form-label fw-semibold">4. Jenis <span class="text-danger">*</span></label>
            <select class="form-select" id="tim_jenis" name="jenis" required>
              <option value="piket">Piket Harian</option>
              <option value="satuan_tugas">Satuan Tugas</option>
              <option value="kegiatan">Rencana Kegiatan</option>
            </select>
          </div>

          <!-- 5. Posisi Fase -->
          <div class="mb-3">
            <label class="form-label fw-semibold">5. Posisi dalam Siklus Piket</label>
            <select class="form-select" id="tim_fase" name="fase_siklus_id" onchange="onFaseChange(this.value)">
              <option value="">-- Pilih Bagian dahulu --</option>
            </select>
            <div class="form-text">Pilih bagian dulu untuk melihat fase yang tersedia.</div>
          </div>

          <!-- 6. Jam & Durasi -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">6. Jam Mulai</label>
              <input type="time" class="form-control" id="tim_jam_mulai" name="jam_mulai_aktif" step="60">
              <div class="form-text">Kosong = ikut siklus default</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Durasi (jam)</label>
              <input type="number" class="form-control form-control-lg" id="tim_durasi" name="durasi_jam"
                     min="0.5" max="24" step="0.5" placeholder="8">
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <div class="w-100">
                <label class="form-label fw-semibold text-muted">Jam Selesai</label>
                <div class="form-control bg-light fw-bold" id="tim_jam_selesai">--:--</div>
              </div>
            </div>
          </div>

          <!-- Keterangan -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <textarea class="form-control" id="tim_keterangan" name="keterangan" rows="2" placeholder="Deskripsi singkat..."></textarea>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="tim_aktif" name="is_active" value="1" checked>
            <label class="form-check-label" for="tim_aktif">Tim Aktif</label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="simpanTim()">
          <i class="fas fa-save me-1"></i> Simpan Tim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL SIKLUS ══ -->
<div class="modal fade" id="siklusModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-sync-alt me-2"></i>Siklus Piket: <span id="siklusNamaBagian">—</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Tipe Siklus</label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="siklusType" id="siklusKhusus" value="khusus" checked onchange="onSiklusTypeChange()">
            <label class="form-check-label" for="siklusKhusus">
              Siklus Khusus (per Bagian)
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="siklusType" id="siklusUmum" value="umum" onchange="onSiklusTypeChange()">
            <label class="form-check-label" for="siklusUmum">
              Siklus Umum (berlaku untuk semua bagian)
            </label>
          </div>
        </div>
        <div class="mb-3" id="siklusBagianPickerWrap">
          <label class="form-label fw-semibold">Pilih Bagian</label>
          <select class="form-select" id="siklusBagianPicker"
                  onchange="loadSiklusFase(this.value, this.options[this.selectedIndex].text)">
            <option value="">-- Pilih --</option>
            <?php foreach ($bagian_piket as $b): ?>
            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nama_bagian']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="siklusFaseContainer">
          <p class="text-muted text-center small">Pilih bagian untuk mengatur siklus fase piket.</p>
        </div>
        <button class="btn btn-sm btn-outline-success mt-2" id="btnTambahFase"
                style="display:none" onclick="tambahBarisFase()">
          <i class="fas fa-plus me-1"></i> Tambah Fase
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" id="btnSimpanSiklus"
                style="display:none" onclick="simpanSiklus()">
          <i class="fas fa-save me-1"></i> Simpan Siklus
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL COPY SIKLUS ══ -->
<div class="modal fade" id="copySiklusModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Copy Siklus Umum ke: <span id="copyTargetBagian"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Fase yang akan dicopy:</label>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="selectAllFases" onchange="toggleAllFases(this)">
            <label class="form-check-label fw-semibold" for="selectAllFases">
              Pilih Semua
            </label>
          </div>
          <div id="faseSelectionContainer" class="border p-3 rounded bg-light" style="max-height:300px;overflow-y:auto">
            <!-- Fase checkboxes will be inserted here -->
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" onclick="executeCopySiklus()">
          <i class="fas fa-save me-1"></i> Copy
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL ANGGOTA ══ -->
<div class="modal fade" id="anggotaModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-users me-2"></i>Anggota: <span id="anggotaTimNama"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5">
            <h6 class="fw-semibold text-muted mb-2">Personil Tersedia</h6>
            <input type="text" class="form-control form-control-sm mb-2" id="filterPersonil"
                   placeholder="Cari nama / NRP..." oninput="renderPersonilLists()">
            <div id="personilTersedia" class="border rounded p-2" style="height:360px;overflow-y:auto"></div>
          </div>
          <div class="col-md-2 d-flex flex-column align-items-center justify-content-center gap-3">
            <button class="btn btn-success btn-sm px-3" onclick="tambahTerpilih()" title="Tambah">&#187;</button>
            <button class="btn btn-danger btn-sm px-3" onclick="hapusTerpilih()" title="Keluarkan">&#171;</button>
          </div>
          <div class="col-md-5">
            <h6 class="fw-semibold text-success mb-2">Anggota Tim <span id="jumlahAnggota" class="badge bg-success">0</span></h6>
            <div id="anggotaTim" class="border rounded p-2" style="height:360px;overflow-y:auto"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" onclick="simpanAnggota()">
          <i class="fas fa-save me-1"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL JADWAL ══ -->
<div class="modal fade" id="jadwalTimModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Buat Jadwal: <span id="jadwalTimNama"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="jadwalTimForm">
          <input type="hidden" id="jt_tim_id" name="tim_id">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
              <select class="form-select" id="jt_shift" name="shift_type" required>
                <option value="PAGI">Pagi (06:00&#8211;14:00)</option>
                <option value="SIANG">Siang (14:00&#8211;22:00)</option>
                <option value="MALAM">Malam (22:00&#8211;06:00)</option>
                <option value="FULL_DAY">Full Day (07:00&#8211;16:00)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lokasi</label>
              <input type="text" class="form-control" id="jt_lokasi" name="location">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="jt_tgl_mulai" name="start_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="jt_tgl_selesai" name="end_date" required>
            </div>
          </div>
          <hr>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Pengulangan</label>
              <select class="form-select" id="jt_recurrence" name="recurrence_type" onchange="toggleRecOpts(this.value)">
                <option value="none">Tidak Berulang</option>
                <option value="daily">Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
                <option value="yearly">Tahunan</option>
              </select>
            </div>
            <div class="col-md-4" id="jt_intGroup" style="display:none">
              <label class="form-label fw-semibold">Setiap</label>
              <div class="input-group">
                <input type="number" class="form-control" id="jt_interval"
                       name="recurrence_interval" min="1" value="1">
                <span class="input-group-text" id="jt_intLabel">hari</span>
              </div>
            </div>
          </div>
          <div class="mb-3" id="jt_daysGroup" style="display:none">
            <label class="form-label fw-semibold">Hari</label>
            <div class="d-flex gap-2 flex-wrap">
              <?php foreach(['1'=>'Sen','2'=>'Sel','3'=>'Rab','4'=>'Kam','5'=>'Jum','6'=>'Sab','0'=>'Min'] as $v=>$l): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="recurrence_days[]"
                       value="<?php echo $v; ?>" id="jday_<?php echo $v; ?>">
                <label class="form-check-label fw-bold" for="jday_<?php echo $v; ?>"><?php echo $l; ?></label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <textarea class="form-control" id="jt_keterangan" name="description" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning fw-bold" onclick="simpanJadwalTim()">
          <i class="fas fa-calendar-check me-1"></i> Generate Jadwal
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL FATIGUE CHECK ══ -->
<div class="modal fade" id="fatigueModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-bed me-2"></i>Fatigue Check — Cek Jeda Istirahat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Personil (NRP)</label>
            <select class="form-select" id="fatiguePersonilId">
              <option value="">-- Pilih Personil --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal</label>
            <input type="date" class="form-control" id="fatigueTanggal">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Min Jeda (jam)</label>
            <input type="number" class="form-control" id="fatigueMinJeda" value="12" min="1" max="48" step="1">
          </div>
        </div>
        <button class="btn btn-warning btn-sm mb-3" onclick="runFatigueCheck()"><i class="fas fa-search me-1"></i>Cek Sekarang</button>
        <div id="fatigueResult"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL SWAP SHIFT ══ -->
<div class="modal fade" id="swapShiftModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Tukar Jadwal Shift</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Tukar jadwal piket antara 2 personil pada hari yang sama.</p>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Jadwal 1 (Schedule ID)</label>
            <input type="number" class="form-control" id="swapId1" placeholder="ID Jadwal 1">
            <div class="form-text" id="swapInfo1">Pilih dari tabel dashboard di bawah</div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Jadwal 2 (Schedule ID)</label>
            <input type="number" class="form-control" id="swapId2" placeholder="ID Jadwal 2">
            <div class="form-text" id="swapInfo2">Pilih dari tabel dashboard di bawah</div>
          </div>
        </div>
        <div id="swapResult"></div>
        <div class="card bg-light mt-3">
          <div class="card-body py-2">
            <h6 class="fw-semibold small mb-2">Jadwal yang bisa ditukar (hari ini):</h6>
            <div class="table-responsive" style="max-height:250px;overflow-y:auto">
              <table class="table table-sm table-bordered mb-0" style="font-size:.8rem">
                <thead class="table-light"><tr><th>ID</th><th>Personil</th><th>Tim</th><th>Shift</th><th>Jam</th><th>Pilih</th></tr></thead>
                <tbody id="swapScheduleList">
                  <tr><td colspan="6" class="text-center text-muted">Memuat...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-info text-white" onclick="executeSwapShift()">
          <i class="fas fa-exchange-alt me-1"></i> Tukar Jadwal
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const API = '../api/tim_piket_api.php';
const siklusData = <?php echo json_encode($siklus_by_bagian); ?>;

// ── Jam Selesai ──────────────────────────────────────────────────────────────
function calcJS(jm, dur) {
    if (!jm || !dur) return '--:--';
    const [h,m] = jm.split(':').map(Number);
    const tot = h*60 + m + Math.round(parseFloat(dur)*60);
    return String(Math.floor(tot/60)%24).padStart(2,'0')+':'+String(tot%60).padStart(2,'0');
}
function updateJamSelesai() {
    document.getElementById('tim_jam_selesai').textContent =
        calcJS(document.getElementById('tim_jam_mulai').value,
               document.getElementById('tim_durasi').value);
}
document.getElementById('tim_jam_mulai').addEventListener('change', updateJamSelesai);
document.getElementById('tim_durasi').addEventListener('input',  updateJamSelesai);

// ── Filter Bagian by Unsur ───────────────────────────────────────────────────
function onUnsurChange(unsurId) {
    const sel  = document.getElementById('tim_bagian');
    const opts = sel.querySelectorAll('option[value]');
    opts.forEach(o => {
        if (!o.value) return;
        if (!unsurId)          { o.hidden = false; return; }
        if (unsurId === 'spkt'){ o.hidden = o.dataset.spkt !== '1'; return; }
        o.hidden = o.dataset.unsur != unsurId;
    });
    sel.value = ''; onBagianChange('');
}

// ── Bagian Change ─────────────────────────────────────────────────────────────
function onBagianChange(bid) {
    const faseSel = document.getElementById('tim_fase');
    const info    = document.getElementById('siklusBagianInfo');
    faseSel.innerHTML = '<option value="">-- Tanpa Fase --</option>';
    info.innerHTML = '';
    if (!bid) return;
    const fases = siklusData[bid];
    if (!fases || !fases.length) {
        info.innerHTML = `<small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Belum ada siklus. <a href="#" onclick="openSiklusModal(${bid});return false">Buat siklus</a></small>`;
        return;
    }
    let badges = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Siklus: </small>';
    fases.forEach(f => {
        const jm  = f.jam_mulai_default.substring(0,5);
        const jsl = calcJS(jm, f.durasi_jam);
        faseSel.innerHTML += `<option value="${f.id}">${f.urutan}. ${f.nama_fase} &#183; ${jm}&#8211;${jsl} (${f.durasi_jam}j)</option>`;
        badges += `<span class="badge bg-secondary me-1">${f.nama_fase} ${jm}&#8211;${jsl}</span>`;
    });
    info.innerHTML = badges;
}
function onFaseChange(faseId) {
    const bid   = document.getElementById('tim_bagian').value;
    const fases = siklusData[bid] || [];
    const fase  = fases.find(f => f.id == faseId);
    if (!fase) return;
    document.getElementById('tim_jam_mulai').value = fase.jam_mulai_default.substring(0,5);
    document.getElementById('tim_durasi').value    = fase.durasi_jam;
    updateJamSelesai();
}

// ── Modal Tim ─────────────────────────────────────────────────────────────────
function openTambahTim(preFillBagianId) {
    document.getElementById('timForm').reset();
    document.getElementById('tim_id').value = '';
    document.getElementById('tim_aktif').checked = true;
    document.getElementById('tim_jam_selesai').textContent = '--:--';
    document.getElementById('siklusBagianInfo').innerHTML = '';
    document.getElementById('tim_fase').innerHTML = '<option value="">-- Pilih Bagian dahulu --</option>';
    document.getElementById('timModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Tim';
    if (preFillBagianId) {
        document.getElementById('tim_bagian').value = preFillBagianId;
        onBagianChange(preFillBagianId);
    }
    new bootstrap.Modal(document.getElementById('timModal')).show();
}
function editTim(t) {
    document.getElementById('tim_id').value        = t.id;
    document.getElementById('tim_unsur').value     = t.id_unsur  || '';
    document.getElementById('tim_bagian').value    = t.id_bagian || '';
    onBagianChange(t.id_bagian);
    setTimeout(() => document.getElementById('tim_fase').value = t.fase_siklus_id || '', 80);
    document.getElementById('tim_nama').value       = t.nama_tim;
    document.getElementById('tim_jenis').value      = t.jenis;
    document.getElementById('tim_jam_mulai').value  = t.jam_mulai_aktif ? t.jam_mulai_aktif.substring(0,5) : '';
    document.getElementById('tim_durasi').value     = t.durasi_jam || '';
    document.getElementById('tim_keterangan').value = t.keterangan || '';
    document.getElementById('tim_aktif').checked    = t.is_active == 1;
    updateJamSelesai();
    document.getElementById('timModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Tim';
    new bootstrap.Modal(document.getElementById('timModal')).show();
}
function simpanTim() {
    const form = document.getElementById('timForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const fd = new FormData(form);
    fd.append('action', document.getElementById('tim_id').value ? 'update_tim' : 'create_tim');
    if (!document.getElementById('tim_aktif').checked) fd.set('is_active','0');
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) { bootstrap.Modal.getInstance(document.getElementById('timModal')).hide(); location.reload(); }
        else alert('Gagal: '+(d.error||d.message));
    }).catch(e=>alert('Error: '+e));
}
function hapusTim(id, nama) {
    if (!confirm(`Hapus tim "${nama}"?\nSemua anggota tim ini juga akan dihapus.`)) return;
    const fd = new FormData(); fd.append('action','delete_tim'); fd.append('id',id);
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) location.reload(); else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Drag & Drop Fase ──────────────────────────────────────────────────────────
let dragTimId = null;
function dragStart(e, timId) { dragTimId = timId; e.dataTransfer.effectAllowed = 'move'; }
function dropTim(e, faseId) {
    e.preventDefault(); e.currentTarget.classList.remove('dragover');
    if (!dragTimId) return;
    const fd = new FormData();
    fd.append('action','geser_fase'); fd.append('tim_id',dragTimId); fd.append('fase_siklus_id',faseId||0);
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) location.reload(); else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Siklus Modal ──────────────────────────────────────────────────────────────
let faseRows = [], siklusBid = 0, isSiklusUmum = false;
let copyTargetBagianId = null, copyTargetBagianName = null, siklusUmumData = [];

async function copySiklusUmum(bagianId, namaBagian) {
    copyTargetBagianId = bagianId;
    copyTargetBagianName = namaBagian;
    
    // Ambil siklus umum
    const r = await fetch(API+'?action=get_siklus&id_bagian=umum&is_umum=1');
    const d = await r.json();
    if (!d.success || !d.data || !d.data.length) {
        alert('Siklus umum tidak ditemukan. Buat siklus umum dahulu.');
        return;
    }
    
    siklusUmumData = d.data;
    
    // Tampilkan modal pemilihan fase
    document.getElementById('copyTargetBagian').textContent = namaBagian;
    renderFaseSelection(siklusUmumData);
    new bootstrap.Modal(document.getElementById('copySiklusModal')).show();
}

function renderFaseSelection(fases) {
    const container = document.getElementById('faseSelectionContainer');
    let html = '';
    fases.forEach((f, i) => {
        const jamMul = f.jam_mulai_default ? f.jam_mulai_default.substring(0,5) : '07:00';
        html += `
            <div class="form-check mb-2">
                <input class="form-check-input fase-checkbox" type="checkbox" 
                       id="fase_${i}" value="${i}" checked>
                <label class="form-check-label" for="fase_${i}">
                    <strong>${f.nama_fase}</strong> - ${jamMul} (${f.durasi_jam} jam)
                </label>
            </div>
        `;
    });
    container.innerHTML = html;
}

function toggleAllFases(checkbox) {
    const checkboxes = document.querySelectorAll('.fase-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

async function executeCopySiklus() {
    const checkboxes = document.querySelectorAll('.fase-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu fase untuk dicopy.');
        return;
    }
    
    if (!confirm('Copy ' + checkboxes.length + ' fase ke bagian ' + copyTargetBagianName + '?\nSiklus khusus yang sudah ada akan ditimpa.')) return;
    
    // Ambil fase yang dipilih
    const selectedIndices = Array.from(checkboxes).map(cb => parseInt(cb.value)).sort((a,b) => a-b);
    const selectedFases = selectedIndices.map(i => siklusUmumData[i]);
    
    // Copy fase yang dipilih ke bagian target
    const fd = new FormData();
    fd.append('action','save_siklus');
    fd.append('id_bagian',copyTargetBagianId);
    fd.append('is_umum','0');
    fd.append('fases',JSON.stringify(selectedFases.map((f, idx) => ({
        id:null,
        id_bagian:copyTargetBagianId,
        nama_fase:f.nama_fase,
        urutan:idx + 1,
        durasi_jam:f.durasi_jam,
        jam_mulai_default:f.jam_mulai_default,
        jam_mulai_mode:f.jam_mulai_mode,
        is_wajib:f.is_wajib,
        keterangan:f.keterangan
    }))));
    
    const res = await fetch(API,{method:'POST',body:fd});
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('copySiklusModal')).hide();
        alert('✅ ' + checkboxes.length + ' fase berhasil dicopy ke ' + copyTargetBagianName);
        location.reload();
    } else {
        alert('Gagal copy siklus: '+(data.error||data.message));
    }
}

async function hapusSiklusBagian(bagianId, namaBagian) {
    if (!confirm('Hapus semua siklus fase untuk bagian ' + namaBagian + '?\nTim yang berada di fase akan dipindahkan ke "Belum Ditempatkan".')) return;
    
    const fd = new FormData();
    fd.append('action','delete_siklus_bagian');
    fd.append('id_bagian',bagianId);
    
    const res = await fetch(API,{method:'POST',body:fd});
    const data = await res.json();
    if (data.success) {
        alert('✅ Siklus berhasil dihapus');
        location.reload();
    } else {
        alert('Gagal hapus siklus: '+(data.error||data.message));
    }
}

function toggleAllFasesInBagian(bagianId, checkbox) {
    const checkboxes = document.querySelectorAll('.fase-checkbox-' + bagianId);
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

async function hapusFaseTerpilih(bagianId, namaBagian) {
    const checkboxes = document.querySelectorAll('.fase-checkbox-' + bagianId + ':checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu fase untuk dihapus.');
        return;
    }
    
    const faseIds = Array.from(checkboxes).map(cb => cb.value);
    if (!confirm('Hapus ' + faseIds.length + ' fase yang dipilih?\nTim yang berada di fase tersebut akan dipindahkan ke "Belum Ditempatkan".')) return;
    
    const fd = new FormData();
    fd.append('action','delete_fases');
    fd.append('fase_ids',JSON.stringify(faseIds));
    
    const res = await fetch(API,{method:'POST',body:fd});
    const data = await res.json();
    if (data.success) {
        alert('✅ ' + faseIds.length + ' fase berhasil dihapus');
        location.reload();
    } else {
        alert('Gagal hapus fase: '+(data.error||data.message));
    }
}

async function hapusSatuFase(faseId, namaFase) {
    if (!confirm('Hapus fase "' + namaFase + '"?\nTim yang berada di fase ini akan dipindahkan ke "Belum Ditempatkan".')) return;
    
    const fd = new FormData();
    fd.append('action','delete_fases');
    fd.append('fase_ids',JSON.stringify([faseId]));
    
    const res = await fetch(API,{method:'POST',body:fd});
    const data = await res.json();
    if (data.success) {
        alert('✅ Fase berhasil dihapus');
        location.reload();
    } else {
        alert('Gagal hapus fase: '+(data.error||data.message));
    }
}

async function editFaseTerpilih(bagianId, namaBagian) {
    const checkboxes = document.querySelectorAll('.fase-checkbox-' + bagianId + ':checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu fase untuk diedit.');
        return;
    }
    
    // Buka modal siklus untuk bagian ini
    siklusBid = bagianId;
    isSiklusUmum = false;
    document.getElementById('siklusKhusus').checked = true;
    document.getElementById('siklusBagianPickerWrap').style.display = '';
    document.getElementById('siklusBagianPicker').value = bagianId;
    document.getElementById('siklusNamaBagian').textContent = namaBagian;
    
    // Load fase yang ada (hanya fase yang dipilih)
    const r = await fetch(API+'?action=get_siklus&id_bagian='+bagianId);
    const d = await r.json();
    if (d.success && d.data) {
        const faseIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
        faseRows = d.data.filter(f => faseIds.includes(f.id)).map(f=>({...f}));
        renderFaseTable();
        document.getElementById('btnTambahFase').style.display = '';
        document.getElementById('btnSimpanSiklus').style.display = '';
    }
    
    new bootstrap.Modal(document.getElementById('siklusModal')).show();
}

async function editSatuFase(faseId, bagianId, namaBagian) {
    // Load fase tunggal ke dalam modal
    siklusBid = bagianId;
    isSiklusUmum = false;
    document.getElementById('siklusKhusus').checked = true;
    document.getElementById('siklusBagianPickerWrap').style.display = '';
    document.getElementById('siklusBagianPicker').value = bagianId;
    document.getElementById('siklusNamaBagian').textContent = namaBagian;
    
    // Load fase yang ada
    const r = await fetch(API+'?action=get_siklus&id_bagian='+bagianId);
    const d = await r.json();
    if (d.success && d.data) {
        faseRows = d.data.filter(f => f.id === faseId).map(f=>({...f}));
        renderFaseTable();
        document.getElementById('btnTambahFase').style.display = '';
        document.getElementById('btnSimpanSiklus').style.display = '';
    }
    
    new bootstrap.Modal(document.getElementById('siklusModal')).show();
}

function onSiklusTypeChange() {
    const isUmum = document.getElementById('siklusUmum').checked;
    isSiklusUmum = isUmum;
    
    const pickerWrap = document.getElementById('siklusBagianPickerWrap');
    
    if (isUmum) {
        // Siklus umum - sembunyikan picker bagian
        pickerWrap.style.display = 'none';
        document.getElementById('siklusNamaBagian').textContent = 'Siklus Umum';
        siklusBid = null;
        loadSiklusFase('umum', 'Siklus Umum');
    } else {
        // Siklus khusus - tampilkan picker bagian
        pickerWrap.style.display = '';
        document.getElementById('siklusNamaBagian').textContent = '—';
        siklusBid = 0;
        document.getElementById('siklusFaseContainer').innerHTML = '<p class="text-muted text-center small">Pilih bagian untuk mengatur siklus.</p>';
        document.getElementById('btnTambahFase').style.display = 'none';
        document.getElementById('btnSimpanSiklus').style.display = 'none';
    }
}

async function rotasiFase(bagianId, namaBagian) {
    if (!confirm('Rotasi semua tim ' + namaBagian + ' ke fase berikutnya?\nSemua tim akan bergerak satu langkah maju dalam siklus.')) return;
    const fd = new FormData();
    fd.append('action', 'rotasi_fase_semua');
    fd.append('id_bagian', bagianId);
    try {
        const r    = await fetch(API, { method:'POST', body:fd });
        const data = await r.json();
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else { alert('Gagal rotasi: ' + (data.error || data.message)); }
    } catch(e) { alert('Network error'); }
}

function openSiklusModal(bid, nama) {
    siklusBid = bid || 0;
    document.getElementById('siklusNamaBagian').textContent = nama || '—';
    document.getElementById('siklusFaseContainer').innerHTML = '<p class="text-muted text-center small">Pilih bagian untuk mengatur siklus.</p>';
    document.getElementById('btnTambahFase').style.display   = 'none';
    document.getElementById('btnSimpanSiklus').style.display = 'none';
    const pw = document.getElementById('siklusBagianPickerWrap');
    if (bid) { pw.style.display='none'; document.getElementById('siklusBagianPicker').value=bid; loadSiklusFase(bid,nama); }
    else     { pw.style.display=''; document.getElementById('siklusBagianPicker').value=''; }
    new bootstrap.Modal(document.getElementById('siklusModal')).show();
}
function loadSiklusFase(bid, nama) {
    if (!bid) return;
    siklusBid = bid === 'umum' ? null : bid;
    if (nama) document.getElementById('siklusNamaBagian').textContent = nama;
    document.getElementById('btnTambahFase').style.display   = '';
    document.getElementById('btnSimpanSiklus').style.display = '';
    document.getElementById('siklusFaseContainer').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    
    let url = API+'?action=get_siklus&id_bagian='+bid;
    if (bid === 'umum') url += '&is_umum=1';
    
    fetch(url).then(r=>r.json()).then(d=>{
        faseRows = (d.data||[]).map(f=>({...f}));
        renderFaseTable();
    });
}
function renderFaseTable() {
    const c = document.getElementById('siklusFaseContainer');
    if (!faseRows.length) { c.innerHTML='<p class="text-muted text-center small py-3">Belum ada fase. Klik "Tambah Fase".</p>'; return; }
    let html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead class="table-light"><tr><th>#</th><th>Nama Fase</th><th>Durasi (jam)</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Mode</th><th>Wajib</th><th></th></tr></thead><tbody>';
    faseRows.forEach((f,i) => {
        const jm  = f.jam_mulai_default ? f.jam_mulai_default.substring(0,5) : '07:00';
        const jsl = calcJS(jm, f.durasi_jam);
        html += `<tr>
            <td class="text-muted">${i+1}</td>
            <td><input class="form-control form-control-sm" value="${f.nama_fase}" onchange="faseRows[${i}].nama_fase=this.value"></td>
            <td><input type="number" class="form-control form-control-sm" value="${f.durasi_jam}" min="0.5" max="24" step="0.5" style="width:75px" onchange="faseRows[${i}].durasi_jam=this.value;refreshJS(${i})"></td>
            <td><input type="time" class="form-control form-control-sm" id="fjm_${i}" value="${jm}" style="width:110px" step="60" onchange="faseRows[${i}].jam_mulai_default=this.value+':00';refreshJS(${i});propagate(${i+1})"></td>
            <td><span class="badge bg-secondary" id="fjs_${i}">${jsl}</span></td>
            <td><select class="form-select form-select-sm" style="width:85px" onchange="faseRows[${i}].jam_mulai_mode=this.value;if(this.value==='auto')propagate(${i})">
                <option value="auto" ${f.jam_mulai_mode!=='manual'?'selected':''}>Auto</option>
                <option value="manual" ${f.jam_mulai_mode==='manual'?'selected':''}>Manual</option>
            </select></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" ${f.is_wajib?'checked':''} onchange="faseRows[${i}].is_wajib=this.checked?1:0"></td>
            <td><button class="btn btn-sm btn-outline-danger py-0" onclick="faseRows.splice(${i},1);renderFaseTable()"><i class="fas fa-times"></i></button></td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    c.innerHTML = html;
}
function refreshJS(i) {
    const f  = faseRows[i]; if (!f) return;
    const jm = f.jam_mulai_default ? f.jam_mulai_default.substring(0,5) : '07:00';
    const el = document.getElementById('fjs_'+i); if (el) el.textContent = calcJS(jm, f.durasi_jam);
}
function propagate(start) {
    for (let i=start; i<faseRows.length; i++) {
        if (faseRows[i].jam_mulai_mode !== 'auto' || !faseRows[i-1]) continue;
        const prev = faseRows[i-1];
        const aj   = calcJS(prev.jam_mulai_default.substring(0,5), prev.durasi_jam);
        faseRows[i].jam_mulai_default = aj+':00';
        const el = document.getElementById('fjm_'+i); if (el) el.value = aj;
        refreshJS(i);
    }
}
function tambahBarisFase() {
    const prev   = faseRows.length ? faseRows[faseRows.length-1] : null;
    const defJam = prev ? calcJS(prev.jam_mulai_default.substring(0,5), prev.durasi_jam)+':00' : '07:00:00';
    faseRows.push({ id:null, id_bagian:siklusBid, nama_fase:'Fase '+(faseRows.length+1),
        urutan:faseRows.length+1, durasi_jam:8, jam_mulai_default:defJam,
        jam_mulai_mode:'auto', is_wajib:1, keterangan:'' });
    renderFaseTable();
}
function simpanSiklus() {
    if (!isSiklusUmum && !siklusBid) { alert('Pilih bagian dahulu.'); return; }
    if (!faseRows.length) { alert('Minimal 1 fase harus ada.'); return; }
    const fd = new FormData();
    fd.append('action','save_siklus');
    fd.append('id_bagian',siklusBid || '');
    fd.append('is_umum', isSiklusUmum ? '1' : '0');
    fd.append('fases',JSON.stringify(faseRows));
    document.getElementById('btnSimpanSiklus').disabled = true;
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        document.getElementById('btnSimpanSiklus').disabled = false;
        if (d.success) { bootstrap.Modal.getInstance(document.getElementById('siklusModal')).hide(); location.reload(); }
        else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Modal Anggota ─────────────────────────────────────────────────────────────
let curTimId=null, allP=[], anggSet=new Set();
function kelolaAnggota(timId, timNama) {
    curTimId = timId;
    document.getElementById('anggotaTimNama').textContent = timNama;
    document.getElementById('personilTersedia').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';
    document.getElementById('anggotaTim').innerHTML = '';
    document.getElementById('jumlahAnggota').textContent = '0';
    Promise.all([
        fetch(API+'?action=get_personil_all').then(r=>r.json()),
        fetch(API+'?action=get_anggota&tim_id='+timId).then(r=>r.json())
    ]).then(([pd,ad]) => {
        allP    = pd.data || [];
        anggPeran = {};
        (ad.data||[]).forEach(a => { anggPeran[a.personil_id] = a.peran || 'anggota'; });
        anggSet = new Set((ad.data||[]).map(a=>a.personil_id));
        renderPersonilLists();
        new bootstrap.Modal(document.getElementById('anggotaModal')).show();
    });
}
let anggPeran = {}; // {nrp: 'ketua'|'wakil'|'anggota'}
function renderPersonilLists() {
    const q   = document.getElementById('filterPersonil').value.toLowerCase();
    const mkI = (p,sel) => `<div class="d-flex align-items-center gap-2 p-2 border-bottom personil-item${sel?' table-active':''}" style="cursor:pointer" data-nrp="${p.nrp}" onclick="toggleP(this)">
        <div><div class="fw-semibold small">${p.nama}</div><div class="text-muted" style="font-size:.7rem">${p.nrp} &middot; ${p.pangkat||''} &middot; ${p.bagian||''}</div></div></div>`;
    const mkA = (p) => {
        const peran = anggPeran[p.nrp] || 'anggota';
        const badge = peran === 'ketua' ? 'bg-danger' : peran === 'wakil' ? 'bg-warning text-dark' : 'bg-secondary';
        return `<div class="d-flex align-items-center justify-content-between p-2 border-bottom personil-item" data-nrp="${p.nrp}">
            <div onclick="toggleP(this.parentElement)" style="cursor:pointer;flex:1">
                <div class="fw-semibold small">${p.nama} <span class="badge ${badge}" style="font-size:.6rem">${peran}</span></div>
                <div class="text-muted" style="font-size:.7rem">${p.nrp} &middot; ${p.pangkat||''} &middot; ${p.bagian||''}</div>
            </div>
            <select class="form-select form-select-sm" style="width:90px;font-size:.7rem" onchange="anggPeran['${p.nrp}']=this.value;renderPersonilLists()">
                <option value="anggota" ${peran==='anggota'?'selected':''}>Anggota</option>
                <option value="ketua" ${peran==='ketua'?'selected':''}>Ketua</option>
                <option value="wakil" ${peran==='wakil'?'selected':''}>Wakil</option>
            </select>
        </div>`;
    };
    const av = allP.filter(p => !anggSet.has(p.nrp) && (!q||p.nama.toLowerCase().includes(q)||p.nrp.includes(q)));
    const am = allP.filter(p =>  anggSet.has(p.nrp));
    document.getElementById('personilTersedia').innerHTML = av.length ? av.map(p=>mkI(p,false)).join('') : '<div class="text-center text-muted py-3 small">Semua sudah masuk tim</div>';
    document.getElementById('anggotaTim').innerHTML      = am.length ? am.map(p=>mkA(p)).join('') : '<div class="text-center text-muted py-3 small">Belum ada anggota</div>';
    document.getElementById('jumlahAnggota').textContent = am.length;
}
function toggleP(el) { el.classList.toggle('table-active'); el.style.outline = el.classList.contains('table-active')?'2px solid #0d6efd':''; }
function tambahTerpilih() { document.querySelectorAll('#personilTersedia .table-active').forEach(el=>{anggSet.add(el.dataset.nrp);if(!anggPeran[el.dataset.nrp])anggPeran[el.dataset.nrp]='anggota';}); renderPersonilLists(); }
function hapusTerpilih()  { document.querySelectorAll('#anggotaTim .table-active').forEach(el=>{anggSet.delete(el.dataset.nrp);delete anggPeran[el.dataset.nrp];}); renderPersonilLists(); }
function simpanAnggota() {
    const anggota = [];
    anggSet.forEach(nrp => anggota.push({personil_id: nrp, peran: anggPeran[nrp] || 'anggota'}));
    const fd = new FormData(); fd.append('action','save_anggota_peran'); fd.append('tim_id',curTimId);
    fd.append('anggota', JSON.stringify(anggota));
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if (d.success) { bootstrap.Modal.getInstance(document.getElementById('anggotaModal')).hide(); location.reload(); }
        else alert('Gagal: '+(d.error||d.message));
    });
}

// ── Modal Jadwal ──────────────────────────────────────────────────────────────
function buatJadwalDariTim(t) {
    document.getElementById('jt_tim_id').value = t.id;
    document.getElementById('jadwalTimNama').textContent = t.nama_tim;
    document.getElementById('jadwalTimForm').reset();
    document.getElementById('jt_tim_id').value = t.id;
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('jt_tgl_mulai').value  = today;
    document.getElementById('jt_tgl_selesai').value = today;
    new bootstrap.Modal(document.getElementById('jadwalTimModal')).show();
}
function toggleRecOpts(val) {
    document.getElementById('jt_intGroup').style.display  = val==='none'?'none':'';
    document.getElementById('jt_daysGroup').style.display = val==='weekly'?'':'none';
    const lb = document.getElementById('jt_intLabel');
    if (lb) lb.textContent = {daily:'hari',weekly:'minggu',monthly:'bulan',yearly:'tahun'}[val]||'hari';
}
function simpanJadwalTim() {
    const form = document.getElementById('jadwalTimForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const s=document.getElementById('jt_tgl_mulai').value, e=document.getElementById('jt_tgl_selesai').value;
    if (e<s) { alert('Tanggal selesai tidak boleh sebelum tanggal mulai.'); return; }
    const fd = new FormData(form);
    fd.append('action','generate_jadwal_tim');
    fd.set('recurrence_days',[...document.querySelectorAll('input[name="recurrence_days[]"]:checked')].map(x=>x.value).join(','));
    const btn = document.querySelector('#jadwalTimModal .btn-warning');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
    fetch(API,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="fas fa-calendar-check me-1"></i> Generate Jadwal';
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('jadwalTimModal')).hide();
            alert(`\u2705 Berhasil generate ${d.count} jadwal!\n${d.message}`);
            window.location.href='../pages/calendar_dashboard.php';
        } else alert('Gagal: '+(d.error||d.message));
    }).catch(e=>{ btn.disabled=false; btn.innerHTML='<i class="fas fa-calendar-check me-1"></i> Generate Jadwal'; alert('Error: '+e); });
}

// ═══════════════════════════════════════════════════════════════════════════════
// DASHBOARD HARI INI
// ═══════════════════════════════════════════════════════════════════════════════
let dashboardLoaded = false;
async function loadDashboard() {
    try {
        const r = await fetch(API+'?action=dashboard_hari_ini');
        const d = await r.json();
        if (!d.success) { alert('Gagal memuat dashboard'); return; }
        dashboardLoaded = true;
        document.getElementById('dsTotalJadwal').textContent = d.stats.total_jadwal;
        document.getElementById('dsHadir').textContent = d.stats.hadir;
        document.getElementById('dsBelumCheckin').textContent = d.stats.belum_checkin;
        document.getElementById('dsTidakHadir').textContent = d.stats.tidak_hadir;
        const tbody = document.getElementById('dashboardTableBody');
        if (!d.jadwal.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada jadwal piket hari ini</td></tr>'; return; }
        tbody.innerHTML = d.jadwal.map((j,i) => {
            const statusBadge = {
                'hadir': '<span class="badge bg-success">Hadir</span>',
                'tidak_hadir': '<span class="badge bg-danger">Tidak Hadir</span>',
                'sakit': '<span class="badge bg-warning text-dark">Sakit</span>',
                'ijin': '<span class="badge bg-info text-dark">Ijin</span>',
                'terlambat': '<span class="badge bg-warning text-dark">Terlambat</span>',
            };
            const st = j.absensi_status ? (statusBadge[j.absensi_status] || '<span class="badge bg-secondary">'+j.absensi_status+'</span>') : '<span class="badge bg-light text-dark">Belum</span>';
            const jam = (j.start_time||'').substring(0,5) + ' - ' + (j.end_time||'').substring(0,5);
            return `<tr>
                <td class="text-muted">${i+1}</td>
                <td class="fw-semibold">${j.personil_name||'-'}</td>
                <td>${j.nama_pangkat||'-'}</td>
                <td>${j.nama_tim||'-'}</td>
                <td>${j.nama_bagian||'-'}</td>
                <td><span class="badge bg-primary">${j.shift_type||'-'}</span></td>
                <td class="small">${jam}</td>
                <td>${st}</td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

// ═══════════════════════════════════════════════════════════════════════════════
// KALENDER
// ═══════════════════════════════════════════════════════════════════════════════
let calBulan = new Date().getMonth() + 1, calTahun = new Date().getFullYear(), calLoaded = false;
const namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function changeCalendarMonth(delta) {
    calBulan += delta;
    if (calBulan < 1) { calBulan = 12; calTahun--; }
    if (calBulan > 12) { calBulan = 1; calTahun++; }
    loadCalendar();
}

async function loadCalendar() {
    calLoaded = true;
    document.getElementById('calendarTitle').textContent = namaBulan[calBulan] + ' ' + calTahun;
    document.getElementById('calendarGrid').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    try {
        const r = await fetch(API+'?action=calendar_data&bulan='+calBulan+'&tahun='+calTahun);
        const d = await r.json();
        if (!d.success) return;
        renderCalendar(d.data, calBulan, calTahun);
    } catch(e) { console.error(e); }
}

function renderCalendar(data, bulan, tahun) {
    const grid = document.getElementById('calendarGrid');
    const firstDay = new Date(tahun, bulan-1, 1).getDay();
    const daysInMonth = new Date(tahun, bulan, 0).getDate();
    const today = new Date().toISOString().split('T')[0];
    const shiftColor = {PAGI:'#ffc107',SIANG:'#0dcaf0',MALAM:'#6f42c1',FULL_DAY:'#198754'};

    let html = '<div class="d-grid" style="grid-template-columns:repeat(7,1fr);gap:2px">';
    ['Min','Sen','Sel','Rab','Kam','Jum','Sab'].forEach(d => {
        html += `<div class="text-center fw-bold small py-1 bg-light border rounded">${d}</div>`;
    });
    for (let i = 0; i < firstDay; i++) html += '<div></div>';
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${tahun}-${String(bulan).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        const isToday = dateStr === today;
        const entries = data[dateStr] || [];
        const border = isToday ? 'border-primary border-2' : 'border';
        html += `<div class="p-1 ${border} rounded bg-white" style="min-height:65px">
            <div class="small fw-bold ${isToday?'text-primary':''}">${day}</div>`;
        if (entries.length) {
            const grouped = {};
            entries.forEach(e => { grouped[e.shift_type] = (grouped[e.shift_type]||0) + 1; });
            Object.entries(grouped).forEach(([shift, count]) => {
                const c = shiftColor[shift] || '#6c757d';
                html += `<div class="rounded-1 px-1 mb-1" style="font-size:.6rem;background:${c};color:#fff">${shift}: ${count}</div>`;
            });
        }
        html += '</div>';
    }
    html += '</div>';
    grid.innerHTML = html;
}

// ═══════════════════════════════════════════════════════════════════════════════
// STATISTIK
// ═══════════════════════════════════════════════════════════════════════════════
let statLoaded = false;
async function loadStatistik() {
    statLoaded = true;
    const bulan = document.getElementById('statBulan').value;
    const tahun = document.getElementById('statTahun').value;
    const tbody = document.getElementById('statistikTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';
    try {
        const r = await fetch(API+'?action=statistik_personil&bulan='+bulan+'&tahun='+tahun);
        const d = await r.json();
        if (!d.success || !d.data.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data untuk periode ini</td></tr>'; return; }
        tbody.innerHTML = d.data.map((s,i) => {
            const pct = s.jumlah_jadwal > 0 ? Math.round((s.hadir / s.jumlah_jadwal) * 100) : 0;
            const pctColor = pct >= 80 ? 'text-success' : pct >= 50 ? 'text-warning' : 'text-danger';
            return `<tr>
                <td class="text-muted">${i+1}</td>
                <td class="fw-semibold">${s.personil_name||'-'}</td>
                <td>${s.nama_bagian||'-'}</td>
                <td>${s.jumlah_jadwal}</td>
                <td><strong>${s.total_jam||0}</strong> jam</td>
                <td><span class="badge bg-success">${s.hadir||0}</span></td>
                <td><span class="badge bg-danger">${s.absen||0}</span></td>
                <td class="fw-bold ${pctColor}">${pct}%</td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

// ═══════════════════════════════════════════════════════════════════════════════
// LOG ROTASI
// ═══════════════════════════════════════════════════════════════════════════════
let logLoaded = false;
async function loadLogRotasi() {
    logLoaded = true;
    const tbody = document.getElementById('logRotasiTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';
    try {
        const r = await fetch(API+'?action=get_rotasi_log&limit=30');
        const d = await r.json();
        if (!d.success || !d.data.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Belum ada riwayat rotasi</td></tr>'; return; }
        tbody.innerHTML = d.data.map((l,i) => {
            const waktu = new Date(l.created_at).toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
            const tipeBadge = l.tipe === 'otomatis' ? '<span class="badge bg-info">Otomatis</span>' : '<span class="badge bg-secondary">Manual</span>';
            return `<tr>
                <td class="text-muted">${i+1}</td>
                <td class="small">${waktu}</td>
                <td>${l.nama_bagian||'-'}</td>
                <td>${l.dari_fase||'-'}</td>
                <td>${l.ke_fase||'-'}</td>
                <td><strong>${l.jumlah_tim}</strong></td>
                <td>${tipeBadge}</td>
                <td>${l.oleh_nama||'Sistem'}</td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

// ═══════════════════════════════════════════════════════════════════════════════
// CETAK SPRIN
// ═══════════════════════════════════════════════════════════════════════════════
async function cetakSprin(bagianId, namaBagian) {
    try {
        const r = await fetch(API+'?action=cetak_sprin_data&id_bagian='+bagianId);
        const d = await r.json();
        if (!d.success) { alert('Gagal memuat data SPRIN'); return; }
        const w = window.open('','_blank','width=800,height=600');
        w.document.write(generateSprinHtml(d, namaBagian));
        w.document.close();
        w.focus();
        setTimeout(() => w.print(), 500);
    } catch(e) { alert('Error: '+e); }
}

function generateSprinHtml(d, namaBagian) {
    const tgl = new Date(d.tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
    let timRows = '';
    (d.tims||[]).forEach((t,ti) => {
        timRows += `<tr class="table-light"><td colspan="5" class="fw-bold">${ti+1}. ${t.nama_tim} ${t.nama_fase ? '('+t.nama_fase+')' : ''}</td></tr>`;
        (t.anggota||[]).forEach((a,ai) => {
            const peranBadge = a.peran === 'ketua' ? ' <strong>[Ketua]</strong>' : a.peran === 'wakil' ? ' [Wakil]' : '';
            timRows += `<tr><td class="text-center">${ai+1}</td><td>${a.pangkat||''}</td><td>${a.nama||'-'}</td><td>${a.nrp||'-'}</td><td>${a.jabatan||'-'}${peranBadge}</td></tr>`;
        });
    });
    return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>SPRIN Piket - ${namaBagian}</title>
    <style>body{font-family:'Times New Roman',serif;padding:40px;font-size:13px;color:#000}
    .kop{text-align:center;border-bottom:3px double #000;padding-bottom:10px;margin-bottom:20px}
    .kop h3{margin:0} .kop h4{margin:2px 0}
    table{width:100%;border-collapse:collapse;margin:15px 0}
    th,td{border:1px solid #000;padding:5px 8px;font-size:12px}
    th{background:#f0f0f0;text-align:center}
    .ttd{margin-top:50px;display:flex;justify-content:space-between}
    .ttd div{text-align:center;width:40%}
    @media print{body{padding:20px} .no-print{display:none}}</style></head>
    <body>
    <div class="kop">
        <h4>KEPOLISIAN NEGARA REPUBLIK INDONESIA</h4>
        <h4>DAERAH SUMATERA UTARA</h4>
        <h3>RESOR SAMOSIR</h3>
    </div>
    <div style="text-align:center;margin-bottom:20px">
        <h4 style="margin:0;text-decoration:underline">SURAT PERINTAH</h4>
        <p style="margin:5px 0">Nomor: SPRIN / _____ / _____ / ${new Date().getFullYear()}</p>
    </div>
    <p><strong>Dasar:</strong> Surat Perintah Kapolres Samosir</p>
    <p><strong>DIPERINTAHKAN:</strong></p>
    <p>Kepada personil yang namanya tersebut di bawah ini untuk melaksanakan tugas piket <strong>${namaBagian}</strong> pada tanggal <strong>${tgl}</strong>:</p>
    <table>
        <thead><tr><th>No</th><th>Pangkat</th><th>Nama</th><th>NRP</th><th>Jabatan</th></tr></thead>
        <tbody>${timRows || '<tr><td colspan="5" class="text-center">Belum ada data tim/anggota</td></tr>'}</tbody>
    </table>
    <p>Demikian Surat Perintah ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>
    <div class="ttd">
        <div></div>
        <div>
            <p>Samosir, ${tgl}</p>
            <p><strong>KABAGOPS POLRES SAMOSIR</strong></p>
            <br><br><br>
            <p>_________________________</p>
        </div>
    </div>
    <button class="no-print" onclick="window.print()" style="margin-top:20px;padding:8px 16px;cursor:pointer">Print</button>
    </body></html>`;
}

// ═══════════════════════════════════════════════════════════════════════════════
// TAB LAZY LOADING
// ═══════════════════════════════════════════════════════════════════════════════
document.querySelectorAll('#piketTabs a[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function(e) {
        const target = e.target.getAttribute('href');
        if (target === '#tabDashboard' && !dashboardLoaded) loadDashboard();
        if (target === '#tabKalender' && !calLoaded) loadCalendar();
        if (target === '#tabStatistik' && !statLoaded) loadStatistik();
        if (target === '#tabLogRotasi' && !logLoaded) loadLogRotasi();
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FATIGUE CHECK
// ═══════════════════════════════════════════════════════════════════════════════
let fatiguePersonilLoaded = false;
async function openFatigueCheckModal() {
    document.getElementById('fatigueResult').innerHTML = '';
    document.getElementById('fatigueTanggal').value = new Date().toISOString().split('T')[0];
    if (!fatiguePersonilLoaded) {
        try {
            const r = await fetch(API+'?action=get_personil_all');
            const d = await r.json();
            const sel = document.getElementById('fatiguePersonilId');
            sel.innerHTML = '<option value="">-- Pilih Personil --</option>';
            (d.data||[]).forEach(p => {
                sel.innerHTML += `<option value="${p.nrp}">${p.nama} (${p.nrp}) - ${p.pangkat||''}</option>`;
            });
            fatiguePersonilLoaded = true;
        } catch(e) { console.error(e); }
    }
    new bootstrap.Modal(document.getElementById('fatigueModal')).show();
}

async function runFatigueCheck() {
    const pid = document.getElementById('fatiguePersonilId').value;
    const tgl = document.getElementById('fatigueTanggal').value;
    const jeda = document.getElementById('fatigueMinJeda').value;
    const result = document.getElementById('fatigueResult');
    if (!pid) { result.innerHTML = '<div class="alert alert-warning py-2 small">Pilih personil terlebih dahulu</div>'; return; }
    result.innerHTML = '<div class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Mengecek...</div>';
    try {
        const r = await fetch(API+`?action=fatigue_check&personil_id=${pid}&tanggal=${tgl}&min_jeda_jam=${jeda}`);
        const d = await r.json();
        if (!d.success) { result.innerHTML = `<div class="alert alert-danger py-2 small">${d.error}</div>`; return; }
        if (!d.warnings.length) {
            result.innerHTML = `<div class="alert alert-success py-2 small"><i class="fas fa-check-circle me-1"></i>Tidak ada warning fatigue. ${d.total_jadwal} jadwal diperiksa sekitar tanggal ${tgl}.</div>`;
        } else {
            let html = `<div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-triangle me-1"></i><strong>${d.warnings.length} warning fatigue ditemukan!</strong></div>`;
            html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size:.8rem"><thead class="table-light"><tr><th>Tanggal</th><th>Shift 1</th><th>Shift 2</th><th>Jeda</th><th>Pesan</th></tr></thead><tbody>';
            d.warnings.forEach(w => {
                html += `<tr class="table-danger"><td>${w.tanggal}</td><td>${w.shift_1}</td><td>${w.shift_2}</td><td><strong>${w.jeda_jam} jam</strong></td><td>${w.pesan}</td></tr>`;
            });
            html += '</tbody></table></div>';
            result.innerHTML = html;
        }
    } catch(e) { result.innerHTML = `<div class="alert alert-danger py-2 small">Error: ${e}</div>`; }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SWAP SHIFT
// ═══════════════════════════════════════════════════════════════════════════════
let swapDashboardData = [];
async function openSwapShiftModal() {
    document.getElementById('swapId1').value = '';
    document.getElementById('swapId2').value = '';
    document.getElementById('swapResult').innerHTML = '';
    document.getElementById('swapInfo1').textContent = 'Pilih dari tabel di bawah';
    document.getElementById('swapInfo2').textContent = 'Pilih dari tabel di bawah';
    // Load today's schedules
    const tbody = document.getElementById('swapScheduleList');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';
    new bootstrap.Modal(document.getElementById('swapShiftModal')).show();
    try {
        const r = await fetch(API+'?action=dashboard_hari_ini');
        const d = await r.json();
        swapDashboardData = d.jadwal || [];
        if (!swapDashboardData.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-2">Tidak ada jadwal hari ini</td></tr>';
            return;
        }
        tbody.innerHTML = swapDashboardData.map(j => {
            const jam = (j.start_time||'').substring(0,5) + '-' + (j.end_time||'').substring(0,5);
            return `<tr>
                <td class="fw-bold">${j.id}</td>
                <td>${j.personil_name||'-'}</td>
                <td>${j.nama_tim||'-'}</td>
                <td><span class="badge bg-primary">${j.shift_type||'-'}</span></td>
                <td class="small">${jam}</td>
                <td>
                    <button class="btn btn-outline-primary btn-sm py-0 px-1" onclick="pickSwap(1,${j.id},'${(j.personil_name||'').replace(/'/g,'')}')">1</button>
                    <button class="btn btn-outline-danger btn-sm py-0 px-1" onclick="pickSwap(2,${j.id},'${(j.personil_name||'').replace(/'/g,'')}')">2</button>
                </td>
            </tr>`;
        }).join('');
    } catch(e) { tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${e}</td></tr>`; }
}

function pickSwap(slot, schedId, personilName) {
    document.getElementById('swapId'+slot).value = schedId;
    document.getElementById('swapInfo'+slot).innerHTML = `<span class="text-success"><i class="fas fa-check me-1"></i>${personilName} (ID: ${schedId})</span>`;
}

async function executeSwapShift() {
    const id1 = document.getElementById('swapId1').value;
    const id2 = document.getElementById('swapId2').value;
    const result = document.getElementById('swapResult');
    if (!id1 || !id2) { result.innerHTML = '<div class="alert alert-warning py-2 small">Pilih 2 jadwal yang akan ditukar</div>'; return; }
    if (id1 === id2) { result.innerHTML = '<div class="alert alert-warning py-2 small">Tidak bisa menukar jadwal yang sama</div>'; return; }
    if (!confirm('Tukar jadwal #'+id1+' dengan #'+id2+'?')) return;
    const fd = new FormData();
    fd.append('action','swap_shift');
    fd.append('schedule_id_1', id1);
    fd.append('schedule_id_2', id2);
    try {
        const r = await fetch(API, {method:'POST', body:fd});
        const d = await r.json();
        if (d.success) {
            result.innerHTML = `<div class="alert alert-success py-2 small"><i class="fas fa-check-circle me-1"></i>${d.message}</div>`;
            if (typeof showToast === 'function') showToast('success', d.message);
            dashboardLoaded = false;
            loadDashboard();
        } else {
            result.innerHTML = `<div class="alert alert-danger py-2 small">${d.error}</div>`;
        }
    } catch(e) { result.innerHTML = `<div class="alert alert-danger py-2 small">Error: ${e}</div>`; }
}

// ═══════════════════════════════════════════════════════════════════════════════
// EXPORT STATISTIK CSV
// ═══════════════════════════════════════════════════════════════════════════════
function exportStatistikCSV() {
    const table = document.querySelector('#tabStatistik table');
    if (!table) return;
    const rows = table.querySelectorAll('tr');
    if (rows.length <= 1) { if (typeof showToast === 'function') showToast('warning','Tidak ada data untuk di-export'); return; }
    let csv = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        cells.forEach(cell => rowData.push('"' + cell.textContent.trim().replace(/"/g,'""') + '"'));
        csv.push(rowData.join(','));
    });
    const bulan = document.getElementById('statBulan').value;
    const tahun = document.getElementById('statTahun').value;
    const blob = new Blob(['\uFEFF' + csv.join('\n')], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `statistik_piket_${tahun}_${String(bulan).padStart(2,'0')}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
    if (typeof showToast === 'function') showToast('success', 'CSV berhasil di-download');
}

// ═══════════════════════════════════════════════════════════════════════════════
// NOTIFIKASI ROTASI
// ═══════════════════════════════════════════════════════════════════════════════
async function checkRotasiNotifications() {
    try {
        const r = await fetch(API+'?action=get_notifikasi_piket');
        const d = await r.json();
        if (!d.success || !d.data || !d.data.length) return;
        const container = document.getElementById('notifRotasiContainer');
        container.style.display = '';
        container.innerHTML = d.data.map(n => {
            const icon = n.tipe === 'rotasi' ? 'fa-sync-alt text-info' : n.tipe === 'warning' ? 'fa-exclamation-triangle text-warning' : 'fa-info-circle text-primary';
            const bg = n.tipe === 'rotasi' ? 'alert-info' : n.tipe === 'warning' ? 'alert-warning' : 'alert-primary';
            const waktu = new Date(n.created_at).toLocaleString('id-ID',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
            return `<div class="alert ${bg} py-2 small d-flex justify-content-between align-items-center mb-1">
                <div><i class="fas ${icon} me-2"></i><strong>${n.judul}</strong> — ${n.pesan||''} <span class="text-muted ms-2">(${waktu})</span></div>
                <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="dismissNotif(${n.id},this)" title="Tandai sudah dibaca"><i class="fas fa-times"></i></button>
            </div>`;
        }).join('');
    } catch(e) { /* silent */ }
}

async function dismissNotif(id, btn) {
    try {
        const fd = new FormData();
        fd.append('action','read_notifikasi');
        fd.append('id', id);
        await fetch(API, {method:'POST', body:fd});
        btn.closest('.alert').remove();
        const container = document.getElementById('notifRotasiContainer');
        if (!container.children.length) container.style.display = 'none';
    } catch(e) { /* silent */ }
}

// Check notifications on page load
checkRotasiNotifications();
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
