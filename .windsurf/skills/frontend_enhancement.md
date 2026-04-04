---
description: Skill untuk frontend enhancement dan UI consistency
---

# Frontend Enhancement Skill

## Overview

Skill ini digunakan untuk meningkatkan pengalaman pengguna (UX) dan konsistensi UI di aplikasi SPRIN v1.2.0.

## Modal Consistency System

### Standard Modal Sizes
```css
/* Override untuk modal-sm */
.modal-dialog.modal-sm {
    max-width: 300px !important;
    margin: 1.75rem auto !important;
}

/* Override untuk modal-md */
.modal-dialog.modal-md {
    max-width: 500px !important;
    margin: 1.75rem auto !important;
}

/* Override untuk modal-lg */
.modal-dialog.modal-lg {
    max-width: 800px !important;
    margin: 1.75rem auto !important;
}
```

### Modal Size Guidelines
| Modal Type | Size | Use Case | Example |
|------------|------|----------|---------|
| Simple Form | `modal-sm` | Password, add jabatan | 300px |
| Medium Form | `modal-md` | Bagian, unsur, user | 500px |
| Complex View | `modal-lg` | Personil detail, calendar | 800px |

## Auto-Generation Patterns

### 1. Kode Unsur Generation
```javascript
function generateKodeUnsur(namaUnsur) {
    return namaUnsur
        .toUpperCase()
        .replace(/[^A-Z0-9_]/g, '_')
        .replace(/_+/g, '_')
        .trim();
}
```

### 2. Bagian Type Assignment
```javascript
function getBagianTypeByUnsur(unsurId, unsurName) {
    const unsurTypeMapping = {
        '1': 'PIMPINAN',      // UNSUR PIMPINAN
        '8': 'BAG',           // PEMBANTU PIMPINAN
        '3': 'SAT',           // UNSUR PELAKSANA TUGAS POKOK
        '4': 'POLSEK',        // UNSUR PELAKSANA KEWILAYAHAN
        '5': 'SIUM',          // UNSUR PENDUKUNG
        '6': 'BKO'            // UNSUR LAINNYA
    };
    
    return unsurTypeMapping[unsurId] || 
           unsurTypeMapping[unsurName] || 
           'BAG/SAT/SIE';
}
```

### 3. Auto-Ordering System
```javascript
function getNextOrder(currentOrders) {
    return Math.max(...currentOrders, 0) + 1;
}
```

## Card-Based Layout System

### Structure Template
```html
<div class="unsur-card" data-unsur-id="{id}">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-{icon}"></i> {nama_unsur}
            <span class="badge bg-primary float-end">{count}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="bagian-section" data-bagian-id="{id}">
            <div class="bagian-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{nama_bagian}</h6>
                <button class="btn btn-sm btn-outline-primary add-jabatan-btn">
                    <i class="fas fa-plus"></i> Jabatan
                </button>
            </div>
            <div class="jabatan-list">
                <!-- Jabatan items here -->
            </div>
        </div>
    </div>
</div>
```

## Drag & Drop Implementation

### Sortable.js Integration
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const unsurList = document.getElementById('unsur-list');
    
    if (unsurList) {
        new Sortable(unsurList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                updateUnsurOrder(evt.oldIndex, evt.newIndex);
            }
        });
    }
});

function updateUnsurOrder(oldIndex, newIndex) {
    const formData = new FormData();
    formData.append('action', 'reorder');
    formData.append('old_index', oldIndex);
    formData.append('new_index', newIndex);
    
    fetch('/api/unsur_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Urutan berhasil diperbarui');
        } else {
            showAlert('error', data.message);
        }
    });
}
```

## Responsive Design Patterns

### Mobile-First Approach
```css
/* Mobile Styles (default) */
.card-based-layout {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Tablet Styles */
@media (min-width: 768px) {
    .card-based-layout {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop Styles */
@media (min-width: 1024px) {
    .card-based-layout {
        grid-template-columns: repeat(3, 1fr);
    }
}
```

## Alert System

### Consistent Notification
```javascript
function showAlert(type, message, duration = 5000) {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after duration
    setTimeout(() => {
        alertDiv.remove();
    }, duration);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1050';
    document.body.appendChild(container);
    return container;
}
```

## Form Validation Patterns

### Client-Side Validation
```javascript
function validateForm(formElement) {
    const errors = [];
    const requiredFields = formElement.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            errors.push(`${field.name} harus diisi`);
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Custom validation
    const nrkField = formElement.querySelector('[name="nrk"]');
    if (nrkField && nrkField.value.length < 3) {
        errors.push('NRK minimal 3 karakter');
        nrkField.classList.add('is-invalid');
    }
    
    return errors;
}
```

## Export Functionality

### Multi-Format Export
```javascript
function exportData(format, filters = {}) {
    const params = new URLSearchParams({
        format: format,
        ...filters
    });
    
    fetch(`/api/export_personil.php?${params}`)
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Export failed');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `personil_export_${format}_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            showAlert('error', 'Export gagal: ' + error.message);
        });
}
```

## Loading States

### Visual Feedback
```javascript
function showLoading(element, text = 'Loading...') {
    element.disabled = true;
    element.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status"></span>
        ${text}
    `;
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}
```

## Usage Examples

### 1. Enhanced Modal Implementation
```php
// In bagian.php
$modalSize = 'modal-md'; // Based on form complexity
echo "<div class='modal fade' id='bagianModal' tabindex='-1'>
        <div class='modal-dialog modal-$modalSize'>
            <div class='modal-content'>
                <!-- Modal content -->
            </div>
        </div>
      </div>";
```

### 2. Auto-Type Assignment
```php
// In bagian.php (backend)
$unsurId = $_POST['id_unsur'] ?? '';
$bagianType = getBagianTypeByUnsur($unsurId);
```

### 3. Card-Based Display
```php
// In jabatan.php
foreach ($unsurList as $unsur) {
    echo "<div class='col-md-6 col-lg-4 mb-3'>
            <div class='card unsur-card'>
                <!-- Card content with bagian sections -->
            </div>
          </div>";
}
```

## Best Practices

1. **Consistent Modal Sizes**: Use sm/md/lg based on complexity
2. **Auto-Generation**: Reduce manual input with smart defaults
3. **Responsive Design**: Mobile-first approach with breakpoints
4. **Visual Feedback**: Loading states and alerts for all actions
5. **Accessibility**: Proper ARIA labels and keyboard navigation
6. **Performance**: Minimize DOM manipulations and use efficient selectors

---

**Last Updated**: 2026-04-01  
**Version**: SPRIN v1.2.0  
**Focus**: UI Consistency & User Experience Enhancement
