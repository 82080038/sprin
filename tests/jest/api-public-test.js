/**
 * API Public Access Test
 * Test public API endpoints (no authentication required)
 */

describe('API Public Access Tests', () => {
    
    test('should access unsur list API without authentication', async () => {
        const response = await fetch('http://localhost/sprint/api/unsur_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_unsur_list'
        });

        expect(response.ok).toBe(true);
        
        const result = await response.json();
        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
        expect(result.data.length).toBeGreaterThan(0);
    });

    test('should access bagian list API without authentication', async () => {
        const response = await fetch('http://localhost/sprint/api/bagian_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_bagian_list'
        });

        expect(response.ok).toBe(true);
        
        const result = await response.json();
        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
        expect(result.data.length).toBeGreaterThan(0);
    });

    test('should access jabatan list API without authentication', async () => {
        const response = await fetch('http://localhost/sprint/api/jabatan_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_jabatan_list'
        });

        expect(response.ok).toBe(true);
        
        const result = await response.json();
        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
        expect(result.data.length).toBeGreaterThan(0);
    });

    test('should access personil list API without authentication', async () => {
        const response = await fetch('http://localhost/sprint/api/personil_list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'per_page=5'
        });

        expect(response.ok).toBe(true);
        
        const result = await response.json();
        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
        expect(result.data.length).toBeGreaterThan(0);
    });

    test('should reject invalid action on unsur API', async () => {
        const response = await fetch('http://localhost/sprint/api/unsur_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=invalid_action'
        });

        expect(response.status).toBe(400);
        
        const result = await response.json();
        expect(result.success).toBe(false);
        expect(result.error_code).toBe(400);
    });

    test('should reject CRUD operations without authentication', async () => {
        const response = await fetch('http://localhost/sprint/api/unsur_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=create_unsur&nama_unsur=Test&urutan=99'
        });

        expect(response.status).toBe(401);
        
        const result = await response.json();
        expect(result.success).toBe(false);
        expect(result.error_code).toBe(401);
    });

    test('should validate API response format', async () => {
        const response = await fetch('http://localhost/sprint/api/unsur_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_unsur_list'
        });

        expect(response.ok).toBe(true);
        
        const result = await response.json();
        
        // Check response structure
        expect(result).toHaveProperty('success');
        expect(result).toHaveProperty('message');
        expect(result).toHaveProperty('timestamp');
        expect(result).toHaveProperty('meta');
        
        if (result.success) {
            expect(result).toHaveProperty('data');
        } else {
            expect(result).toHaveProperty('error_code');
        }
        
        // Check meta structure
        expect(result.meta).toHaveProperty('version');
        expect(result.meta).toHaveProperty('environment');
    });
});

module.exports = {};
