 <div class="flex-grow-1"></div>
        <div class="app-footer">
          <div class="row">
            <div class="col-md-9">
             
            </div>
          </div>
          <div
            class="footer-bottom border-top pt-3 d-flex flex-column flex-sm-row align-items-center"
          >
            <span class="flex-grow-1"></span>
            <div class="d-flex align-items-center">
              <img
                class="logo"
                src="{{url('assets/backend')}}/images/header-logo.png"
                alt=""
              />
              <div>
                <p class="m-0">&copy; {{date('Y')}} LVN School</p>
                <p class="m-0">All rights reserved</p>
              </div>
            </div>
          </div>
        </div>


         <script>
            // Function to set a cookie
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + value + expires + "; path=/";
            }

            // Function to get a cookie value
            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            // Function to handle the change event of the select element
            document.getElementById('year').addEventListener('change', function () {
                setCookie('selectedYear', this.value, 365); // Store the selected value in a cookie for 1 year
            });

            // Function to set the selected option based on the cookie value
            window.addEventListener('load', function () {
                var selectedYear = getCookie('selectedYear');
                if (selectedYear) {
                    document.getElementById('year').value = selectedYear;
                }
            });
</script>
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script type="text/javascript">
    // $.noConflict();
    jQuery(document).ready(function($) {
        // $("#inq-form-nomenu").select2();
        $("#inq-form-nomenu").select2().on('change', function (e) {
          var id = $("#inq-form-nomenu").val();
          // window.open(`{{url('/registrationviewlist')}}/${id}`);
          window.location.href=(`{{url('/registrationviewlist')}}/${id}`);
        })

        $("#sibling_name").select2();

        setTimeout(function() {
            var year = $("#year").val()
            $("#session_name").val(year);
        }, 1000);
    });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" />
<style type="text/css"></style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
<script type="text/javascript">
    $.noConflict();
</script>