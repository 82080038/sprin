/**
 * SPRIN Bagian Module
 * Handles bagian management functionality
 */

class SPRINBagianModule extends SPRINBaseModule {
    constructor(core) {
        super(core);
        this.bagianData = [];
        this.unsurData = [];
        this.init();
    }
    
    init() {
        console.log('🔧 Initializing Bagian Module...');
        this.bindEvents();
        this.loadData();
        this.loadUnsurData();
    }
    
    destroy() {
        console.log('🗑️ Bagian Module destroyed');
    }
    
    bindEvents() {
        // Bind form submission
        const form = document.getElementById('bagianForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        }
        
        // Bind add button
        const addBtn = document.querySelector('[data-action="add-bagian"]');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showAddModal());
        }
        
        // Bind unsur change handler
        const unsurSelect = document.getElementById('id_unsur');
        if (unsurSelect) {
            unsurSelect.addEventListener('change', (e) => this.handleUnsurChange(e));
        }
    }
    
    async loadData() {
        try {
            this.uiHelper.showLoading('Loading bagian data...');
            
            const response = await this.apiClient.get('/bagian_api.php?action=get_all_bagian');
            
            if (response.success) {
                this.bagianData = response.data;
                this.renderTable();
                this.updateStatistics();
            } else {
                this.uiHelper.showError('Failed to load bagian data');
            }
        } catch (error) {
            console.error('Load bagian error:', error);
            this.uiHelper.showError('Failed to load bagian data: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    async loadUnsurData() {
        try {
            const response = await this.apiClient.get('/unsur_api.php?action=get_all_unsur');
            
            if (response.success) {
                this.unsurData = response.data;
                this.populateUnsurSelect();
            }
        } catch (error) {
            console.error('Load unsur error:', error);
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
    
    renderTable() {
        const tbody = document.querySelector('#bagianTable tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        this.bagianData.forEach((bagian, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${bagian.nama_bagian}</td>
                <td>${bagian.kode_bagian}</td>
                <td>${bagian.nama_unsur || '-'}</td>
                <td>${bagian.urutan}</td>
                <td>
                    <span class="badge badge-${bagian.is_active ? 'success' : 'secondary'}">
                        ${bagian.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info btn-sm" onclick="window.SPRINT_CORE.modules.bagian.editBagian(${bagian.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="window.SPRINT_CORE.modules.bagian.deleteBagian(${bagian.id}, '${bagian.nama_bagian}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    updateStatistics() {
        const totalBagian = this.bagianData.length;
        const activeBagian = this.bagianData.filter(b => b.is_active).length;
        
        const totalEl = document.getElementById('totalBagian');
        const activeEl = document.getElementById('activeBagian');
        
        if (totalEl) totalEl.textContent = totalBagian;
        if (activeEl) activeEl.textContent = activeBagian;
    }
    
    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const action = formData.get('action');
        const data = Object.fromEntries(formData);
        
        try {
            this.uiHelper.showLoading('Saving...');
            
            let response;
            switch (action) {
                case 'create_bagian':
                    response = await this.apiClient.post('/bagian_api.php', data);
                    break;
                case 'update_bagian':
                    response = await this.apiClient.post('/bagian_api.php', data);
                    break;
                default:
                    throw new Error('Invalid action');
            }
            
            if (response.success) {
                this.uiHelper.showSuccess(response.message);
                await this.loadData();
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('bagianModal'));
                if (modal) modal.hide();
                
                // Reset form
                form.reset();
                document.getElementById('modalTitle').textContent = 'Tambah Bagian';
                document.getElementById('formAction').value = 'create_bagian';
                document.getElementById('formId').value = '';
            } else {
                this.uiHelper.showError(response.message);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.uiHelper.showError('Failed to save bagian: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    showAddModal() {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('bagianModal'));
        document.getElementById('modalTitle').textContent = 'Tambah Bagian';
        document.getElementById('formAction').value = 'create_bagian';
        document.getElementById('formId').value = '';
        
        // Reset form
        const form = document.getElementById('bagianForm');
        form.reset();
        
        modal.show();
    }
    
    async editBagian(id) {
        try {
            this.uiHelper.showLoading('Loading bagian data...');
            
            const response = await this.apiClient.post('/bagian_api.php', {
                action: 'get_bagian_detail',
                id: id
            });
            
            if (response.success && response.data) {
                const bagian = response.data;
                
                // Fill form
                document.getElementById('modalTitle').textContent = 'Edit Bagian';
                document.getElementById('formAction').value = 'update_bagian';
                document.getElementById('formId').value = bagian.id;
                document.getElementById('nama_bagian').value = bagian.nama_bagian;
                document.getElementById('kode_bagian').value = bagian.kode_bagian;
                document.getElementById('deskripsi').value = bagian.deskripsi || '';
                document.getElementById('id_unsur').value = bagian.id_unsur || '';
                document.getElementById('urutan').value = bagian.urutan;
                
                // Show modal
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('bagianModal'));
                modal.show();
            } else {
                this.uiHelper.showError('Bagian not found');
            }
        } catch (error) {
            console.error('Edit bagian error:', error);
            this.uiHelper.showError('Failed to load bagian: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    deleteBagian(id, nama) {
        this.uiHelper.confirm(
            `Apakah Anda yakin ingin menghapus bagian "${nama}"?`,
            async () => {
                try {
                    this.uiHelper.showLoading('Deleting...');
                    
                    const response = await this.apiClient.post('/bagian_api.php', {
                        action: 'delete_bagian',
                        id: id
                    });
                    
                    if (response.success) {
                        this.uiHelper.showSuccess(response.message);
                        await this.loadData();
                    } else {
                        this.uiHelper.showError(response.message);
                    }
                } catch (error) {
                    console.error('Delete bagian error:', error);
                    this.uiHelper.showError('Failed to delete bagian: ' + error.message);
                } finally {
                    this.uiHelper.hideLoading();
                }
            }
        );
    }
    
    handleUnsurChange(e) {
        const unsurId = e.target.value;
        const unsur = this.unsurData.find(u => u.id == unsurId);
        
        if (unsur) {
            // Auto-set type based on unsur
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                typeSelect.value = this.getBagianTypeByUnsur(unsurId, unsur.nama_unsur);
            }
        }
    }
    
    getBagianTypeByUnsur(unsurId, unsurName) {
        // Auto-determine bagian type based on unsur
        if (unsurName.includes('PELAKSANA')) {
            return 'SAT';
        } else if (unsurName.includes('PEMBANTU')) {
            return 'BAG';
        } else if (unsurName.includes('PIMPINAN')) {
            return 'BAG';
        }
        return 'BAG';
    }
}

// Export for global access
window.SPRINBagianModule = SPRINBagianModule;
