basePath = '../';
urlRoot = '/__karma/';
autoWatch = true;
browsers = ['Chrome'];
proxies = {
  '/': 'http://gims.local/'
};

files = [
  ANGULAR_SCENARIO,
  ANGULAR_SCENARIO_ADAPTER,
  'tests/e2e/**/*.js'
];

junitReporter = {
  outputFile: 'data/logs/karma-e2e.xml',
  suite: 'e2e'
};

