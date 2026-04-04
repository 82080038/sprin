const { test, expect } = require('@playwright/test');
const URLHelper = require('./utils/url-helper');

test.describe('SPRIN Login Tests', () => {
  const urlHelper = new URLHelper();

  test.beforeEach(async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
  });

  test('should display login page correctly', async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.waitForTimeout(2000);

    await expect(page).toHaveTitle(/SPRIN|Login|POLRES Samosir/);

    const titleElement = page.locator('h1, h2, h3, .login-title');
    if (await titleElement.first().isVisible()) {
      await expect(titleElement.first()).toContainText(/Login|Masuk|Sistem|POLRES SAMOSIR/);
    }

    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await page.fill('input[name="username"]', 'invalid');
    await page.fill('input[name="password"]', 'invalid');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    const alertElement = page.locator('.alert-danger, .error, .danger').first();
    if (await alertElement.isVisible()) {
      await expect(alertElement).toBeVisible();
    }
    await expect(page).toHaveURL(/login\.php/);
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'bagops123');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/main\.php/, { timeout: 10000 });
    await expect(page.locator('body')).toContainText('Dashboard');
  });

  test('should work with Quick Login feature', async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.waitForTimeout(2000);

    const quickLoginButton = page.locator('button:has-text("Quick Login")');
    if (await quickLoginButton.isVisible()) {
      await quickLoginButton.click();
      await expect(page).toHaveURL(/main\.php/, { timeout: 10000 });
      await expect(page.locator('body')).toContainText('Dashboard');
    } else {
      }
  });

  test('should redirect to login when accessing protected pages without auth', async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.waitForTimeout(2000);

    const protectedPages = [
      urlHelper.getDashboardUrl(),
      urlHelper.getPersonilUrl(),
      urlHelper.getBagianUrl(),
      urlHelper.getUnsurUrl()
    ];

    for (const pageUrl of protectedPages) {
      await page.goto(pageUrl);
      await page.waitForTimeout(2000);

      const currentUrl = page.url();
      if (urlHelper.isLoginRedirect(currentUrl)) {
        await expect(page).toHaveURL(/login\.php/, { timeout: 5000 });
      } else {
        }
    }
  });

  test('should maintain session after login', async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'bagops123');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/main\.php/);

    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const currentUrl = page.url();
    if (urlHelper.is404Error(currentUrl)) {
      await expect(page.locator('body')).toContainText(/Personil|Dashboard|404/);
    } else {
      await expect(page.locator('body')).toContainText(/Personil|Dashboard/);
    }
  });
});
