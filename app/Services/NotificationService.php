<?php

namespace App\Services;

use App\Models\AddVehial;
use App\Models\AppNotification;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    public function syncFor(User $user): void
    {
        $today = Carbon::today();
        $latestAllowedDate = $today->copy()->addDays(30);

        $vehicles = AddVehial::query()
            ->where('is_delete', 0)
            ->whereNotNull('validto')
            ->whereDate('validto', '<=', $latestAllowedDate)
            ->get(['id', 'vehicelno', 'validto']);

        foreach ($vehicles as $vehicle) {
            $validTo = Carbon::parse($vehicle->validto);
            $eventKey = 'RTO_EXPIRY:' . $vehicle->id . ':' . $validTo->toDateString();
            $daysLeft = $today->diffInDays($validTo, false);
            $vehicleNumber = $vehicle->vehicelno ?: ('Vehicle #' . $vehicle->id);

            if ($daysLeft < 0) {
                $message = sprintf(
                    'RTO certificate for Vehicle %s expired %d days ago.',
                    $vehicleNumber,
                    abs($daysLeft)
                );
            } elseif ($daysLeft === 0) {
                $message = sprintf('RTO certificate for Vehicle %s expires today.', $vehicleNumber);
            } else {
                $message = sprintf(
                    'RTO certificate for Vehicle %s will expire in %d days.',
                    $vehicleNumber,
                    $daysLeft
                );
            }

            AppNotification::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('type', 'RTO_EXPIRY')
                ->where('entity_type', 'vehicle')
                ->where('entity_id', $vehicle->id)
                ->where('event_key', '!=', $eventKey)
                ->delete();

            AppNotification::query()->updateOrCreate(
                [
                    'user_id' => $user->getAuthIdentifier(),
                    'event_key' => $eventKey,
                ],
                [
                    'type' => 'RTO_EXPIRY',
                    'title' => 'RTO certificate expiry',
                    'message' => $message,
                    'entity_id' => $vehicle->id,
                    'entity_type' => 'vehicle',
                    'expires_at' => $validTo->toDateString(),
                    'url' => url('AddVehical-view/' . $vehicle->id),
                ]
            );
        }
    }

    public function forUser(User $user, int $limit = 25)
    {
        $this->syncFor($user);

        return AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('is_hidden', false)
            ->orderBy('is_read')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
