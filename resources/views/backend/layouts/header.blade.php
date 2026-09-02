<style>
  .app-admin-wrap {
    /* margin-top: -30px; */
}
.text-red{
  color: red!important;
}
.text-success{
  color: green!important;
}
.fontcolor-error{
    color: crimson;
    font-weight: bold;
}

.uperletter {
  text-transform: capitalize;
}

.main-header,
.layout-sidebar-large .main-header {
  top: 0;
}

  
.select2-search__field, .select2-results__options{
  text-transform: capitalize;
}

.sidebar-left-secondary .childNav li.nav-item.open > .submenu{
  max-height: 2000px!important;
}

</style>

      <div class="main-header">
        <div class="logo">
          <img src="{{url('assets/backend')}}/images/header-logo.png" alt="" />
        </div>
        <div class="menu-toggle">
          <div></div>
          <div></div>
          <div></div>
        </div>
        @role('Admin')
        <div class="col-md-3 align-items-center ">         
          <!-- Mega menu -->
          <label for="lastName1">Please Select Scholar:</label>
          <select id="inq-form-nomenu" class="form-control uperletter select2" onchange="getValAndAssign(event);" name="inq_form_selection" required>
                <option selected></option>
                <?php $student_data = app('global_areas');
                // print_r($student_data);              
                
                ?>
                @if (!empty($student_data))
                           
                @foreach ($student_data as $each)
                @if ($each->type != 't')
                <option value="{{ $each->id }}">
                    {{ $each->student_name }}

                    @if ($each->form_number)
                    - {{ $each->form_number }}
                    @endif

                    <?php
                    $jsondata = json_decode($each->json_str);
                    ?>
                    @if ($each->json_str)
                    @if (isset($jsondata->is_staff_applied_for_admission) && $jsondata->is_staff_applied_for_admission != '')
                    - Staff
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif
                    @else
                    @if ($each->application_for == 'RTE')
                    - RTE
                    @else
                    - Non RTE
                    @endif
                    @endif

                    @if ($each->json_str)
                    @if (isset($jsondata->siblings_name) && $jsondata->siblings_name != '')
                    - Sibling
                    @endif
                    @endif
                </option>
                @endif
                @endforeach
                @endif
            </select>
          <!-- / Mega menu -->
        </div>
		@endrole


        <div style="margin: auto"></div>

        <div class="col-md-1 form-group mb-3">
          <div class="form-outline w-auto">
              <label class="form-label" for="form1"></label>
              <?php 
                $currentYear = date('Y');
                $nextYear = date('Y') + 1;
                // $currentSchoolYear = $currentYear . '_' . $nextYear;
                $currentSchoolYear = session('db_names');
                // if (!empty($currentSchoolYear)){
                //   unset($_COOKIE['selectedYear']); 
                //   setcookie("selectedYear", "", -1, '/');
                //   Session::put('db_names',null);
                // }
                // echo $currentSchoolYear; // Outputs something like "2023_2024"
              ?>
              <!-- <input type="date" name="student_dob" class="form-control" id="student_dob" placeholder="Enter Student DOB"/> -->
              <select name="year" id="year" class="form-control" >
                  <!-- <option value="select"> - Year - </option> -->
                  @foreach($databaseNames as $databaseName)
                    @if (is_numeric(substr($databaseName, 0, 1)))
                      <?php  
                        if($currentSchoolYear == $databaseName){ 
                          ?>
                          <option selected value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                      <?php } else { ?>
                        <option value=<?php echo $databaseName; ?> ><?php echo $databaseName; ?></option>
                      <?php } ?>
                    @endif
                  @endforeach
              </select>

              <script>
                // function setCookie(name, value, days) {
                //     var expires = "";
                //     if (days) {
                //         var date = new Date();
                //         date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                //         expires = "; expires=" + date.toUTCString();
                //     }
                //     document.cookie = name + "=" + value + expires + "; path=/";
                //     alert(document.cookie);
                // }

                // Function to get a cookie value
                // function getCookie(name) {
                //     var nameEQ = name + "=";
                //     var ca = document.cookie.split(';');
                //     for (var i = 0; i < ca.length; i++) {
                //         var c = ca[i];
                //         while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                //         if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                //     }
                //     return null;
                // }

                // Function to handle the change event of the select element
                document.getElementById('year').addEventListener('change', function () {
                    setCookie('selectedYear', this.value, 1); // Store the selected value in a cookie for 1 year
                    window.location.reload(); // Reload the page
                    
                });

                // Function to set the selected option based on the cookie value
                window.addEventListener('load', function () {
                    var selectedYear = getCookie('selectedYear');
                    if (selectedYear) {
                        document.getElementById('year').value = selectedYear;
                    }
                    if(selectedYear == null){
                      setCookie('selectedYear', document.getElementById('year').value, 1);
                      // alert(document.getElementById('year').value);
                    }
                    
                });

              </script>
          </div>
      </div>


        <div class="header-part-right">

          <!-- Full screen toggle -->
          <i
            class="i-Full-Screen header-icon d-none d-sm-inline-block"
            data-fullscreen
          ></i>
		  
          <!-- User avatar dropdown -->
          <div class="dropdown">
            <div class="user col align-self-end">
              <img
                src="{{url('assets/backend')}}/images/faces/avatar-lvn.jpeg"
                id="userDropdown"
                alt=""
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              />
              <div
                class="dropdown-menu dropdown-menu-right"
                aria-labelledby="userDropdown"
              >
                <div class="dropdown-header">
                  <i class="i-Lock-User me-1"></i> {{ optional(Auth::user())->student_name ?? optional(Auth::user())->name ?? 'User' }}
                </div>
                <!-- <a class="dropdown-item" href="signin.html">Sign out</a> -->

                 <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                      {{ __('Logout') }}
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
              </div>
            </div>
          </div>
        </div>
      </div>
