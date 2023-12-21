import { test, expect } from '@playwright/test';

const dolibarrUrl = 'http://localhost/dolibarr/htdocs/';
const digiriskUrl = dolibarrUrl + 'custom/digiriskdolibarr/';

test('digirisk standard page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskstandard/digiriskstandard_card.php');

  // Looking for first groupment (id1 = Trash, id2 = first GP)
  const gpOne = page.locator('.linkElement.id2');
  await gpOne.click();

  const gpId = page.locator('.refid');
  await expect(gpId).toBeVisible();
  await expect(gpId).toContainText('GP1');

  const riskList = page.locator('.titre.inline-block');
  await expect(riskList).toBeVisible();
  await expect(riskList).toContainText('Liste des risques');
});

test('create risk', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskelement/digiriskelement_risk.php?id=2');

  // Looking for add risk button
  const riskAddButton = page.locator('.risk-add.wpeo-button');
  await riskAddButton.click();

  // Check if the modal is visible
  const riskAddModal = page.locator('.modal-risk.modal-risk-0.modal-active');
  await expect(riskAddModal).toBeVisible();

  // Check if we can click on the category dropdown button
  const categoryButton = page.locator('.dropdown-add-button.button-cotation').first();
  await categoryButton.click();

  // Selector for the <ul> element
  const ulSelector = '.saturne-dropdown-content.wpeo-gridlayout';
  // Create a locator for the <li> elements inside the first <ul>
  const liLocator = page.locator(`${ulSelector} >> nth=0 >> li`);
  // Count the <li> elements inside the first <ul> - the number of INRS category
  await expect(liLocator).toHaveCount(22);
});
