String.prototype.capitalizeFirst = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

var app = angular.module("apidocs", []);

var directives = {

	"section": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@"
			},
			template: '<div class="section"><h1 class="section">{{name.capitalizeFirst()}} Class</h1><div ng-transclude></div></div>',
			controller: function ($attrs) {
				this.sectionName = $attrs.name;
			}
		}
	},

	"description": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			template: '<div class="description" ng-transclude></div>'
		}
	},

	"endpoint": function () {
		return {
			require: "^?section",
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@"
			},
			template: '<div class="endpoint"><h2 ng-click="toggleShow()" class="endpoint">{{sectionName}}.{{name}}</h2><div ng-show="shown" ng-transclude></div></div>',
			link: function (scope, element, attrs, sectionController) {
				scope.shown = false;
				scope.toggleShow = function() {
					scope.shown = !scope.shown;
				}
				scope.sectionName = sectionController.sectionName;
			},
			controller: function ($attrs) {
				this.endpointName = $attrs.name;
			}
		}
	},

	"parameters": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			template: '' +
				'<div class="parameterscontainer">' +
					'<h3>Parameters</h3>' +
					'<div faketable class="parameters" ng-transclude>' +
						'<parameterheaders></parameterheaders>' +
						'<parameter required name="session_id" type="integer" default_value="">The session id.</parameter>' +
						'<parameter required name="session_key" type="string" default_value="">The session key.</parameter>' +
						'<parameter required name="action_class" type="string" default_value="">The class the action is in.</parameter>' +
						'<parameter required name="action" type="string" default_value="">The action/endpoint to be called.</parameter>' +
					'</div>' +
					'<div class="notes"><span required>█</span> denotes a required field</div>' +
				'</div>'
		}
	},

	"parameterheaders": function () {
		return {
			restrict: "E",
			replace: true,
			template: '<div fakeheaderrow class="parameterheaders"><div>Name</div><div>Type</div><div>Default Value</div><div>Description</div></div>'
		}
	},

	"parameter": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@",
				type: "@",
				default_value: "@"
			},
			template: '<div fakerow class="parameter"><div>{{name}}</div><div>{{type}}</div><div>{{default_value}}</div><div ng-transclude></div></div>'
		}
	},

	"returnvalues": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			template: '' +
				'<div class="returnvaluescontainer">' +
					'<h3>Return Values</h3>' +
					'<div faketable class="returnvalues" ng-transclude>' +
						'<returnvalueheaders></returnvalueheaders>' +
					'</div>' +
				'</div>'
		}
	},

	"returnvalueheaders": function () {
		return {
			restrict: "E",
			replace: true,
			template: '<div fakeheaderrow class="returnvalueheaders"><div>Name</div><div>Type</div><div>Location</div><div>Default Value</div><div>Description</div></div>'
		}
	},

	"returnvalue": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@",
				type: "@",
				location: "@",
				default_value: "@"
			},
			template: '<div fakerow class="returnvalue"><div>{{name}}</div><div>{{type}}</div><div>{{location}}</div><div>{{default_value}}</div><div ng-transclude></div></div>'
		}
	},

	"example": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@"
			},
			template: '<div class="example"><h3 ng-click="toggleShow()" class="example">{{name}}</h2><div ng-show="shown" ng-transclude></div></div>',
			link: function (scope, element, attrs, sectionController) {
				scope.shown = false;
				scope.toggleShow = function() {
					scope.shown = !scope.shown;
				}
			},
		}
	},

	"json": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			template: '<div class="json" ng-transclude></div>'
		}
	},

	"objectnode": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@",
				type: "@"
			},
			template: '<div class="nodecontainer"><span class="nodename">{{this.getName()}}</span>{<div class="node" ng-show="shown" ng-transclude></div>}</div>',
			link: function (scope, element) {
				scope.shown = !!(element.contents()[2].children.length);
				scope.getName = function() {
					if (scope.name)
						return ('"' + scope.name + '": ');
				}
			}
		}
	},

	"arraynode": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@",
				type: "@"
			},
			template: '' +
				'<div class="nodecontainer">' +
					'<span class="nodename">{{this.getName()}}</span>[<div class="node" ng-show="shown" ng-transclude></div>]' +
				'</div>' +
			'',
			link: function (scope, element) {
				scope.shown = !!(element.contents()[2].children.length);
				scope.getName = function() {
					if (scope.name)
						return ('"' + scope.name + '": ');
				}
			}
		}
	},


	"requestexample": function () { // "requestexample" is kinda a hack, because it doesn't use <example> and <json> etc.
		return {
			require: ["^?section", "^?endpoint"],
			restrict: "E",
			transclude: true,
			replace: false,
			template: '' +
				'<div class="example">' +
					'<h3 ng-click="toggleShow()" class="example">Request Example - json</h3>' +
					'<div class="json" ng-show="shown">' +
						'<div>' +
							'<div class="nodecontainer">{<div class="node" ng-transclude>' +
								'<variable required name="session_id">20324</variable>' +
								'<variable required name="session_key">2SVHO2VB997FBWYTARRCPMF4SLA2NQJ6</variable>' +
								'<variable required name="action_class">{{sectionName}}</variable>' +
								'<variable required name="action">{{endpointName}}</variable>' +
							'</div>}</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'',
			link: function (scope, element, attrs, controllers) {
				scope.shown = false;
				scope.toggleShow = function() {
					scope.shown = !scope.shown;
				}
				scope.sectionName = controllers[0].sectionName;
				scope.endpointName = controllers[1].endpointName;
			}
		}
	},

	"commonvariables": function () {
		return {
			require: ["^?section", "^?endpoint"],
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@"
			},
			template: '<div>' +
					'<variable required name="session_id">20324</variable>' +
					'<variable required name="session_key">2SVHO2VB997FBWYTARRCPMF4SLA2NQJ6</variable>' +
					'<variable required name="action_class">{{sectionName}}</variable>' +
					'<variable required name="action">{{endpointName}}</variable>' +
			'</div>',
			link: function (scope, element, attrs, controllers) {
				scope.sectionName = controllers[0].sectionName;
				scope.endpointName = controllers[1].endpointName;
			}
		}
	},

	"variable": function () {
		return {
			restrict: "E",
			transclude: true,
			replace: true,
			scope: {
				name: "@"
			},
			template: '<div class="variable">"<span class="variablename">{{name}}</span>": "<span class="variablevalue" ng-transclude></span>"</div>'
		}
	},

	"faketable": function () {
		return {
			restrict: "A",
			link: function(scope, element) {
				element.addClass('faketable');
			}
		}
	},

	"fakeheaderrow": function () {
		return {
			restrict: "A",
			link: function(scope, element) {
				element.addClass('fakeheaderrow');
			}
		}
	},

	"fakerow": function () {
		return {
			restrict: "A",
			link: function(scope, element) {
				element.addClass('fakerow');
			}
		}
	},

	"required": function () {
		return {
			restrict: "A",
			link: function(scope, element) {
				element.addClass('required');
			}
		}
	},

	"help": function () {
		return {
			restrict: "A",
			link: function(scope, element, attrs) {
            	$(element).attr('title', attrs.help).tooltip({ position: { my: "left bottom+60", at: "left top"}, show: { duration: 50 }, hide: { duration: 50 } });
            }
		}
	}
}

app.directive(directives);