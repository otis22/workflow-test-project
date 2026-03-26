<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Dashboard\GetDashboardData;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, GetDashboardData $getDashboard): View
    {
        $data = $getDashboard->execute((int) $request->user()->id);

        return view('dashboard', $data);
    }
}
