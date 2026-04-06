/**
 * SPRIN Unsur Module
 * Handles unsur management functionality
 */

class SPRINUnsurModule extends SPRINBaseModule {
    constructor(core) {
        super(core);
        this.unsurData = [];
        this.sortableInstance = null;
        this.init();
    }
    
    init() {
        console.log('🔧 Initializing Unsur Module...');
        this.bindEvents();
        this.loadData();
        this.setupSortable();
    }
    
    destroy() {
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }
        console.log('🗑️ Unsur Module destroyed');
    }
    
    bindEvents() {
        // Bind form submission
        const form = document.getElementById('unsurForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        }
        
        // Bind add button
        const addBtn = document.querySelector('[data-action="add-unsur"]');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showAddModal());
        }
    }
    
    async loadData() {
        try {
            this.uiHelper.showLoading('Loading unsur data...');
            
            const response = await this.apiClient.get('/unsur_api.php?action=get_all_unsur');
            
            if (response.success) {
                this.unsurData = response.data;
                this.renderTable();
                this.updateStatistics();
            } else {
                this.uiHelper.showError('Failed to load unsur data');
            }
        } catch (error) {
            console.error('Load unsur error:', error);
            this.uiHelper.showError('Failed to load unsur data: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    renderTable() {
        const tbody = document.querySelector('#unsurTable tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        this.unsurData.forEach((unsur, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${unsur.nama_unsur}</td>
                <td>${unsur.kode_unsur}</td>
                <td>${unsur.tingkat}</td>
                <td>${unsur.urutan}</td>
                <td>
                    <span class="badge badge-${unsur.is_active ? 'success' : 'secondary'}">
                        ${unsur.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info btn-sm" onclick="window.SPRINT_CORE.modules.unsur.editUnsur(${unsur.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="window.SPRINT_CORE.modules.unsur.deleteUnsur(${unsur.id}, '${unsur.nama_unsur}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    updateStatistics() {
        const totalUnsur = this.unsurData.length;
        const activeUnsur = this.unsurData.filter(u => u.is_active).length;
        
        const totalEl = document.getElementById('totalUnsur');
        const activeEl = document.getElementById('activeUnsur');
        
        if (totalEl) totalEl.textContent = totalUnsur;
        if (activeEl) activeEl.textContent = activeUnsur;
    }
    
    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const action = formData.get('action');
        const data = Object.fromEntries(formData);
        
        try {
            this.uiHelper.showLoading('Saving...');
            
            let response;
            switch (action) {
                case 'create_unsur':
                    response = await this.apiClient.post('/unsur_api.php', data);
                    break;
                case 'update_unsur':
                    response = await this.apiClient.post('/unsur_api.php', data);
                    break;
                default:
                    throw new Error('Invalid action');
            }
            
            if (response.success) {
                this.uiHelper.showSuccess(response.message);
                await this.loadData();
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('unsurModal'));
                if (modal) modal.hide();
                
                // Reset form
                form.reset();
                document.getElementById('modalTitle').textContent = 'Tambah Unsur';
                document.getElementById('formAction').value = 'create_unsur';
                document.getElementById('formId').value = '';
            } else {
                this.uiHelper.showError(response.message);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.uiHelper.showError('Failed to save unsur: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    showAddModal() {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('unsurModal'));
        document.getElementById('modalTitle').textContent = 'Tambah Unsur';
        document.getElementById('formAction').value = 'create_unsur';
        document.getElementById('formId').value = '';
        
        // Reset form
        const form = document.getElementById('unsurForm');
        form.reset();
        
        modal.show();
    }
    
    async editUnsur(id) {
        try {
            this.uiHelper.showLoading('Loading unsur data...');
            
            const response = await this.apiClient.post('/unsur_api.php', {
                action: 'get_unsur_detail',
                id: id
            });
            
            if (response.success && response.data) {
                const unsur = response.data;
                
                // Fill form
                document.getElementById('modalTitle').textContent = 'Edit Unsur';
                document.getElementById('formAction').value = 'update_unsur';
                document.getElementById('formId').value = unsur.id;
                document.getElementById('nama_unsur').value = unsur.nama_unsur;
                document.getElementById('kode_unsur').value = unsur.kode_unsur;
                document.getElementById('deskripsi').value = unsur.deskripsi || '';
                document.getElementById('tingkat').value = unsur.tingkat;
                document.getElementById('urutan').value = unsur.urutan;
                
                // Show modal
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('unsurModal'));
                modal.show();
            } else {
                this.uiHelper.showError('Unsur not found');
            }
        } catch (error) {
            console.error('Edit unsur error:', error);
            this.uiHelper.showError('Failed to load unsur: ' + error.message);
        } finally {
            this.uiHelper.hideLoading();
        }
    }
    
    deleteUnsur(id, nama) {
        this.uiHelper.confirm(
            `Apakah Anda yakin ingin menghapus unsur "${nama}"?`,
            async () => {
                try {
                    this.uiHelper.showLoading('Deleting...');
                    
                    const response = await this.apiClient.post('/unsur_api.php', {
                        action: 'delete_unsur',
                        id: id
                    });
                    
                    if (response.success) {
                        this.uiHelper.showSuccess(response.message);
                        await this.loadData();
                    } else {
                        this.uiHelper.showError(response.message);
                    }
                } catch (error) {
                    console.error('Delete unsur error:', error);
                    this.uiHelper.showError('Failed to delete unsur: ' + error.message);
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
        const container = document.getElementById('sortable-container');
        if (!container) return;
        
        this.sortableInstance = new Sortable(container, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-dragging',
            handle: '.drag-handle',
            onEnd: (evt) => {
                this.handleSortUpdate(evt);
            }
        });
    }
    
    async handleSortUpdate(evt) {
        const items = Array.from(evt.to.children);
        const orders = items.map((item, index) => ({
            id: item.dataset.id,
            urutan: index + 1
        }));
        
        try {
            const response = await this.apiClient.post('/unsur_api.php', {
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
window.SPRINUnsurModule = SPRINUnsurModule;
