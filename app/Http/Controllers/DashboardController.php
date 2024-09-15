<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\Utils;
use App\Constants\RBAC;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    use Utils;

    public function index()
    {
        if (!Gate::allows(sprintf('%s/%s', RBAC::PAGE_DASHBOARD, RBAC::SCOPE_READ))) {
            abort(403);
        }

        $greeting = $this->greetingByHour();

        return view("dashboard.pages.index", compact("greeting"));
    }
}
