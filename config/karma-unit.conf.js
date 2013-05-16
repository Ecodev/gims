basePath = '../';
urlRoot = '/__karma/';
autoWatch = true;
browsers = ['Chrome'];
proxies = {
  '/': 'http://gims.local/'
};

files = [
  JASMINE,
  JASMINE_ADAPTER,
  'htdocs/lib/autoload/*',
  'htdocs/lib/angular/angular-mocks.js',
  'htdocs/js/*.js',
  'tests/unit/**/*.js'
];


junitReporter = {
  outputFile: 'data/logs/karma-unit.xml',
  suite: 'unit'
};