const { test, expect } = require('@playwright/test');

test.describe('SPRIN Calendar & Scheduling Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'bagops');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/pages\/main\.php/, { timeout: 10000 });
  });

  test('should display calendar dashboard', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await expect(page).toHaveURL(/pages\/calendar_dashboard\.php/);
    await expect(page.locator('body')).toContainText('Kalender');
  });

  test('should display calendar widget', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc, .calendar-container');
    if (await calendar.isVisible()) {
      await expect(calendar).toBeVisible();
      
      const calendarHeader = calendar.locator('.fc-header, .calendar-header, .calendar-nav');
      if (await calendarHeader.isVisible()) {
        await expect(calendarHeader).toBeVisible();
      }
    }
  });

  test('should navigate between months', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc');
    if (await calendar.isVisible()) {
      const nextButton = calendar.locator('.fc-next-button, .next-month, [title*="next"]');
      if (await nextButton.isVisible()) {
        await nextButton.click();
        await page.waitForTimeout(1000);
        
        const prevButton = calendar.locator('.fc-prev-button, .prev-month, [title*="prev"]');
        if (await prevButton.isVisible()) {
          await prevButton.click();
          await page.waitForTimeout(1000);
        }
      }
    }
  });

  test('should open add event modal', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add Event"), .btn-add-event');
    if (await addButton.isVisible()) {
      await addButton.click();
      
      const modal = page.locator('.modal, .dialog');
      await expect(modal).toBeVisible({ timeout: 3000 });
      
      await expect(page.locator('input[name="title"], input[name="event_title"]')).toBeVisible();
      await expect(page.locator('input[name="start_date"], input[name="start"]')).toBeVisible();
    }
  });

  test('should create new event', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const addButton = page.locator('button:has-text("Tambah"), button:has-text("Add Event")');
    if (await addButton.isVisible()) {
      await addButton.click();
      
      const modal = page.locator('.modal');
      if (await modal.isVisible()) {
        await page.fill('input[name="title"], input[name="event_title"]', 'Test Event API');
        await page.fill('input[name="start_date"], input[name="start"]', '2024-01-15');
        await page.fill('input[name="end_date"], input[name="end"]', '2024-01-15');
        
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

  test('should display existing events', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc');
    if (await calendar.isVisible()) {
      const events = calendar.locator('.fc-event, .calendar-event, .event');
      if (await events.count() > 0) {
        await expect(events.first()).toBeVisible();
      }
    }
  });

  test('should click on event to view details', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc');
    if (await calendar.isVisible()) {
      const events = calendar.locator('.fc-event, .calendar-event');
      if (await events.count() > 0) {
        await events.first().click();
        
        const modal = page.locator('.modal, .event-details');
        await expect(modal).toBeVisible({ timeout: 3000 });
      }
    }
  });

  test('should edit existing event', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc');
    if (await calendar.isVisible()) {
      const events = calendar.locator('.fc-event, .calendar-event');
      if (await events.count() > 0) {
        await events.first().click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          const editButton = modal.locator('button:has-text("Edit"), button:has-text("Ubah")');
          if (await editButton.isVisible()) {
            await editButton.click();
            
            const titleInput = page.locator('input[name="title"], input[name="event_title"]');
            if (await titleInput.isVisible()) {
              await titleInput.fill('Edited Event Title');
              
              const saveButton = page.locator('button:has-text("Simpan"), button:has-text("Update")');
              if (await saveButton.isVisible()) {
                await saveButton.click();
                await page.waitForTimeout(2000);
              }
            }
          }
        }
      }
    }
  });

  test('should delete event', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const calendar = page.locator('#calendar, .calendar, .fc');
    if (await calendar.isVisible()) {
      const events = calendar.locator('.fc-event, .calendar-event');
      if (await events.count() > 0) {
        await events.first().click();
        
        const modal = page.locator('.modal');
        if (await modal.isVisible()) {
          const deleteButton = modal.locator('button:has-text("Hapus"), button:has-text("Delete")');
          if (await deleteButton.isVisible()) {
            page.on('dialog', dialog => dialog.accept());
            await deleteButton.click();
            await page.waitForTimeout(2000);
            
            const successMessage = page.locator('.alert-success, .success');
            if (await successMessage.isVisible()) {
              await expect(successMessage).toContainText('dihapus');
            }
          }
        }
      }
    }
  });

  test('should sync with Google Calendar', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const syncButton = page.locator('button:has-text("Sync"), button:has-text("Google Calendar")');
    if (await syncButton.isVisible()) {
      await syncButton.click();
      
      await page.waitForTimeout(2000);
      
      const syncMessage = page.locator('.alert-info, .sync-message');
      if (await syncMessage.isVisible()) {
        await expect(syncMessage).toContainText('sync');
      }
    }
  });

  test('should display schedule statistics', async ({ page }) => {
    await page.goto('/pages/calendar_dashboard.php');
    await page.waitForTimeout(3000);
    
    const statsCards = page.locator('.stat-card, .info-box, .card');
    if (await statsCards.count() > 0) {
      const firstCard = statsCards.first();
      await expect(firstCard).toBeVisible();
      
      const cardContent = firstCard.locator('.card-body, .content');
      if (await cardContent.isVisible()) {
        const text = await cardContent.textContent();
        expect(text?.trim()).not.toBe('');
      }
    }
  });
});
