<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Enums\UserRole;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin User Controller
 * 
 * Handles user/customer management in the admin panel.
 */
class UserController
{
    /**
     * List all users
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $role = $request->query('role');
        $status = $request->query('status');
        $search = trim($request->query('q', '') ?? '');
        
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Build query
        $sql = "SELECT u.*, r.name as role_name FROM users u 
                INNER JOIN roles r ON u.role_id = r.id WHERE 1=1";
        $params = [];
        
        if ($role && in_array($role, array_column(UserRole::cases(), 'value'))) {
            $sql .= " AND r.name = ?";
            $params[] = $role;
        }
        
        if ($status && in_array($status, ['active', 'inactive', 'banned'])) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = str_replace('SELECT u.*, r.name as role_name', 'SELECT COUNT(*) as count', $sql);
        $countResult = $db->selectOne($countSql, $params);
        $total = (int) ($countResult['count'] ?? 0);
        
        // Add ordering and pagination
        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $usersData = $db->select($sql, $params);
        
        $users = array_map(function($row) {
            return [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'avatar' => $row['avatar'] ? '/uploads/avatars/' . $row['avatar'] : null,
                'role' => $row['role_name'],
                'role_label' => UserRole::tryFrom($row['role_name'])?->label() ?? $row['role_name'],
                'status' => $row['status'],
                'email_verified' => $row['email_verified_at'] !== null,
                'created_at' => $row['created_at'],
            ];
        }, $usersData);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'users' => $users,
                ],
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('admin.users.index', [
            'title' => 'Users',
            'users' => $users,
            'roles' => UserRole::cases(),
            'filters' => [
                'role' => $role,
                'status' => $status,
                'search' => $search,
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Show user details with orders
     */
    public function show(Request $request, string $id): Response
    {
        $user = User::find((int) $id);
        
        if ($user === null) {
            if ($request->expectsJson()) {
                return Response::error('User not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('User not found.');
            
            return Response::redirect('/admin/users');
        }
        
        $userDetails = [
            'id' => $user->getKey(),
            'name' => $user->attributes['name'],
            'email' => $user->attributes['email'],
            'phone' => $user->attributes['phone'],
            'avatar' => $user->getAvatarUrl(),
            'role' => $user->getRole()?->value,
            'role_label' => $user->getRole()?->label(),
            'status' => $user->attributes['status'],
            'email_verified' => $user->isEmailVerified(),
            'email_verified_at' => $user->attributes['email_verified_at'],
            'created_at' => $user->attributes['created_at'],
            'updated_at' => $user->attributes['updated_at'],
        ];
        
        // Get user's orders
        $orders = Order::forUser((int) $id);
        $orderStats = [
            'total_orders' => count($orders),
            'total_spent' => 0,
        ];
        
        $formattedOrders = [];
        foreach ($orders as $order) {
            $orderStats['total_spent'] += (float) $order->attributes['total'];
            $formattedOrders[] = [
                'id' => $order->getKey(),
                'order_number' => $order->attributes['order_number'],
                'total' => (float) $order->attributes['total'],
                'status' => $order->getStatus()->value,
                'status_label' => $order->getStatus()->label(),
                'status_color' => $order->getStatus()->color(),
                'created_at' => $order->attributes['created_at'],
            ];
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'user' => $userDetails,
                    'orders' => $formattedOrders,
                    'stats' => $orderStats,
                ],
            ]);
        }
        
        return Response::view('admin.users.show', [
            'title' => 'User Details - ' . $user->attributes['name'],
            'user' => $userDetails,
            'orders' => $formattedOrders,
            'stats' => $orderStats,
        ]);
    }

    /**
     * Show edit user form
     */
    public function edit(Request $request, string $id): Response
    {
        $user = User::find((int) $id);
        
        if ($user === null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('User not found.');
            
            return Response::redirect('/admin/users');
        }
        
        $app = Application::getInstance();
        $db = $app?->db();
        $roles = $db?->select("SELECT * FROM roles") ?? [];
        
        return Response::view('admin.users.edit', [
            'title' => 'Edit User',
            'user' => $user->toArray(),
            'roles' => $roles,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, string $id): Response
    {
        $user = User::find((int) $id);
        
        if ($user === null) {
            if ($request->expectsJson()) {
                return Response::error('User not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('User not found.');
            
            return Response::redirect('/admin/users');
        }
        
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/users/' . $id . '/edit');
        }

        // Check if email is unique (excluding current user)
        $existingUser = User::findByEmail($request->input('email'));
        if ($existingUser !== null && $existingUser->getKey() !== (int) $id) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['email' => ['This email is already in use.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/users/' . $id . '/edit');
        }

        // Update user
        $user->fill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'status' => $request->input('status', 'active'),
        ]);
        
        // Update role if provided
        if ($request->input('role_id')) {
            $user->role_id = (int) $request->input('role_id');
        }
        
        // Update password if provided
        if ($request->input('password')) {
            $user->password = $request->input('password');
        }
        
        $user->save();

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('User updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'User updated successfully',
            ]);
        }

        return Response::redirect('/admin/users');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request, string $id): Response
    {
        $user = User::find((int) $id);
        
        if ($user === null) {
            if ($request->expectsJson()) {
                return Response::error('User not found', 404);
            }
            
            return Response::redirect('/admin/users');
        }
        
        // Don't allow disabling own account
        $app = Application::getInstance();
        $session = $app?->session();
        $currentUserId = $session?->get('user_id');
        
        if ($currentUserId && (int) $currentUserId === (int) $id) {
            if ($request->expectsJson()) {
                return Response::error('Cannot disable your own account', 400);
            }
            
            $session?->error('Cannot disable your own account.');
            return Response::redirect('/admin/users');
        }
        
        $currentStatus = $user->attributes['status'];
        $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
        
        $user->status = $newStatus;
        $user->save();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'User status updated',
                'status' => $newStatus,
            ]);
        }

        $session?->success('User status updated!');

        return Response::redirect('/admin/users');
    }

    /**
     * Delete user
     */
    public function delete(Request $request, string $id): Response
    {
        $user = User::find((int) $id);
        
        if ($user === null) {
            if ($request->expectsJson()) {
                return Response::error('User not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('User not found.');
            
            return Response::redirect('/admin/users');
        }
        
        // Don't allow deleting own account
        $app = Application::getInstance();
        $session = $app?->session();
        $currentUserId = $session?->get('user_id');
        
        if ($currentUserId && (int) $currentUserId === (int) $id) {
            if ($request->expectsJson()) {
                return Response::error('Cannot delete your own account', 400);
            }
            
            $session?->error('Cannot delete your own account.');
            return Response::redirect('/admin/users');
        }
        
        // Don't allow deleting admin users (for safety)
        if ($user->isAdmin()) {
            if ($request->expectsJson()) {
                return Response::error('Cannot delete admin users', 400);
            }
            
            $session?->error('Cannot delete admin users.');
            return Response::redirect('/admin/users');
        }
        
        $user->delete();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        }

        $session?->success('User deleted successfully!');

        return Response::redirect('/admin/users');
    }
}
