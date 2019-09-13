// public/js/services/customerService.js

angular.module('editorService', [])

.factory('Editor', function($http) {

    return {
        // get all the datas
        get : function(url) {
            return $http.get(url);
        },

        // save a data (pass in data)
        save : function(url,data) {
            return $http({
                method: 'POST',
                url: url,
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
                data: $.param(data)
            });
        },

        // save a data (pass in data)
        update : function(url,data) {
            return $http({
                method: 'PUT',
                url: url,
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
                data: $.param(data)
            });
        },


        // destroy a data
        destroy : function(url,id) {
            return $http.delete(url + id);
        },

    }

});
