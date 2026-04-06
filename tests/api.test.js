/**
 * API Integration Tests
 * Tests all API endpoints for consistency and functionality
 */

describe('API Integration', () => {
    const baseUrl = global.testConfig.apiBaseUrl;
    
    describe('Unified API Gateway', () => {
        test('should respond to health check', async () => {
            const response = await global.testUtils.checkApiResponse(`${baseUrl}/test_api.php`);
            
            expect(response.status).toBe(200);
            expect(response.ok).toBe(true);
            
            console.log('✅ API health check passed');
        });
        
        test('should return consistent JSON format', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`);
            
            expect(data.success).toBe(true);
            expect(data).toHaveProperty('message');
            expect(data).toHaveProperty('timestamp');
            expect(data).toHaveProperty('data');
            
            console.log('✅ Consistent JSON format verified');
        });
        
        test('should handle invalid requests properly', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=invalid&action=invalid`);
            
            expect(data.success).toBe(false);
            expect(data).toHaveProperty('message');
            expect(data).toHaveProperty('code');
            
            console.log('✅ Invalid requests handled properly');
        });
    });
    
    describe('Unsur API', () => {
        test('should get all unsur data', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=unsur&action=get_all`);
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const unsur = data.data[0];
            expect(unsur).toHaveProperty('id');
            expect(unsur).toHaveProperty('nama_unsur');
            expect(unsur).toHaveProperty('jabatan_count');
            expect(unsur).toHaveProperty('personil_count');
            
            console.log(`✅ Unsur API: ${data.data.length} records`);
        });
        
        test('should get unsur detail', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=unsur&action=get&id=1`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('id');
            expect(data.data).toHaveProperty('nama_unsur');
            expect(data.data.id).toBe(1);
            
            console.log('✅ Unsur detail API working');
        });
        
        test('should get unsur statistics', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=unsur&action=stats`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('total_unsur');
            expect(data.data).toHaveProperty('total_jabatan');
            expect(data.data).toHaveProperty('total_personil');
            
            console.log(`✅ Unsur stats: ${data.data.total_unsur} unsur`);
        });
    });
    
    describe('Bagian API', () => {
        test('should get all bagian data', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=bagian&action=get_all`);
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const bagian = data.data[0];
            expect(bagian).toHaveProperty('id');
            expect(bagian).toHaveProperty('nama_bagian');
            expect(bagian).toHaveProperty('personil_count');
            expect(bagian).toHaveProperty('jabatan_count');
            
            console.log(`✅ Bagian API: ${data.data.length} records`);
        });
        
        test('should get bagian detail', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=bagian&action=get&id=1`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('id');
            expect(data.data).toHaveProperty('nama_bagian');
            
            console.log('✅ Bagian detail API working');
        });
    });
    
    describe('Jabatan API', () => {
        test('should get all jabatan data', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=jabatan&action=get_all`);
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const jabatan = data.data[0];
            expect(jabatan).toHaveProperty('id');
            expect(jabatan).toHaveProperty('nama_jabatan');
            expect(jabatan).toHaveProperty('personil_count');
            expect(jabatan).toHaveProperty('nama_unsur');
            
            console.log(`✅ Jabatan API: ${data.data.length} records`);
        });
        
        test('should get jabatan detail', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=jabatan&action=get&id=1`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('id');
            expect(data.data).toHaveProperty('nama_jabatan');
            
            console.log('✅ Jabatan detail API working');
        });
    });
    
    describe('Personil API', () => {
        test('should get personil data', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=personil&action=get_all&limit=10`);
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const personil = data.data[0];
            expect(personil).toHaveProperty('id');
            expect(personil).toHaveProperty('nama');
            expect(personil).toHaveProperty('nrp');
            expect(personil).toHaveProperty('nama_pangkat');
            
            console.log(`✅ Personil API: ${data.data.length} records`);
        });
        
        test('should get personil statistics', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=personil&action=stats`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('total_personil');
            expect(data.data).toHaveProperty('active_personil');
            
            console.log(`✅ Personil stats: ${data.data.total_personil} total`);
        });
    });
    
    describe('Stats API', () => {
        test('should get dashboard statistics', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`);
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('total_personil');
            expect(data.data).toHaveProperty('total_unsur');
            expect(data.data).toHaveProperty('total_bagian');
            expect(data.data).toHaveProperty('total_jabatan');
            
            console.log(`✅ Dashboard stats: ${data.data.total_personil} personil`);
        });
        
        test('should get unsur statistics', async () => {
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=unsur`);
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            
            console.log(`✅ Unsur stats: ${data.data.length} unsur groups`);
        });
    });
    
    describe('API Performance', () => {
        test('should respond within acceptable time', async () => {
            const startTime = Date.now();
            
            const data = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`);
            
            const responseTime = Date.now() - startTime;
            expect(responseTime).toBeLessThan(2000); // 2 seconds max
            
            console.log(`✅ API response time: ${responseTime}ms`);
        });
        
        test('should handle concurrent requests', async () => {
            const startTime = Date.now();
            
            // Make multiple concurrent requests
            const requests = [
                global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`),
                global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=unsur&action=get_all`),
                global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=bagian&action=get_all`)
            ];
            
            const results = await Promise.all(requests);
            
            const responseTime = Date.now() - startTime;
            expect(responseTime).toBeLessThan(5000); // 5 seconds max for concurrent
            
            // All requests should succeed
            results.forEach(result => {
                expect(result.success).toBe(true);
            });
            
            console.log(`✅ Concurrent requests completed in ${responseTime}ms`);
        });
    });
    
    describe('Data Integrity', () => {
        test('should maintain data consistency across APIs', async () => {
            // Get stats from dashboard API
            const dashboardStats = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`);
            
            // Get individual counts
            const unsurData = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=unsur&action=get_all`);
            const bagianData = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=bagian&action=get_all`);
            const jabatanData = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=jabatan&action=get_all`);
            const personilData = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=personil&action=stats`);
            
            // Verify consistency
            expect(dashboardStats.data.total_unsur).toBe(unsurData.data.length);
            expect(dashboardStats.data.total_bagian).toBe(bagianData.data.length);
            expect(dashboardStats.data.total_jabatan).toBe(jabatanData.data.length);
            expect(dashboardStats.data.total_personil).toBe(personilData.data.total_personil);
            
            console.log('✅ Data consistency verified across APIs');
        });
        
        test('should handle data relationships correctly', async () => {
            // Get jabatan with unsur relationship
            const jabatanData = await global.testUtils.getApiData(`${baseUrl}/unified-api.php?resource=jabatan&action=get_all`);
            
            // Check if relationships are properly loaded
            const jabatanWithUnsur = jabatanData.data.filter(j => j.nama_unsur);
            expect(jabatanWithUnsur.length).toBeGreaterThan(0);
            
            // Check if personil counts are accurate
            const jabatanWithPersonil = jabatanData.data.filter(j => j.personil_count > 0);
            expect(jabatanWithPersonil.length).toBeGreaterThan(0);
            
            console.log(`✅ Data relationships: ${jabatanWithUnsur.length} jabatan with unsur, ${jabatanWithPersonil.length} with personil`);
        });
    });
});
