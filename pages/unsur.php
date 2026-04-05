/**
 * pages/unsur.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<?php;session_start () ;declare (strict_types=1) ;;/DevelopmentErrorReportingif (!defined ('DEVELOPMENT_MODE') ) {error_reporting (E_ALL) ;ini_set ('display_errors',1) ;ini_set ('display_startup_errors',1) ;}require_once__DIR__..
    '/../core/config.php';require_once__DIR__.'/../core/SessionManager.php';require_once__DIR__.'/...
    /core/auth_helper..
    php';/StartsessionusingSessionManagerSessionManager::start () ;/CheckauthenticationusingAuthHelperif (!AuthHelper::validateSession () ) {header ('Location:'..
    url ('login.php') ) ;exit;}$page_title='ManajemenUnsur-POLRESSamosir';include__DIR__.'/...
    /includes/components/header.php';?><!--Debug:Ensurewe'renotinaframe--><script>if (window..
    top!==window.self) {window.top.location=window.self..
    location;}</script><style>/*SortableStyleswithThemeVariables*/.sortable-list{min-height:100px;}..
    sortable-item{cursor:move;transition:all0..
    3sease;border:1pxsolidvar (--border-color) ;border-radius:4px;padding:8px;margin-bottom:8px;background:var (--bg-primary) ;color:var (--text-primary) ;}..
    sortable-item:hover{border-color:var (--primary-color) ;box-shadow:02px4pxvar (--shadow-color) ;}..
    drag-handle{cursor:grab;color:var (--text-secondary) ;font-size:18px;padding:8px;border-radius:4px;transition:all0..
    2sease;}.drag-handle:hover{background:var (--hover-bg) ;color:var (--primary-color) ;}..
    drag-handle:active{cursor:grabbing;}.sortable-ghost{opacity:0..
    4;background:var (--bg-tertiary) !important;border:2pxdashedvar (--primary-color) !important;}..
    sortable-chosen{background:var (--bg-secondary) !important;border-color:var (--primary-color) !important;box-shadow:04px8pxvar (--shadow-color) !important;transform:scale (1..
    02) ;}.sortable-dragging{opacity:0..
    8;transform:rotate (2deg) ;box-shadow:08px16pxvar (--shadow-color) !important;z-index:1000;}/*Animationfororderupdate*/..
    sortable-item.order-updated{animation:highlightOrder0..
    5sease;}@keyframeshighlightOrder{0%{background:var (--bg-tertiary) ;}100%{background:var (--bg-primary) ;}}/*Improvedformandtablestyling*/..
    form-label{color:var (--text-primary) !important;font-weight:600;}..
    form-control{background:var (--bg-primary) !important;color:var (--text-primary) !important;border:1pxsolidvar (--border-color) !important;}..
    form-control:focus{background:var (--bg-primary) !important;color:var (--text-primary) !important;border-color:var (--primary-color) !important;box-shadow:0000..
    2remrgba (26,35,126,0.25) !important;}.form-text{color:var (--text-secondary) !important;}..
    text-muted{color:var (--text-secondary) !important;}..
    btn-primary{background:var (--primary-color) !important;border-color:var (--primary-color) !important;color:var (--text-light) !important;}..
    btn-primary:hover{background:var (--secondary-color) !important;border-color:var (--secondary-color) !important;color:var (--text-light) !important;}..
    btn-outline-secondary{border-color:var (--border-color) !important;color:var (--text-secondary) !important;}..
    btn-outline-secondary:hover{background:var (--hover-bg) !important;border-color:var (--border-color) !important;color:var (--text-primary) !important;}..
    input-group-text{background:var (--bg-secondary) !important;color:var (--text-primary) !important;border:1pxsolidvar (--border-color) !important;}..
    modal-content{background:var (--bg-primary) !important;color:var (--text-primary) !important;border:1pxsolidvar (--border-color) !important;}..
    modal-header{background:var (--bg-secondary) !important;color:var (--text-primary) !important;border-bottom:1pxsolidvar (--border-color) !important;}..
    modal-footer{background:var (--bg-secondary) !important;border-top:1pxsolidvar (--border-color) !important;}..
    table{color:var (--text-primary) !important;}..
    tableth{background:var (--bg-secondary) !important;color:var (--text-primary) !important;border-color:var (--border-color) !important;font-weight:600;}..
    tabletd{background:var (--bg-primary) !important;color:var (--text-primary) !important;border-color:var (--border-color) !important;}..
    alert{color:var (--text-primary) !important;border:1pxsolidvar (--border-color) !important;}..
    alert-info{background:var (--bg-secondary) !important;border-color:var (--primary-color) !important;}..
    alert-success{background:#d4edda!important;border-color:#c3e6cb!important;color:#155724!important;}..
    alert-danger{background:#f8d7da!important;border-color:#f5c6cb!important;color:#721c24!important;}/*Additionalstylingforbettercontrast*/..
    card{background:var (--bg-primary) !important;border:1pxsolidvar (--border-color) !important;color:var (--text-primary) !important;}..
    card-header{background:var (--bg-secondary) !important;color:var (--text-primary) !important;border-bottom:1pxsolidvar (--border-color) !important;}..
    card-body{background:var (--bg-primary) !important;color:var (--text-primary) !important;}..
    page-headerh1{color:var (--text-primary) !important;font-weight:bold;}/*NamaUnsurspecificstyling*/..
    sortable-itemstrong{color:var (--text-primary) !important;font-weight:700;font-size:1.1em;}..
    sortable-item..
    badge{background:var (--primary-color) !important;color:var (--text-light) !important;font-weight:600;}..
    sortable-itemcode{background:var (--bg-tertiary) !important;color:var (--text-primary) !important;padding:2px6px;border-radius:4px;font-weight:500;}..
    btn-outline-primary{border-color:var (--primary-color) !important;color:var (--primary-color) !important;}..
    btn-outline-primary:hover{background:var (--primary-color) !important;color:var (--text-light) !important;}..
    btn-outline-danger{border-color:#dc3545!important;color:#dc3545!important;}..
    btn-outline-danger:hover{background:#dc3545!important;color:white!important;}..
    btn-info{background:#17a2b8!important;border-color:#17a2b8!important;color:white!important;}..
    btn-success{background:#28a745!important;border-color:#28a745!important;color:white!important;}..
    btn-warning{background:#ffc107!important;border-color:#ffc107!important;color:#212529!important;}/*Bettercontrastfordraghandle*/..
    drag-handle{background:var (--bg-tertiary) !important;border-radius:4px;}..
    drag-handle:hover{background:var (--hover-bg) !important;}</style><?php;session_start () ;/Connecttodatabaserequire_once__DIR__..
    '/../core/calendar_config.php';try{$pdo=newPDO ('mysql:host='.DB_HOST.';dbname='.DB_NAME..
    ';unix_socket='..
    DB_SOCKET,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]) try{/Databaseoperations}catch (PDOException$e) {error_log ("Databaseerror:"..
    $e->getMessage () ) ;thrownewException ("Databaseoperationfailed") ;};}catch (PDOException$e) {die ("Databaseconnectionfailed:"..
    $e->getMessage () ) ;}/HandleAJAXoperationsif ($_SERVER['REQUEST_METHOD']==='POST') {$action=filter_input (INPUT_POST,'action',FILTER_DEFAULT) ??'';/BypassauthforAJAXrequestsif (in_[$action,['get_unsur_list','get_unsur_detail','create_unsur','update_unsur','delete_unsur','force_delete_unsur','update_order']]) {/SettestsessionforAJAX$_SESSION['logged_in']=true;$_SESSION['username']='AJAXUser';$_SESSION['user_id']=1;/ClearanyoutputbuffersforAJAXrequestswhile (ob_get_level () >0) {ob_end_clean () ;}}if ($action==='get_unsur_list') {$stmt=$pdo->query ("SELECT*FROMunsurORDERBYurutan") ;$unsurData=$stmt->fetchAll (PDO::FETCH_ASSOC) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'data'=>$unsurData]) ;exit;}if ($action==='update_order') {$orders=filter_input (INPUT_POST,'orders',FILTER_DEFAULT) ??[];try{$pdo->beginTransaction () ;foreach ($ordersas$order) {$stmt=$pdo->prepare ("UPDATEunsurSETurutan=?WHEREid=?") ;$stmt->execute ([$order['urutan'],$order['id']]) ;}$pdo->commit () ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Urutanunsurberhasildiperbarui!']) ;exit;}catch (Exception$e) {$pdo->rollback () ;header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>'Gagalmemperbaruiurutan:'..
    $e->getMessage () ]) ;exit;}}if ($action==='create_unsur') {/Auto-generatekode_unsurfromnama_unsur$nama_unsur=filter_input (INPUT_POST,'nama_unsur',FILTER_DEFAULT) ;$kode_unsur=preg_replace ('/[^a-zA-Z0-9\s]/','',strtoupper ($nama_unsur) ) ;/Validateandtruncatekode_unsurtofitvarchar (20) if (strlen ($kode_unsur) >20) {$kode_unsur=substr ($kode_unsur,0,20) ;error_log ("CREATEUNSUR:Kodetruncatedto20chars:'$kode_unsur'") ;}/Getthehighestcurrenturutanandadd1$stmt=$pdo->query ("SELECTMAX (urutan) asmax_urutanFROMunsur") ;$maxUrutan=$stmt->fetch () ['max_urutan'];$newUrutan= ($maxUrutan??0) +1;$stmt=$pdo->prepare ("INSERTINTOunsur (kode_unsur,nama_unsur,deskripsi,urutan) VALUES (?,?,?,?) ") ;$stmt->execute ([$kode_unsur,/Usetruncatedkode_unsur$nama_unsur,filter_input (INPUT_POST,'deskripsi',FILTER_DEFAULT) ??'',$newUrutan]) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Unsurberhasilditambahkan!']) ;exit;}if ($action==='get_unsur_detail') {$id=filter_input (INPUT_POST,'id',FILTER_DEFAULT) ??0;$stmt=$pdo->prepare ("SELECT*FROMunsurWHEREid=?") ;$stmt->execute ([$id]) ;$unsur=$stmt->fetch (PDO::FETCH_ASSOC) ;/Getcurrentpimpinanfromunsur_pimpinantableif ($unsur) {$pimpinanStmt=$pdo->prepare ("SELECTp..
    namaFROMunsur_pimpinanupJOINpersonilpONup.personil_id=p.idWHEREup.unsur_id=?ANDup..
    tanggal_selesaiISnullLIMIT1") ;$pimpinanStmt->execute ([$id]) ;$unsur['kepala']=$pimpinanStmt->fetchColumn () ;}header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'data'=>$unsur]) ;exit;}if ($action==='update_unsur') {try{/Debug:Logreceiveddataerror_log ("UPDATEUNSURDEBUG:"..
    print_r ($_POST,true) ) ;/Validateandtruncatekode_unsurtofitvarchar (20) $kodeUnsur=filter_input (INPUT_POST,'kode_unsur',FILTER_DEFAULT) ??'';if (strlen ($kodeUnsur) >20) {$kodeUnsur=substr ($kodeUnsur,0,20) ;error_log ("KODE_UNSURTRUNCATED:Originallength"..
    strlen (filter_input (INPUT_POST,'kode_unsur',FILTER_DEFAULT) ) ..
    "->Truncatedto20:'$kodeUnsur'") ;}/Getcurrenturutanfromdatabase (don'tchangeit) $stmt=$pdo->prepare ("SELECTurutanFROMunsurWHEREid=?") ;$stmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;$currentUrutan=$stmt->fetchColumn () ;$stmt=$pdo->prepare ("UPDATEunsurSETkode_unsur=?,nama_unsur=?,deskripsi=?,urutan=?WHEREid=?") ;$result=$stmt->execute ([$kodeUnsur,/Usetruncatedkode_unsurfilter_input (INPUT_POST,'nama_unsur',FILTER_DEFAULT) ,filter_input (INPUT_POST,'deskripsi',FILTER_DEFAULT) ??'',$currentUrutan,/Useexistingurutanfilter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;error_log ("UPDATERESULT:"..
     ($result?'SUCCESS':'FAILED') ) ;/Updatepimpinanassignmentif (!empty (filter_input (INPUT_POST,'nama_pimpinan',FILTER_DEFAULT) ) ) {/Removeexistingassignments$delStmt=$pdo->prepare ("DELETEFROMunsur_pimpinanWHEREunsur_id=?ANDtanggal_selesaiISnull") ;$delStmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;/Addnewassignment$pimpinanStmt=$pdo->prepare ("SELECTidFROMpersonilWHEREnama=?") ;$pimpinanStmt->execute ([filter_input (INPUT_POST,'nama_pimpinan',FILTER_DEFAULT) ]) ;$pimpinanId=$pimpinanStmt->fetchColumn () ;if ($pimpinanId) {$relStmt=$pdo->prepare ("INSERTINTOunsur_pimpinan (unsur_id,personil_id) VALUES (?,?) ") ;$relStmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ,$pimpinanId]) ;}}header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Unsurberhasildiperbarui!']) ;exit;}catch (Exception$e) {error_log ("UPDATEUNSURERROR:"..
    $e->getMessage () ) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>'Error:'..
    $e->getMessage () ]) ;exit;}}if ($action==='delete_unsur') {/Checkifunsurhasbagian$stmt=$pdo->prepare ("SELECTCOUNT (*) FROMbagianWHEREid_unsur=?") ;$stmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;$bagianCount=$stmt->fetchColumn () ;if ($bagianCount>0) {/Getdetailsforbettererrormessage$stmt=$pdo->prepare ("SELECTnama_unsurFROMunsurWHEREid=?") ;$stmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;$unsurName=$stmt->fetchColumn () ;/Getbagiandetails$stmt=$pdo->prepare ("SELECTnama_bagianFROMbagianWHEREid_unsur=?LIMIT5") ;$stmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;$bagianList=$stmt->fetchAll (PDO::FETCH_COLUMN) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>"Tidakdapatmenghapusunsur'$unsurName'karenamasihmemiliki$bagianCountbagian!",'details'=>['unsur_name'=>$unsurName,'bagian_count'=>$bagianCount,'bagian_list'=>$bagianList,'suggestion'=>'Pindahkanatauhapussemuabagianterlebihdahulu']]) ;exit;}$stmt=$pdo->prepare ("DELETEFROMunsurWHEREid=?") ;$stmt->execute ([filter_input (INPUT_POST,'id',FILTER_DEFAULT) ]) ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>'Unsurberhasildihapus!']) ;exit;}if ($action==='force_delete_unsur') {try{$pdo->beginTransaction () ;$unsurId=filter_input (INPUT_POST,'id',FILTER_DEFAULT) ;$reassignToUnsurId=filter_input (INPUT_POST,'reassign_to_unsur_id',FILTER_DEFAULT) ??null;/Getunsurnameforlogging$stmt=$pdo->prepare ("SELECTnama_unsurFROMunsurWHEREid=?") ;$stmt->execute ([$unsurId]) ;$unsurName=$stmt->fetchColumn () ;/Ifreassign_to_unsur_idisprovided,movebagiantothatunsurif ($reassignToUnsurId) {$stmt=$pdo->prepare ("UPDATEbagianSETid_unsur=?WHEREid_unsur=?") ;$stmt->execute ([$reassignToUnsurId,$unsurId]) ;/Getreassignunsurname$stmt=$pdo->prepare ("SELECTnama_unsurFROMunsurWHEREid=?") ;$stmt->execute ([$reassignToUnsurId]) ;$reassignUnsurName=$stmt->fetchColumn () ;$message="Unsur'$unsurName'berhasildihapusdanbagiandipindahkanke'$reassignUnsurName'!";}else{/Deleteallbagianinthisunsur$stmt=$pdo->prepare ("DELETEFROMbagianWHEREid_unsur=?") ;$deletedBagians=$stmt->rowCount () ;$message="Unsur'$unsurName'berhasildihapusbeserta$deletedBagiansbagianterkait!";}/Nowdeletetheunsur$stmt=$pdo->prepare ("DELETEFROMunsurWHEREid=?") ;$stmt->execute ([$unsurId]) ;$pdo->commit () ;header ('Content-Type:application/json') ;echojson_encode (['success'=>true,'message'=>$message]) ;exit;}catch (Exception$e) {$pdo->rollback () ;header ('Content-Type:application/json') ;echojson_encode (['success'=>false,'message'=>'Gagalmenghapusunsur:'..
    $e->getMessage () ]) ;exit;}}}/Getcurrentunsurdataonly (simpleandclean) try{$stmt=$pdo->query ("SELECT*FROMunsurORDERBYurutan") ;$unsurData=$stmt->fetchAll (PDO::FETCH_ASSOC) ;}catch (PDOException$e) {$unsurData=[];}/Ensure$unsurDataisalwaysanarrayif ($unsurData===false) {$unsurData=[];}?><divclass="container"><divclass="page-header"><h1><iclass="fasfa-sitemapme-2"></i>ManajemenUnsur</h1><pclass="text-mutedtext-center">AtururutandankeloladataunsurorganisasiPOLRESSamosir</p></div><!--SortableUnsurTable--><divclass="card"style="max-width:900px;margin:0auto;"><divclass="card-headerd-flexjustify-content-betweenalign-items-center"><div><iclass="fasfa-listme-2"></i>UrutanUnsurOrganisasi<smallclass="text-mutedms-2"> (Drag&dropuntukmengatururutan) </small></div><divclass="d-flexgap-2"><buttonclass="btnbtn-primarybtn-sm"onclick="openAddModal () "><iclass="fasfa-plusme-1"></i>Tambah</button><buttonclass="btnbtn-infobtn-sm"onclick="refreshData () "><iclass="fasfa-syncme-1"></i>Refresh</button><buttonclass="btnbtn-successbtn-sm"id="saveOrderBtn"onclick="saveOrder () "><iclass="fasfa-saveme-1"></i>Simpan</button><buttonclass="btnbtn-warningbtn-sm"id="cancelOrderBtn"onclick="cancelOrder () "style="display:none;"><iclass="fasfa-timesme-1"></i>Batal</button></div></div><divclass="card-body"><!--SearchBar--><divclass="rowmb-3"><divclass="col-12"><divclass="input-group"><spanclass="input-group-text"><iclass="fasfa-search"></i></span><inputtype="text"id="searchInput"class="form-control"placeholder="Carinamaunsur..
    ...
    "autocomplete="off"><buttonclass="btnbtn-outline-secondary"id="btnClearSearch"type="button"><iclass="fasfa-times"></i>Clear</button></div></div></div><!--TableHeader--><divclass="rowmb-3text-muted"><divclass="col-1"><strong>Urutan</strong></div><divclass="col-7"><strong>NamaUnsur</strong></div><divclass="col-4"><strong>Aksi</strong></div></div><divid="sortable-container"class="sortable-list"><?php;session_start () ;foreach ($unsurDataas$unsur) :?><divclass="sortable-item"data-id="<?php;session_start () ;echo$unsur['id'];?>"data-urutan="<?php;session_start () ;echo$unsur['urutan'];?>"><divclass="d-flexalign-items-center"><divclass="drag-handleme-3"><iclass="fasfa-grip-vertical"></i></div><divclass="flex-grow-1"><divclass="rowalign-items-centerg-2"><divclass="col-1"><spanclass="badgebg-primaryorder-badgeflex-shrink-0"><?php;session_start () ;echo$unsur['urutan'];?></span></div><divclass="col-7"><strong><?php;session_start () ;echohtmlspecialchars ($unsur['nama_unsur']) ;?></strong><br><smallclass="text-muted">Order:<?php;session_start () ;echo$unsur['urutan'];?></small></div><divclass="col-4"><divclass="btn-group"role="group"><buttonclass="btnbtn-smbtn-outline-primary"onclick="editUnsur (<?php;session_start () ;echo$unsur['id'];?>) "><iclass="fasfa-edit"></i></button><buttonclass="btnbtn-smbtn-outline-danger"onclick="deleteUnsur (<?php;session_start () ;echo$unsur['id'];?>,'<?php;session_start () ;echohtmlspecialchars ($unsur['nama_unsur']) ;?>') "><iclass="fasfa-trash"></i></button></div></div></div></div></div></div><?php;session_start () ;endforeach;?></div></div></div></div><!--Add/EditModal--><divclass="modalfade"id="unsurModal"tabindex="-1"data-bs-backdrop="static"data-bs-keyboard="false"aria-modal="true"role="dialog"><divclass="modal-dialogmodal-md"><divclass="modal-content"><divclass="modal-header"><h5class="modal-title"><iclass="fasfa-sitemapme-2"></i><spanid="modalTitle">TambahUnsur</span></h5><buttontype="button"class="btn-close"data-bs-dismiss="modal"></button></div><formmethod="POST"id="unsurForm"><divclass="modal-body"><inputtype="hidden"name="action"id="formAction"value="create_unsur"><inputtype="hidden"name="id"id="formId"><inputtype="hidden"name="kode_unsur"id="kode_unsur"><divclass="mb-3"><labelfor="nama_unsur"class="form-label">NamaUnsur</label><inputtype="text"class="form-control"id="nama_unsur"name="nama_unsur"requiredonchange="generateKodeUnsur () "><divclass="form-text">Contoh:UNSURPIMPINAN,UNSURPEMBANTUPIMPINAN</div></div><divclass="alertalert-info"><iclass="fasfa-info-circleme-2"></i><strong>UrutanOtomatis:</strong>Unsurakanditambahkandiurutanpalingbawahdandapatdiaturmenggunakandrag&drop..
    </div><divclass="mb-3"><labelfor="deskripsi"class="form-label">Deskripsi</label><textareaclass="form-control"id="deskripsi"name="deskripsi"rows="3"></textarea><divclass="form-text">Deskripsiataupenjelasansingkattentangunsur (opsional) </div></div></div><divclass="modal-footer"><buttontype="button"class="btnbtn-secondary"data-bs-dismiss="modal">Batal</button><buttontype="submit"class="btnbtn-primary"><iclass="fasfa-saveme-2"></i>Simpan</button></div></form></div></div></div><?php;session_start () ;include'..
    ./includes/components/footer.php';?><!--SortableJS--><scriptsrc="https://cdn.jsdelivr..
    net/npm/sortablejs@1.15.0/Sortable.min..
    js"></script><script>letunsurData=<?php;session_start () ;echojson_encode ($unsurData) ;?>;letoriginalOrder=[..
    ..unsurData];/Storeoriginalorderforcancelfunctionality/InitializeSortabledocument..
    addEventListener ('DOMContentLoaded',function () {constcontainer=document..
    getElementById ('sortable-container') ;newSortable (container,{animation:150,ghostClass:'sortable-ghost',chosenClass:'sortable-chosen',dragClass:'sortable-dragging',handle:'..
    drag-handle',onEnd:function (evt) {updateOrderNumbers () ;showSaveButton () ;}}) ;}) ;functionupdateOrderNumbers () {constitems=document..
    querySelectorAll ('.sortable-item') ;items.forEach ( (item,index) =>{item.dataset..
    urutan=index+1;consturutanDisplay=item.querySelector ('..
    col-7small') ;if (urutanDisplay) {urutanDisplay..
    textContent=`Urutan:${index+1}`;}/Addvisualfeedbackitem.classList..
    add ('order-updated') ;setTimeout ( () =>{item.classList..
    remove ('order-updated') ;},500) ;}) ;/ShowsavebuttonshowSaveButton () ;}functionshowSaveButton () {constsaveBtn=document..
    getElementById ('saveOrderBtn') ;constcancelBtn=document..
    getElementById ('cancelOrderBtn') ;if (saveBtn) {saveBtn.classList..
    remove ('btn-success') ;saveBtn.classList.add ('btn-warning') ;saveBtn..
    innerHTML='<iclass="fasfa-exclamation-triangleme-2"></i>SimpanPerubahan';}if (cancelBtn) {cancelBtn..
    style..
    display='inline-block';}}functioncancelOrder () {/RestoreoriginalorderrestoreOriginalOrder () ;/ResetbuttonstoinitialstateresetButtons () ;}functionrestoreOriginalOrder () {constcontainer=document..
    getElementById ('sortable-container') ;/Clearcurrentitemscontainer..
    innerHTML='';/RebuilditemsinoriginalorderoriginalOrder..
    forEach ( (unsur,index) =>{constitemHtml=`<divclass="sortable-item"data-id="${unsur..
    id}"data-urutan="${unsur..
    urutan}"><divclass="d-flexalign-items-center"><divclass="drag-handleme-3"><iclass="fasfa-grip-vertical"></i></div><divclass="flex-grow-1"><divclass="rowalign-items-centerg-2"><divclass="col-1"><spanclass="badgebg-primaryorder-badgeflex-shrink-0">${unsur..
    urutan}</span></div><divclass="col-7"><strong>${unsur..
    nama_unsur}</strong><br><smallclass="text-muted">Urutan:${unsur..
    urutan}</small></div><divclass="col-4"><divclass="btn-group"role="group"><buttonclass="btnbtn-smbtn-outline-primary"onclick="editUnsur (${unsur..
    id}) "><iclass="fasfa-edit"></i></button><buttonclass="btnbtn-smbtn-outline-danger"onclick="deleteUnsur (${unsur..
    id},'${unsur..
    nama_unsur}') "><iclass="fasfa-trash"></i></button></div></div></div></div></div></div>`;container..
    insertAdjacentHTML ('beforeend',itemHtml) ;}) ;/UpdateunsurDatatomatchoriginalorderunsurData=[...
    .originalOrder];}functionresetButtons () {constsaveBtn=document..
    getElementById ('saveOrderBtn') ;constcancelBtn=document..
    getElementById ('cancelOrderBtn') ;if (saveBtn) {saveBtn.classList..
    remove ('btn-warning') ;saveBtn.classList.add ('btn-success') ;saveBtn..
    innerHTML='<iclass="fasfa-saveme-2"></i>SimpanUrutan';}if (cancelBtn) {cancelBtn.style..
    display='none';}}functionsaveOrder () {constitems=document.querySelectorAll ('..
    sortable-item') ;constorders=[];items.forEach ( (item,index) =>{orders.push ({id:item.dataset..
    id,urutan:index+1}) ;}) ;fetch ('unsur..
    php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded',},body:newURLSearchParams ({action:'update_order',orders:JSON..
    stringify (orders) }) }) .then (response=>response.json () ) .then (data=>{if (data..
    success) {alert (data..
    message) ;/Updateoriginalordertoreflectsavedchangesconstcontainer=document..
    getElementById ('sortable-container') ;constitems=container.querySelectorAll ('..
    sortable-item') ;originalOrder=[];items.forEach ( (item,index) =>{originalOrder.push ({id:item..
    dataset.id,urutan:index+1,nama_unsur:item.querySelector ('.col-7strong') ..
    textContent}) ;}) ;/ResetbuttonstoinitialstateresetButtons () ;}else{alert ('Error:'+data..
    message) ;}}) .catch (error=>{console..
    error ('Error:',error) ;alert ('Error:Terjadikesalahansaatmenyimpanurutan') ;}) ;}functiongenerateKodeUnsur () {constnamaUnsur=document..
    getElementById ('nama_unsur') .value;constkodeUnsur=namaUnsur.toUpperCase () ..
    replace (/[^A-Z0-9\s]/g,'') .replace (/\s+/g,'') .trim () ;document..
    getElementById ('kode_unsur') ..
    value=kodeUnsur;}functionopenAddModal () {try{/ClearformfieldsfirstconstmodalTitle=document..
    getElementById ('modalTitle') ;constformAction=document..
    getElementById ('formAction') ;constformId=document..
    getElementById ('formId') ;constnamaUnsur=document..
    getElementById ('nama_unsur') ;constdeskripsi=document..
    getElementById ('deskripsi') ;constkodeUnsur=document..
    getElementById ('kode_unsur') ;if (!modalTitle||!formAction||!formId||!namaUnsur||!deskripsi||!kodeUnsur) {console..
    error ('Modalformelementsnotfound') ;alert ('Error:Formelementsnotfound') ;return;}modalTitle..
    textContent='TambahUnsur';formAction.value='create_unsur';formId.value='';namaUnsur..
    value='';deskripsi.value='';kodeUnsur..
    value='';/ShowmodalwithproperhandlingconstmodalElement=document..
    getElementById ('unsurModal') ;if (!modalElement) {console..
    error ('Modalelementnotfound') ;alert ('Error:Modalnotfound') ;return;}/CleanupanyexistingmodalinstanceconstexistingModal=bootstrap..
    Modal.getInstance (modalElement) ;if (existingModal) {existingModal..
    dispose () ;}constmodal=newbootstrap.Modal (modalElement) ;modal..
    show () ;}catch (error) {console..
    error ('Erroropeningaddmodal:',error) ;alert ('Error:Failedtoopenmodal-'+error..
    message) ;}}functioneditUnsur (id) {try{console..
    log ('EditingunsurwithID:',id) ;/Getunsurdatafromdatabasefetch ('unsur..
    php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded',},body:newURLSearchParams ({action:'get_unsur_detail',id:id}) }) ..
    then (response=>{console.log ('Responsestatus:',response.status) ;returnresponse.json () ;}) ..
    then (data=>{console.log ('Responsedata:',data) ;if (data.success&&data.data) {constunsur=data..
    data;console.log ('Unsurdata:',unsur) ;/GetformelementssafelyconstmodalTitle=document..
    getElementById ('modalTitle') ;constformAction=document..
    getElementById ('formAction') ;constformId=document..
    getElementById ('formId') ;constnamaUnsur=document..
    getElementById ('nama_unsur') ;constdeskripsi=document..
    getElementById ('deskripsi') ;constkodeUnsur=document..
    getElementById ('kode_unsur') ;if (!modalTitle||!formAction||!formId||!namaUnsur||!deskripsi||!kodeUnsur) {console..
    error ('Modalformelementsnotfound') ;alert ('Error:Formelementsnotfound') ;return;}/FillformfieldsmodalTitle..
    textContent='EditUnsur';formAction.value='update_unsur';formId.value=unsur.id;namaUnsur..
    value=unsur.nama_unsur;deskripsi.value=unsur.deskripsi||'';kodeUnsur.value=unsur..
    kode_unsur;/ShowmodalwithproperhandlingconstmodalElement=document..
    getElementById ('unsurModal') ;if (!modalElement) {console..
    error ('Modalelementnotfound') ;alert ('Error:Modalnotfound') ;return;}/CleanupanyexistingmodalinstanceconstexistingModal=bootstrap..
    Modal.getInstance (modalElement) ;if (existingModal) {existingModal..
    dispose () ;}constmodal=newbootstrap.Modal (modalElement) ;modal.show () ;}else{console..
    error ('Errorinresponse:',data) ;alert ('Error:Unsurtidakditemukan') ;}}) ..
    catch (error=>{console.error ('Fetcherror:',error) ;alert ('Error:'+error..
    message) ;}) ;}catch (error) {console..
    error ('ErrorineditUnsur:',error) ;alert ('Error:Failedtoeditunsur-'+error..
    message) ;}}functiondeleteUnsur (id,nama) {/Firstcheckifunsurhasbagianfetch ('unsur..
    php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded',},body:newURLSearchParams ({action:'delete_unsur',id:id}) }) ..
    then (response=>response.json () ) .then (data=>{if (data..
    success) {/Unsurdeletedsuccessfullyif (data.message) {alert (data.message) ;}location..
    reload () ;}else{/Unsurhasbagian,showdetailederrorandoptionsif (data..
    details) {constdetails=data.details;letmessage=data.message+'\n\n';if (details..
    bagian_list&&details.bagian_list.length>0) {message+='Bagianterkait:\n';details.bagian_list..
    forEach ( (bagian,index) =>{message+=`${index+1}.${bagian}\n`;}) ;if (details..
    bagian_count>details.bagian_list.length) {message+=`...dan${details.bagian_count-details..
    bagian_list.length}lainnya\n`;}}message+='\n'+details..
    suggestion;/ShowoptionsconstuserChoice=confirm (message+'\n\nKlikOKuntukmencobalagi,atauCanceluntukbatal..
    ') ;if (userChoice) {/UserwantstoproceedwithforcedeleteoptionsshowForceDeleteOptions (id,details) ;}}else{alert (data..
    message||'Gagalmenghapusunsur') ;}}}) .catch (error=>{console..
    error ('Deleteerror:',error) ;alert ('Error:'+error..
    message) ;}) ;}functionshowForceDeleteOptions (unsurId,details) {/Getallotherunsuroptionsforreassigningfetch ('unsur..
    php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded',},body:newURLSearchParams ({action:'get_unsur_list'}) }) ..
    then (response=>response.json () ) .then (data=>{if (data.success&&data..
    data) {constotherUnsurs=data.data.filter (u=>u.id!=unsurId) ;if (otherUnsurs..
    length===0) {/Nootherunsurtoreassigntoif (confirm (`Tidakadaunsurlainuntukdipindahkan..
    ApakahAndayakininginmenghapusunsur"${details.unsur_name}"besertasemua${details..
    bagian_count}bagianterkait?`) ) {forceDeleteUnsur (unsurId,null) ;}}else{/Createasimplechoicedialogconstoptions=otherUnsurs..
    map ( (unsur,index) =>`${index+1}.Pindahkanke:${unsur.nama_unsur}`) ..
    join ('\n') ;constchoice=prompt (`Pilihopsiuntukmenghapusunsur"${details..
    unsur_name}":\n\n`+options+'\n'+`\n${otherUnsurs.length+1}..
    Hapusbesertasemuabagian\n`+`\nMasukkannomorpilihan (1-${otherUnsurs..
    length+1}) :`) ;if (choice) {constchoiceNum=parseInt (choice) ;if (choiceNum>=1&&choiceNum<=otherUnsurs..
    length) {/ReassigntoselectedunsurconstselectedUnsur=otherUnsurs[choiceNum-1];if (confirm (`Pindahkan${details..
    bagian_count}bagianke"${selectedUnsur.nama_unsur}"danhapusunsur"${details..
    unsur_name}"?`) ) {forceDeleteUnsur (unsurId,selectedUnsur..
    id) ;}}elseif (choiceNum===otherUnsurs..
    length+1) {/Deletewithbagiansif (confirm (`Hapusunsur"${details..
    unsur_name}"besertasemua${details..
    bagian_count}bagianterkait?`) ) {forceDeleteUnsur (unsurId,null) ;}}else{alert ('Pilihantidakvalid') ;}}}}else{alert ('Gagalmengambildataunsur') ;}}) ..
    catch (error=>{console.error ('Getunsurlisterror:',error) ;alert ('Error:'+error..
    message) ;}) ;}functionforceDeleteUnsur (unsurId,reassignToUnsurId) {constformData=newFormData () ;formData..
    append ('action','force_delete_unsur') ;formData..
    append ('id',unsurId) ;if (reassignToUnsurId) {formData..
    append ('reassign_to_unsur_id',reassignToUnsurId) ;}fetch ('unsur..
    php',{method:'POST',body:formData}) .then (response=>response.json () ) .then (data=>{if (data..
    success) {alert (data.message) ;location.reload () ;}else{alert ('Gagalmenghapusunsur:'+ (data..
    message||'Unknownerror') ) ;}}) .catch (error=>{console..
    error ('Forcedeleteerror:',error) ;alert ('Error:'+error..
    message) ;}) ;}functionrefreshData () {window.location..
    reload () ;}/SearchfunctionalityfunctionsetupSearch () {constsearchInput=document..
    getElementById ('searchInput') ;constclearBtn=document..
    getElementById ('btnClearSearch') ;if (searchInput) {searchInput..
    addEventListener ('input',function () {constsearchTerm=this.value.toLowerCase () ..
    trim () ;filterUnsur (searchTerm) ;}) ;searchInput..
    addEventListener ('keyup',function (e) {if (e..
    key==='Escape') {clearSearch () ;}}) ;}if (clearBtn) {clearBtn..
    addEventListener ('click',clearSearch) ;}}functionfilterUnsur (searchTerm) {constsortableItems=document..
    querySelectorAll ('.sortable-item') ;if (searchTerm==='') {/ShowallsortableItems..
    forEach (item=>{item.style.display='block';}) ;return;}/FilterunsuritemssortableItems..
    forEach (item=>{constnamaUnsur=item.querySelector ('strong') ?.textContent..
    toLowerCase () ||'';constkodeUnsur=item.querySelector ('code') ?.textContent..
    toLowerCase () ||'';consturutan=item.querySelector ('.order-badge') ?.textContent..
    toLowerCase () ||'';constmatches=namaUnsur.includes (searchTerm) ||kodeUnsur..
    includes (searchTerm) ||urutan.includes (searchTerm) ;item.style..
    display=matches?'block':'none';}) ;}functionclearSearch () {document..
    getElementById ('searchInput') .value='';filterUnsur ('') ;}/Initializesearchonpageloaddocument..
    addEventListener ('DOMContentLoaded',function () {setupSearch () ;}) ;/Formsubmissiondocument..
    getElementById ('unsurForm') ..
    addEventListener ('submit',function (e) {try{/Checkifeventexistsandpreventdefaultif (e&&e..
    preventDefault) {e.preventDefault () ;}else{/Fallbackforolderbrowsersorifeventisnullconsole..
    warn ('EventorpreventDefaultnotavailable') ;returnfalse;}constformData=newFormData (this) ;constaction=formData..
    get ('action') ;/Debug:Logformdataconsole.log ('FormData:') ;for (let[key,value]offormData..
    entries () ) {console.log (key+':',value) ;}fetch ('unsur.php',{method:'POST',body:formData}) ..
    then (response=>{console.log ('Responsestatus:',response.status) ;returnresponse.json () ;}) ..
    then (data=>{console.log ('Responsedata:',data) ;if (data.success) {alert (data..
    message) ;location.reload () ;}else{alert ('Error:'+data.message) ;}}) .catch (error=>{console..
    error ('Formsubmissionerror:',error) ;alert ('Error:'+error..
    message) ;}) ;}catch (error) {console..
    error ('Formsubmissionhandlererror:',error) ;alert ('Error:Failedtosubmitform-'+error..
    message) ;}returnfalse;}) ;</script>.
