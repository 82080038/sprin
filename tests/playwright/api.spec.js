const { test, expect } = require('@playwright/test');

test.describe('SPRIN API Tests', () => {
  const baseURL = 'http://localhost/sprint/api';

  test('personil_api.php - should return personil data', async ({ request }) => {
    const response = await request.get(`${baseURL}/personil_api.php`);
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBeTruthy();
    
    if (data.length > 0) {
      expect(data[0]).toHaveProperty('nrp');
      expect(data[0]).toHaveProperty('nama');
    }
  });

  test('personil_api.php - should search personil', async ({ request }) => {
    const response = await request.get(`${baseURL}/personil_api.php?search=admin`);
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBeTruthy();
  });

  test('personil_api.php - should add new personil', async ({ request }) => {
    const newPersonil = {
      nrp: '99999999',
      nama: 'API Test Personil',
      id_pangkat: 1,
      id_jabatan: 1,
      id_bagian: 1,
      id_unsur: 1,
      status_ket: 'Aktif',
      jenis_kelamin: 'L'
    };

    const response = await request.post(`${baseURL}/personil_api.php`, {
      data: newPersonil
    });
    
    expect(response.status()).toBe(200);
    
    const result = await response.json();
    expect(result).toHaveProperty('status');
    expect(result.status).toBe('success');
  });

  test('personil_api.php - should update personil', async ({ request }) => {
    const updateData = {
      nrp: '99999999',
      nama: 'API Test Personil Updated',
      id_pangkat: 2,
      id_jabatan: 2,
      id_bagian: 2,
      id_unsur: 2,
      status_ket: 'Aktif',
      jenis_kelamin: 'L'
    };

    const response = await request.put(`${baseURL}/personil_api.php`, {
      data: updateData
    });
    
    expect(response.status()).toBe(200);
    
    const result = await response.json();
    expect(result).toHaveProperty('status');
    expect(result.status).toBe('success');
  });

  test('personil_api.php - should delete personil', async ({ request }) => {
    const response = await request.delete(`${baseURL}/personil_api.php?nrp=99999999`);
    expect(response.status()).toBe(200);
    
    const result = await response.json();
    expect(result).toHaveProperty('status');
    expect(result.status).toBe('success');
  });

  test('calendar_api.php - should get calendar data', async ({ request }) => {
    const response = await request.get(`${baseURL}/calendar_api.php`);
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(data).toHaveProperty('events');
    expect(Array.isArray(data.events)).toBeTruthy();
  });

  test('calendar_api.php - should add calendar event', async ({ request }) => {
    const newEvent = {
      title: 'API Test Event',
      start: '2024-01-01T10:00:00',
      end: '2024-01-01T12:00:00',
      description: 'Test event from API'
    };

    const response = await request.post(`${baseURL}/calendar_api.php`, {
      data: newEvent
    });
    
    expect(response.status()).toBe(200);
    
    const result = await response.json();
    expect(result).toHaveProperty('status');
  });

  test('unsur_stats.php - should return unsur statistics', async ({ request }) => {
    const response = await request.get(`${baseURL}/unsur_stats.php`);
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(data).toHaveProperty('unsur_data');
    expect(data).toHaveProperty('total_personil');
  });

  test('search_personil.php - should search personil', async ({ request }) => {
    const response = await request.get(`${baseURL}/search_personil.php?q=admin`);
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBeTruthy();
  });

  test('API should handle invalid requests gracefully', async ({ request }) => {
    const response = await request.get(`${baseURL}/personil_api.php?invalid=param`);
    expect(response.status()).toBeLessThan(500);
  });

  test('API should validate required fields', async ({ request }) => {
    const invalidData = {
      nama: 'Test without required fields'
    };

    const response = await request.post(`${baseURL}/personil_api.php`, {
      data: invalidData
    });
    
    expect(response.status()).toBe(200);
    
    const result = await response.json();
    expect(result.status).toBe('error');
  });
});
