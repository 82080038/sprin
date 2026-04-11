<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

AuthHelper::requireRole('admin', 'operator');

$page_title = 'Apel Nominal Digital';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-flag me-2 text-primary"></i>Apel Nominal Digital</h4>
            <small class="text-muted">Absensi Apel Pagi / Sore — seluruh personil Polres Samosir</small>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Tanggal</label>
                    <input type="date" id="apelTanggal" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Jenis Apel</label>
                    <select id="apelJenis" class="form-select form-select-sm">
                        <option value="pagi">Apel Pagi</option>
                        <option value="sore">Apel Sore</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Unsur</label>
                    <select id="filterUnsur" class="form-select form-select-sm">
                        <option value="">Semua Unsur</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Bagian</label>
                    <select id="filterBagian" class="form-select form-select-sm">
                        <option value="">Semua Bagian</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100" onclick="loadApel()"><i class="fas fa-search me-1"></i>Muat Data</button>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="switchTab('rekap')"><i class="fas fa-chart-bar me-1"></i>Rekap Bulanan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Summary -->
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-primary" id="stTotal">0</div><div class="text-muted small">Total</div></div></div>
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-success" id="stHadir">0</div><div class="text-muted small">Hadir</div></div></div>
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-danger" id="stAbsen">0</div><div class="text-muted small">Tidak Hadir</div></div></div>
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-warning" id="stSakit">0</div><div class="text-muted small">Sakit</div></div></div>
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-info" id="stIjin">0</div><div class="text-muted small">Ijin/Cuti</div></div></div>
        <div class="col-4 col-md-2"><div class="card border-0 shadow-sm text-center py-2"><div class="fs-4 fw-bold text-secondary" id="stDinas">0</div><div class="text-muted small">Dinas Luar</div></div></div>
    </div>

    <!-- Tabs -->
    <div id="tabAbsensi">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-check me-2"></i>Input Absensi Apel</h6>
                <?php if (AuthHelper::canEdit()): ?>
                <div>
                    <button class="btn btn-sm btn-outline-success me-1" onclick="setAllStatus('hadir')"><i class="fas fa-check-double me-1"></i>Semua Hadir</button>
                    <button class="btn btn-sm btn-success" onclick="simpanApel()"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama</th>
                                <th style="width:100px">Pangkat</th>
                                <th style="width:140px">Bagian</th>
                                <th style="width:160px" class="text-center">Status</th>
                                <th style="width:90px">Jam</th>
                                <th style="width:160px">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="apelBody">
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Klik "Muat Data" untuk menampilkan personil...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekap Tab (hidden) -->
    <div id="tabRekap" style="display:none">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2"></i>Rekap Apel Bulanan</h6>
                <div class="d-flex gap-2">
                    <input type="month" id="rekapBulan" class="form-control form-control-sm" style="width:150px">
                    <button class="btn btn-sm btn-primary" onclick="loadRekap()"><i class="fas fa-search"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="switchTab('absensi')"><i class="fas fa-arrow-left me-1"></i>Input</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark small">
                            <tr><th>No</th><th>Nama</th><th>Pangkat</th><th>Bagian</th><th class="text-center">Hadir</th><th class="text-center">Tidak</th><th class="text-center">Sakit</th><th class="text-center">Ijin</th><th class="text-center">Cuti</th><th class="text-center">DL</th><th class="text-center">TB</th><th class="text-center">Total</th><th class="text-center">%</th></tr>
                        </thead>
                        <tbody id="rekapBody"><tr><td colspan="13" class="text-center py-4 text-muted">Pilih bulan lalu klik cari</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API = '<?= url("api/apel_api.php") ?>';
let allData = [];
let unsurList = [], bagianList = [];

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('apelTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('rekapBulan').value = new Date().toISOString().slice(0,7);
    loadUnsurBagian();
});

function loadUnsurBagian() {
    fetch(API + '?action=get_unsur_bagian').then(r=>r.json()).then(d => {
        if (!d.success) return;
        unsurList = d.unsur; bagianList = d.bagian;
        const su = document.getElementById('filterUnsur');
        d.unsur.forEach(u => su.innerHTML += `<option value="${u.id}">${u.nama_unsur}</option>`);
    });
}

document.getElementById('filterUnsur').addEventListener('change', function() {
    const sb = document.getElementById('filterBagian');
    sb.innerHTML = '<option value="">Semua Bagian</option>';
    if (this.value) {
        bagianList.filter(b => b.id_unsur == this.value).forEach(b =>
            sb.innerHTML += `<option value="${b.id}">${b.nama_bagian}</option>`);
    }
});

function loadApel() {
    const tgl = document.getElementById('apelTanggal').value;
    const jenis = document.getElementById('apelJenis').value;
    const unsur = document.getElementById('filterUnsur').value;
    const bagian = document.getElementById('filterBagian').value;
    let url = `${API}?action=get_apel&tanggal=${tgl}&jenis_apel=${jenis}`;
    if (unsur) url += `&unsur_id=${unsur}`;
    if (bagian) url += `&bagian_id=${bagian}`;

    fetch(url).then(r=>r.json()).then(d => {
        if (!d.success) throw new Error(d.message);
        allData = d.data;
        renderApel();
    }).catch(e => {
        document.getElementById('apelBody').innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${e.message}</td></tr>`;
    });
}

function renderApel() {
    const canEdit = <?= AuthHelper::canEdit() ? 'true' : 'false' ?>;
    const statuses = [
        {v:'hadir',l:'Hadir',c:'success'},{v:'tidak_hadir',l:'Absen',c:'danger'},
        {v:'sakit',l:'Sakit',c:'warning'},{v:'ijin',l:'Ijin',c:'info'},
        {v:'cuti',l:'Cuti',c:'secondary'},{v:'dinas_luar',l:'DL',c:'dark'},
        {v:'tugas_belajar',l:'TB',c:'dark'}
    ];

    // Stats
    let st = {total:allData.length, hadir:0, tidak_hadir:0, sakit:0, ijin:0, dinas:0};
    allData.forEach(r => {
        const s = r.status || '';
        if (s==='hadir') st.hadir++;
        else if (s==='tidak_hadir') st.tidak_hadir++;
        else if (s==='sakit') st.sakit++;
        else if (s==='ijin'||s==='cuti') st.ijin++;
        else if (s==='dinas_luar'||s==='tugas_belajar') st.dinas++;
    });
    document.getElementById('stTotal').textContent = st.total;
    document.getElementById('stHadir').textContent = st.hadir;
    document.getElementById('stAbsen').textContent = st.tidak_hadir;
    document.getElementById('stSakit').textContent = st.sakit;
    document.getElementById('stIjin').textContent = st.ijin;
    document.getElementById('stDinas').textContent = st.dinas;

    if (!allData.length) {
        document.getElementById('apelBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data personil.</td></tr>';
        return;
    }

    let html = '';
    allData.forEach((r,i) => {
        const curStatus = r.status || '';
        const statusBtns = canEdit ? statuses.map(s =>
            `<button type="button" class="btn btn-${curStatus===s.v?'':'outline-'}${s.c} btn-sm px-1 py-0" style="font-size:.65rem" onclick="setStatus(${i},'${s.v}')">${s.l}</button>`
        ).join(' ') : (curStatus ? `<span class="badge bg-${statuses.find(s=>s.v===curStatus)?.c||'light'}">${statuses.find(s=>s.v===curStatus)?.l||curStatus}</span>` : '-');

        html += `<tr data-idx="${i}">
            <td class="text-muted small">${i+1}</td>
            <td class="fw-semibold small">${r.nama}</td>
            <td class="small text-muted">${r.nama_pangkat||'-'}</td>
            <td class="small">${r.nama_bagian||'-'}</td>
            <td class="text-center">${statusBtns}</td>
            <td>${canEdit ? `<input type="time" class="form-control form-control-sm py-0" style="font-size:.75rem" value="${r.jam_hadir||''}" onchange="allData[${i}].jam_hadir=this.value">` : (r.jam_hadir||'-')}</td>
            <td>${canEdit ? `<input type="text" class="form-control form-control-sm py-0" style="font-size:.75rem" value="${r.keterangan||''}" placeholder="..." onchange="allData[${i}].keterangan=this.value">` : (r.keterangan||'-')}</td>
        </tr>`;
    });
    document.getElementById('apelBody').innerHTML = html;
}

function setStatus(idx, status) {
    allData[idx].status = status;
    renderApel();
}

function setAllStatus(status) {
    allData.forEach(r => { if (!r.status) r.status = status; });
    renderApel();
}

function simpanApel() {
    const items = allData.filter(r => r.status).map(r => ({
        personil_id: r.personil_id,
        status: r.status,
        jam_hadir: r.jam_hadir || null,
        keterangan: r.keterangan || null
    }));
    if (!items.length) { showToast('warning','Belum ada data yang diisi'); return; }

    const fd = new FormData();
    fd.append('action', 'save_apel');
    fd.append('tanggal', document.getElementById('apelTanggal').value);
    fd.append('jenis_apel', document.getElementById('apelJenis').value);
    fd.append('items', JSON.stringify(items));

    fetch(API, {method:'POST', body:fd}).then(r=>r.json()).then(d => {
        if (d.success) { showToast('success', d.message); loadApel(); }
        else showToast('danger', d.message);
    }).catch(e => showToast('danger', e.message));
}

function switchTab(tab) {
    document.getElementById('tabAbsensi').style.display = tab==='absensi' ? '' : 'none';
    document.getElementById('tabRekap').style.display   = tab==='rekap'   ? '' : 'none';
    if (tab==='rekap') loadRekap();
}

function loadRekap() {
    const bulan = document.getElementById('rekapBulan').value;
    fetch(`${API}?action=get_rekap&bulan=${bulan}`).then(r=>r.json()).then(d => {
        if (!d.success) throw new Error(d.message);
        if (!d.data.length) {
            document.getElementById('rekapBody').innerHTML = '<tr><td colspan="13" class="text-center py-4 text-muted">Tidak ada data rekap</td></tr>';
            return;
        }
        let html = '';
        d.data.forEach((r,i) => {
            const total = parseInt(r.total_apel)||0;
            const hadir = parseInt(r.hadir)||0;
            const persen = total ? Math.round(hadir/total*100) : 0;
            const persenClass = persen >= 90 ? 'text-success' : (persen >= 70 ? 'text-warning' : 'text-danger');
            html += `<tr>
                <td class="small">${i+1}</td>
                <td class="fw-semibold small">${r.nama}</td>
                <td class="small text-muted">${r.nama_pangkat||'-'}</td>
                <td class="small">${r.nama_bagian||'-'}</td>
                <td class="text-center"><span class="badge bg-success">${r.hadir||0}</span></td>
                <td class="text-center"><span class="badge bg-danger">${r.tidak_hadir||0}</span></td>
                <td class="text-center"><span class="badge bg-warning text-dark">${r.sakit||0}</span></td>
                <td class="text-center"><span class="badge bg-info text-dark">${r.ijin||0}</span></td>
                <td class="text-center"><span class="badge bg-secondary">${r.cuti||0}</span></td>
                <td class="text-center">${r.dinas_luar||0}</td>
                <td class="text-center">${r.tugas_belajar||0}</td>
                <td class="text-center fw-bold">${total}</td>
                <td class="text-center fw-bold ${persenClass}">${persen}%</td>
            </tr>`;
        });
        document.getElementById('rekapBody').innerHTML = html;
    }).catch(e => {
        document.getElementById('rekapBody').innerHTML = `<tr><td colspan="13" class="text-center text-danger py-4">${e.message}</td></tr>`;
    });
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
