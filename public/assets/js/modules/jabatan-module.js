/**
 * SPRIN Jabatan Module
 * Handles jabatan management functionality
 */

class SPRINJabatanModule extends SPRINBaseModule {
    constructor(core) {
        super(core);
        this.jabatanData = [];
        this.unsurData = [];
        this.bagianData = [];
        this.sortableInstances = {};
        this.init();
    }
    
    init() {
        console.log('🔧 Initializing Jabatan Module...');
        this.bindEvents();
        this.loadData();
        this.loadReferenceData();
        this.setupSortable();
    }
    
    destroy() {
        // Destroy sortable instances
        Object.values(this.sortableInstances).forEach(instance => {
            if (instance) instance.destroy();
        });
        console.log('🗑️ Jabatan Module destroyed');
    }
    
    bindEvents() {
        // Bind form submission
        const form = document.getElementById('jabatanForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        }
        
        // Bind add button
        const addBtn = document.querySelector('[data-action="add-jabatan"]');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showAddModal());
        }
        
        // Bind filter handlers
        const unsurFilter = document.getElementById('unsur-filter');
        const bagianFilter = document.getElementById('bagian-filter');
        
        if (unsurFilter) {
            unsurFilter.addEventListener('change', () => this.applyFilters());
        }
        
        if (bagianFilter) {
            bagianFilter.addEventListener('change', () => this.applyFilters());
        }
    }
    
    async loadData() {
        try {
            this.uiHelper.showLoading('Loading jabatan data...');
            
            const response = await this.apiClient.get('/jabatan_api.php?action=get_all_jabatan');
            
            if (response.success) {
                this.jabatanData = response.data;
                this.renderTable();
                this.updateStatistics();
            } else {
                this.uiHelper.showError('Failed to load jabatan data');
            }
        } catch (error) {
            console.error('Load jabatan error:', error);
            this.uiHelper.showError('Failed to load jabatan data: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    async loadReferenceData() {
        try {
            const [unsurResponse, bagianResponse] = await Promise.all([
                this.apiClient.get('/unsur_api.php?action=get_all_unsur'),
                this.apiClient.get('/bagian_api.php?action=get_all_bagian')
            ]);
            
            if (unsurResponse.success) {
                this.unsurData = unsurResponse.data;
                this.populateUnsurSelect();
            }
            
            if (bagianResponse.success) {
                this.bagianData = bagianResponse.data;
                this.populateBagianSelect();
            }
        } catch (error) {
            console.error('Load reference data error:', error);
        }
    }
    
    populateUnsurSelect() {
        const select = document.getElementById('id_unsur');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Unsur</option>';
        
        this.unsurData.forEach(unsur => {
            const option = document.createElement('option');
            option.value = unsur.id;
            option.textContent = unsur.nama_unsur;
            select.appendChild(option);
        });
    }
    
    populateBagianSelect() {
        const select = document.getElementById('id_bagian');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Bagian</option>';
        
        this.bagianData.forEach(bagian => {
            const option = document.createElement('option');
            option.value = bagian.id;
            option.textContent = bagian.nama_bagian;
            select.appendChild(option);
        });
    }
    
    renderTable() {
        const filteredData = this.getFilteredData();
        const tbody = document.querySelector('#jabatanTable tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        filteredData.forEach((jabatan, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${jabatan.nama_jabatan}</td>
                <td>${jabatan.nama_unsur || '-'}</td>
                <td>${jabatan.nama_bagian || '-'}</td>
                <td>${jabatan.urutan}</td>
                <td>${jabatan.personil_count || 0}</td>
                <td>
                    <span class="badge badge-${jabatan.is_active ? 'success' : 'secondary'}">
                        ${jabatan.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info btn-sm" onclick="window.SPRINT_CORE.modules.jabatan.editJabatan(${jabatan.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="window.SPRINT_CORE.modules.jabatan.deleteJabatan(${jabatan.id}, '${jabatan.nama_jabatan}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    getFilteredData() {
        const unsurFilter = document.getElementById('unsur-filter')?.value;
        const bagianFilter = document.getElementById('bagian-filter')?.value;
        
        return this.jabatanData.filter(jabatan => {
            if (unsurFilter && jabatan.id_unsur != unsurFilter) return false;
            if (bagianFilter && jabatan.id_bagian != bagianFilter) return false;
            return true;
        });
    }
    
    applyFilters() {
        this.renderTable();
    }
    
    updateStatistics() {
        const totalJabatan = this.jabatanData.length;
        const activeJabatan = this.jabatanData.filter(j => j.is_active).length;
        
        const totalEl = document.getElementById('totalJabatan');
        const activeEl = document.getElementById('activeJabatan');
        
        if (totalEl) totalEl.textContent = totalJabatan;
        if (activeEl) activeEl.textContent = activeJabatan;
    }
    
    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const action = formData.get('action');
        const data = Object.fromEntries(formData);
        
        try {
            this.uiHelper.showLoading('Saving...');
            
            let response;
            switch (action) {
                case 'create_jabatan':
                    response = await this.apiClient.post('/jabatan_api.php', data);
                    break;
                case 'update_jabatan':
                    response = await this.apiClient.post('/jabatan_api.php', data);
                    break;
                default:
                    throw new Error('Invalid action');
            }
            
            if (response.success) {
                this.uiHelper.showSuccess(response.message);
                await this.loadData();
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('jabatanModal'));
                if (modal) modal.hide();
                
                // Reset form
                form.reset();
                document.getElementById('modalTitle').textContent = 'Tambah Jabatan';
                document.getElementById('formAction').value = 'create_jabatan';
                document.getElementById('formId').value = '';
            } else {
                this.uiHelper.showError(response.message);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.uiHelper.showError('Failed to save jabatan: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    showAddModal() {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('jabatanModal'));
        document.getElementById('modalTitle').textContent = 'Tambah Jabatan';
        document.getElementById('formAction').value = 'create_jabatan';
        document.getElementById('formId').value = '';
        
        // Reset form
        const form = document.getElementById('jabatanForm');
        form.reset();
        
        modal.show();
    }
    
    async editJabatan(id) {
        try {
            this.uiHelper.showLoading('Loading jabatan data...');
            
            const response = await this.apiClient.post('/jabatan_api.php', {
                action: 'get_jabatan_detail',
                id: id
            });
            
            if (response.success && response.data) {
                const jabatan = response.data;
                
                // Fill form
                document.getElementById('modalTitle').textContent = 'Edit Jabatan';
                document.getElementById('formAction').value = 'update_jabatan';
                document.getElementById('formId').value = jabatan.id;
                document.getElementById('nama_jabatan').value = jabatan.nama_jabatan;
                document.getElementById('kode_jabatan').value = jabatan.kode_jabatan || '';
                document.getElementById('deskripsi').value = jabatan.deskripsi || '';
                document.getElementById('id_unsur').value = jabatan.id_unsur || '';
                document.getElementById('id_bagian').value = jabatan.id_bagian || '';
                document.getElementById('urutan').value = jabatan.urutan;
                
                // Show modal
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('jabatanModal'));
                modal.show();
            } else {
                this.uiHelper.showError('Jabatan not found');
            }
        } catch (error) {
            console.error('Edit jabatan error:', error);
            this.uiHelper.showError('Failed to load jabatan: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    deleteJabatan(id, nama) {
        this.uiHelper.confirm(
            `Apakah Anda yakin ingin menghapus jabatan "${nama}"?`,
            async () => {
                try {
                    this.uiHelper.showLoading('Deleting...');
                    
                    const response = await this.apiClient.post('/jabatan_api.php', {
                        action: 'delete_jabatan',
                        id: id
                    });
                    
                    if (response.success) {
                        this.uiHelper.showSuccess(response.message);
                        await this.loadData();
                    } else {
                        this.uiHelper.showError(response.message);
                    }
                } catch (error) {
                    console.error('Delete jabatan error:', error);
                    this.uiHelper.showError('Failed to delete jabatan: ' + error.message);
                } finally {
                    this.uiHelper.hideLoading();
                }
            }
        );
    }
    
    setupSortable() {
        if (!window.Sortable) {
            // Load SortableJS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
            script.onload = () => this.initSortable();
            document.head.appendChild(script);
        } else {
            this.initSortable();
        }
    }
    
    initSortable() {
        // Setup sortable for each unsur group
        this.unsurData.forEach(unsur => {
            const container = document.getElementById(`sortable-unsur-${unsur.id}`);
            if (container) {
                this.sortableInstances[unsur.id] = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-dragging',
                    handle: '.drag-handle',
                    group: 'jabatan',
                    onEnd: (evt) => {
                        this.handleSortUpdate(evt);
                    }
                });
            }
        });
    }
    
    async handleSortUpdate(evt) {
        const items = Array.from(evt.to.children);
        const orders = items.map((item, index) => ({
            id: item.dataset.id,
            id_unsur: evt.to.dataset.unsurId,
            urutan: index + 1
        }));
        
        try {
            const response = await this.apiClient.post('/jabatan_api.php', {
                action: 'update_order',
                orders: orders
            });
            
            if (response.success) {
                this.uiHelper.showSuccess('Order updated successfully');
                await this.loadData();
            } else {
                this.uiHelper.showError('Failed to update order');
            }
        } catch (error) {
            console.error('Sort update error:', error);
            this.uiHelper.showError('Failed to update order: ' + error.message);
        }
    }
}

// Export for global access
window.SPRINJabatanModule = SPRINJabatanModule;
