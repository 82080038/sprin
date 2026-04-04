/**
 * API Authentication Test
 * Test API endpoints with proper authentication
 */

const TestAuth = require('../utils/test-auth');

describe('API Authentication Tests', () => {
    let auth;

    beforeAll(async () => {
        auth = new TestAuth();
        const loginSuccess = await auth.login();
        if (!loginSuccess) {
            throw new Error('Failed to establish test session');
        }
    });

    afterAll(async () => {
        await auth.logout();
    });

    test('should access unsur CRUD API with authentication', async () => {
        const result = await auth.apiRequest('/api/unsur_crud.php', {
            action: 'list'
        });

        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
    });

    test('should access bagian CRUD API with authentication', async () => {
        const result = await auth.apiRequest('/api/bagian_crud.php', {
            action: 'list'
        });

        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
    });

    test('should access jabatan CRUD API with authentication', async () => {
        const result = await auth.apiRequest('/api/jabatan_crud.php', {
            action: 'list'
        });

        expect(result.success).toBe(true);
        expect(result.data).toBeDefined();
        expect(Array.isArray(result.data)).toBe(true);
    });

    test('should create new unsur with authentication', async () => {
        const testData = {
            action: 'create',
            nama_unsur: 'Test Unsur API Auth',
            urutan: 99
        };

        const result = await auth.apiRequest('/api/unsur_crud.php', testData);

        expect(result.success).toBe(true);
        
        // Cleanup - delete the test record
        if (result.data && result.data.id) {
            await auth.apiRequest('/api/unsur_crud.php', {
                action: 'delete',
                id: result.data.id
            });
        }
    });

    test('should reject invalid action without proper authentication', async () => {
        // Test dengan invalid session
        const invalidAuth = new TestAuth();
        
        try {
            await invalidAuth.apiRequest('/api/unsur_crud.php', {
                action: 'list'
            });
            fail('Should have thrown error for invalid session');
        } catch (error) {
            expect(error.message).toContain('No active session');
        }
    });
});

module.exports = {};
