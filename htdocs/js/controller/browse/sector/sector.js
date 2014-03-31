angular.module('myApp').controller('Browse/SectorCtrl', function($scope, $location, $http, $timeout, Restangular) {
    'use strict';

    $scope.params = {fields: 'paths,color,genericColor'};
    $scope.select2Template = "" +
        "<div>" +
        "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>" +
        "    <i class='fa fa-gims-filter' style='color:[[item.color]];' ></i> [[item.name]]" +
        "</div>" +
        "<div class='col-sm-7 col-md-7'>" +
        "    <small>" +
        "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
        "    </small>" +
        "</div>" +
        "<div class='clearfix'></div>" +
        "</div>";

    $scope.values = {};
    $scope.selectByChildren = 'true';
    $scope.dates = [];//[{value:1985}, {value:1990}, {value:1995}, {value:2000}, {value:2005}, {value:2010} ];
    $scope.questions = [
        {
            name: 'Equipment',
            desc: 'Number of connections'
        },
        {
            name: 'People per equipement',
            desc: 'Estimated number of persons having access to a connection'
        }
    ];

    $scope.$watch('filter + selectByChildren', function() {
        syncFilters();
    });

    var syncFilters = function() {
        if ($scope.selectByChildren == 'true' && $scope.filter) {
            Restangular.one('filter', $scope.filter.id).get({fields: 'children,children.paths'}).then(function(filter) {
                if (filter.children) {
                    $scope.filters = filter.children;
                }
            });
        } else {
            $scope.filter = null;
        }
    };

    var redirect = function() {
        $location.url($location.search().returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

    $scope.init = function(filter, question, index) {
        var values = $scope.values;
        if (!values[filter.id]) {
            values[filter.id] = {};
        }
        if (!values[filter.id][question.name]) {
            values[filter.id][question.name] = [];
        }

        if (!values[filter.id][question.name][index]) {
            values[filter.id][question.name][index] = null;
        }
    };

    $scope.addDate = function() {
        $scope.dates.push({});
    };

    $scope.removeDate = function(index) {
        _.forEach($scope.values, function(questions) {
            _.forEach(questions, function(dates) {
                dates.splice(index, 1);
            });
        });
        $scope.dates.splice(index, 1);
    };

    $scope.getUsedYearsPattern = function() {
        var dates = [];
        _.forEach($scope.dates, function(date) {
            if (!_.isUndefined(date.value)) {
                dates.push(date.value);
            }
        });
        if (dates.length > 0) {
            var pattern = "(?!" + dates.join('|') + ")\\d{4}";
            return new RegExp(pattern);
        }
        return /.*/;
    };

    $scope.validateUnicity = function(index) {
        if (_.map($scope.dates,function(el) {
            return el.value;
        }).indexOf($scope.dates[index].value) !== index) {
            $scope.dates[index].duplicate = true;
        } else {
            delete($scope.dates[index].duplicate);
        }
    };

});
