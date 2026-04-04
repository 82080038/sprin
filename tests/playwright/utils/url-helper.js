/**
 * URL Helper for SPRIN Testing
 * Standardizes URL structure across all tests
 */

class URLHelper {
  constructor(baseURL = 'http://localhost') {
    this.baseURL = baseURL;
    this.appPath = '/sprint';
  }

  /**
   * Get full URL for application pages
   */
  getAppUrl(path = '') {
    return `${this.baseURL}${this.appPath}${path}`;
  }

  /**
   * Get login URL
   */
  getLoginUrl() {
    return this.getAppUrl('/login.php');
  }

  /**
   * Get dashboard URL
   */
  getDashboardUrl() {
    return this.getAppUrl('/pages/main.php');
  }

  /**
   * Get personil URL
   */
  getPersonilUrl() {
    return this.getAppUrl('/pages/personil.php');
  }

  /**
   * Get bagian URL
   */
  getBagianUrl() {
    return this.getAppUrl('/pages/bagian.php');
  }

  /**
   * Get unsur URL
   */
  getUnsurUrl() {
    return this.getAppUrl('/pages/unsur.php');
  }

  /**
   * Get calendar URL
   */
  getCalendarUrl() {
    return this.getAppUrl('/pages/calendar_dashboard.php');
  }

  /**
   * Get API URL
   */
  getApiUrl(endpoint = '') {
    return this.getAppUrl(`/api/${endpoint}`);
  }

  /**
   * Get logout URL
   */
  getLogoutUrl() {
    return this.getAppUrl('/core/logout.php');
  }

  /**
   * Check if URL is a login redirect
   */
  isLoginRedirect(url) {
    return url.includes('login.php');
  }

  /**
   * Check if URL is a 404 error
   */
  is404Error(url) {
    return url.includes('404') || url.includes('Object not found');
  }

  /**
   * Normalize URL for testing
   */
  normalizeUrl(url) {
    if (url.startsWith(this.baseURL)) {
      return url;
    }
    if (url.startsWith('/')) {
      return `${this.baseURL}${url}`;
    }
    return `${this.baseURL}/${url}`;
  }

  /**
   * Get relative path from full URL
   */
  getRelativePath(url) {
    return url.replace(this.baseURL, '');
  }
}

// Export for use in tests
module.exports = URLHelper;

// Also provide global instance
global.URLHelper = new URLHelper();
