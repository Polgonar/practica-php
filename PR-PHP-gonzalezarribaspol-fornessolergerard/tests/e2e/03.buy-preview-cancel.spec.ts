import { expect, test } from '@playwright/test';
const BASE = process.env.BASE_URL || 'http://localhost:8000';

test('Flujo: compra → preview → cancelar', async ({ page }) => {
  // Login
  await page.goto(`${BASE}/login.php`);
  await page.fill('#email-input', 'alumno@example.com');
  await page.click('#login-form button[type="submit"]');
  await expect(page).toHaveURL(new RegExp('/buy\\.php$'));

  // Selecciona una cantidad para generar pedido PENDING
  const firstQuantity = page.locator('[id^="quantity-"]').first();
  await firstQuantity.fill('1');
  await page.click('#buy-form button[type="submit"]');

  await expect(page).toHaveURL(new RegExp('/preview\\.php$'));
  await expect(page.locator('#cart-preview')).toBeVisible();

  // Cancelar
  await page.click('#cancel-button');
  await expect(page).toHaveURL(new RegExp('/confirm\\.php$'));
  await expect(page.locator('#flash-message')).toBeVisible(); // “Pedido cancelado”
});
