import { test, expect } from '@playwright/test';

const dolibarrUrl = 'http://localhost/dolibarr/htdocs/';
const digiriskUrl = dolibarrUrl + 'custom/digiriskdolibarr/';

test('digirisk element page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskstandard/digiriskstandard_card.php');

  // Looking for first groupment (id1 = Trash)
  const gp_one = page.locator('.linkElement.id2');

  await gp_one.click();

  const gp_id = page.locator('.refid');

  await expect(gp_id).toBeVisible();
  await expect(gp_id).toContainText('GP1');

  const gp_info = page.locator('.refidno');

  await expect(gp_id).toBeVisible();
  await expect(gp_id).toContainText('Document Unique');

  const risklist = page.locator('.titre.inline-block');

  await expect(risklist).toBeVisible();
  await expect(risklist).toContainText('Liste des risques');
});
