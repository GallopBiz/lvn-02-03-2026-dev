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

.lvn-notification-root {
  position: relative;
  margin-right: 18px;
}

.lvn-notification-bell {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  padding: 0;
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
}

.lvn-notification-bell:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

.lvn-notification-badge {
  position: absolute;
  top: -3px;
  right: -4px;
  min-width: 18px;
  padding: 2px 5px;
  border-radius: 10px;
  background: #dc3545;
  color: #fff;
  font-size: 10px;
  line-height: 14px;
  text-align: center;
}

.lvn-notification-panel {
  position: absolute;
  z-index: 1050;
  top: 42px;
  right: -12px;
  width: min(360px, calc(100vw - 24px));
  max-height: 420px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
}

.lvn-notification-panel[hidden] {
  display: none;
}

.lvn-notification-heading {
  padding: 12px 14px;
  border-bottom: 1px solid #e9ecef;
  color: #343a40;
  font-weight: 600;
}

.lvn-notification-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  width: 100%;
  padding: 12px 14px;
  border-bottom: 1px solid #f0f1f2;
  background: #fff;
  color: #343a40;
}

.lvn-notification-main {
  flex: 1;
  min-width: 0;
  padding: 0;
  border: 0;
  background: transparent;
  border-bottom: 1px solid #f0f1f2;
  color: #343a40;
  text-align: left;
  cursor: pointer;
}

.lvn-notification-main:hover,
.lvn-notification-main:focus-visible {
  background: #f8f9fa;
}

.lvn-notification-item.lvn-notification-unread {
  border-left: 3px solid #007bff;
  background: #eef6ff;
}

.lvn-notification-hide {
  flex: 0 0 auto;
  padding: 2px 0;
  border: 0;
  background: transparent;
  color: #6c757d;
  font-size: 12px;
  cursor: pointer;
}

.lvn-notification-hide:hover,
.lvn-notification-hide:focus-visible {
  color: #343a40;
  text-decoration: underline;
}

.lvn-notification-message {
  display: block;
  font-size: 13px;
  line-height: 1.4;
}

.lvn-notification-meta {
  display: block;
  margin-top: 4px;
  color: #6c757d;
  font-size: 11px;
}

.lvn-notification-empty,
.lvn-notification-loading {
  padding: 20px 14px;
  color: #6c757d;
  font-size: 13px;
  text-align: center;
}

@media (max-width: 576px) {
  .lvn-notification-root {
    margin-right: 8px;
  }

  .lvn-notification-panel {
    position: fixed;
    top: 56px;
    right: 12px;
    left: 12px;
    width: auto;
  }
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
        @php
          $notificationUser = Auth::user();
          $notificationService = app(\App\Services\NotificationService::class);
          $notificationService->syncFor($notificationUser);
          $notificationUnreadCount = \App\Models\AppNotification::where('user_id', $notificationUser->getAuthIdentifier())
              ->where('is_hidden', false)
              ->where('is_read', false)
              ->count();
        @endphp
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

          <div class="lvn-notification-root" data-lvn-notifications
               data-notifications-url="{{ route('notifications.index') }}"
               data-read-url-template="{{ url('notifications/__notification__/read') }}"
               data-hide-url-template="{{ url('notifications/__notification__/hide') }}">
            <button type="button" class="lvn-notification-bell" data-lvn-notification-toggle
                    aria-label="Notifications" aria-expanded="false" aria-controls="lvn-notification-panel">
              <i class="i-Bell"></i>
              <span class="lvn-notification-badge" data-lvn-notification-count @if($notificationUnreadCount === 0) hidden @endif>{{ $notificationUnreadCount }}</span>
            </button>
            <div id="lvn-notification-panel" class="lvn-notification-panel" data-lvn-notification-panel hidden>
              <div class="lvn-notification-heading">Notifications</div>
              <div data-lvn-notification-list>
                <div class="lvn-notification-loading">Loading notifications...</div>
              </div>
            </div>
          </div>

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

      <script>
        (function () {
          var root = document.querySelector('[data-lvn-notifications]');
          if (!root) return;

          var toggle = root.querySelector('[data-lvn-notification-toggle]');
          var panel = root.querySelector('[data-lvn-notification-panel]');
          var list = root.querySelector('[data-lvn-notification-list]');
          var count = root.querySelector('[data-lvn-notification-count]');
          var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          function updateCount(value) {
            count.textContent = value;
            count.hidden = value < 1;
          }

          function renderNotifications(data) {
            updateCount(data.unreadCount);
            if (!data.notifications.length) {
              list.replaceChildren();
              var empty = document.createElement('div');
              empty.className = 'lvn-notification-empty';
              empty.textContent = 'No new notifications.';
              list.appendChild(empty);
              return;
            }

            list.replaceChildren();
            data.notifications.forEach(function (notification) {
              var date = notification.expiresAt ? 'Valid to: ' + notification.expiresAt : '';
              var item = document.createElement('div');
              var main = document.createElement('button');
              var hide = document.createElement('button');
              var message = document.createElement('span');
              var meta = document.createElement('span');
              item.className = 'lvn-notification-item' + (notification.isRead ? '' : ' lvn-notification-unread');
              item.dataset.notificationId = notification.id;
              item.dataset.notificationUrl = notification.url || '';
              main.type = 'button';
              main.className = 'lvn-notification-main';
              hide.type = 'button';
              hide.className = 'lvn-notification-hide';
              hide.dataset.notificationHide = 'true';
              hide.textContent = 'Hide';
              message.className = 'lvn-notification-message';
              message.textContent = notification.message;
              meta.className = 'lvn-notification-meta';
              meta.textContent = date;
              main.appendChild(message);
              main.appendChild(meta);
              item.appendChild(main);
              item.appendChild(hide);
              list.appendChild(item);
            });
          }

          function loadNotifications() {
            fetch(root.dataset.notificationsUrl, { headers: { 'Accept': 'application/json' } })
              .then(function (response) { return response.json(); })
              .then(renderNotifications)
              .catch(function () { list.innerHTML = '<div class="lvn-notification-empty">Unable to load notifications.</div>'; });
          }

          toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var isOpening = panel.hidden;
            panel.hidden = !isOpening;
            toggle.setAttribute('aria-expanded', String(isOpening));
            if (isOpening) loadNotifications();
          });

          list.addEventListener('click', function (event) {
            var item = event.target.closest('[data-notification-id]');
            if (!item) return;
            var hideButton = event.target.closest('[data-notification-hide]');
            if (hideButton) {
              var hideUrl = root.dataset.hideUrlTemplate.replace('__notification__', item.dataset.notificationId);
              fetch(hideUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
              }).then(function (response) { return response.json(); })
                .then(function (data) {
                  updateCount(data.unreadCount);
                  item.remove();
                  if (!list.querySelector('[data-notification-id]')) {
                    var empty = document.createElement('div');
                    empty.className = 'lvn-notification-empty';
                    empty.textContent = 'No new notifications.';
                    list.appendChild(empty);
                  }
                });
              return;
            }
            var readUrl = root.dataset.readUrlTemplate.replace('__notification__', item.dataset.notificationId);
            fetch(readUrl, {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(function (response) { return response.json(); })
              .then(function (data) {
                updateCount(data.unreadCount);
                item.classList.remove('lvn-notification-unread');
                var targetUrl = item.dataset.notificationUrl;
                if (targetUrl) window.location.href = targetUrl;
              });
          });

          document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
              panel.hidden = true;
              toggle.setAttribute('aria-expanded', 'false');
            }
          });
        }());
      </script>
