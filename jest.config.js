{
  "testEnvironment": "node",
  "roots": ["<rootDir>/tests"],
  "testMatch": [
    "**/__tests__/**/*.js",
    "**/?(*.)+(spec|test).js"
  ],
  "collectCoverageFrom": [
    "api/**/*.js",
    "core/**/*.js",
    "pages/**/*.js",
    "!**/node_modules/**",
    "!**/tests/**"
  ],
  "coverageDirectory": "tests/coverage",
  "coverageReporters": [
    "text",
    "lcov",
    "html"
  ],
  "setupFilesAfterEnv": ["<rootDir>/tests/setup.js"],
  "testTimeout": 10000,
  "verbose": true
}