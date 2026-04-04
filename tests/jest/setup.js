/**
 * Jest Setup File
 * Global setup untuk Jest tests
 */

// Set global test timeout
jest.setTimeout(30000);

// Global test utilities
global.testUtils = {
  // Helper untuk delay
  delay: (ms) => new Promise(resolve => setTimeout(resolve, ms)),
  
  // Helper untuk generate test data
  generateTestData: (type) => {
    const timestamp = Date.now();
    switch (type) {
      case 'unsur':
        return {
          nama_unsur: `Test Unsur ${timestamp}`,
          urutan: 99
        };
      case 'bagian':
        return {
          nama_bagian: `Test Bagian ${timestamp}`,
          id_unsur: 1,
          urutan: 99
        };
      case 'personil':
        return {
          nama: `Test Personil ${timestamp}`,
          nrp: `TEST${timestamp}`,
          id_pangkat: 1,
          id_jabatan: 1,
          id_bagian: 1,
          id_unsur: 1
        };
      default:
        return {};
    }
  },
  
  // Helper untuk cleanup test data
  async cleanupTestData(auth, type, testData) {
    try {
      switch (type) {
        case 'unsur':
          if (testData.id) {
            await auth.apiRequest('/api/unsur_crud.php', {
              action: 'delete',
              id: testData.id
            });
          }
          break;
        case 'bagian':
          if (testData.id) {
            await auth.apiRequest('/api/bagian_crud.php', {
              action: 'delete',
              id: testData.id
            });
          }
          break;
        // Add more cleanup cases as needed
      }
    } catch (error) {
      console.warn('Cleanup warning:', error.message);
    }
  }
};

// Console override untuk cleaner test output
const originalConsoleLog = console.log;
console.log = (...args) => {
  if (process.env.VERBOSE_TESTS === 'true') {
    originalConsoleLog(...args);
  }
};
