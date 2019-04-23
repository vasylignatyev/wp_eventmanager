app.service('appService', ['$http', function ($http) {
        var appService = {
            token: 'qwerty'
        };

        this.currentEvent = {
            i_event: null,
            title: "",
            short_desc: "",
            full_desc: "",
            duration: 1
        };
        this.currentSchedule = {
            i_schedule: null,
            start_date: ""
        }
        this.currentTicket = {
            title: "",
            description: "",
            price: 0,
            quantity: 0,
            i_schedule: null,
            i_ticket: null
        };
        this.currentTrainer = {
            name: "",
            second_name: "",
            last_name: "",
            email: "",
            short_desc: "",
            long_desc: "",
            i_trainer: null
        };
        this.currentDonor = {
            title: "",
            short_desc: "",
            full_desc: "",
            i_donor: null
        };
        this.currentProject = {
            title: "",
            short_desc: "",
            full_desc: "",
            start_date: null,
            end_date: null,
            i_donor: null,
            i_project: null
        };
        this.currentCompany = {
            i_company: null,
            title: "",
            description: "",
            options: null,
            i_address: null,
            zip: null,
            country: null,
            region: null,
            locality: null,
            street: null,
            office: null
        };
        this.currentCustomer = {
            i_customer: null,
        };


        this.getCurrentEvent = function () {
            return currentEvent;
        };

        var that = this;

        var postConfig = {
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            transformRequest: function (data) {
                return jQuery.param(data);
            }
        };
        
        var filePostConfig = {
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            transformRequest: function (data) {
                return jQuery.param(data);
            }
        };
        
        /************************ Event Services ************************/
        this.getEventList = function () {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                {
                    functionName: 'getEventList',
                    token: appService.token
                }, postConfig);
            return promise;
        };
        this.setEventInfo = function (event) {
            var duration = "";
            event.duration.forEach(function(row, i){
                row.forEach(function(item){
                    duration += (item === true) ? "1" : "0";
                });
            });
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'setEventInfo',
                        token: appService.token,
                        i_event: event.i_event,
                        duration: duration,
                        title: event.title,
                        short_desc: event.short_desc,
                        full_desc: event.full_desc,
                        options: event.options
                    }, postConfig);
            return promise;
        };
        this.getEventInfo = function (i_event) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'getEventInfo',
                        token: appService.token,
                        i_event: i_event
                    }, postConfig);
            return promise;
        };
        this.delEventInfo = function (i_event) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delEventInfo',
                        token: appService.token,
                        i_event: i_event
                    }, postConfig);
            return promise;
        };
        /************************ Schedule Services ************************/
        this.getScheduleList = function (i_event) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getScheduleList',
                        token: appService.token,
                        i_event: i_event
                    }, postConfig);
            return promise;
        };
        this.getScheduleInfo = function (i_schedule) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getScheduleInfo',
                        token: appService.token,
                        i_schedule: i_schedule
                    }, postConfig);
            return promise;
        };
        this.setScheduleInfo = function (schedule) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'setScheduleInfo',
                        token: appService.token,
                        i_schedule: schedule.i_schedule,
                        i_event: schedule.i_event,
                        start_date: schedule.start_date.toISOString()
                    }, postConfig);
            return promise;
        };
        this.delScheduleInfo = function (i_schedule) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'delScheduleInfo',
                        token: appService.token,
                        i_schedule: i_schedule,
                    }, postConfig);
            return promise;
        };
        /************************ Trainer Services ************************/
        this.getTrainerList = function () {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getTrainerList',
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.getTrainerInfo = function (i_trainer) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getTrainerList',
                        i_trainer: i_trainer,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.setTrainerInfo = function (trainer) {
            params = {
                        functionName: 'setTrainerInfo',
                        token: appService.token,
                        name: trainer.name,
                        second_name: trainer.second_name,
                        last_name: trainer.last_name,
                        email: trainer.email,
                        short_desc: trainer.short_desc,
                        full_desc: trainer.full_desc,
                        i_trainer: trainer.i_trainer
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delTrainerInfo = function (i_trainer) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delTrainerInfo',
                        token: appService.token,
                        i_trainer: i_trainer
                    }, postConfig);
            return promise;
        };
        /************************ Ticket Services ************************/
        this.getTicketList = function (i_schedule) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getTicketList',
                        i_schedule: i_schedule,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.getTicketInfo = function (i_ticket) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getTicketInfo',
                        i_ticket: i_ticket,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.setTicketInfo = function (ticket) {
            console.log("setTicketInfo");
            console.log(ticket);
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'setTicketInfo',
                        token: appService.token,
                        title: ticket.title,
                        description: ticket.description,
                        price: ticket.price,
                        quantity: ticket.quantity,
                        i_schedule: ticket.i_schedule,
                        i_event: ticket.i_event,
                        i_ticket: ticket.i_ticket,
                        ordered: ticket.ordered,
                    }, postConfig);
            return promise;
        };
        this.delTicketInfo = function (i_ticket) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'delTicketInfo',
                        i_ticket: i_ticket,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        /************************ Donor Services ************************/
        this.getDonorList = function () {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getDonorList',
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.getDonorInfo = function (i_donor) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getDonorList',
                        i_donor: i_donor,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.setDonorInfo = function (donor) {
            params = {
                        functionName: 'setDonorInfo',
                        token: appService.token,
                        title: donor.title,
                        tagline: donor.tagline,
                        country: donor.country,
                        short_desc: donor.short_desc,
                        full_desc: donor.full_desc,
                        i_donor: donor.i_donor
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.getDonorListByProject = function(i_project) {
            params = {
                        functionName: 'getDonorListByProject',
                        token: appService.token,
                        i_project: i_project
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.addDonor2Project = function(i_project, i_donor) {
            params = {
                        functionName: 'addDonor2Project',
                        token: appService.token,
                        i_project: i_project,
                        i_donor: i_donor
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delDonorFromProject = function(i_project, i_donor) {
            params = {
                        functionName: 'delDonorFromProject',
                        token: appService.token,
                        i_project: i_project,
                        i_donor: i_donor
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delDonor = function (i_donor) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delDonor',
                        token: appService.token,
                        i_donor: i_donor
                    }, postConfig);
            return promise;
        };
        /************************ Project Services ************************/
        this.getProjectList = function (i_project = null) {
            params = {};
            params.functionName = 'getProjectList',
            params.token = appService.token
            if(i_project) {
                params.i_project = i_project;
            }
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.setProjectInfo = function (project) {
            params = {
                        functionName: 'setProjectInfo',
                        token: appService.token,
                        title: project.title,
                        short_desc: project.short_desc,
                        full_desc: project.full_desc,
                        start_date: project.start_date,
                        end_date: project.end_date,
                        i_project: project.i_project
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delProject = function (i_project) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delProject',
                        token: appService.token,
                        i_project: i_project
                    }, postConfig);
            return promise;
        };
        /************************ Project Services *********************
        this.getProjectList = function (i_project = null) {
            params = {};
            params.functionName = 'getProjectList',
            params.token = appService.token
            if(i_project) {
                params.i_project = i_project;
            }
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.setProjectInfo = function (project) {
            console.log(project);
            params = {
                        functionName: 'setProjectInfo',
                        token: appService.token,
                        title: project.title,
                        short_desc: project.short_desc,
                        full_desc: project.full_desc,
                        start_date: project.start_date,
                        end_date: project.end_date,
                        i_project: project.i_project
                    };
            console.log(params);
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delProject = function (i_project) {
            console.log(this);
            
            //if( typeof i_project !== 'number') {
            //    return false;
            //}
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delProject',
                        token: appService.token,
                        i_project: i_project
                    }, postConfig);
            return promise;
        };
        */
        /************************ Company Services ************************/
        this.getCompanyList = function () {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getCompanyList',
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.getCompanyInfo = function (i_company) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getCompanyList',
                        i_company: i_company,
                        token: appService.token
                    }, postConfig);
            return promise;
        };
        this.setCompanyInfo = function (request) {
            request.functionName = 'setCompanyInfo';
            request.token = appService.token;

            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    request, postConfig);
            return promise;
        };
        this.getCompanyListByProject = function(i_project) {
            params = {
                        functionName: 'getCompanyListByProject',
                        token: appService.token,
                        i_project: i_project
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.addCompany2Project = function(i_project, i_company) {
            params = {
                        functionName: 'addCompany2Project',
                        token: appService.token,
                        i_project: i_project,
                        i_company: i_company
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delCompanyFromProject = function(i_project, i_company) {
            params = {
                        functionName: 'delCompanyFromProject',
                        token: appService.token,
                        i_project: i_project,
                        i_company: i_company
                    };
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    params, postConfig);
            return promise;
        };
        this.delCompany = function (i_company) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delCompany',
                        token: appService.token,
                        i_company: i_company
                    }, postConfig);
            return promise;
        };
        /************************ Address Services ************************/
        this.setAddress = function(address) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'setAddress',
                        token: appService.token,
                        i_address: address.i_address,
                        zip: address.zip,
                        country: address.country,
                        region: address.region,
                        locality: address.locality,
                        street: address.street,
                        ofice: address.office
                        
                    }, postConfig);
            return promise;
        };
        /************************ Customer Services ************************/
        this.getCustomerList = function (i_customer = null) {
            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    {
                        functionName: 'getCustomerList',
                        token: appService.token,
                        i_customer: i_customer
                    }, postConfig);
            return promise;
        };
        this.submitCustomerInfo = function (request) {
            request.functionName = 'submitCustomerInfo';
            request.token = appService.token;

            var promise = $http.post('/wp-content/themes/angular-bootstrap/Controller/ajax.php',
                    request, postConfig);
            return promise;
        };
        this.delCustomer = function (i_customer) {
            var promise = $http.post("/wp-content/themes/angular-bootstrap/Controller/ajax.php",
                    {
                        functionName: 'delCustomer',
                        token: appService.token,
                        i_customer: i_customer
                    }, postConfig);
            return promise;
        };
        
        
        /********************************************************************/
        this.getTextDuration = function(duration){
            var hours = 0;
            if (duration) {
                //$scope.schedule.duration.forEach(function(item, i){
                duration.forEach(function(item, i){
                    item.split("").forEach(function(hour){
                        hours += parseInt(hour);
                    });
                });
                return duration.length + " д. (" + hours + " г.)";
            }
            return "";
        };
}]);

        


/*******************************************************************************/
app.factory('authFact', ['$cookieStore', function ($cookieStore) {
        var authFact = {
            setToken: function (token) {
                $cookieStore.put('token', token);
                //this.token = token;
            },
            getToken: function () {
                var token = $cookieStore.get('token');
                console.log('getToken: ' + token);
                return $cookieStore.get('token');
            }
        };
        return authFact;
    }]);
