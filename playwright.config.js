const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './test/Browser',
  outputDir: './test-results',
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],
  use: {
    baseURL: process.env.DOMAINING_BASE_URL || 'http://127.0.0.1:8000',
    trace: 'retain-on-failure'
  }
});
