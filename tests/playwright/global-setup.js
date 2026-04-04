/**
 * Playwright Global Setup
 * Setup tasks sebelum semua Playwright tests
 */

async function globalSetup(config) {
  // Pastikan XAMPP running
  const http = require('http');
  
  // Check jika server accessible
  await new Promise((resolve, reject) => {
    const req = http.get('http://localhost/sprint', (res) => {
      resolve();
    });
    
    req.on('error', (err) => {
      console.error('❌ Server not accessible:', err.message);
      reject(err);
    });
    
    req.setTimeout(5000, () => {
      req.destroy();
      reject(new Error('Server connection timeout'));
    });
  });
  
  }

module.exports = globalSetup;
