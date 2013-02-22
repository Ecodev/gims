basePath = '../';

files = [
  ANGULAR_SCENARIO,
  ANGULAR_SCENARIO_ADAPTER,
  'module/Application/test/e2e/**/*.js'
];

autoWatch = true;
//singleRun = true;

browsers = ['Chrome', 'PhantomJS'];


junitReporter = {
  outputFile: 'data/logs/testacular-e2e.xml',
  suite: 'e2e'
};


proxies = {
  '/': 'http://gims.local/'
};
