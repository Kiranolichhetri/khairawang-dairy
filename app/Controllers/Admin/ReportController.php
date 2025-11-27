<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use App\Services\ReportService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Admin Report Controller
 * 
 * Handles reporting and analytics in the admin panel.
 */
class ReportController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /**
     * Sales report
     */
    public function sales(Request $request): Response
    {
        $period = $request->query('period', 'month');
        $dateFrom = $request->query('date_from', date('Y-m-01'));
        $dateTo = $request->query('date_to', date('Y-m-d'));
        
        $data = $this->reportService->getSalesReport($dateFrom, $dateTo, $period);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }
        
        return Response::view('admin.reports.sales', [
            'title' => 'Sales Report',
            'data' => $data,
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Product performance report
     */
    public function products(Request $request): Response
    {
        $dateFrom = $request->query('date_from', date('Y-m-01'));
        $dateTo = $request->query('date_to', date('Y-m-d'));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));
        
        $data = $this->reportService->getProductReport($dateFrom, $dateTo, $limit);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }
        
        return Response::view('admin.reports.products', [
            'title' => 'Product Performance',
            'data' => $data,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Customer report
     */
    public function customers(Request $request): Response
    {
        $dateFrom = $request->query('date_from', date('Y-m-01'));
        $dateTo = $request->query('date_to', date('Y-m-d'));
        
        $data = $this->reportService->getCustomerReport($dateFrom, $dateTo);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }
        
        return Response::view('admin.reports.customers', [
            'title' => 'Customer Report',
            'data' => $data,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Inventory report
     */
    public function inventory(Request $request): Response
    {
        $data = $this->reportService->getInventoryReport();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }
        
        return Response::view('admin.reports.inventory', [
            'title' => 'Inventory Report',
            'data' => $data,
        ]);
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request, string $type): Response
    {
        $dateFrom = $request->query('date_from', date('Y-m-01'));
        $dateTo = $request->query('date_to', date('Y-m-d'));
        
        switch ($type) {
            case 'sales':
                $csv = $this->reportService->exportSalesReport($dateFrom, $dateTo);
                $filename = 'sales_report_' . date('Y-m-d') . '.csv';
                break;
            case 'products':
                $csv = $this->reportService->exportProductReport($dateFrom, $dateTo);
                $filename = 'product_report_' . date('Y-m-d') . '.csv';
                break;
            case 'customers':
                $csv = $this->reportService->exportCustomerReport($dateFrom, $dateTo);
                $filename = 'customer_report_' . date('Y-m-d') . '.csv';
                break;
            case 'inventory':
                $csv = $this->reportService->exportInventoryReport();
                $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
                break;
            default:
                return Response::error('Invalid report type', 400);
        }
        
        $response = new Response($csv);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
}
