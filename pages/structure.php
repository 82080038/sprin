<?php
declare(strict_types=1);
// Start output buffering if not already started
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config first
require_once __DIR__ . '/../core/config.php';

// Include authentication check
require_once __DIR__ . '/../core/auth_check.php';

$page_title = 'Struktur Organisasi - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>

<style>
/* Structure page styles with theme variables */
.structure-container {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    margin: 20px 0;
    box-shadow: var(--shadow-color);
}

.structure-header {
    text-align: center;
    margin-bottom: 40px;
}

.structure-header h1 {
    color: var(--text-primary);
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.structure-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.structure-tree {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 1000px;
    margin: 0 auto;
}

.structure-level {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    position: relative;
}

.structure-level::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: var(--border-color);
}

.structure-level:last-child::before {
    display: none;
}

.structure-item {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px 20px;
    min-width: 200px;
    position: relative;
    transition: all 0.3s ease;
}

.structure-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-color);
    border-color: var(--primary-color);
}

.structure-item::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    background: var(--primary-color);
    border-radius: 50%;
    border: 3px solid var(--bg-primary);
}

.structure-item h3 {
    color: var(--primary-color);
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.structure-item .position {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.structure-item .badge {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.structure-children {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-left: 40px;
    position: relative;
}

.structure-children::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: var(--border-color);
}

@media (max-width: 768px) {
    .structure-container {
        padding: 20px 15px;
        margin: 10px 0;
    }
    
    .structure-header h1 {
        font-size: 2rem;
    }
    
    .structure-level {
        flex-direction: column;
        gap: 15px;
    }
    
    .structure-level::before {
        display: none;
    }
    
    .structure-item::before {
        display: none;
    }
    
    .structure-children {
        margin-left: 0;
    }
    
    .structure-children::before {
        display: none;
    }
}
</style>

<div class="container">
    <div class="structure-container">
        <div class="structure-header">
            <h1><i class="fas fa-sitemap me-3"></i>Struktur Organisasi</h1>
            <p>Struktur hierarki POLRES Samosir lengkap dengan unsur, bagian, dan jabatan</p>
        </div>

        <div class="structure-tree">
            <!-- Pimpinan Level -->
            <div class="structure-level">
                <div class="structure-item">
                    <h3>PIMPINAN</h3>
                    <div class="position">Kapolres Samosir</div>
                    <span class="badge">1 Jabatan</span>
                </div>
            </div>

            <!-- Unsur Pimpinan -->
            <div class="structure-level">
                <div class="structure-item">
                    <h3>UNSUR PEMBANTU PIMPINAN</h3>
                    <div class="position">Wakapolres, Kabag, dll</div>
                    <span class="badge">5 Jabatan</span>
                </div>
                <div class="structure-children">
                    <div class="structure-item">
                        <h3>WAKAPOLRES</h3>
                        <div class="position">Wakil Kapolres</div>
                        <span class="badge">1 Jabatan</span>
                    </div>
                    <div class="structure-item">
                        <h3>KABAG SUMDA</h3>
                        <div class="position">Kepala Bagian Sumda</div>
                        <span class="badge">1 Jabatan</span>
                    </div>
                    <div class="structure-item">
                        <h3>KABAG OPS</h3>
                        <div class="position">Kepala Bagian Operasional</div>
                        <span class="badge">1 Jabatan</span>
                    </div>
                    <div class="structure-item">
                        <h3>KABAG REN</h3>
                        <div class="position">Kepala Bagian Perencanaan</div>
                        <span class="badge">1 Jabatan</span>
                    </div>
                    <div class="structure-item">
                        <h3>KABAG LOG</h3>
                        <div class="position">Kepala Bagian Logistik</div>
                        <span class="badge">1 Jabatan</span>
                    </div>
                </div>
            </div>

            <!-- Unsur Pelaksana -->
            <div class="structure-level">
                <div class="structure-item">
                    <h3>UNSUR PELAKSANA</h3>
                    <div class="position">Satuan Fungsi, Subbag, dll</div>
                    <span class="badge">15+ Unit</span>
                </div>
                <div class="structure-children">
                    <div class="structure-item">
                        <h3>SAT INTELKAM</h3>
                        <div class="position">Satuan Intelijen Keamanan</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SAT RESKRIM</h3>
                        <div class="position">Satuan Reserse Kriminal</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SAT SABHARA</h3>
                        <div class="position">Satuan Samapta Bhayangkara</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SAT LANTAS</h3>
                        <div class="position">Satuan Lalu Lintas</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SAT POLAIR</h3>
                        <div class="position">Satuan Polisi Air</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SAT POLAIRUD</h3>
                        <div class="position">Satuan Polisi Air Udara</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SUBBAG SIM</h3>
                        <div class="position">Subbagian SIM</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SUBBAG KEU</h3>
                        <div class="position">Subbagian Keuangan</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>SUBBAG SARPRAS</h3>
                        <div class="position">Subbagian Sarana Prasarana</div>
                        <span class="badge">1 Unit</span>
                    </div>
                </div>
            </div>

            <!-- Unsur Pendidikan -->
            <div class="structure-level">
                <div class="structure-item">
                    <h3>UNSUR PENDIDIKAN</h3>
                    <div class="position">Sprip, Bintara, Tamtama</div>
                    <span class="badge">3 Unit</span>
                </div>
                <div class="structure-children">
                    <div class="structure-item">
                        <h3>SPRIP POLRES</h3>
                        <div class="position">Sekolah Polisi</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>BINTARA</h3>
                        <div class="position">Pendidikan Bintara</div>
                        <span class="badge">1 Unit</span>
                    </div>
                    <div class="structure-item">
                        <h3>TAMTAMA</h3>
                        <div class="position">Pendidikan Tamtama</div>
                        <span class="badge">1 Unit</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="text-center">
                    <h3 style="color: var(--text-primary); margin-bottom: 20px;">Statistik Struktur</h3>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-number">4</div>
                                <div class="stat-label">Total Unsur</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-number">15+</div>
                                <div class="stat-label">Total Bagian</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-number">50+</div>
                                <div class="stat-label">Total Jabatan</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-number">200+</div>
                                <div class="stat-label">Total Personil</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Animate structure items on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Initially hide structure items
    document.querySelectorAll('.structure-item').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'all 0.6s ease';
        observer.observe(item);
    });
    
    // Add click functionality to structure items
    document.querySelectorAll('.structure-item').forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            const title = this.querySelector('h3').textContent;
            console.log('Clicked on:', title);
            // Could add modal or navigation functionality here
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
