<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Events\DriverLocationUpdated;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب بيانات الملف الشخصي',
            'data' => [
                'user' => new UserResource($request->user())
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'trip_id' => 'nullable|integer', // Optional: if they are on a specific trip
        ]);

        $user = $request->user();
        
        $user->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        if ($user->role === 'driver') {
            DriverLocationUpdated::dispatch($user, $validated['trip_id'] ?? null);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الموقع الجغرافي',
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'driver') {
            return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:driving_license,id_card,vehicle_registration',
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('document_file')->store('driver-documents', 'public');

        $document = $user->driverDocuments()->create([
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم رفع الوثيقة بنجاح، بانتظار المراجعة',
            'data' => [
                'document' => $document
            ]
        ], 201);
    }

    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update([
            'avatar_url' => $path,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الصورة الشخصية بنجاح',
            'data' => [
                'avatar_url' => asset('storage/' . $path)
            ]
        ]);
    }
}
