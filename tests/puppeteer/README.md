# SPRIN Puppeteer Testing Suite

Comprehensive end-to-end testing suite for POLRES Samosir Management System (SPRIN) menggunakan Puppeteer.

## Prerequisites

- Node.js 16+ terinstall
- XAMPP running dengan Apache dan MySQL
- Aplikasi SPRIN sudah setup dan running
- Dependencies terinstall: `npm install`

## Installation

```bash
cd /opt/lampp/htdocs/sprint/tests/puppeteer
npm install
```

## Running Tests

### Run All Tests
```bash
npm test
# atau
node run-all-tests.js
```

### Run in Headless Mode
```bash
npm run test:headless
```

### Run Specific Test Categories
```bash
# API tests only
node tests/api.test.js

# Login tests only  
node tests/login.test.js
```

## Test Categories

### 1. Login & Authentication Tests (6 tests)
- ✅ Login page loads
- ✅ Valid credentials login
- ✅ Invalid credentials handling
- ✅ Quick login button
- ✅ Landing page display
- ✅ Logout functionality

### 2. Dashboard & Navigation Tests (8 tests)
- ✅ Dashboard loads after login
- ✅ Statistics load correctly
- ✅ Navigation menu works
- ✅ Navigate to Personil page
- ✅ Navigate to Bagian page
- ✅ Navigate to Unsur page
- ✅ Navigate to Jabatan page
- ✅ Navigate to Calendar page

### 3. Personil Management Tests (8 tests)
- ✅ Personil list loads
- ✅ Add personil form opens
- ✅ Search functionality
- ✅ Filter by bagian
- ✅ Export buttons exist
- ✅ API: Get personil list
- ✅ API: Get statistics
- ✅ API: Get unsur statistics

### 4. Organization Structure Tests (6 tests)
- ✅ Bagian page with data
- ✅ Unsur page with data
- ✅ Jabatan page loads
- ✅ Organization structure visibility
- ✅ API: Get bagian list
- ✅ API: Unsur stats detail

### 5. Calendar & Schedule Tests (5 tests)
- ✅ Calendar page loads
- ✅ API: Calendar stats
- ✅ API: Calendar events
- ✅ Schedule page elements
- ✅ Google Calendar integration check

### 6. API Endpoint Tests (10 tests)
- ✅ Personil CRUD endpoint
- ✅ Personil detail endpoint
- ✅ Calendar endpoint
- ✅ Advanced search endpoint
- ✅ Jabatan CRUD endpoint
- ✅ Search personil endpoint
- ✅ Pagination endpoint
- ✅ Simple API endpoint
- ✅ Export API
- ✅ Response format validation

**Total: 43+ comprehensive tests**

## Configuration

Edit `config.js` untuk mengubah:
- Base URL aplikasi
- Test credentials
- Browser settings
- Timeouts
- Selectors

## Output

### Screenshots
Screenshots disimpan di: `tests/puppeteer/results/screenshots/`

### Reports
- JSON: `tests/puppeteer/results/test-report.json`
- HTML: `tests/puppeteer/results/test-report.html`

## Test Report Example

```
╔════════════════════════════════════════════════════════╗
║                   TEST SUMMARY                         ║
╠════════════════════════════════════════════════════════╣
║  Total Tests:  43                                      ║
║  Passed:        40                                     ║
║  Failed:        3                                      ║
║  Pass Rate:     93.02%                                 ║
║  Duration:      125000ms                               ║
╚════════════════════════════════════════════════════════╝
```

## Troubleshooting

### Chrome/Chromium tidak ditemukan
```bash
# Install Chromium
sudo apt-get install chromium-browser

# Atau set executable path di config.js
executablePath: '/usr/bin/chromium-browser'
```

### Permission denied saat screenshot
```bash
sudo chmod -R 777 tests/puppeteer/results
```

### XAMPP tidak running
```bash
sudo /opt/lampp/lampp start
```

### Test timeout
Edit `config.js` dan naikkan timeout values:
```javascript
timeouts: {
    navigation: 60000,  // Naikkan dari 30000
    element: 20000      // Naikkan dari 10000
}
```

## Adding New Tests

1. Buat file test baru di `tests/` folder
2. Export test functions
3. Import dan tambahkan ke `run-all-tests.js`
4. Ikuti pattern test yang sudah ada

Contoh:
```javascript
// tests/my-feature.test.js
function myFeatureTests(runner) {
    return {
        async testMyFeature() {
            await runner.test('My Feature', async (page) => {
                await page.goto(config.baseUrl + '/my-page.php');
                await runner.waitForSelector('.my-element');
                await runner.screenshot('my_feature');
            });
        }
    };
}

module.exports = myFeatureTests;
```

## Continuous Integration

Untuk CI/CD (GitHub Actions, GitLab CI, dll):

```yaml
# .github/workflows/test.yml
name: E2E Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: '16'
      - name: Install dependencies
        run: npm install
      - name: Run tests
        run: npm run test:headless
```

## License

MIT License - SPRIN Development Team
