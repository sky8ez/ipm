<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('css/app.css') }}" rel="stylesheet">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('css/jquery-ui-1.9.2.custom.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('css/jquery-ui.theme.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('css/font-awesome.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/datatables/datatables.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/angular/angular-material.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/dragtable/dragtable.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/ngtable/ng-table.min.css') }}">
    <!-- <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/bootstrap-datepicker/css/datetimepicker.css') }}"> -->
    <!-- <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/easyautocomplete/easy-autocomplete.min.css') }}"> -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('css/style.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/tablesorter/pager/jquery.tablesorter.pager.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/dragtable/dragtable.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}">

    <!-- slick grid -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/slick/slick.grid.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/slick/slick-default-theme.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ URL::asset('plugins/slick/totalsPlugin/TotalsPlugin.css') }}">
    <!-- slick grid -->

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <!-- <script src="{{ asset('js/jquery.history.js') }}"></script> -->

    <script src="{{ asset('js/angular.min.js') }}"></script>
    <script src="{{ asset('plugins/angular/angular-animate.min.js') }}"></script>
    <script src="{{ asset('plugins/angular/angular-aria.min.js') }}"></script>
    <script src="{{ asset('plugins/angular/angular-material.min.js') }}"></script>
    <script src="{{ asset('plugins/angular/angular-sanitize.min.js') }}"></script>
    <script src="{{ asset('plugins/angular/angular-messages.min.js') }}"></script>
    <script src="{{ asset('js/angular-route.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('plugins/colResizeable/colResizable-1.6.min.js') }}"></script>
    <script src="{{ asset('plugins/upload/ng-file-upload-shim.min.js') }}"></script>
    <script src="{{ asset('plugins/upload/ng-file-upload.min.js') }}"></script>
    <!-- <script src="{{ asset('plugins/sortable/sortable.min.js') }}"></script> -->

    <script src="{{ asset('plugins/table-export/tableExport.js') }}"></script>
    <script src="{{ asset('plugins/table-export/jquery.base64.js') }}"></script>
    <script src="{{ asset('plugins/table-export/jspdf/libs/sprintf.js') }}"></script>
    <script src="{{ asset('plugins/table-export/jspdf/jspdf.js') }}"></script>
    <script src="{{ asset('plugins/table-export/jspdf/libs/base64.js') }}"></script>
    <script src="{{ asset('plugins/ngtable/ng-table.min.js') }}"></script>
    <script src="{{ asset('plugins/angular-dragtable/dragtable.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>

    <script src="{{ asset('plugins/chart/Chart.min.js') }}"></script>
    <script src="{{ asset('plugins/chart/angular-chart.min.js') }}"></script>

    <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/html2canvas.min.js') }}"></script>
    <!-- <script src="{{ asset('plugins/virtual-table/angular-virtual-scroll.min.js') }}"></script> -->

    <!-- slick grid -->
    <script src="{{ asset('plugins/slick/jquery.event.drag-2.3.0.js') }}"></script>
    <script src="{{ asset('plugins/slick/slick.core.js') }}"></script>
    <script src="{{ asset('plugins/slick/slick.formatters.js') }}"></script>
    <script src="{{ asset('plugins/slick/slick.grid.js') }}"></script>
    <script src="{{ asset('plugins/slick/slick.dataview.js') }}"></script>
    <script src="{{ asset('plugins/slick/totalsPlugin/TotalsDataView.js') }}"></script>
    <script src="{{ asset('plugins/slick/totalsPlugin/TotalsPlugin.js') }}"></script>
    <!-- <script src="{{ asset('js/excel/js/require.js') }}"></script>
    <script src="{{ asset('js/excel/js/underscore.js') }}"></script>
    <script src="{{ asset('js/excel/jquery.slickgrid.export.excel.js') }}"></script> -->
    <!-- slick grid -->


    <!-- <script src="{{ asset('plugins/bootstrap-datepicker/js/datetimepicker.js') }}"></script> -->
    <!-- <script src="{{ asset('plugins/bootstrap-datepicker/js/datetimepicker.templates.js') }}"></script> -->
    <!-- <script src="{{ asset('plugins/easyautocomplete/jquery.easy-autocomplete.min.js') }}"></script> -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>
<?php $access_list = []; ?>
@if((count(Session::get('user_access')) > 0))
  @foreach (Session::get('user_access') as $access)
    @if ($access->condition === 'nav' && $access->cond_flag == 1)
        <?php array_push($access_list, $access->module_id); ?>
    @endif
  @endforeach
@endif

<body  ng-app="erpApp">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a style="margin-right:40px" class="navbar-brand" href="{{ url('#/') }}">
                    {{ config('app.name', 'Laravel') }}<br>
                    <b><span style="font-size:10px;margin-top:-10px;position:absolute">Password Management</span></b>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                      <li class="dropdown">
                          <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                              Master <span class="caret"></span>
                          </a>
                          <ul class="dropdown-menu" role="menu">
                              @if (in_array("PASSWORD", $access_list))
                              <li>
                                  <a href="{{ url('#/list/password') }}">
                                      Password
                                  </a>
                              </li>
                              @endif
                              @if (in_array("PASSWORD-CATEGORY", $access_list))
                              <li>
                                  <a href="{{ url('#/list/password-category') }}">
                                      Password Category
                                  </a>
                              </li>
                              @endif
                                @if (in_array("PRINT", $access_list))
                              <li>
                                  <a href="{{ url('#/list/print-template') }}">
                                      Print Template
                                  </a>
                              </li>
                                @endif
                                @if (in_array("FILE", $access_list))
                              <li>
                                  <a href="{{ url('#/list/file-manager') }}">
                                      File Manager
                                  </a>
                              </li>
                                @endif
                                @if (in_array("ACTIVITY", $access_list))
                              <li>
                                  <a href="{{ url('#/list/activity') }}">
                                      Activity Log
                                  </a>
                              </li>
                                @endif

                          </ul>
                      </li>
                      <li class="dropdown">
                          <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                              Transaction <span class="caret"></span>
                          </a>
                          <ul class="dropdown-menu" role="menu">
                              @if (in_array("PASSWORD-LIST", $access_list))
                              <li>
                                  <a href="{{ url('#/list/password-list') }}">
                                      Password List
                                  </a>
                              </li>
                              @endif
                          </ul>
                      </li>

                      <!-- @if (in_array("REPORTS", $access_list))
                      <li class="dropdown">
                          <a href="#/reports" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                             <i class="fa fa-line-chart"></i>  Reports
                          </a>
                      </li>
                      @endif -->
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li><a href="{{ url('/register') }}">Register</a></li>
                    @else
                        <li>
                          <!-- <div class="input-group" style="margin-top:10px;max-width:200px">
                            <input  type="text" class="form-control input-sm" placeholder="Search.." name="name" value="">
                            <span class="input-group-btn">
                              <button class="btn btn-secondary btn-sm btn-primary" type="button"><i class="fa fa-search"></i></button>
                            </span>
                          </div> -->

                        </li>

                        @if (session()->get('role') == "admin")
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                               <i class="fa fa-gear"></i>  Settings <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="{{ url('#/general') }}">
                                        General
                                    </a>
                                    <a href="{{ url('#/list/user') }}">
                                        Users
                                    </a>
                                    <a href="{{ url('#/list/user-access') }}">
                                        User Access
                                    </a>
                                    @if (in_array("IMPORT-EXPORT", $access_list))
                                    <a href="{{ url('#/tools/import-export') }}">
                                        Import / Export Data
                                    </a>
                                    @endif
                                </li>
                            </ul>
                        </li>
                        @endif
                        <!-- <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                               <i class="fa fa-envelope"></i>
                            </a>
                        </li> -->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                              <i class=" fa fa-user"></i>  {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li ng-controller="ReloadController">
                                    <a ng-click="reloadPage()">
                                        Reload
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('#/profile') }}">
                                        Profile
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('/logout') }}"
                                        onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>


    <!-- @yield('content') -->
    <div ng-view></div>

    <!-- <div class="footer navbar-fixed-bottom" style="background-color:#D9D9D9;padding:3px;font-size:12px">
      <div class="container">
        <div class="row" style="text-align:right">
          <b>Current Period : {{Session::get('period')}} </b>
        </div>

      </div>

    </div> -->

    <!-- Scripts -->
    <script src="/js/bootstrap.min.js"></script>
    <!-- <script src="{{ asset('js/validator.min.js') }}"></script> -->
    <script src="{{ asset('js/bootbox.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui-1.9.2.custom.min.js') }}"></script>
    <script src="{{ asset('plugins/dragtable/jquery.dragtable.js') }}"></script>
    <script src="{{ asset('plugins/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('plugins/tablesorter/pager/jquery.tablesorter.pager.js') }}"></script>
    <!-- Scripts Angular -->
    <script src="{{ asset('js/controllers/master/listCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/formCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/editorCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/printCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/floatingFormCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/searchCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/auditCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/printDetailCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/detailCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/reportCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/reportViewCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/reportSlickViewCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/dashboardCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/generalCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/importCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/modules/displayCtrl.js') }}"></script>
    <script src="{{ asset('js/controllers/master/profilCtrl.js') }}"></script>
    <script src="{{ asset('js/services/listservice.js') }}"></script>
    <script src="{{ asset('js/services/formservice.js') }}"></script>
    <script src="{{ asset('js/services/editorservice.js') }}"></script>

</body>
</html>
