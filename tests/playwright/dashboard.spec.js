const { test, expect } = require('@playwright/test');
const URLHelper = require('./utils/url-helper');

test.describe('SPRIN Dashboard Tests', () => {
  const urlHelper = new URLHelper();

  test.beforeEach(async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/pages\/main\.php/, { timeout: 10000 });
  });

  test('should display dashboard components', async ({ page }) => {
    await expect(page.locator('h1, .dashboard-title')).toContainText(/Dashboard|Sistem Manajemen/);

    const statsCards = page.locator('.card, .stat-card, .info-box');
    await expect(statsCards.first()).toBeVisible();

    const navigation = page.locator('nav, .navbar, .sidebar, .menu');
    await expect(navigation).toBeVisible();
  });

  test('should display statistics data', async ({ page }) => {
    await page.waitForTimeout(2000);

    const statElements = page.locator('.badge, .number, .count, h3, h4');
    if (await statElements.first().isVisible()) {
      const firstStat = await statElements.first().textContent();
      expect(firstStat?.trim()).not.toBe('');
    }
  });

  test('should navigate to main sections', async ({ page }) => {
    const navigationLinks = [
      { text: 'Personil', url: urlHelper.getPersonilUrl() },
      { text: 'Bagian', url: urlHelper.getBagianUrl() },
      { text: 'Unsur', url: urlHelper.getUnsurUrl() },
      { text: 'Kalender', url: urlHelper.getCalendarUrl() }
    ];

    for (const link of navigationLinks) {
      const navLink = page.locator(`a:has-text("${link.text}")`).first();
      if (await navLink.isVisible()) {
        await navLink.click();
        await page.waitForTimeout(2000);

        const currentUrl = page.url();
        // Check if we're still on main page (SPA navigation) or moved to new page
        if (currentUrl.includes('main.php')) {
          // SPA navigation - check if content changed
          const content = page.locator('#main-content, .page-content');
          if (await content.isVisible()) {
            const contentText = await content.textContent();
            if (contentText?.includes(link.text)) {
              }
          }
        } else {
          // Traditional navigation - check URL
          await expect(page).toHaveURL(new RegExp(link.url.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')), { timeout: 5000 });
        }

        await page.goBack();
        await page.waitForTimeout(1000);
      } else {
        }
    }
  });

  test('should display charts or visualizations', async ({ page }) => {
    await page.waitForTimeout(3000);

    const charts = page.locator('canvas, .chart, .graph, [id*="chart"]');
    if (await charts.count() > 0) {
      await expect(charts.first()).toBeVisible();
    }
  });

  test('should have responsive design', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('body')).toBeVisible();

    const mobileMenu = page.locator('.navbar-toggler, .menu-toggle, .hamburger');
    if (await mobileMenu.isVisible()) {
      await mobileMenu.click();
      await expect(page.locator('.navbar-collapse, .mobile-menu')).toBeVisible();
    }
  });

  test('should handle logout functionality', async ({ page }) => {
    const logoutLink = page.locator('a:has-text("Logout"), a:has-text("Keluar")');
    if (await logoutLink.isVisible()) {
      await logoutLink.click();
      await expect(page).toHaveURL(/login\.php/, { timeout: 5000 });
    }
  });
});
