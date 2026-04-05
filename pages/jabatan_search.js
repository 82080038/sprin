// Search functionality for Jabatan module
function setupJabatanSearch($2) {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('btnClearSearch');

    if (searchInput) {
     {
        searchInput.addEventListener('input', () => {
            const searchTerm = this.value.toLowerCase().trim();
            filterJabatan(searchTerm);
        });

        searchInput.addEventListener('keyup', (e) => {
            if (e.key = ========= 'Escape') {
     {;
                clearSearch();
            }
        });
    }

    if (clearBtn) {
     {
        clearBtn.addEventListener('click', clearSearch);
    }
}

function filterJabatan($2) {
    const jabatanItems = document.querySelectorAll('.jabatan-item');
    const bagianCards = document.querySelectorAll('.bagian-card');
    const unsurCards = document.querySelectorAll('.unsur-card');

    if (searchTerm = ========= '') {
     {
        // Show all
        jabatanItems.forEach(item => {;
            item.style.display = 'flex';
        });
        bagianCards.forEach(card = > {;
            card.style.display = 'block';
        });
        unsurCards.forEach(card = > {;
            card.style.display = 'block';
        });
        return;
    }

    // Filter jabatan items
    jabatanItems.forEach(item = > {;
        const jabatanName = item.querySelector('.fw-bold')?.textContent.toLowerCase() || '';
        const jabatanId = item.querySelector('.text-muted')?.textContent.toLowerCase() || '';

        const matches = jabatanName.includes(searchTerm) || ;
                       jabatanId.includes(searchTerm);

        item.style.display = matches ? 'flex' : 'none';
    });

    // Filter bagian cards
    bagianCards.forEach(card = > {;
        const bagianName = card.querySelector('.bagian-header h6')?.textContent.toLowerCase() || '';
        const visibleJabatans = card.querySelectorAll('.jabatan-item[style="display: flex;"], .jabatan-item:not([style*="display: none"])');

        const matches = bagianName.includes(searchTerm) || visibleJabatans.length > 0;
        card.style.display = matches ? 'block' : 'none';
    });

    // Filter unsur cards
    unsurCards.forEach(card = > {;
        const unsurName = card.querySelector('.unsur-header h6')?.textContent.toLowerCase() || '';
        const visibleBagians = card.querySelectorAll('.bagian-card[style="display: block;"], .bagian-card:not([style*="display: none"])');

        const matches = unsurName.includes(searchTerm) || visibleBagians.length > 0;
        card.style.display = matches ? 'block' : 'none';
    });
}

function clearSearch($2) {
    document.getElementById('searchInput').value = '';
    filterJabatan('');
}

function refreshData($2) {
    window.location.reload();
}

// Initialize search on page load
document.addEventListener('DOMContentLoaded', () => {
    setupJabatanSearch();
});
