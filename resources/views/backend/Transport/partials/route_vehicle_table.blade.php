@forelse($routes as $route)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $route['route_name'] }}</td>
        <td>{{ $route['vehicle_no'] }}</td>
        <td>
            @if(!empty($route['gps_tracking_url']))
                {{-- Show static DB button if gps_tracking_url exists --}}
                <a href="{{ $route['gps_tracking_url'] }}" 
                   class="btn btn-primary" 
                   target="_blank">
                   GPS Tracking
                </a>
            @else
                {{-- Otherwise show default tracking --}}
                <a href="https://login.gpstracks.co.in/jsp/VehicleLiveTracking.jsp?username=Lokmanyaschool&password=gps@123&vehicle_no={{ $route['vehicle_no'] }}" 
                   class="btn btn-primary" 
                   target="_blank">
                   GPS Tracking
                </a>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center">No data found</td>
    </tr>
@endforelse
