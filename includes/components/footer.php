/**
 * includes/components/footer.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

<divclass="footer"><divclass="container"><divclass="row"><divclass="col-md-6"><h5><iclass="fasfa-shield-altme-2"></i>POLRESSAMOSIR</h5><pclass="mb-0">SistemManajemenPersonil&ScheduleManagement</p><small>BagianOperasional(BAGOPS)</small></div><divclass="col-md-6text-md-end"><pclass="mb-0"><iclass="fasfa-userme-1"></i>User:<strong><?php

declare(strict_types=1);

session_start();echohtmlspecialchars($_SESSION['username']??'Guest');?></strong></p><pclass="mb-0"><iclass="fasfa-clockme-1"></i>Login:<?php
session_start();echoisset($_SESSION['login_time'])?date('dMYH:i',strtotime($_SESSION['login_time'])):'Notavailable';?></p><smallclass="text-muted"><iclass="fasfa-codeme-1"></i>Version1.0.0|©2026</small></div></div></div></div><style>.footer{background:var(--primary-color);color:white;padding:30px0;margin-top:50px;}.footerh5{color:var(--accent-color);font-weight:bold;margin-bottom:15px;}.footerp{margin-bottom:5px;}.footera{color:var(--accent-color);text-decoration:none;}.footera:hover{color:white;}@media(max-width:768px){.footer{padding:20px0;margin-top:30px;}.footer.col-md-6,.footer.col-md-6.text-md-end{text-align:center!important;margin-bottom:20px;}}</style><!--BootstrapJSalreadyloadedinheader.php-noduplicateloadingneeded--></body></html>
