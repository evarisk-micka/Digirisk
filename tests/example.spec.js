// @ts-check
const { test, setup, expect } = require('@playwright/test');
const authFile = 'playwright/.auth/user.json';

test('has title', async ({ page }) => {
  await page.goto('http://localhost/dolibarr/htdocs/index.php');
  await page.getByLabel('username').fill('adminadmin');
  await page.getByLabel('password').fill('adminadmin');
  await page.getByRole('button', { name: 'Se connecter' }).click();
  await page.waitForURL('http://localhost/dolibarr/htdocs/custom/digiriskdolibarr/digiriskdolibarrindex.php');

  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/DigiriskDolibarr/);
});

test('get started link', async ({ page }) => {
  await page.goto('http://localhost/dolibarr/htdocs/custom/digiriskdolibarr/digiriskdolibarrindex.php');

  // Click the get started link.
  await page.getByRole('link', { name: 'Document unique' }).click();

  // Expects page to have a heading with the name of Installation.
  await expect(page.getByRole('heading', { name: 'Digirisk' })).toBeVisible();
});
