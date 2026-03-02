<!DOCTYPE html>
<html>
<head>
    <title>Leave Application</title>
</head>
<body>
    <h1>Leave Application {{ ucfirst($leaveDetails['action']) }}</h1>
    @if($leaveDetails['action'] === 'approved')
        <p>Dear {{ $leaveDetails['employee_name'] }},</p>
        <p>Congratulations! Your leave application has been approved successfully. Here are the details:</p>
    @elseif($leaveDetails['action'] === 'rejected')
        <p>Dear {{ $leaveDetails['employee_name'] }},</p>
        <p>We regret to inform you that your leave application has been rejected. Here are the details:</p>
    @else
    <p>Dear {{ $leaveDetails['employee_name'] }},</p>
    <p>Your leave application has been {{ $leaveDetails['action'] }} successfully. Here are the details:</p>
    @endif
   
    <ul>
        <li><strong>Leave Type:</strong> {{ $leaveDetails['leave_type'] }}</li>
        <li><strong>Start Date:</strong>{{ \Carbon\Carbon::parse($leaveDetails['start_date'])->format('d M, Y') }}</li>
        <li><strong>End Date:</strong>{{ \Carbon\Carbon::parse($leaveDetails['end_date'])->format('d M, Y') }}</li>
        <li><strong>Reason:</strong> {{ $leaveDetails['reason'] }}</li>
        @php
        $start = \Carbon\Carbon::parse($leaveDetails['start_date']);
        $end = \Carbon\Carbon::parse($leaveDetails['end_date']);
        $leaveDays = $start->diffInDays($end) + 1; // Add 1 to include both start and end dates
    @endphp
    <li><strong>Number of Leave Days:</strong> {{ $leaveDays }} days</li>
    </ul>
    <p>Thank you.</p>
</body>
</html>
