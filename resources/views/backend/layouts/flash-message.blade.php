@if ($message = Session::get('success'))
<script>
    (function() {
        function showMsg() {
            if (typeof toastr !== 'undefined') {
                toastr.success("{!! addslashes($message) !!}", "Success");
            }
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(showMsg, 100);
        } else {
            document.addEventListener("DOMContentLoaded", showMsg);
        }
    })();
</script>
@endif 
    
@if ($message = Session::get('error'))
<script>
    (function() {
        function showMsg() {
            if (typeof toastr !== 'undefined') {
                toastr.error("{!! addslashes($message) !!}", "Error");
            }
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(showMsg, 100);
        } else {
            document.addEventListener("DOMContentLoaded", showMsg);
        }
    })();
</script>
@endif
     
@if ($message = Session::get('warning'))
<script>
    (function() {
        function showMsg() {
            if (typeof toastr !== 'undefined') {
                toastr.warning("{!! addslashes($message) !!}", "Warning");
            }
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(showMsg, 100);
        } else {
            document.addEventListener("DOMContentLoaded", showMsg);
        }
    })();
</script>
@endif
     
@if ($message = Session::get('info'))
<script>
    (function() {
        function showMsg() {
            if (typeof toastr !== 'undefined') {
                toastr.info("{!! addslashes($message) !!}", "Info");
            }
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(showMsg, 100);
        } else {
            document.addEventListener("DOMContentLoaded", showMsg);
        }
    })();
</script>
@endif
    
@if ($errors->any())
<script>
    (function() {
        function showMsg() {
            if (typeof toastr !== 'undefined') {
                toastr.warning("Please check the form below for errors", "Warning");
            }
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(showMsg, 100);
        } else {
            document.addEventListener("DOMContentLoaded", showMsg);
        }
    })();
</script>
@endif
