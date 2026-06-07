import { expect, test } from '@playwright/test';
const BASE = process.env.BASE_URL || 'http://localhost:8000';

test('Login: email válido redirige automáticamente a buy.php', async ({ page }) => {
  await page.goto(`${BASE}/login.php`);
  await expect(page.locator('#login-form')).toBeVisible();
  await page.fill('#email-input', 'alumno@example.com');
  await page.click('#login-form button[type="submit"]');
  await expect(page).toHaveURL(new RegExp('/buy\\.php$'));
  await expect(page.locator('#buy-form')).toBeVisible();
});

test('Login: email inválido muestra error (flash o validación en servidor)', async ({ page }) => {
  await page.goto(`${BASE}/login.php`);
  await page.fill('#email-input', 'no-es-email');
  await page.click('#login-form button[type="submit"]');
  // Debes implementar en servidor: o se queda en login con flash, o 422, etc.
  await expect(page.locator('#flash-message')).toBeVisible();
});
