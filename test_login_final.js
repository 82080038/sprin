const puppeteer = require('puppeteer');

(async () => {
  console.log('Testing Quick Login dengan Puppeteer...');
  
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });
  
  try {
    const page = await browser.newPage();
    
    // Buka halaman login
    await page.goto('http://localhost/sprint/login.php', { waitUntil: 'networkidle0' });
    console.log('Halaman login loaded');
    
    // Tunggu tombol Quick Login
    await page.waitForSelector('.btn-quick-login');
    console.log('Tombol Quick Login ditemukan');
    
    // Klik Quick Login
    await page.click('.btn-quick-login');
    console.log('Quick Login diklik');
    
    // Tunggu 3 detik untuk redirect
    await new Promise(resolve => setTimeout(resolve, 3000));
    
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
      
      // Ambil page title untuk debugging
      const pageTitle = await page.title();
      console.log('Page title:', pageTitle);
    }
    
  } catch (error) {
    console.error('Error:', error.message);
  } finally {
    await browser.close();
  }
})();
