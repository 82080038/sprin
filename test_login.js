const puppeteer = require('puppeteer');

(async () => {
  console.log('Testing Quick Login dengan Puppeteer...');
  
  const browser = await puppeteer.launch({
    headless: false,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const page = await browser.newPage();
    
    // Buka halaman login
    await page.goto('http://localhost/sprint/login.php');
    console.log('Halaman login loaded');
    
    // Tunggu tombol Quick Login
    await page.waitForSelector('.btn-quick-login');
    console.log('Tombol Quick Login ditemukan');
    
    // Klik Quick Login
    await page.click('.btn-quick-login');
    console.log('Quick Login diklik');
    
    // Tunggu redirect ke main.php (max 5 detik)
    await page.waitForNavigation({ 
      timeout: 5000,
      waitUntil: 'networkidle0'
    }).catch(() => {
      console.log('Redirect gagal, cek error...');
    });
    
    // Cek URL saat ini
    const currentUrl = page.url();
    console.log('Current URL:', currentUrl);
    
    if (currentUrl.includes('main.php')) {
      console.log('✅ LOGIN BERHASIL!');
    } else {
      console.log('❌ LOGIN GAGAL');
      
      // Cek apakah ada error message
      const errorElement = await page.$('.alert-danger');
      if (errorElement) {
        const errorText = await errorElement.textContent();
        console.log('Error message:', errorText.trim());
      }
      
      // Screenshot untuk debugging
      await page.screenshot({ path: 'login_debug.png' });
      console.log('Screenshot disimpan: login_debug.png');
    }
    
  } catch (error) {
    console.error('Error:', error.message);
  } finally {
    await browser.close();
  }
})();
