basePath = '../';
autoWatch = true;
browsers = ['Chrome'];
proxies = {
  '/': 'http://gims.local/'
};

files = [
  JASMINE,
  JASMINE_ADAPTER,
  'htdocs/lib/angular/angular.js',
  'htdocs/lib/angular/angular-*.js',
  'module/Application/test/lib/angular/angular-mocks.js',
  'htdocs/js/*.js',
  'module/Application/test/unit/**/*.js'
];


junitReporter = {
  outputFile: 'data/logs/testacular-unit.xml',
  suite: 'unit'
};
