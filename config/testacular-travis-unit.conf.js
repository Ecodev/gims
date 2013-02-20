basePath = '../';

files = [
  JASMINE,
  JASMINE_ADAPTER,
  'htdocs/lib/angular/angular.js',
  'htdocs/lib/angular/angular-*.js',
  'module/Application/test/lib/angular/angular-mocks.js',
  'htdocs/js/**/*.js',
  'module/Application/test/unit/**/*.js'
];

autoWatch = true;

browsers = ['Chrome'];

junitReporter = {
  outputFile: 'data/logs/testacular-unit.xml',
  suite: 'unit'
};

proxies = {
  '/': 'http://gims.local/'
};

// Common configuration for Travis (no GUI)
autoWatch = false;
singleRun = true;

browsers = ['PhantomJS'];

proxies = {
  '/': 'http://gims.local/'
};
