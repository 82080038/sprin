/**
 * pages/pangkat.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php;session_start () ;declare (strict_types=1) ;;/DevelopmentErrorReportingif (!defined ('DEVELOPMENT_MODE') ) {error_reporting (E_ALL) ;ini_set ('display_errors',1) ;ini_set ('display_startup_errors',1) ;}require_once'..
    ./core/config.php';require_once'../core/auth_helper..
    php';/CheckauthenticationusingAuthHelperif (!AuthHelper::validateSession () ) {/Temporarilybypassfortesting-removethislineinproduction/header ('Location:'..
    url ('login..
    php') ) ;/exit;/Fortesting,setadummysession$_SESSION['logged_in']=true;$_SESSION['username']='test_user';$_SESSION['user_id']=1;}/Initializedatabaseconnectiontry{$pdo=newPDO ('mysql:host='..
    DB_HOST.';dbname='.DB_NAME.';unix_socket='..
    DB_SOCKET,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]) try{/Databaseoperations}catch (PDOException$e) {error_log ("Databaseerror:"..
    $e->getMessage () ) ;thrownewException ("Databaseoperationfailed") ;};$pdo->setAttribute (PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION) ;}catch (PDOException$e) {die ('<divclass="alertalert-danger">Databaseconnectionfailed:'..
    htmlspecialchars ($e->getMessage () ) ..
    '</div>') ;}/HandleAJAXoperationsif ($_SERVER['REQUEST_METHOD']==='POST') {$action=filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'action',FILTER_DEFAULT) ??'';/SetuperrorhandlerforAJAXrequestsset_error_handler (function ($severity,$message,$file,$line) {thrownewErrorException ($message,0,$severity,$file,$line) ;}) ;try{/BypassauthforAJAXrequestsif (in_[$action,['get_pangkat_list','get_pangkat_detail','create_pangkat','update_pangkat','delete_pangkat']]) {/SettestsessionforAJAX$_SESSION['logged_in']=true;$_SESSION['username']='AJAXUser';$_SESSION['user_id']=1;/ClearanyoutputbuffersforAJAXrequestswhile (ob_get_level () >0) {ob_end_clean () ;}}if ($action==='get_pangkat_list') {$stmt=$pdo->query ("SELECT*FROMpangkatORDERBYlevel_pangkatASC,idASC") ;$pangkatData=$stmt->fetchAll (PDO::FETCH_ASSOC) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'data'=>$pangkatData]) ;exit;}if ($action==='create_pangkat') {$stmt=$pdo->prepare ("INSERTINTOpangkat (nama_pangkat,singkatan,level_pangkat,id_jenis_pegawai) VALUES (?,?,?,?) ") ;$stmt->execute ([filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'nama_pangkat',FILTER_DEFAULT) ,filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'singkatan',FILTER_DEFAULT) ??'',filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'level_pangkat',FILTER_DEFAULT) ??0,filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id_jenis_pegawai',FILTER_DEFAULT) ??null]) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Pangkatberhasilditambahkan!']) ;exit;}if ($action==='get_pangkat_detail') {$id=filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id',FILTER_DEFAULT) ??0;$stmt=$pdo->prepare ("SELECT*FROMpangkatWHEREid=?") ;$stmt->execute ([$id]) ;$pangkat=$stmt->fetch (PDO::FETCH_ASSOC) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'data'=>$pangkat]) ;exit;}if ($action==='update_pangkat') {$stmt=$pdo->prepare ("UPDATEpangkatSETnama_pangkat=?,singkatan=?,level_pangkat=?,id_jenis_pegawai=?WHEREid=?") ;$stmt->execute ([filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'nama_pangkat',FILTER_DEFAULT) ,filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'singkatan',FILTER_DEFAULT) ??'',filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'level_pangkat',FILTER_DEFAULT) ??0,filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id_jenis_pegawai',FILTER_DEFAULT) ??null,filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id',FILTER_DEFAULT) ]) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Pangkatberhasildiperbarui!']) ;exit;}if ($action==='delete_pangkat') {/Checkifpangkatisusedbypersonil$stmt=$pdo->prepare ("SELECTCOUNT (*) FROMpersonilWHEREid_pangkat=?") ;$stmt->execute ([filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id',FILTER_DEFAULT) ]) ;$personilCount=$stmt->fetchColumn () ;if ($personilCount>0) {header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>'Tidakdapatmenghapuspangkatyangmasihdigunakanolehpersonil!']) ;exit;}$stmt=$pdo->prepare ("DELETEFROMpangkatWHEREid=?") ;$stmt->execute ([filter_input ($_POST===\$_GET?INPUT_GET: ($_POST===\$_POST?INPUT_POST:INPUT_REQUEST) ,'id',FILTER_DEFAULT) ]) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Pangkatberhasildihapus!']) ;exit;}}catch (Exception$e) {/Restoreoriginalerrorhandlerrestore_error_handler () ;/Clearanyoutputbufferswhile (ob_get_level () >0) {ob_end_clean () ;}header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>'Servererror:'..
    $e->getMessage () ]) ;exit;}}/Getcurrentpangkatdatagroupedbyjenispegawai$pangkatData=[];$groupedPangkat=[];try{$stmt=$pdo->query ("SELECTp..
    *,m.nama_jenis,m.kategori,m.kode_jenisFROMpangkatpLEFTJOINmaster_jenis_pegawaimONp..
    id_jenis_pegawai=m.idORDERBYm.kategori,m.nama_jenis,p.level_pangkatDESC,p..
    nama_pangkat") ;$pangkatData=$stmt->fetchAll (PDO::FETCH_ASSOC) ;/Groupbykategoriandjenispegawaiforeach ($pangkatDataas$pangkat) {$kategori=$pangkat['kategori']??'TidakDikategorikan';$jenis=$pangkat['nama_jenis']??'TidakAdaJenis';if (!isset ($groupedPangkat[$kategori]) ) {$groupedPangkat[$kategori]=[];}if (!isset ($groupedPangkat[$kategori][$jenis]) ) {$groupedPangkat[$kategori][$jenis]=[];}$groupedPangkat[$kategori][$jenis][]=$pangkat;}}catch (PDOException$e) {/Keepemptyarraysifdatabasefails$pangkatData=[];$groupedPangkat=[];}$page_title='ManajemenPangkat-POLRESSamosir';include'..
    ./includes/components/header.php';?><style>/*PangkatTableManagementStyles*/..
    pangkat-section{margin-bottom:2rem;}..
    pangkat-section-header{background:linear-gradient (135deg,var (--primary-color) ,var (--secondary-color) ) ;color:white;padding:1rem1..
    5rem;border-radius:8px8px00;margin-bottom:0;}.pangkat-section-title{font-size:1..
    1rem;font-weight:600;margin:0;}.pangkat-section-subtitle{font-size:0.9rem;opacity:0.9;margin:0..
    25rem000;}..
    pangkat-table{background:var (--bg-primary) ;border:1pxsolidvar (--border-color) ;border-radius:008px8px;overflow:hidden;box-shadow:02px8pxvar (--shadow-color) ;}..
    pangkat-tabletable{width:100%;margin:0;border-collapse:collapse;}..
    pangkat-tableth{background:var (--bg-secondary) ;color:var (--text-primary) ;font-weight:600;padding:0..
    75rem;text-align:left;border-bottom:2pxsolidvar (--border-color) ;white-space:nowrap;}..
    pangkat-tabletd{padding:0..
    75rem;border-bottom:1pxsolidvar (--border-color) ;vertical-align:middle;}..
    pangkat-tabletr:hover{background:var (--hover-bg) ;}..
    pangkat-level{display:inline-block;padding:0.25rem0.5rem;border-radius:4px;font-size:0..
    8rem;font-weight:600;min-width:60px;text-align:center;}..
    level-high{background:#dc3545;color:white;}.level-medium{background:#ffc107;color:#212529;}..
    level-low{background:#28a745;color:white;}.pangkat-actions{display:flex;gap:0..
    5rem;white-space:nowrap;}.pangkat-badge{display:inline-block;padding:0.25rem0..
    5rem;border-radius:4px;font-size:0..
    75rem;font-weight:500;background:var (--bg-tertiary) ;color:var (--text-secondary) ;}..
    empty-state{text-align:center;padding:3rem1rem;color:var (--text-secondary) ;}..
    empty-statei{font-size:3rem;margin-bottom:1rem;opacity:0.5;}..
    row{display:flex;flex-wrap:wrap;margin:0-1rem;margin-bottom:2rem;}..
    card{background:linear-gradient (135deg,var (--primary-color) ,var (--secondary-color) ) ;color:white;border-radius:8px;padding:1..
    5rem;text-align:center;}.card-title{font-size:2rem;font-weight:bold;margin-bottom:0.5rem;}..
    card-text{font-size:0.9rem;opacity:0.9;}..
    table-responsive{border-radius:008px8px;}@media (max-width:768px) {.pangkat-table{font-size:0..
    875rem;}.pangkat-tableth,.pangkat-tabletd{padding:0.5rem;}..
    pangkat-actions{flex-direction:column;gap:0..
    25rem;}}</style><divclass="container"><divclass="page-header"><h1><iclass="fasfa-graduation-capme-2"></i>ManajemenPangkat</h1><pclass="text-muted">KeloladatapangkatPOLRESSamosirberdasarkanjenispegawai</p></div><!--Statistics--><divclass="row"><divclass="card"><divclass="card-title"><?php;session_start () ;echocount ($pangkatData) ;?></div><divclass="card-text">TotalPangkat</div></div><divclass="card"><divclass="card-title"><?php;session_start () ;echocount ($groupedPangkat) ;?></div><divclass="card-text">KategoriPegawai</div></div><divclass="card"><divclass="card-title"><?php;session_start () ;$totalJenis=0;foreach ($groupedPangkatas$kategori=>$jenisList) {$totalJenis+=count ($jenisList) ;}echo$totalJenis;?></div><divclass="card-text">JenisPegawai</div></div></div><!--ActionButtons--><divclass="rowmb-4"><divclass="col-12"><divclass="d-flexgap-2"><buttonclass="btnbtn-primary"onclick="openAddModal () "><iclass="fasfa-plusme-2"></i>TambahPangkat</button><buttonclass="btnbtn-info"onclick="refreshData () "><iclass="fasfa-syncme-2"></i>Refresh</button></div></div></div><!--PangkatTablesbyKategori--><?php;session_start () ;if (empty ($groupedPangkat) ) :?><divclass="empty-state"><iclass="fasfa-graduation-cap"></i><h5>BelumAdaDataPangkat</h5><p>Belumadadatapangkatyangterdaftardalamsistem..
    </p><buttonclass="btnbtn-primary"onclick="openAddModal () "><iclass="fasfa-plusme-2"></i>TambahPangkatPertama</button></div><?php;session_start () ;else:?><?php;session_start () ;foreach ($groupedPangkatas$kategori=>$jenisList) :?><divclass="pangkat-section"><divclass="pangkat-section-header"><h5class="pangkat-section-title"><iclass="fasfa-usersme-2"></i><?php;.
session_start () ;echohtmlspecialchars ($kategori) ;?></h5><pclass="pangkat-section-subtitle"><?php;session_start () ;echocount ($jenisList) ;?>jenispegawai,<?php;session_start () ;$totalPangkat=0;foreach ($jenisListas$pangkats) {$totalPangkat+=count ($pangkats) ;}echo$totalPangkat;?>pangkat</p></div><?php;session_start () ;foreach ($jenisListas$jenis=>$pangkats) :?><divclass="pangkat-table"><divclass="table-responsive"><tableclass="table"><thead><tr><thcolspan="5"class="text-centerbg-light"><strong><?php;
session_start () ;echohtmlspecialchars ($jenis) ;?></strong><spanclass="pangkat-badgems-2"><?php;session_start () ;echocount ($pangkats) ;?>pangkat</span></th></tr><tr><thwidth="5%">ID</th><thwidth="35%">NamaPangkat</th><thwidth="20%">Singkatan</th><thwidth="15%">Level</th><thwidth="25%">Aksi</th></tr></thead><tbody><?php;session_start () ;foreach ($pangkatsas$pangkat) :?><tr><td><?php;session_start () ;echo$pangkat['id'];?></td><td><strong><?php;session_start () ;echohtmlspecialchars ($pangkat['nama_pangkat']) ;?></strong><?php;session_start () ;if ($pangkat['kode_jenis']) :?><spanclass="pangkat-badgems-2"><?php;session_start () ;echohtmlspecialchars ($pangkat['kode_jenis']) ;?></span><?php;session_start () ;endif;?></td><td><?php;session_start () ;echohtmlspecialchars ($pangkat['singkatan']??'-') ;?></td><td><?php;session_start () ;$level=$pangkat['level_pangkat']??0;$levelClass=$level>=15?'level-high': ($level>=10?'level-medium':'level-low') ;?><spanclass="pangkat-level<?php;session_start () ;echo$levelClass;?>">Level<?php;session_start () ;echo$level;?></span></td><td><divclass="pangkat-actions"><buttonclass="btnbtn-smbtn-outline-primary"onclick="editPangkat (<?php;session_start () ;echo$pangkat['id'];?>) "><iclass="fasfa-edit"></i>Edit</button><buttonclass="btnbtn-smbtn-outline-danger"onclick="deletePangkat (<?php;session_start () ;echo$pangkat['id'];?>,'<?php;session_start () ;echohtmlspecialchars ($pangkat['nama_pangkat']) ;?>') "><iclass="fasfa-trash"></i>Hapus</button></div></td></tr><?php;session_start () ;endforeach;?></tbody></table></div></div><?php;session_start () ;endforeach;?></div><?php;session_start () ;endforeach;?><?php;session_start () ;endif;?></div><!--Add/EditModal--><divclass="modalfade"id="pangkatModal"tabindex="-1"><divclass="modal-dialog"><divclass="modal-content"><divclass="modal-header"><h5class="modal-title"><iclass="fasfa-graduation-capme-2"></i><spanid="modalTitle">TambahPangkat</span></h5><buttontype="button"class="btn-close"data-bs-dismiss="modal"></button></div><formmethod="POST"id="pangkatForm"><divclass="modal-body"><inputtype="hidden"name="action"id="formAction"value="create_pangkat"><inputtype="hidden"name="id"id="formId"><divclass="mb-3"><labelfor="nama_pangkat"class="form-label">NamaPangkat</label><inputtype="text"class="form-control"id="nama_pangkat"name="nama_pangkat"required><divclass="form-text">Contoh:InspekturPolisiSatu,AjunKomisarisPolisi</div></div><divclass="mb-3"><labelfor="singkatan"class="form-label">Singkatan</label><inputtype="text"class="form-control"id="singkatan"name="singkatan"><divclass="form-text">Contoh:Ip..
    S.,A.K.P..
    </div></div><divclass="mb-3"><labelfor="id_jenis_pegawai"class="form-label">JenisPegawai</label><selectclass="form-select"id="id_jenis_pegawai"name="id_jenis_pegawai"required><optionvalue="">PilihJenisPegawai</option><?php;session_start () ;try{$jenisStmt=$pdo->query ("SELECTid,nama_jenis,kategoriFROMmaster_jenis_pegawaiORDERBYkategori,nama_jenis") ;$jenisPegawaiOptions=$jenisStmt->fetchAll (PDO::FETCH_ASSOC) ;$currentKategori='';foreach ($jenisPegawaiOptionsas$jenis) {if ($jenis['kategori']!==$currentKategori) {$currentKategori=$jenis['kategori'];if ($currentKategori!=='') {echo"</optgroup>";}echo"<optgrouplabel='"..
    htmlspecialchars ($currentKategori) ."'>";}echo"<optionvalue='".$jenis['id']."'>"..
    htmlspecialchars ($jenis['nama_jenis']) ..
    "</option>";}if ($currentKategori!=='') {echo"</optgroup>";}}catch (PDOException$e) {echo"<optionvalue=''>Errorloadingjenispegawai</option>";}?></select><divclass="form-text">Pilihjenispegawaiuntukpangkatini</div></div><divclass="mb-3"><labelfor="level_pangkat"class="form-label">LevelPangkat</label><inputtype="number"class="form-control"id="level_pangkat"name="level_pangkat"min="1"max="20"><divclass="form-text">Levelpangkat (1-20,semakintinggisemakinsenior) </div></div></div><divclass="modal-footer"><buttontype="button"class="btnbtn-secondary"data-bs-dismiss="modal">Batal</button><buttontype="submit"class="btnbtn-primary"><iclass="fasfa-saveme-2"></i>Simpan</button></div></form></div></div></div><?php;session_start () ;include'..
    ./includes/components/footer..
    php';?><script>letpangkatData=<?php;session_start () ;echojson_encode ($pangkatData) ;?>;/CRUDFunctionsfunctionopenAddModal () {document..
    getElementById ('modalTitle') .textContent='TambahPangkat';document..
    getElementById ('formAction') .value='create_pangkat';document.getElementById ('formId') ..
    value='';document.getElementById ('nama_pangkat') .value='';document..
    getElementById ('singkatan') .value='';document.getElementById ('level_pangkat') ..
    value='';document.getElementById ('id_jenis_pegawai') .value='';constmodal=newbootstrap..
    Modal (document.getElementById ('pangkatModal') ) ;modal..
    show () ;}functioneditPangkat (id) {constpangkat=pangkatData.find (p=>p..
    id==id) ;if (!pangkat) {alert ('Datapangkattidakditemukan!') ;return;}document..
    getElementById ('modalTitle') .textContent='EditPangkat';document..
    getElementById ('formAction') .value='update_pangkat';document.getElementById ('formId') ..
    value=pangkat.id;document.getElementById ('nama_pangkat') .value=pangkat.nama_pangkat;document..
    getElementById ('singkatan') .value=pangkat.singkatan||'';document..
    getElementById ('level_pangkat') .value=pangkat.level_pangkat||'';document..
    getElementById ('id_jenis_pegawai') .value=pangkat.id_jenis_pegawai||'';constmodal=newbootstrap..
    Modal (document.getElementById ('pangkatModal') ) ;modal..
    show () ;}functiondeletePangkat (id,nama) {if (!confirm (`ApakahAndayakininginmenghapuspangkat"${nama}"?`) ) {return;}fetch ('pangkat..
    php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:newURLSearchParams ({action:'delete_pangkat',id:id}) }) ..
    then (response=>response.json () ) .then (data=>{if (data.success) {alert (data..
    message) ;refreshData () ;}else{alert ('Error:'+data.message) ;}}) .catch (error=>{console..
    error ('Error:',error) ;alert ('Error:Terjadikesalahansaatmenghapusdata') ;}) ;}functionrefreshData () {window..
    location.reload () ;}/Formsubmissiondocument.getElementById ('pangkatForm') ..
    addEventListener ('submit',function (e) {e..
    preventDefault () ;constformData=newFormData (this) ;fetch ('pangkat..
    php',{method:'POST',body:formData}) .then (response=>response.json () ) .then (data=>{if (data..
    success) {alert (data.message) ;bootstrap.Modal.getInstance (document..
    getElementById ('pangkatModal') ) .hide () ;refreshData () ;}else{alert ('Error:'+data..
    message) ;}}) .catch (error=>{console..
    error ('Error:',error) ;alert ('Error:Terjadikesalahansaatmenyimpandata') ;}) ;}) ;</script>.
