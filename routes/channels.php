<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels (hospital dashboard real-time)
|--------------------------------------------------------------------------
| Authorize hospital.{id} for hospital_admin (own hospital) and super_admin (any).
*/

Broadcast::channel('hospital.{hospitalId}', function ($user, $hospitalId) {
    if ($user->role === 'super_admin') {
        return true;
    }
    if ($user->role === 'hospital_admin' && (int) $user->hospital_id === (int) $hospitalId) {
        return true;
    }
    return false;
});
