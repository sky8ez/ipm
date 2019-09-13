// public/js/services/customerService.js

angular.module('listService', [])

.factory('List', function($http) {

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

        // destroy a data
        destroy : function(url,id) {
            return $http.delete(url +  '/' + id);
        },

        // destroy a data
        destroyMany : function(url,data) {
          return $http({
              method: 'delete',
              url: url,
              headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
              data: $.param(data)
          });
        },

        // get filter a data (pass in data)
        getFilter : function(form_id) {
            return $http.get('api/table-filter/' + form_id);
        },

        // set filter a data (pass in data)
        setFilter : function(data) {
            return $http({
                method: 'POST',
                url: "api/table-filter",
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
                data: $.param(data)
            });
        },

        // destroy filter a data
        destroyFilter : function(id) {
            return $http({
                method: 'DELETE',
                url: "api/table-filter/" + id,
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        }

    }

});
