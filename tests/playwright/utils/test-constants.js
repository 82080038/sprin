/**
 * Test Constants for SPRIN Application
 * Centralized configuration for all test files
 */

// Test Credentials
const TEST_CREDENTIALS = {
  VALID: {
    username: 'bagops',
    password: 'admin123'
  },
  INVALID: {
    username: 'invalid',
    password: 'invalid'
  }
};

// Test Data
const TEST_DATA = {
  PERSONIL: {
    VALID: {
      nrp: '99999999',
      nama: 'Test Personil',
      id_pangkat: 1,
      id_jabatan: 1,
      id_bagian: 1,
      id_unsur: 1,
      status_ket: 'Aktif',
      jenis_kelamin: 'L'
    },
    UPDATED: {
      nrp: '99999999',
      nama: 'Test Personil Updated',
      id_pangkat: 2,
      id_jabatan: 2,
      id_bagian: 2,
      id_unsur: 2,
      status_ket: 'Aktif',
      jenis_kelamin: 'L'
    }
  },
  BAGIAN: {
    VALID: {
      nama_bagian: 'Test Bagian API'
    },
    UPDATED: {
      nama_bagian: 'Test Bagian Edited'
    }
  },
  UNSUR: {
    VALID: {
      nama_unsur: 'Test Unsur API'
    },
    UPDATED: {
      nama_unsur: 'Test Unsur Edited'
    }
  },
  CALENDAR: {
    VALID: {
      title: 'Test Event API',
      start: '2024-01-15T10:00:00',
      end: '2024-01-15T12:00:00',
      description: 'Test event from API'
    }
  }
};

// Timeouts (in milliseconds)
const TIMEOUTS = {
  SHORT: 1000,
  MEDIUM: 2000,
  LONG: 5000,
  EXTRA_LONG: 10000
};

// Selectors
const SELECTORS = {
  LOGIN: {
    USERNAME_INPUT: 'input[name="username"]',
    PASSWORD_INPUT: 'input[name="password"]',
    SUBMIT_BUTTON: 'button[type="submit"]',
    QUICK_LOGIN_BUTTON: 'button:has-text("Quick Login")',
    ERROR_ALERT: '.alert-danger, .error, .danger',
    SUCCESS_ALERT: '.alert-success, .success'
  },
  DASHBOARD: {
    TITLE: 'h1, .dashboard-title',
    STATS_CARDS: '.card, .stat-card, .info-box',
    NAVIGATION: 'nav, .navbar, .sidebar, .menu',
    CONTENT_AREA: '#main-content, .page-content',
    LOGOUT_LINK: 'a:has-text("Logout"), a:has-text("Keluar")'
  },
  PERSONIL: {
    TABLE: 'table',
    SEARCH_INPUT: 'input[placeholder*="Cari"], input[placeholder*="Search"], #search',
    ADD_BUTTON: 'button:has-text("Tambah"), button:has-text("Add"), .btn-add',
    EDIT_BUTTON: 'button:has-text("Edit"), .btn-edit, .edit-btn',
    DELETE_BUTTON: 'button:has-text("Hapus"), button:has-text("Delete"), .btn-delete',
    EXPORT_BUTTON: 'button:has-text("Export"), button:has-text("PDF"), button:has-text("Excel")',
    MODAL: '.modal, .dialog, .popup',
    NAME_INPUT: 'input[name="nama"]',
    NRP_INPUT: 'input[name="nrp"]',
    SAVE_BUTTON: 'button:has-text("Simpan"), button:has-text("Save")'
  },
  COMMON: {
    LOADING_SPINNER: '.loading-spinner, .spinner-border',
    ERROR_MESSAGE: '.error, .invalid-feedback, .text-danger',
    SUCCESS_MESSAGE: '.alert-success, .success, .toast-success'
  }
};

// Expected text patterns
const TEXT_PATTERNS = {
  LOGIN_TITLE: /Login|Masuk|Sistem/,
  DASHBOARD_TITLE: /Dashboard|Sistem Manajemen/,
  PERSONIL_TITLE: /Personil|Data Personil/,
  BAGIAN_TITLE: /Bagian|Manajemen Bagian/,
  UNSUR_TITLE: /Unsur|Manajemen Unsur/,
  CALENDAR_TITLE: /Kalender|Calendar/,
  SUCCESS_MESSAGE: /berhasil|sukses|success/,
  ERROR_MESSAGE: /error|gagal|salah/,
  LOGIN_REQUIRED: /login|authentication|unauthorized/
};

// Viewport sizes for responsive testing
const VIEWPORTS = {
  DESKTOP: { width: 1920, height: 1080 },
  LAPTOP: { width: 1366, height: 768 },
  TABLET: { width: 768, height: 1024 },
  MOBILE: { width: 375, height: 667 }
};

module.exports = {
  TEST_CREDENTIALS,
  TEST_DATA,
  TIMEOUTS,
  SELECTORS,
  TEXT_PATTERNS,
  VIEWPORTS
};
