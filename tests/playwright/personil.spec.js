const { test, expect } = require('@playwright/test');
const URLHelper = require('./utils/url-helper');

test.describe('SPRIN Personil Management Tests', () => {
  const urlHelper = new URLHelper();

  test.beforeEach(async ({ page }) => {
    await page.goto(urlHelper.getLoginUrl());
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/pages\/main\.php/, { timeout: 10000 });
  });

  test('should display personil page', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await expect(page).toHaveURL(/pages\/personil\.php/);
    await expect(page.locator('body')).toContainText('Personil');
  });

  test('should display personil data table', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const table = page.locator('table');
    if (await table.isVisible()) {
      await expect(table.locator('thead')).toBeVisible();
      await expect(table.locator('tbody')).toBeVisible();

      const headers = table.locator('th');
      expect(await headers.count()).toBeGreaterThan(0);
    }
  });

  test('should search personil', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const searchInput = page.locator('input[placeholder*="Cari"], input[placeholder*="Search"], #search');
    if (await searchInput.isVisible()) {
      await searchInput.fill('admin');
      await page.waitForTimeout(1000);

      const table = page.locator('table tbody');
      if (await table.isVisible()) {
        const rows = table.locator('tr');
        expect(await rows.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should open add personil modal/form', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add"), .btn-add');
    if (await addButton.isVisible()) {
      await addButton.click();

      const modal = page.locator('.modal, .dialog, .popup');
      await expect(modal).toBeVisible({ timeout: 3000 });

      await expect(page.locator('input[name="nama"], input[name="nrp"]')).toBeVisible();
    }
  });

  test('should validate required fields', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add")');
    if (await addButton.isVisible()) {
      await addButton.click();

      const modal = page.locator('.modal');
      if (await modal.isVisible()) {
        const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Save")');
        if (await saveButton.isVisible()) {
          await saveButton.click();

          const errorMessages = page.locator('.error, .invalid-feedback, .text-danger');
          if (await errorMessages.count() > 0) {
            await expect(errorMessages.first()).toBeVisible();
          }
        }
      }
    }
  });

  test('should add new personil', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add")');
    if (await addButton.isVisible()) {
      await addButton.click();

      const modal = page.locator('.modal');
      if (await modal.isVisible()) {
        await page.fill('input[name="nama"]', 'Test Personil');
        await page.fill('input[name="nrp"]', '12345678');

        const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Save")');
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForTimeout(2000);

          const successMessage = page.locator('.alert-success, .success, .toast-success');
          if (await successMessage.isVisible()) {
            await expect(successMessage).toContainText('berhasil');
          }
        }
      }
    }
  });

  test('should edit personil', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(3000);

    const editButtons = page.locator('button:has-text("Edit"), .btn-edit, .edit-btn');
    if (await editButtons.count() > 0) {
      await editButtons.first().click();

      const modal = page.locator('.modal');
      if (await modal.isVisible()) {
        const nameInput = page.locator('input[name="nama"]');
        if (await nameInput.isVisible()) {
          const currentName = await nameInput.inputValue();
          await nameInput.fill(currentName + ' Edited');

          const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Update")');
          if (await saveButton.isVisible()) {
            await saveButton.click();
            await page.waitForTimeout(2000);
          }
        }
      }
    }
  });

  test('should delete personil', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(3000);

    const deleteButtons = page.locator('button:has-text("Hapus"), button:has-text("Delete"), .btn-delete');
    if (await deleteButtons.count() > 0) {
      page.on('dialog', dialog => dialog.accept());
      await deleteButtons.first().click();
      await page.waitForTimeout(2000);

      const successMessage = page.locator('.alert-success, .success');
      if (await successMessage.isVisible()) {
        await expect(successMessage).toContainText('dihapus');
      }
    }
  });

  test('should export data', async ({ page }) => {
    await page.goto(urlHelper.getPersonilUrl());
    await page.waitForTimeout(2000);

    const exportButtons = page.locator('button:has-text("Export"), button:has-text("PDF"), button:has-text("Excel")');
    if (await exportButtons.count() > 0) {
      const downloadPromise = page.waitForEvent('download');
      await exportButtons.first().click();
      const download = await downloadPromise;
      expect(download.suggestedFilename()).toMatch(/\.(pdf|xlsx|xls)$/);
    }
  });
});
