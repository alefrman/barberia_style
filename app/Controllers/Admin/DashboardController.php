<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Service;

/**
 * DashboardController
 *
 * Vista principal del panel de administración.
 */
class DashboardController extends Controller
{
    public function index(Request $request, array $params): Response
    {
        $user = Auth::user();

        $data = [
            'title'        => 'Dashboard',
            'user'         => $user,
            'totalServices' => Service::count(),
            'totalProducts' => Product::count(),
            'totalExpenses' => Expense::count(),
            'todayAppointments' => Appointment::count([
                'appointment_date' => date('Y-m-d'),
            ]),
        ];

        return $this->view('admin/dashboard/index', $data, 'admin');
    }
}
