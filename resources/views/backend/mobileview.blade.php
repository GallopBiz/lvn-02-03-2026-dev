<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="icon" href="animated.jpg">
  
        <link rel="stylesheet" href="SearchBar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous"/>
        <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/datatables.min.css"/>
        <style>
    * { box-sizing: border-box}
body{
  font-family: 'Asap', sans-serif;
}
.phoneswrapper{
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-evenly;
  /* padding: 20px; */
}

.phone{
  height:  auto;
  width: 400px; 
  padding: 10px 20px;
  /* border-radius: 32px; */
  /* border: 2px solid #3e3e3e; */
  overflow: hidden;
  position: relative;
  box-shadow: 0px 17px 20px rgba(0, 0, 0, 0.15);

}
.phone_head {
    display: flex;
    flex-direction: row;
    justify-content: space-between; 
     align-items: center;
    height: 45px;
}
.phone_head .title{
  font-size: 24px;
  font-weight: 500;
  color: #34394F;
}
.phone_body {
    max-height: 600px; /* Set the maximum height of the chat body */
    overflow-y: auto; /* Enable vertical scrolling */
  }
.icon_bubble.msg {
    width: 30px;
    height: 30px;
    background: linear-gradient( 90deg, #d13eea, #53d9ea);
    border-radius: 50%;
}

.divider {
    height: 1px;
    width: 111%;
    background: linear-gradient( 90deg, #d13eea, #53d9ea);
    margin-bottom: 12px;
}
.grad_pb{ background: linear-gradient( 90deg, #d13eea, #53d9ea);
  color: white; 
  }
img.chat_avatar {
    width: 45px;
  height: 45px;
    border-radius: 7px;
  margin-right: 8px;
}
img.chat_avatar {
    width: 45px;
   box-shadow: 1px 6px 18px rgba(31, 37, 72, 0.45);
    border-radius: 7px;
}

.chat {
    display: flex;
    justify-content: space-between;
    padding: 15px 0px;
    border-bottom: 1px solid #d3d3d35e;
}

.contact_name {font-weight: 500;

  color: #34394F;
     font-size: 15px;
    margin-bottom: 2px;}

.contact_msg {
   
     font-size: 11px;
    color: #a5a5a5;
    font-weight: lighter;
}
.chat_info {width: 50%;}

.chat_date {
    font-size: 12px;
    color: #5a5a5a;
  margin-bottom: 2px;
}

.chat_new{
  padding: 2px 5px;
  font-size: 11px;
  border-radius: 2px;
}
.chat_status {
    width: 25%;
    display: flex;
    flex-direction: column;
    align-items: center; 
}
input {
    outline: none;
}
input[type=search] {
    -webkit-appearance: textfield;
    -webkit-box-sizing: content-box;
    font-family: inherit;
    font-size: 100%;
}
input {
   position: relative;
}



input[type=search] {
    background: #ededed url(https://static.tumblr.com/ftv85bp/MIXmud4tx/search-icon.png) no-repeat 9px center;
    border: solid 1px #ccc;
    padding: 9px 10px 9px 32px;
    width: 55px;
    
    -webkit-border-radius: 10em;
    -moz-border-radius: 10em;
    border-radius: 10em;
    
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    transition: all .5s;
}

#demo-2 input[type=search] {
    width: 15px;
    padding-left: 10px;
    color: transparent;
    cursor: pointer;
}
#demo-2 input[type=search]:hover {
    background-color: #fff;
}
#demo-2 input[type=search]:focus {
    width: 130px;
    padding-left: 32px;
    color: #000;
    background-color: #fff;
    cursor: auto;
}
#demo-2 input:-moz-placeholder {
    color: transparent;
}
#demo-2 input::-webkit-input-placeholder {
    color: transparent;
}

.phone_footer {
    position: absolute;
    bottom: 0;
    height: 30px;
    width: 100%;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    right: 0px;
    border-radius: 10px;
    
    /* background: linear-gradient(0deg, white 40%, #ffffffa1 85%, transparent 95%); */
    background-color: #3863a0;
}

.footer_divider {
    height: 5px;
    width: 45%;
    border-radius: 10px;
  
     margin-top: 35px;
}
span {
  content: "\2191";
}
  </style>   
</head>

<body>
  
<div class="phoneswrapper">
  
  <div class="phone">
    <div class="phone_head">
      <div class="title"> LVN School </div>
      <!-- <div class="icon_bubble msg"> </div> -->
      <span id="demo-2">
          <input type="search" placeholder="Search">
      </span>
    </div>
    <div class="divider"> </div>
    <div class="phone_body">
    <form method="POST" action="{{ route('mobileview') }}" id="attendanceForm">
    @csrf
    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%"> 
    <tbody>
      <?php $i =1;?>
    @foreach($students as $student)
    <tr><td>
      <input type="hidden" name="Student_name[]" value="{{ $student->student_name }}">
      <input type="hidden" name="Class_name[]" value="{{ $student->class_name }}">
      <input type="hidden" name="student_id[]" value="{{ $student->id }}">
      <input type="hidden" name="Bus_no" value="mp09ab0102">
      <input type="hidden" name="DC_name" value="Driver/Conductor_name">
      <div class="chat">
        <img class="chat_avatar" src="{{url('assets/backend/')}}/images/header-logo.png">
        <div class="chat_info">
          <div class="contact_name">{{ $student->student_name }}</div>
          <?php $Address = json_decode($student->json_str, true);?>
          <input type="hidden" name="Address[]" value="{{ $Address['present_address'] ?? 'Address not available'}} ">

        <div class="chat_date">{{ $Address['present_address'] ?? 'Address not available' }}</div>
        </div>
        <div class="chat_status">
          <div class="chat_date">{{ $student->class_name }}</div>
          <span>&#8593; &#8595; <input type="checkbox" id="<?php echo $i; ?>" name="atte[]" checked value="present">
          <input type="hidden" id="hidden_vehicle<?php echo $i; ?>" name="attendance[]" value="present"></span>
        </div>
      </div>
      </td></tr>
     <?php $i++; ?>
    @endforeach
    </tbody>
   </table>
   <!-- <button type="button" id="submitAttendance">Submit Attendance</button> -->
   
</form>
</div>

    
    <a href="#"> <div class="phone_footer" >
      submit
    </div>
  </div></a>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
<script src="{{url('assets/backend/')}}/js/plugins/datatables.min.js"></script>
<script src="{{url('assets/backend/')}}/js/scripts/datatables.script.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
  document.querySelector('.phone_footer').addEventListener('click', function() {
    Swal.fire({
      title: 'Bus Attendance',
      text: 'Do you want to submit?',
      icon: 'success',
      showCancelButton: true,
      confirmButtonText: 'Submit',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        document.querySelector('form').submit();
      }
    });
  });
</script>
<script>
    $("input[name='atte[]']").on("change",  function () {
      hiddenInput=$(`#hidden_vehicle${this.id}`)
      if ($(this).is(':checked')) {
                hiddenInput.val('Present');
            } else {
                hiddenInput.val('Absent');
            }
        });
    
</script>
</body>
</html>