<?php

namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FuelConsumptionController extends Controller
{
    private const FUEL_TYPES = [
        'Diesel' => 'Litres',
        'Petrol' => 'Litres',
        'CNG' => 'Kg',
        'Electric' => 'kWh',
        'Other' => 'Unit',
    ];

    public function __construct(Request $request)
    {
        $sessionDatabase = $request->hasSession() ? $request->session()->get('session_name') : null;
        $sessionDatabase = $sessionDatabase ?: ($request->hasSession() ? $request->session()->get('session') : null);
        $database = $sessionDatabase
            ?: $request->cookie('selectedYear')
            ?: config('database.connections.dynamic.database');

        $dynamicConfig = Config::get('database.connections.dynamic');
        $dynamicConfig['database'] = $database;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::purge('dynamic');
        DB::reconnect('dynamic');
    }

    public function index(Request $request)
    {
        $connection = DB::connection('dynamic');
        $vehicleColumns = ['id', 'vehicelno'];
        $hasOpeningOdometer = Schema::connection('dynamic')->hasColumn('vehicel', 'fuel_opening_odometer');
        if ($hasOpeningOdometer) {
            $vehicleColumns[] = 'fuel_opening_odometer';
        }
        $vehicles = $connection->table('vehicel')
            ->where('is_delete', 0)
            ->orderBy('vehicelno')
            ->get($vehicleColumns);
        $latestOdometers = $connection->table('fuel_entries')
            ->select('vehicle_id', DB::raw('MAX(current_odometer) as current_odometer'))
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');
        $vehicles->each(function ($vehicle) use ($latestOdometers) {
            $latestOdometer = $latestOdometers->get($vehicle->id);
            $vehicle->fuel_previous_odometer = $latestOdometer?->current_odometer
                ?? ($vehicle->fuel_opening_odometer ?? null);
        });
        $drivers = $connection->table('busstaff')
            ->where('is_delete', 0)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['driver'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%driver%']);
            })
            ->orderBy('ename')
            ->get(['id', 'ename']);
        $stations = $connection->table('fuel_stations')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
        $filteredEntries = $this->fuelEntriesQuery($request);
        $totals = (clone $filteredEntries)->select([])->selectRaw(
            'COALESCE(SUM(fuel.quantity), 0) as quantity, COALESCE(SUM(fuel.total_amount), 0) as total_amount,
            COALESCE(SUM(fuel.previous_odometer), 0) as previous_odometer,
            COALESCE(SUM(fuel.current_odometer), 0) as current_odometer,
            COALESCE(SUM(fuel.distance), 0) as distance'
        )->first();
        $totals->average = (float) $totals->quantity > 0
            ? (float) $totals->distance / (float) $totals->quantity
            : null;
        $entries = (clone $filteredEntries)
            ->orderByDesc('fuel.fuel_date')
            ->orderByDesc('fuel.id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.Transport.fuel.index', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'stations' => $stations,
            'entries' => $entries,
            'totals' => $totals,
            'fuelTypes' => self::FUEL_TYPES,
        ]);
    }

    public function export(Request $request)
    {
        $filteredEntries = $this->fuelEntriesQuery($request);
        $query = (clone $filteredEntries)
            ->orderBy('fuel.fuel_date')
            ->orderBy('fuel.id')
            ->get();
        $totals = (clone $filteredEntries)->select([])->selectRaw(
            'COALESCE(SUM(fuel.quantity), 0) as quantity, COALESCE(SUM(fuel.total_amount), 0) as total_amount,
            COALESCE(SUM(fuel.previous_odometer), 0) as previous_odometer,
            COALESCE(SUM(fuel.current_odometer), 0) as current_odometer,
            COALESCE(SUM(fuel.distance), 0) as distance'
        )->first();

        return response()->streamDownload(function () use ($query, $totals) {
            $output = fopen('php://output', 'w');
                fputcsv($output, ['S.No.', 'Date', 'Vehicle', 'Fuel Type', 'Quantity', 'Unit', 'Rate', 'Total Amount', 'Previous KM', 'Current KM', 'Distance', 'Efficiency', 'Driver', 'Fuel Station', 'Bill Number']);
                foreach ($query as $index => $entry) {
                fputcsv($output, [
                    $index + 1,
                    $entry->fuel_date,
                    $entry->vehicelno,
                    $entry->fuel_type,
                    number_format($entry->quantity, 2, '.', ''),
                    $entry->quantity_unit,
                    number_format($entry->rate, 2, '.', ''),
                    number_format($entry->total_amount, 2, '.', ''),
                    number_format($entry->previous_odometer, 2, '.', ''),
                    number_format($entry->current_odometer, 2, '.', ''),
                    number_format($entry->distance, 2, '.', ''),
                    $entry->efficiency !== null ? number_format($entry->efficiency, 2, '.', '') : 'N/A',
                    $entry->driver_name,
                    $entry->station_name,
                    $entry->bill_number,
                ]);
            }
            fputcsv($output, [
                'TOTAL', '', '', '', number_format($totals->quantity, 2, '.', ''), '', '',
                number_format($totals->total_amount, 2, '.', ''),
                number_format($totals->previous_odometer, 2, '.', ''),
                number_format($totals->current_odometer, 2, '.', ''),
                number_format($totals->distance, 2, '.', ''),
                (float) $totals->quantity > 0 ? number_format($totals->distance / $totals->quantity, 2, '.', '') : 'N/A',
                '', '', '',
            ]);
            fclose($output);
        }, 'fuel-entries-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function fuelEntriesQuery(Request $request)
    {
        return DB::connection('dynamic')->table('fuel_entries as fuel')
            ->leftJoin('vehicel as vehicle', 'vehicle.id', '=', 'fuel.vehicle_id')
            ->leftJoin('fuel_stations as station', 'station.id', '=', 'fuel.fuel_station_id')
            ->leftJoin('busstaff as driver', 'driver.id', '=', 'fuel.driver_id')
            ->select('fuel.*', 'vehicle.vehicelno', 'station.name as station_name', 'driver.ename as driver_name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('vehicle.vehicelno', 'like', "%{$search}%")
                        ->orWhere('fuel.fuel_type', 'like', "%{$search}%")
                        ->orWhere('station.name', 'like', "%{$search}%")
                        ->orWhere('driver.ename', 'like', "%{$search}%")
                        ->orWhere('fuel.bill_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('fuel.fuel_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('fuel.fuel_date', '<=', $request->date_to))
            ->when($request->filled('filter_vehicle_id'), fn ($query) => $query->where('fuel.vehicle_id', $request->filter_vehicle_id))
            ->when($request->filled('filter_fuel_type'), fn ($query) => $query->where('fuel.fuel_type', $request->filter_fuel_type));
    }

    public function store(Request $request)
    {
        $fuelTypes = array_keys(self::FUEL_TYPES);
        $validated = $request->validate([
            'fuel_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'integer'],
            'fuel_type' => ['required', 'in:' . implode(',', $fuelTypes)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'gte:0'],
            'fuel_station_id' => ['required', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'current_odometer' => ['required', 'numeric'],
            'bill_number' => ['nullable', 'string', 'max:100'],
            'payment_mode' => ['nullable', 'in:Cash,UPI,Card,Bank Transfer,Other'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
        ]);

        $connection = DB::connection('dynamic');
        $vehicle = $connection->table('vehicel')
            ->where('id', $validated['vehicle_id'])
            ->where('is_delete', 0)
            ->first();
        abort_unless($vehicle, 404);

        $latest = $connection->table('fuel_entries')
            ->where('vehicle_id', $vehicle->id)
            ->orderByDesc('current_odometer')
            ->orderByDesc('id')
            ->first();
        $previousOdometer = $latest?->current_odometer ?? $vehicle->fuel_opening_odometer;

        if ($previousOdometer === null) {
            return back()->withInput()->withErrors(['current_odometer' => 'Set the vehicle fuel opening odometer before adding an entry.']);
        }
        if ((float) $validated['current_odometer'] < (float) $previousOdometer) {
            return back()->withInput()->withErrors(['current_odometer' => 'Current odometer cannot be less than the previous odometer reading.']);
        }

        $distance = round((float) $validated['current_odometer'] - (float) $previousOdometer, 2);
        $quantity = (float) $validated['quantity'];
        $totalAmount = round($quantity * (float) $validated['rate'], 2);
        $efficiency = $distance > 0 && $quantity > 0 ? round($distance / $quantity, 3) : null;

        $connection->table('fuel_entries')->insert([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $validated['driver_id'] ?? null,
            'fuel_station_id' => $validated['fuel_station_id'],
            'fuel_date' => $validated['fuel_date'],
            'fuel_type' => $validated['fuel_type'],
            'quantity' => $quantity,
            'quantity_unit' => self::FUEL_TYPES[$validated['fuel_type']],
            'rate' => $validated['rate'],
            'total_amount' => $totalAmount,
            'previous_odometer' => $previousOdometer,
            'current_odometer' => $validated['current_odometer'],
            'distance' => $distance,
            'efficiency' => $efficiency,
            'bill_number' => $validated['bill_number'] ?? null,
            'payment_mode' => $validated['payment_mode'] ?? null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('transport.fuel.index')->with('success', 'Fuel entry saved successfully.');
    }

    public function edit(int $id)
    {
        $connection = DB::connection('dynamic');
        $entry = $connection->table('fuel_entries')->where('id', $id)->first();
        abort_unless($entry, 404);

        $vehicles = $connection->table('vehicel')
            ->where('is_delete', 0)
            ->orderBy('vehicelno')
            ->get(['id', 'vehicelno']);
        $drivers = $connection->table('busstaff')
            ->where('is_delete', 0)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['driver'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%driver%']);
            })
            ->orderBy('ename')
            ->get(['id', 'ename']);
        $stations = $connection->table('fuel_stations')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('backend.Transport.fuel.edit', compact('entry', 'vehicles', 'drivers', 'stations'))
            ->with('fuelTypes', self::FUEL_TYPES);
    }

    public function update(Request $request, int $id)
    {
        $fuelTypes = array_keys(self::FUEL_TYPES);
        $validated = $request->validate([
            'fuel_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'integer'],
            'fuel_type' => ['required', 'in:' . implode(',', $fuelTypes)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'gte:0'],
            'fuel_station_id' => ['required', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'current_odometer' => ['required', 'numeric'],
            'bill_number' => ['nullable', 'string', 'max:100'],
            'payment_mode' => ['nullable', 'in:Cash,UPI,Card,Bank Transfer,Other'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
        ]);

        $connection = DB::connection('dynamic');
        $entry = $connection->table('fuel_entries')->where('id', $id)->first();
        abort_unless($entry, 404);
        $vehicle = $connection->table('vehicel')
            ->where('id', $validated['vehicle_id'])
            ->where('is_delete', 0)
            ->first();
        abort_unless($vehicle, 404);

        $latest = $connection->table('fuel_entries')
            ->where('vehicle_id', $vehicle->id)
            ->where('id', '!=', $id)
            ->orderByDesc('current_odometer')
            ->orderByDesc('id')
            ->first();
        $previousOdometer = $latest?->current_odometer ?? ($vehicle->fuel_opening_odometer ?? null);
        if ($previousOdometer === null) {
            return back()->withInput()->withErrors(['current_odometer' => 'Set the vehicle fuel opening odometer before updating this entry.']);
        }
        if ((float) $validated['current_odometer'] < (float) $previousOdometer) {
            return back()->withInput()->withErrors(['current_odometer' => 'Current odometer cannot be less than the previous odometer reading.']);
        }

        $quantity = (float) $validated['quantity'];
        $distance = round((float) $validated['current_odometer'] - (float) $previousOdometer, 2);
        $totalAmount = round($quantity * (float) $validated['rate'], 2);
        $efficiency = $distance > 0 ? round($distance / $quantity, 3) : null;
        $connection->table('fuel_entries')->where('id', $id)->update([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $validated['driver_id'] ?? null,
            'fuel_station_id' => $validated['fuel_station_id'],
            'fuel_date' => $validated['fuel_date'],
            'fuel_type' => $validated['fuel_type'],
            'quantity' => $quantity,
            'quantity_unit' => self::FUEL_TYPES[$validated['fuel_type']],
            'rate' => $validated['rate'],
            'total_amount' => $totalAmount,
            'previous_odometer' => $previousOdometer,
            'current_odometer' => $validated['current_odometer'],
            'distance' => $distance,
            'efficiency' => $efficiency,
            'bill_number' => $validated['bill_number'] ?? null,
            'payment_mode' => $validated['payment_mode'] ?? null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('transport.fuel.index')->with('success', 'Fuel entry updated successfully.');
    }

    public function destroy(int $id)
    {
        $deleted = DB::connection('dynamic')->table('fuel_entries')->where('id', $id)->delete();
        abort_unless($deleted, 404);

        return back()->with('success', 'Fuel entry deleted successfully.');
    }

    public function updateOpeningOdometer(Request $request)
    {
        if (!Schema::connection('dynamic')->hasColumn('vehicel', 'fuel_opening_odometer')) {
            return back()->withErrors(['fuel_opening_odometer' => 'Fuel database migration is pending. Please run the fuel migration before setting opening KM.']);
        }
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'fuel_opening_odometer' => ['required', 'numeric', 'min:0'],
        ]);
        $connection = DB::connection('dynamic');
        if ($connection->table('fuel_entries')->where('vehicle_id', $validated['vehicle_id'])->exists()) {
            return back()->withErrors(['fuel_opening_odometer' => 'Opening odometer cannot be changed after a fuel entry has been recorded.']);
        }
        $connection->table('vehicel')->where('id', $validated['vehicle_id'])->where('is_delete', 0)->update([
            'fuel_opening_odometer' => $validated['fuel_opening_odometer'],
        ]);

        return back()->with('success', 'Fuel opening odometer saved.');
    }

    public function stationStore(Request $request)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:150']]);
        DB::connection('dynamic')->table('fuel_stations')->insert([
            'name' => $validated['name'],
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Fuel station added.');
    }

    public function report(Request $request)
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $connection = DB::connection('dynamic');
        $query = $connection->table('fuel_entries as fuel')
            ->join('vehicel as vehicle', 'vehicle.id', '=', 'fuel.vehicle_id')
            ->whereBetween('fuel.fuel_date', [Carbon::create($year, $month, 1)->startOfMonth(), Carbon::create($year, $month, 1)->endOfMonth()]);

        if ($request->filled('vehicle_id')) {
            $query->where('fuel.vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('fuel_type')) {
            $query->where('fuel.fuel_type', $request->fuel_type);
        }

        $report = $query->select(
            'fuel.vehicle_id', 'vehicle.vehicelno', 'fuel.fuel_type', 'fuel.quantity_unit',
            DB::raw('SUM(fuel.quantity) as total_quantity'),
            DB::raw('SUM(fuel.total_amount) as total_amount'),
            DB::raw('SUM(fuel.distance) as total_distance'),
            DB::raw('MIN(fuel.previous_odometer) as starting_km'),
            DB::raw('MAX(fuel.current_odometer) as last_km'),
            DB::raw('CASE WHEN SUM(fuel.quantity) > 0 THEN SUM(fuel.distance) / SUM(fuel.quantity) ELSE NULL END as efficiency'),
            DB::raw('CASE WHEN SUM(fuel.quantity) > 0 THEN SUM(fuel.total_amount) / SUM(fuel.quantity) ELSE NULL END as average_rate')
        )->groupBy('fuel.vehicle_id', 'vehicle.vehicelno', 'fuel.fuel_type', 'fuel.quantity_unit')->orderBy('vehicle.vehicelno')->get();

        $vehicles = $connection->table('vehicel')->where('is_delete', 0)->orderBy('vehicelno')->get();
        return view('backend.Transport.fuel.report', compact('report', 'vehicles', 'month', 'year'));
    }
}
