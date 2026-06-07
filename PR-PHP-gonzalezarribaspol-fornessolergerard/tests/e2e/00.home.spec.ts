import { expect, test } from '@playwright/test';
import 'dotenv/config';

const BASE = process.env.BASE_URL || 'http://localhost:8000';

test('Home: IDs y filtro desplegable + enlace a login', async ({ page }) => {
  await page.goto(`${BASE}/index.php`);

  await expect(page.locator('#theme-image')).toBeVisible();
  await expect(page.locator('#filter-maintenance')).toBeVisible();
  await expect(page.locator('#attraction-count')).toBeVisible();
  await expect(page.locator('#attraction-list')).toBeVisible();

  // Cambiar filtro y comprobar que el count cambia (requiere que la app aplique el filtro)
  const initialCount = await page.locator('#attraction-count').innerText();
  await page.selectOption('#filter-maintenance', 'maintenance');
  await expect(page.locator('#attraction-count')).not.toHaveText(initialCount);

  // Link a login
  const link = page.getByRole('link', { name: /iniciar compra/i });
  await expect(link).toBeVisible();
});
