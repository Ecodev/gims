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
  'htdocs/lib/jquery/jquery-*.min.js',
  'htdocs/lib/select2/select2.js',
  'htdocs/lib/angular/angular.js',
  'htdocs/lib/angular/angular-*.js',
  'htdocs/lib/angular-ui/build/angular-ui.min.js',
  'htdocs/lib/ui-bootstrap/ui-bootstrap-tpls*.js',
  'htdocs/lib/ng-grid/build/ng-grid.min.js',
  'htdocs/lib/angular-highcharts-directive/src/directives/highchart.js',
  'tests/lib/angular/angular-mocks.js',
  'htdocs/js/*.js',
  'tests/unit/**/*.js'
];


junitReporter = {
  outputFile: 'data/logs/karma-unit.xml',
  suite: 'unit'
};