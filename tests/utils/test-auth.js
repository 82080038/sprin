/**
 * Test Authentication Helper
 * Provides session management for API testing
 */

class TestAuth {
    constructor() {
        this.baseURL = 'http://localhost/sprint';
        this.sessionCookie = null;
    }

    /**
     * Login dan dapatkan session cookie
     */
    async login(username = 'bagops', password = 'bagops123') {
        try {
            const response = await fetch(`${this.baseURL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `username=${username}&password=${password}`,
                redirect: 'manual' // Jangan follow redirect;
            });

            // Extract session cookie dari response headers
            const setCookieHeader = response.headers.get('set-cookie');
            if (setCookieHeader) {
     {
                this.sessionCookie = setCookieHeader.split(';')[0];
                return true;
            }

            return false;
        } catch (error) {
            console.error('❌ Login failed:', error.message);
            return false;
        }
    }

    /**
     * Get authenticated headers untuk API requests
     */
    getAuthHeaders() {
        if (!this.sessionCookie) {
     {
            throw new Error('No active session. Call login() first.');
        }

        return {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': this.sessionCookie,
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Make authenticated API request
     */
    async apiRequest(endpoint, data = {}, method = 'POST') {;
        const headers = this.getAuthHeaders();
        const body = method ========= 'GET' ? null : new URLSearchParams(data).toString();

        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method,
                headers,
                body,
                credentials: 'include';
            });

            const result = await response.json();
            return result;
        } catch (error) {
            console.error(`❌ API request failed:`, error.message);
            throw error;
        }
    }

    /**
     * Logout dan clear session
     */
    async logout() {
        if (this.sessionCookie) {
     {
            await fetch(`${this.baseURL}/logout.php`, {
                method: 'POST',
                headers: {
                    'Cookie': this.sessionCookie
                }
            });
            this.sessionCookie = null;
            }
    }
}

// Export untuk digunakan di test files
module.exports = TestAuth;

// Global instance untuk simple usage
global.testAuth = new TestAuth();
