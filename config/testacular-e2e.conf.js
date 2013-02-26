basePath = '../';
autoWatch = true;
browsers = ['Chrome'];
proxies = {
  '/': 'http://gims.local/'
};

files = [
  ANGULAR_SCENARIO,
  ANGULAR_SCENARIO_ADAPTER,
  'module/Application/test/e2e/**/*.js'
];

junitReporter = {
  outputFile: 'data/logs/testacular-e2e.xml',
  suite: 'e2e'
};

