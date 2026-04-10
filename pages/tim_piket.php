<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';
if (!AuthHelper::validateSession()) { header('Location: ' . url('login.php')); exit; }

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

    // Siklus per bagian
    $siklus_raw = $pdo->query("
        SELECT s.* FROM siklus_piket_fase s ORDER BY s.id_bagian, s.urutan
    ")->fetchAll(PDO::FETCH_ASSOC);
    $siklus_by_bagian = [];
    foreach ($siklus_raw as $s) $siklus_by_bagian[$s['id_bagian']][] = $s;

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

    <!-- Papan Siklus per Bagian -->
    <h5 class="fw-bold mb-3"><i class="fas fa-project-diagram me-2 text-success"></i>Papan Siklus Piket per Satuan</h5>

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
                <button class="btn btn-sm btn-outline-info py-0" onclick="rotasiFase(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')" title="Rotasi semua tim ke fase berikutnya">
                    <i class="fa-solid fa-rotate"></i> Rotasi
                </button>
                <button class="btn btn-sm btn-outline-secondary py-0" onclick="openSiklusModal(<?php echo $bid; ?>,'<?php echo htmlspecialchars(addslashes($bag['nama_bagian'])); ?>')">
                    <i class="fas fa-cog"></i> Siklus
                </button>
                <button class="btn btn-sm btn-primary py-0" onclick="openTambahTim(<?php echo $bid; ?>)">
                    <i class="fas fa-plus"></i> Tim
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
            <div class="d-flex gap-0 overflow-auto pb-1">
            <?php foreach ($fases as $fi => $fase):
                $fc     = $colorFase[$fi % count($colorFase)];
                $jamMul = substr($fase['jam_mulai_default'],0,5);
                $jamSel = jamSelesai($fase['jam_mulai_default'], $fase['durasi_jam']);
                $tsFase = $timPerFase[$fase['id']] ?? [];
            ?>
                <?php if ($fi > 0): ?><div class="arrow-siklus">&#8594;</div><?php endif; ?>
                <div style="min-width:175px;max-width:250px;flex:1">
                    <div class="fase-hdr" style="background:<?php echo $fc; ?>">
                        <?php echo htmlspecialchars($fase['nama_fase']); ?>
                        <?php if (!$fase['is_wajib']): ?><span class="badge bg-light text-dark ms-1" style="font-size:.6rem">Opsional</span><?php endif; ?>
                        <div class="jam-info"><?php echo $jamMul; ?>&#8211;<?php echo $jamSel; ?> &nbsp;(<?php echo (float)$fase['durasi_jam']; ?> jam)</div>
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
  <div class="modal-dialog modal-lg">
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
            <div class="col-md-4">
              <label class="form-label fw-semibold">6. Jam Mulai</label>
              <input type="time" class="form-control" id="tim_jam_mulai" name="jam_mulai_aktif">
              <div class="form-text">Kosong = ikut siklus default</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Durasi (jam)</label>
              <input type="number" class="form-control" id="tim_durasi" name="durasi_jam"
                     min="0.5" max="24" step="0.5" placeholder="8">
            </div>
            <div class="col-md-4 d-flex align-items-end">
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-sync-alt me-2"></i>Siklus Piket: <span id="siklusNamaBagian">—</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
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
  <div class="modal-dialog modal-lg">
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
let faseRows = [], siklusBid = 0;
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
    siklusBid = bid;
    if (nama) document.getElementById('siklusNamaBagian').textContent = nama;
    document.getElementById('btnTambahFase').style.display   = '';
    document.getElementById('btnSimpanSiklus').style.display = '';
    document.getElementById('siklusFaseContainer').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    fetch(API+'?action=get_siklus&id_bagian='+bid).then(r=>r.json()).then(d=>{
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
            <td><input type="time" class="form-control form-control-sm" id="fjm_${i}" value="${jm}" style="width:110px" onchange="faseRows[${i}].jam_mulai_default=this.value+':00';refreshJS(${i});propagate(${i+1})"></td>
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
    if (!siklusBid) { alert('Pilih bagian dahulu.'); return; }
    if (!faseRows.length) { alert('Minimal 1 fase harus ada.'); return; }
    const fd = new FormData();
    fd.append('action','save_siklus'); fd.append('id_bagian',siklusBid); fd.append('fases',JSON.stringify(faseRows));
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
        anggSet = new Set((ad.data||[]).map(a=>a.personil_id));
        renderPersonilLists();
        new bootstrap.Modal(document.getElementById('anggotaModal')).show();
    });
}
function renderPersonilLists() {
    const q   = document.getElementById('filterPersonil').value.toLowerCase();
    const mkI = (p,sel) => `<div class="d-flex align-items-center gap-2 p-2 border-bottom personil-item${sel?' table-active':''}" style="cursor:pointer" data-nrp="${p.nrp}" onclick="toggleP(this)">
        <div><div class="fw-semibold small">${p.nama}</div><div class="text-muted" style="font-size:.7rem">${p.nrp} &middot; ${p.pangkat||''} &middot; ${p.bagian||''}</div></div></div>`;
    const av = allP.filter(p => !anggSet.has(p.nrp) && (!q||p.nama.toLowerCase().includes(q)||p.nrp.includes(q)));
    const am = allP.filter(p =>  anggSet.has(p.nrp));
    document.getElementById('personilTersedia').innerHTML = av.length ? av.map(p=>mkI(p,false)).join('') : '<div class="text-center text-muted py-3 small">Semua sudah masuk tim</div>';
    document.getElementById('anggotaTim').innerHTML      = am.length ? am.map(p=>mkI(p,false)).join('') : '<div class="text-center text-muted py-3 small">Belum ada anggota</div>';
    document.getElementById('jumlahAnggota').textContent = am.length;
}
function toggleP(el) { el.classList.toggle('table-active'); el.style.outline = el.classList.contains('table-active')?'2px solid #0d6efd':''; }
function tambahTerpilih() { document.querySelectorAll('#personilTersedia .table-active').forEach(el=>anggSet.add(el.dataset.nrp)); renderPersonilLists(); }
function hapusTerpilih()  { document.querySelectorAll('#anggotaTim .table-active').forEach(el=>anggSet.delete(el.dataset.nrp)); renderPersonilLists(); }
function simpanAnggota() {
    const fd = new FormData(); fd.append('action','save_anggota'); fd.append('tim_id',curTimId);
    anggSet.forEach(nrp=>fd.append('personil_ids[]',nrp));
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
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
