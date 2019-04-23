"use strict";

var app = angular.module('app', ['ngResource','ngCookies','ngSanitize','ngRoute','angularModalService','angularFileUpload']);
//var app = angular.module('app', ['ngResource','ngCookies','ngSanitize','ngRoute','angularModalService']);

app.config(['$resourceProvider', '$httpProvider', '$routeProvider', '$locationProvider',
    function ($resourceProvider, $httpProvider, $routeProvider, $locationProvider) {
        //$resourceProvider.defaults.stripTrailingSlashes = false;

        $httpProvider.defaults.transformRequest = function (data) {
            if (data === undefined) {
                return data;
            }
            return jQuery.param(data);
        };

        $routeProvider
                .when('/', {
                    templateUrl: localized.partials + 'eventList.html',
                    controller: 'eventListCtrl'
                })
                .when('/set_event_info', {
                    templateUrl: localized.partials + 'set_event_info.html',
                    controller: 'setEventInfoCtrl'
                })
                .when('/schedule', {
                    templateUrl: localized.partials + 'schedule_list.html',
                    controller: 'scheduleListCtrl'
                })
                .when('/set_schedule_info', {
                    templateUrl: localized.partials + 'set_schedule_info.html',
                    controller: 'setScheduleInfoCtrl'
                })
                .when('/tickets', {
                    templateUrl: localized.partials + 'ticket_list.html',
                    controller: 'ticketListCtrl'
                })
                .when('/ticket_info', {
                    templateUrl: localized.partials + 'ticket_info.html',
                    controller: 'setTicketInfoCtrl'
                })
                .when('/trainer_list', {
                    templateUrl: localized.partials + 'trainer_list.html',
                    controller: 'trainerListCtrl'
                })
                .when('/trainer_info', {
                    templateUrl: localized.partials + 'trainer_info.html',
                    controller: 'trainerInfoCtrl'
                })
                .when('/project_list', {
                    templateUrl: localized.partials + 'project_list.html',
                    controller: 'projectListCtrl'
                })
                .when('/project_info', {
                    templateUrl: localized.partials + 'project_info.html',
                    controller: 'projectInfoCtrl'
                })
                .when('/donor_list', {
                    templateUrl: localized.partials + 'donor_list.html',
                    controller: 'donorListCtrl'
                })
                .when('/donor_info', {
                    templateUrl: localized.partials + 'donor_info.html',
                    controller: 'donorInfoCtrl'
                })
                .when('/company_list', {
                    templateUrl: localized.partials + 'company_list.html',
                    controller: 'companyListCtrl'
                })
                .when('/company_info', {
                    templateUrl: localized.partials + 'company_info.html',
                    controller: 'companyInfoCtrl'
                })
                .when('/customer_list', {
                    templateUrl: localized.partials + 'customer_list.html',
                    controller: 'customerListCtrl'
                })
                .when('/customer_info', {
                    templateUrl: localized.partials + 'customer_info.html',
                    controller: 'customerInfoCtrl'
                })
                .otherwise({
                    redirectTo: '/'
                });

        $locationProvider.html5Mode(false);
        /*
         $locationProvider.html5Mode({
         enabled: true,
         requireBase: false
         });
         */
        $locationProvider.hashPrefix('');

    }]);

app.run(['$rootScope', '$location', 'authFact', function ($rootScope, $location, authFact) {
        /*
         $rootScope.$on('$locationChangeStart', function (event, next, current) {
         console.info("[event] locationChangeStart");
         console.log(next);
         
         // in case, that TO state is the one we want to redirect
         // get out of here   
         if (next.name === "onboard") {
         return;
         }
         if (next.$$route.autheticated) {
         if (!authFact.getToken()) {
         $location.path('/');
         }
         }
         });
         */

    }]);
//Controller
app.controller('setEventInfoCtrl', ['$scope', '$location','$compile', 'appService', function ($scope, $location, $compile, appService) {

        $scope.event = {
            i_event: 0,
            title: "",
            shortDesc: "",
            fullDesc: "",
            duration:[]
        };

        if (appService.currentEvent.i_event) {
            appService.getEventInfo(appService.currentEvent.i_event)
                    .then(function (response) {
                        if(response.data.duration && response.data.duration.constructor === Array) {
                            console.log(response.data.duration);
                            response.data.duration.forEach(function(val, i){
                                response.data.duration[i] = val.split("");
                                response.data.duration[i].forEach(function(value, j){
                                    response.data.duration[i][j] = (value === '1') ? true : false;
                                });
                            });
                        } else {
                            response.data.duration = [];
                        }
                        console.log(response.data);
                        $scope.event = response.data;
                        console.log($scope.event);
                    });
        }

        $scope.addEventSubmit = function (isValid) {
            console.log("FORM addEvForm is " + ((isValid) ? "valid" : "invalid"));
            
            console.log($scope.event.duration);
            
            if (isValid) {
                console.log($scope.event);
                appService.setEventInfo($scope.event).then(function (response) {
                    console.log(response);
                    $location.path('/');
                });
            }
        };
        $scope.checkWorkHour = function(event) {
             var tr = angular.element(event.currentTarget).parent().parent();
             var i = tr.parent().children().index(tr);
             $scope.event.duration[i].fill(true, 9, 18);
        };
        $scope.uncheckWorkHour = function(event) {
             var tr = angular.element(event.currentTarget).parent().parent();
             var i = tr.parent().children().index(tr);
             $scope.event.duration[i].fill(false);
        };
        $scope.addDay = function(event) {
            if ($scope.event.duration === null) {
                $scope.event.duration = [];
            }
            var i = $scope.event.duration.length;
            if(i === 0 ) {
                $scope.event.duration[0] = Array(24).fill(false);
            } else {
                $scope.event.duration[i] = $scope.event.duration[i-1].slice();
            }
            console.log($scope.event.duration);
        };
        $scope.delDay = function(event) {
            var i = $scope.event.duration.length;
            if(i > 0) {
                console.log(i);
                $scope.event.duration.splice (i-1, 1);
            }
        };
    }]);

app.controller('eventListCtrl', ['$scope', '$location', 'appService', 'ModalService', function ($scope, $location, appService, ModalService) {
        $scope.eventList = [];
        $scope.schedule = {
            duration: []
        };
        
        appService.getEventList().then(function (response) {
            for( var i in response.data ) {
                response.data[i].durationStr = appService.getTextDuration(response.data[i].duration);
            } 
            $scope.eventList = response.data;
        });

        $scope.showModal = function () {

            // Just provide a template url, a controller and call 'showModal'.
            ModalService.showModal({
                templateUrl: localized.partials + 'yesno.html',
                controller: "YesNoController"
            }).then(function (modal) {
                // The modal object has the element built, if this is a bootstrap modal
                // you can call 'modal' to show it, if it's a custom modal just show or hide
                // it as you need to.
                modal.element.modal();
                modal.close.then(function (result) {
                    $scope.message = result ? "You said Yes" : "You said No";
                });
            });
        };

        var modalOptions = {
            closeButtonText: 'Cancel',
            actionButtonText: 'Ignore Changes',
            headerText: 'Unsaved Changes',
            bodyText: 'You have unsaved changes. Leave the page?'
        };

        $scope.setEventInfo = function (i_event) {
            appService.currentEvent.i_event = i_event;
            $location.path('/set_event_info');
        };
        $scope.eventCalendar = function (i_event) {
            appService.currentEvent.i_event = i_event;
            $location.path('/schedule');
        };
        $scope.eventDelete = function (i_event) {
            appService.delEventInfo(i_event).then(function (response) {
                appService.getEventList().then(function (response) {
                    consoli.log(response)
                    $scope.eventList = response.data;
                });
            });
        };
        $scope.eventNew = function () {
            appService.currentEvent.i_event = null;
            $location.path('/set_event_info');
        }
    }]);

app.controller('scheduleListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.scheduleList = [];
        

        appService.getScheduleList(appService.currentEvent.i_event).then(function (response) {

            for( var i in response.data ) {
                response.data[i].durationStr = appService.getTextDuration(response.data[i].duration);
            } 

            $scope.scheduleList = response.data;
        });

        $scope.setSheduleInfo = function (i_schedule) {
            appService.currentSchedule.i_schedule = i_schedule;
            $location.path('/set_schedule_info');
        };
        $scope.scheduleTickets = function (i_schedule) {
            appService.currentSchedule.i_schedule = i_schedule;
            $location.path('/tickets');
        };
        $scope.delScheduleInfo = function (i_schedule) {
            appService.delScheduleInfo(i_schedule).then(function(response) {
                appService.getScheduleList(appService.currentEvent.i_event).then(function (response) {
                    $scope.scheduleList = response.data;
                });
            });
        };
        $scope.scheduleNew = function () {
            appService.currentSchedule.i_schedule = null;
            $location.path('/set_schedule_info');
        };
    }]);

app.controller('setScheduleInfoCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {
    $scope.schedule = {
        i_schedule: null,
        start_date: "",
        options: [],
        i_event: appService.currentEvent.i_event,
        title: "",
        duration: []
    };
    console.log(appService.currentSchedule);
    console.log(appService.currentEvent);

    if (appService.currentSchedule.i_schedule) {
        appService.getScheduleInfo(appService.currentSchedule.i_schedule)
                .then(function (response) {
                    console.log(response.data);
                    if (response.data.start_date) {
                        var t = response.data.start_date.split(/[- :]/);
                        response.data.start_date = new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4], t[5]));
                        console.log(response.data.start_date);
                    }
                    $scope.schedule = response.data;
                    $scope.durationText = appService.getTextDuration($scope.schedule.duration);
                });
    } else if (appService.currentEvent.i_event){
        appService.getEventInfo(appService.currentEvent.i_event)
            .then(function (response) {
                    console.log(response.data);
                    $scope.schedule.i_event = response.data.i_event;
                    $scope.schedule.title = response.data.title;
                    $scope.schedule.duration = response.data.duration;
                    $scope.schedule.start_date = new Date();
                    $scope.durationText = appService.getTextDuration($scope.schedule.duration);
                });
    }

    $scope.save = function () {
        appService.setScheduleInfo($scope.schedule).then(function (response) {
            console.log(response);
            $location.path("/schedule");
        });
    };
}]);

app.controller('ticketListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.ticketList = [];

        appService.getTicketList(appService.currentSchedule.i_schedule).then(function (response) {
            console.log(response.data);
            $scope.ticketList = response.data;
        });
        
        $scope.ticketAdd = function () {
            appService.currentTicket = {
                title: "",
                description: "",
                price: 0,
                quantity: 0,
                i_schedule: appService.currentSchedule.i_schedule,
                i_ticket: null,
            };
            $location.path("/ticket_info");
        };
        
        $scope.ticketEdit = function (i_ticket) {
            console.log("ticketEdit: " + i_ticket);
            appService.currentTicket = {
                title: "",
                description: "",
                price: 0,
                quantity: 0,
                i_schedule: appService.currentSchedule.i_schedule,
                i_ticket: i_ticket
            };
            $location.path('/ticket_info');
        };
        
        $scope.ticketDelete = function (i_ticket) {
            console.log("ticketDelete: " + i_ticket);
            appService.delTicketInfo(i_ticket).then(function (response) {
                console.log(response.data);
                $scope.ticketList = response.data;
            });
        };
    }]);

app.controller('setTicketInfoCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.ticket = appService.currentTicket;

        if ($scope.ticket.i_ticket) {
            console.log("i_ticket: " + $scope.ticket.i_ticket);
            appService.getTicketInfo($scope.ticket.i_ticket).then(function (response) {
                console.log(response.data);
                $scope.ticket = response.data;
            });
        }

        $scope.setTicketInfo = function () {
            appService.setTicketInfo($scope.ticket).then(function (response) {
                console.log(response.data);
                $location.path('/tickets')

            });
        };
    }]);

app.controller('trainerListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.trainerList = [];

        appService.getTrainerList().then(function (response) {
            console.log(response.data);
            $scope.trainerList = response.data;
        });

        $scope.addTrainer = function () {
            console.log("addTrainer");
            appService.currentTicket.i_trainer = null;
            $location.path('/trainer_info');
        }
        
        $scope.editTrainerInfo = function (i_trainer) {
            console.log("editTrainerInfo: " + i_trainer);
            appService.currentTrainer.i_trainer = i_trainer;
            $location.path('/trainer_info');
        }

        $scope.delTrainer = function (i_trainer) {
            appService.delTrainerInfo(i_trainer).then(function (response) {
                console.log(response)
                appService.getTrainerList().then(function (response) {
                    console.log(response)
                    $scope.trainerList = response.data;
                });
            });
        };
    }]);

app.controller('trainerInfoCtrl', ['$scope', '$location', '$http', 'appService', function ($scope, $location, $http, appService) {
        
        console.log("trainerInfoCtrl");

        $scope.trainer = {
            i_trainer: null,
            name: "",
            second_name: "",
            last_name: "",
            email: "",
            role: "",
            short_desc: "",
            full_desc: "",
            photo_url: ""
        };
        
        if (appService.currentTrainer.i_trainer) {
            console.log("trainerInfoCtrl " + appService.currentTrainer.i_trainer);
            appService.getTrainerInfo(appService.currentTrainer.i_trainer)
                    .then(function (response) {
                        console.log(response.data);
                        $scope.trainer = response.data[0];
                    });
        }
        $scope.setTrainerInfo = function () {
            appService.setTrainerInfo($scope.trainer).then(function (response) {
                console.log(response.data);
                $location.path('/trainer_list')

            });
        };
        $scope.addTrainerSubmit = function (isValid) {
            if (isValid) {
                console.log($scope.trainer);
                appService.setTrainerInfo($scope.trainer).then(function (response) {
                    console.log(response);
                    $location.path('/trainer_list');
                });
            }
        };
   }]);

app.controller('donorListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.donorList = [];

        appService.getDonorList().then(function (response) {
            console.log(response.data);
            $scope.donorList = response.data;
        });

        $scope.addDonor = function () {
            console.log("add Donor");
            appService.currentDonor.i_donor = null;
            $location.path('/donor_info');
        }

        $scope.delDonor = function (i_donor) {
            appService.delDonor(i_donor).then(function (response) {
                console.log(response)
                appService.getDonorList().then(function (response) {
                    console.log(response)
                    $scope.donorList = response.data;
                });
            });
        };
        
        $scope.showDonorInfo = function(i_donor) {
            console.log("showDonorInfo: " + i_donor);
            appService.currentDonor.i_donor = i_donor;
            $location.path('/donor_info');
        };
    }]);

app.controller('donorInfoCtrl', ['$scope', '$location', '$http', 'appService', function ($scope, $location, $http, appService) {
        
        console.log("donorInfoCtrl");

        $scope.donor = {
            i_donor: 0,
            title: "",
            short_desc: "",
            full_desc: ""
        };
        
        if (appService.currentDonor.i_donor) {
            console.log("donorInfoCtrl " + appService.currentProject.i_donor);
            appService.getDonorInfo(appService.currentDonor.i_donor)
                    .then(function (response) {
                        console.log(response.data);
                        $scope.donor = response.data[0];
                    });
        }

        $scope.setDonorInfo = function () {
            appService.setDonorInfo($scope.donor).then(function (response) {
                console.log(response.data);
                $location.path('/donor_list')

            });
        };

        $scope.addDonorSubmit = function (isValid) {
            if (isValid) {
                console.log($scope.donor);
                appService.setDonorInfo($scope.donor).then(function (response) {
                    console.log(response);
                    $location.path('/donor_list');
                });
            }
        };
   }]);

app.controller('projectListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.projectList = [];

        appService.getProjectList().then(function (response) {
            console.log(response.data);
            $scope.projectList = response.data;
        });

        $scope.addProject = function () {
            console.log("add Project");
            appService.currentProject.i_project = null;
            $location.path('/project_info');
        }
        $scope.showProjectInfo = function(i_project) {
            console.log("showProjectInfo: " + i_project);
            appService.currentProject.i_project = i_project;
            $location.path('/project_info');
        };
        $scope.delProject = function (i_project) {
            appService.delProject(i_project).then(function (response) {
                console.log(response)
                appService.getProjectList().then(function (response) {
                    console.log(response)
                    $scope.projectList = response.data;
                });
            });
        };
    }]);

app.controller('projectInfoCtrl', ['$scope', '$location', '$http', 'appService', 'FileUploader', function ($scope, $location, $http, appService, FileUploader) {
        $scope.selectedDonor = null;
        
        $scope.project = {
            i_project: null,
            title: "",
            start_date: null,
            end_date: null,
            short_desc: "",
            full_desc: ""
        };
        
        $scope.uploader = new FileUploader();
        
        appService.getDonorListByProject(appService.currentProject.i_project).then(function(response) {
                    $scope.assignedDonerList = response.data.assigned;
                    $scope.unassignedDonerList = response.data.unassigned;  
                });

        $scope.afterGetDonorList = function(response) {
            $scope.donorList = response.data;
            $scope.donorList.forEach(function(item) {
                if(item.i_donor === $scope.project.i_donor) {
                    $scope.selectedDonor = item;
                }
            });
        };
        $scope.afterGetProjectList = function(response) {
            if(response) {
                response.data[0]['start_date'] = new Date(response.data[0]['start_date']);
                response.data[0]['end_date'] = new Date(response.data[0]['end_date']);
                $scope.project = response.data[0];
            }
            appService.getDonorList().then( $scope.afterGetDonorList );
        };
        
        if (appService.currentProject.i_project) {
            appService.getProjectList(appService.currentProject.i_project)
                    .then( $scope.afterGetProjectList );
            
        } else {
            appService.getDonorList().then( $scope.afterGetDonorList );
        }
        
        $scope.setProjectInfo = function (params) {
            appService.setProjectInfo(params).then(function (response) {
                $location.path('/project_list');
            });
        };
        $scope.addDonor2Project = function(i_project, i_donor) {
            console.log("addDonor2Project " + i_project + " " + i_donor);
            appService.addDonor2Project(i_project, i_donor).then(function (response) {
                    $scope.assignedDonerList = response.data.assigned;
                    $scope.unassignedDonerList = response.data.unassigned;
                    appService.getDonorListByProject(appService.currentProject.i_project).then(function(response) {
                        $scope.assignedDonerList = response.data.assigned;
                        $scope.unassignedDonerList = response.data.unassigned;  
                    });
            });
        };
        $scope.delDonorFromProject = function(i_project, i_donor) {
            appService.delDonorFromProject(i_project, i_donor).then(function (response) {
                    $scope.assignedDonerList = response.data.assigned;
                    $scope.unassignedDonerList = response.data.unassigned;
                    appService.getDonorListByProject(appService.currentProject.i_project).then(function(response) {
                        $scope.assignedDonerList = response.data.assigned;
                        $scope.unassignedDonerList = response.data.unassigned;  
                    });
            });
        }
        $scope.addProjectSubmit = function (isValid) {

            if (isValid) {
                params = Object.assign({}, $scope.project); ;
                params.start_date = ($scope.project.start_date) ? $scope.project.start_date.getTime() : null;
                params.end_date = ($scope.project.end_date) ? $scope.project.end_date.getTime() : null;

                appService.setProjectInfo(params).then(function (response) {
                    $location.path('/project_list');
                });
            }
        };
        $scope.cancelChanges = function () {
            $location.path('/project_list');
        };
   }]);

app.controller('companyListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {

        $scope.companyList = [];

        appService.getCompanyList().then(function (response) {
            console.log(response.data);
            $scope.companyList = response.data;
        });

        $scope.addCompany = function () {
            console.log("add Company");
            appService.currentCompany.i_company = null;
            $location.path('/company_info');
        }

        $scope.delCompany = function (i_company) {
            appService.delCompany(i_company).then(function (response) {
                console.log(response)
                appService.getCompanyList().then(function (response) {
                    console.log(response)
                    $scope.companyList = response.data;
                });
            });
        };
        
        $scope.showCompanyInfo = function(i_company) {
            console.log("showCompanyInfo: " + i_company);
            appService.currentCompany.i_company = i_company;
            $location.path('/company_info');
        };
    }]);

app.controller('companyInfoCtrl', ['$scope', '$location', '$http', 'appService', function ($scope, $location, $http, appService) {
        
        console.log("companyInfoCtrl: " + appService.currentCompany.i_company );
        
        console.log( appService.currentCompany );

        $scope.company = Object.assign({}, appService.currentCompany);
        
        if (appService.currentCompany.i_company) {
            console.log("getCompanyInfo");
            appService.getCompanyInfo(appService.currentCompany.i_company)
                .then(function (response) {
                    console.log(response.data);
                    $scope.company = response.data[0];
                });
        };
        
        $scope.setCompanyInfo = function () {
            console.log("setCompanyInfo");
            console.log($scope.company);
            appService.setCompanyInfo($scope.company).then(function (response) {
                console.log(response.data);
                $location.path('/company_list')

            });
        };
        
        $scope.setAddress = function () {
            appService.setAddress($scope.company).then(function (response) {
                console.log(response.data);
            });
        };  

        $scope.addCompanySubmit = function (isValid) {
            if (isValid) {
                console.log($scope.company);
                appService.setCompanyInfo($scope.company).then(function (response) {
                    console.log(response);
                    $location.path('/company_list');
                });
            }
        };
   }]);

app.controller('customerListCtrl', ['$scope', '$location', 'appService', function ($scope, $location, appService) {
        $scope.customerList = [];
        
        appService.getCustomerList().then(function (response) {
            console.log(response.data);
            $scope.customerList = response.data;
        });

        $scope.showCustomerInfo = function(i_customer) {
            console.log("showCustomerInfo: " + i_customer);
            appService.currentCustomer.i_customer = i_customer;
            $location.path('/customer_info');
        };
        $scope.addCustomer = function() {
            appService.currentCustomer.i_customer = null;
            $location.path('/customer_info');
        };
        $scope.delCustomer = function(i_customer) {
            console.log("delCustomer:" + i_customer);
            appService.delCustomer(i_customer).then(function(response){
                console.log(response.data);
                $scope.customerList = response.data;
            });
        };
    }]);

app.controller('customerInfoCtrl', ['$scope', '$location', '$http', 'appService', function ($scope, $location, $http, appService) {
        $scope.customer = {
            i_customer: null,
            first_name: "",
            second_name: "",
            las_name: "",
            email: "",
        };
        $scope.companyList = [];

        appService.getCompanyList().then(function (response) {
            console.log(response.data);
            $scope.companyList = response.data;
        });
        
        if(appService.currentCustomer.i_customer !== null) { 
            appService.getCustomerList(appService.currentCustomer.i_customer).then(function (response) {
                console.log(response.data);
                $scope.customer = response.data[0];

                if($scope.customer.i_company === null) {
                    $scope.customer.i_company = 0;
                }
            });
        } else {
            $scope.customer.sex = "M";
            $scope.customer.i_company = 0;
        }
        
        $scope.submitCustomerInfo = function() {
            console.log("submitCustomerInfo");
            console.log($scope.customer);
            appService.submitCustomerInfo($scope.customer).then(function(response){
                console.log(response);
                $location.path('/customer_list');
            });
        };
        
        

   }]);

app.controller('YesNoController', ['$scope', 'close', function ($scope, close) {

        $scope.close = function (result) {
            close(result, 500); // close, but give 500ms for bootstrap to animate
        };

    }]);

/**
 * 
 * DIORECTIVE
 * 
 */
app.directive('angularte', function () {
    return {
        restrict: 'A',
        require: '^ngModel',
        link: function (scope, element, attrs, ngModel) {
            jQuery(function () {
                element.jqte({
                    // On focus show the toolbar
                    focus: function () {
                        if (!scope.$$phase) {
                            scope.$apply(function () {
                                element.parents(".jqte").find(".jqte_toolbar").show();
                                element.parents(".jqte").click(function () {
                                    element.parents(".jqte").find(".jqte_toolbar").show();
                                });
                            });
                        }
                    },
                    // On blur hide the toolar
                    blur: function () {
                        if (!scope.$$phase) {
                            scope.$apply(function () {
                                element.parents(".jqte").find(".jqte_toolbar").hide();
                            });
                        }
                    },
                    // On change refresh the model with the textarea value
                    change: function () {
                        if (!scope.$$phase) {
                            scope.$apply(function () {
                                ngModel.$setViewValue(element.parents(".jqte").find(".jqte_editor")[0].innerHTML);
                            });
                        }
                    }
                });
                element.parents(".jqte").find(".jqte_toolbar").hide();
            });

            // On render refresh the textarea with the model value 
            ngModel.$render = function () {
                element.parents(".jqte").find(".jqte_editor")[0].innerHTML = ngModel.$viewValue || '';
            };
        }
    };
});
app.directive('scheduleRow', function () {
    return {
        restrict: 'C',
        replace: true,
        templateUrl: localized.partials + 'scheduleRow.html',
        scope: {
            row: "=",
            checkWorkHour: "&",
            uncheckWorkHour: "&"
        }
    };
});

