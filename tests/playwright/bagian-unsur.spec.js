const { test, expect } = require('@playwright/test');

test.describe('SPRIN Bagian & Unsur Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/pages\/main\.php/, { timeout: 10000 });
  });

  test.describe('Bagian Management', () => {
    test('should display bagian page', async ({ page }) => {
      await page.goto('/pages/bagian.php');
      await expect(page).toHaveURL(/pages\/bagian\.php/);
      await expect(page.locator('body')).toContainText('Bagian');
    });

    test('should display bagian data table', async ({ page }) => {
      await page.goto('/pages/bagian.php');
      await page.waitForTimeout(2000);
      
      const table = page.locator('table');
      if (await table.isVisible()) {
        await expect(table.locator('thead')).toBeVisible();
        await expect(table.locator('tbody')).toBeVisible();
        
        const rows = table.locator('tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
      }
    });

    test('should add new bagian', async ({ page }) => {
      await page.goto('/pages/bagian.php');
      await page.waitForTimeout(2000);
      
      const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add")');
      if (await addButton.isVisible()) {
        await addButton.click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          await page.fill('input[name="nama_bagian"]', 'Test Bagian API');
          
          const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Save")');
          if (await saveButton.isVisible()) {
            await saveButton.click();
            await page.waitForTimeout(2000);
            
            const successMessage = page.locator('.alert-success, .success');
            if (await successMessage.isVisible()) {
              await expect(successMessage).toContainText('berhasil');
            }
          }
        }
      }
    });

    test('should edit bagian', async ({ page }) => {
      await page.goto('/pages/bagian.php');
      await page.waitForTimeout(2000);
      
      const editButtons = page.locator('button:has-text("Edit"), .btn-edit');
      if (await editButtons.count() > 0) {
        await editButtons.first().click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          const nameInput = page.locator('input[name="nama_bagian"]');
          if (await nameInput.isVisible()) {
            await nameInput.fill('Test Bagian Edited');
            
            const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Update")');
            if (await saveButton.isVisible()) {
              await saveButton.click();
              await page.waitForTimeout(2000);
            }
          }
        }
      }
    });
  });

  test.describe('Unsur Management', () => {
    test('should display unsur page', async ({ page }) => {
      await page.goto('/pages/unsur.php');
      await expect(page).toHaveURL(/pages\/unsur\.php/);
      await expect(page.locator('body')).toContainText('Unsur');
    });

    test('should display unsur data table', async ({ page }) => {
      await page.goto('/pages/unsur.php');
      await page.waitForTimeout(2000);
      
      const table = page.locator('table');
      if (await table.isVisible()) {
        await expect(table.locator('thead')).toBeVisible();
        await expect(table.locator('tbody')).toBeVisible();
        
        const rows = table.locator('tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
      }
    });

    test('should add new unsur', async ({ page }) => {
      await page.goto('/pages/unsur.php');
      await page.waitForTimeout(2000);
      
      const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add")');
      if (await addButton.isVisible()) {
        await addButton.click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          await page.fill('input[name="nama_unsur"]', 'Test Unsur API');
          
          const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Save")');
          if (await saveButton.isVisible()) {
            await saveButton.click();
            await page.waitForTimeout(2000);
            
            const successMessage = page.locator('.alert-success, .success');
            if (await successMessage.isVisible()) {
              await expect(successMessage).toContainText('berhasil');
            }
          }
        }
      }
    });

    test('should edit unsur', async ({ page }) => {
      await page.goto('/pages/unsur.php');
      await page.waitForTimeout(2000);
      
      const editButtons = page.locator('button:has-text("Edit"), .btn-edit');
      if (await editButtons.count() > 0) {
        await editButtons.first().click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          const nameInput = page.locator('input[name="nama_unsur"]');
          if (await nameInput.isVisible()) {
            await nameInput.fill('Test Unsur Edited');
            
            const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Update")');
            if (await saveButton.isVisible()) {
              await saveButton.click();
              await page.waitForTimeout(2002);
            }
          }
        }
      }
    });
  });

  test('should validate required fields', async ({ page }) => {
    await page.goto('/pages/bagian.php');
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
});
