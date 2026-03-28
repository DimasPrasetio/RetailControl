<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfirmPasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $valid = Hash::check(
            $request->input('password', ''),
            $request->user()->password
        );

        return response()->json(['valid' => $valid]);
    }
}
