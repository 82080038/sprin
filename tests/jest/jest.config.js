module.exports = {
  testEnvironment: 'node',
  roots: ['<rootDir>/..'],
  testMatch: [
    '**/jest/**/*.js',
    '**/?(*.)+(spec|test).js'
  ],
  collectCoverageFrom: [
    'jest/**/*.js',
    '!jest/**/*.test.js',
    '!jest/**/*.spec.js'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['text', 'lcov', 'html'],
  setupFilesAfterEnv: ['<rootDir>/setup.js'],
  testTimeout: 30000,
  verbose: true,
  transform: {},
  moduleFileExtensions: ['js', 'json']
};
