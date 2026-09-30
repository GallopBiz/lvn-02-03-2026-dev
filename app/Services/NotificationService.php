<?php

namespace App\Services;

use App\Models\AddVehial;
use App\Models\AppNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function syncFor(User $user): void
    {
        $today = Carbon::today();
        $latestAllowedDate = $today->copy()->addDays(30);

        $docFields = [
            'validto' => 'RTO Paper',
            'fitness_validto' => 'Fitness Paper',
            'insurance_validto' => 'Insurance',
            'permit_validto' => 'Permit',
            'tax_validto' => 'Tax',
            'puc_validto' => 'PUC',
            'gprs_validto' => 'GPRS / GPS',
        ];

        $existingColumns = Schema::hasTable('vehicel') 
            ? Schema::getColumnListing('vehicel') 
            : [];

        $selectColumns = array_merge(['id', 'vehicelno'], array_intersect(array_keys($docFields), $existingColumns));

        $vehicleQuery = AddVehial::query();
        if (in_array('is_delete', $existingColumns, true)) {
            $vehicleQuery->where('is_delete', 0);
        }
        $vehicles = $vehicleQuery->get($selectColumns);

        $activeEventKeys = [];

        foreach ($vehicles as $vehicle) {
            $vehicleNumber = $vehicle->vehicelno ?: ('Vehicle #' . $vehicle->id);

            foreach ($docFields as $field => $label) {
                if (!isset($vehicle->{$field})) {
                    continue;
                }

                $rawDate = (string) $vehicle->{$field};
                if (empty($rawDate) || str_starts_with($rawDate, '0000-00-00') || str_starts_with($rawDate, '-')) {
                    continue;
                }

                try {
                    $validTo = Carbon::parse($rawDate)->startOfDay();
                    if ($validTo->year < 2000) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    continue;
                }

                if ($validTo->gt($latestAllowedDate)) {
                    continue;
                }

                $eventKey = 'DOC_EXPIRY:vehicle:' . $vehicle->id . ':' . $field . ':' . $validTo->toDateString();
                $activeEventKeys[] = $eventKey;
                $daysLeft = (int) $today->diffInDays($validTo, false);

                if ($daysLeft < 0) {
                    $message = sprintf(
                        '%s for Vehicle %s expired %d days ago.',
                        $label,
                        $vehicleNumber,
                        abs($daysLeft)
                    );
                } elseif ($daysLeft === 0) {
                    $message = sprintf('%s for Vehicle %s expires today.', $label, $vehicleNumber);
                } else {
                    $message = sprintf(
                        '%s for Vehicle %s will expire in %d days.',
                        $label,
                        $vehicleNumber,
                        $daysLeft
                    );
                }

                AppNotification::query()->updateOrCreate(
                    [
                        'user_id' => $user->getAuthIdentifier(),
                        'event_key' => $eventKey,
                    ],
                    [
                        'type' => 'RTO_EXPIRY',
                        'title' => $label . ' Expiry',
                        'message' => $message,
                        'entity_id' => $vehicle->id,
                        'entity_type' => 'vehicle',
                        'expires_at' => $validTo->toDateString(),
                        'url' => url('AddVehical-view/' . $vehicle->id),
                        'is_hidden' => false,
                        'is_read' => false,
                    ]
                );
            }
        }

        // Also check rto_paper table
        if (Schema::hasTable('rto_paper')) {
            $rtoPaperQuery = DB::table('rto_paper');
            if (Schema::hasColumn('rto_paper', 'is_delete')) {
                $rtoPaperQuery->where('is_delete', 0);
            }
            $rtoPapers = $rtoPaperQuery
                ->whereNotNull('Next_Renewal_Date')
                ->where('Next_Renewal_Date', '!=', '')
                ->where('Next_Renewal_Date', '!=', '0000-00-00')
                ->get();

            foreach ($rtoPapers as $paper) {
                $rawDate = (string) $paper->Next_Renewal_Date;
                if (empty($rawDate) || str_starts_with($rawDate, '0000-00-00') || str_starts_with($rawDate, '-')) {
                    continue;
                }

                try {
                    $validTo = Carbon::parse($rawDate)->startOfDay();
                    if ($validTo->year < 2000) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    continue;
                }

                if ($validTo->gt($latestAllowedDate)) {
                    continue;
                }

                $paperName = $paper->RTO_Paper_Name ?: 'RTO Paper';
                $vehRef = $paper->Vehicle ?: ('ID #' . $paper->id);
                $eventKey = 'RTO_PAPER_EXPIRY:' . $paper->id . ':' . $validTo->toDateString();
                $activeEventKeys[] = $eventKey;
                $daysLeft = (int) $today->diffInDays($validTo, false);

                if ($daysLeft < 0) {
                    $message = sprintf(
                        'RTO Document "%s" (%s) expired %d days ago.',
                        $paperName,
                        $vehRef,
                        abs($daysLeft)
                    );
                } elseif ($daysLeft === 0) {
                    $message = sprintf('RTO Document "%s" (%s) expires today.', $paperName, $vehRef);
                } else {
                    $message = sprintf(
                        'RTO Document "%s" (%s) will expire in %d days.',
                        $paperName,
                        $vehRef,
                        $daysLeft
                    );
                }

                AppNotification::query()->updateOrCreate(
                    [
                        'user_id' => $user->getAuthIdentifier(),
                        'event_key' => $eventKey,
                    ],
                    [
                        'type' => 'RTO_EXPIRY',
                        'title' => $paperName . ' Expiry',
                        'message' => $message,
                        'entity_id' => $paper->id,
                        'entity_type' => 'rto_paper',
                        'expires_at' => $validTo->toDateString(),
                        'url' => url('rtopaper-view/' . $paper->id),
                        'is_hidden' => false,
                        'is_read' => false,
                    ]
                );
            }
        }

        // Delete notifications for documents whose expiry date is now > 30 days or no longer valid
        AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('type', 'RTO_EXPIRY')
            ->whereNotIn('event_key', $activeEventKeys)
            ->delete();
    }

    public function forUser(User $user, int $limit = 50)
    {
        $this->syncFor($user);

        return AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('type', 'RTO_EXPIRY')
            ->orderBy('expires_at', 'asc')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
