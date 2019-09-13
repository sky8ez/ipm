var erpApp = angular.module('erpApp', ['dashboardCtrl','profilCtrl','importCtrl','displayCtrl','generalCtrl','listCtrl','formCtrl','printCtrl','reportCtrl','reportViewCtrl','reportSlickViewCtrl','editorCtrl','floatingFormCtrl','searchCtrl','auditCtrl','printDetailCtrl','listService','editorService','formService','ngRoute', 'ngMaterial','ngSanitize']);
//'ngMessages',
// configure our routes
	erpApp.config(function($routeProvider) {
		$routeProvider
			.when('/', {
				templateUrl : 'pages/master/dashboard.html',
				controller  : 'dashboardController'
			})
			// route for the list page
			.when('/list/:form', {
				templateUrl : 'pages/master/master_list.html',
				controller  : 'listController'
			})
			// route for the form page
			.when('/form/:form', {
				templateUrl : 'pages/master/master_form.html',
				controller  : 'formController'
			})
			.when('/form/:form/:ID', {
				templateUrl : 'pages/master/master_form.html',
				controller  : 'formController'
			})
			.when('/form/:form/:ID/editor', {
				templateUrl : 'pages/master/print_editor.html',
				controller  : 'editorController'
			})
			// route for the print
			.when('/print/:form/:ID', {
				templateUrl : 'pages/master/print_page_tes.html',
				controller  : 'printController'
			})
			.when('/print/:form/:ID/:printID', {
				templateUrl : 'pages/master/print_page_tes.html',
				controller  : 'printController'
			})
			.when('/general', {
				templateUrl : 'pages/master/general.html',
				controller  : 'generalController'
			})
			.when('/tools/import-export', {
				templateUrl : 'pages/master/import.html',
				controller  : 'importController'
			})
			.when('/profile', {
				templateUrl : 'pages/master/profil.html',
				controller  : 'profilController'
			})
			.when('/reports', {
				templateUrl : 'pages/master/master_reports_list.html',
				controller  : 'reportController'
			})
			.when('/reports/:reportName', {
				templateUrl : 'pages/master/master_reports_slick_view.html',
				controller  : 'reportSlickViewController'
			})
			// .when('/reports/:reportName', {
			// 	templateUrl : 'pages/master/master_reports_view.html',
			// 	controller  : 'reportViewController'
			// })
	});

// 	erpApp.config(['$locationProvider', function($locationProvider) {
//          $locationProvider.html5Mode({
//   enabled: true,
//   requireBase: false
// });
//     }]);

	erpApp.directive('format', ['$filter', function ($filter) {
	    return {
	        require: '?ngModel',
	        link: function (scope, elem, attrs, ctrl) {
	            if (!ctrl) return;

	            ctrl.$formatters.unshift(function (a) {

	                return $filter(attrs.format)(Math.round(ctrl.$modelValue * 100) / 100)
	            });

	            ctrl.$parsers.unshift(function (viewValue) {
	                var plainNumber = viewValue.replace(/[^\d|\-+|\.+]/g, '');
	                elem.val($filter(attrs.format)(plainNumber));
	                return plainNumber;
	            });
	        }
	    };
		}]);

erpApp.directive('isNumber', function () {
		return {
		    require: 'ngModel',
		    link: function (scope) {
		        scope.$watch('wks.number', function(newValue,oldValue) {
		            var arr = String(newValue).split("");
		            if (arr.length === 0) return;
		            if (arr.length === 1 && (arr[0] == '-' || arr[0] === '.' )) return;
		            if (arr.length === 2 && newValue === '-.') return;
		            if (isNaN(newValue)) {
		                scope.wks.number = oldValue;
		            }
		        });
		    }
		};
});


	erpApp.filter('capitalize', function() {
	    return function(input) {
	      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
	    }
	});

	erpApp.filter('getById', function() {
	  return function(input, id) {
	    var i=0, len=input.length;
	    for (; i<len; i++) {
	      if (input[i].label == id) {
	        return input[i];
	      }
	    }
	    return null;
	  }
	});

	erpApp.factory('Excel',function($window){
	        var uri='data:application/vnd.ms-excel;base64,',
	            template='<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
	            base64=function(s){return $window.btoa(unescape(encodeURIComponent(s)));},
	            format=function(s,c){return s.replace(/{(\w+)}/g,function(m,p){return c[p];})};
	        return {
	            tableToExcel:function(tableId,worksheetName){
	                var table=$(tableId),
	                    ctx={worksheet:worksheetName,table:table.html()},
	                    href=uri+base64(format(template,ctx));
	                return href;
	            }
	        };
	    })

	erpApp.controller('ReloadController', function($scope,$route, $window, Forms) {
		    //show alert
			$scope.reloadPage = function() {
				//$route.reload();
				//get table list ajax
				Forms.get('reload-general')
					.success(function(data) {
							$window.location.reload();
					})
					.error(function(data) {
						//  $scope.loading = false;
						//  $scope.showAlert(data.error);
					});


			};

	});


	// erpApp.controller('dashboardController', function($scope) {
	// 	$scope.message = "Dashboard";
	// });

	//general setting
	// erpApp.controller('generalController', function($scope) {
	// 	$scope.message = "General";
	// });

	erpApp.factory('httpResponseInterceptor', ['$q', '$rootScope', '$location', function($q, $rootScope, $location) {
	    return {
	        responseError: function(rejection) {
	            if (rejection.status === 401) {
	                // $location.path('login');
									window.location.replace("/login");
	            }
	            return $q.reject(rejection);
	        }
	    };
	}]);

	erpApp.config(function($httpProvider) {
	    $httpProvider.interceptors.push('httpResponseInterceptor');
	});


// (function(window,undefined){
//
//     // Bind to StateChange Event
//     History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
//         var State = History.getState(); // Note: We are using History.getState() instead of event.state
//     });
//
//     // // Change our States
//      History.pushState({state:1}, "State 1", "?state=1"); // logs {state:1}, "State 1", "?state=1"
//     // History.pushState({state:2}, "State 2", "?state=2"); // logs {state:2}, "State 2", "?state=2"
//     // History.replaceState({state:3}, "State 3", "?state=3"); // logs {state:3}, "State 3", "?state=3"
//     // History.pushState(null, null, "?state=4"); // logs {}, '', "?state=4"
//     // History.back(); // logs {state:3}, "State 3", "?state=3"
//     // History.back(); // logs {state:1}, "State 1", "?state=1"
//     // History.back(); // logs {}, "Home Page", "?"
//     // History.go(2); // logs {state:3}, "State 3", "?state=3"
//
// })(window);
