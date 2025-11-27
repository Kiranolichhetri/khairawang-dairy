<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Address;
use App\Services\AddressService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Address Controller
 * 
 * Handles user address book management.
 */
class AddressController
{
    private AddressService $addressService;

    public function __construct()
    {
        $this->addressService = new AddressService();
    }

    /**
     * Get current user ID from session
     */
    private function getUserId(): ?int
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        return $userId ? (int) $userId : null;
    }

    /**
     * List all addresses
     */
    public function index(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $addresses = $this->addressService->getUserAddresses($userId);
        
        $formattedAddresses = array_map(fn($addr) => [
            'id' => $addr->getKey(),
            'label' => $addr->attributes['label'],
            'full_name' => $addr->attributes['full_name'],
            'phone' => $addr->attributes['phone'],
            'address_line_1' => $addr->attributes['address_line_1'],
            'address_line_2' => $addr->attributes['address_line_2'],
            'city' => $addr->attributes['city'],
            'district' => $addr->attributes['district'],
            'postal_code' => $addr->attributes['postal_code'],
            'address_type' => $addr->attributes['address_type'],
            'is_default' => (bool) $addr->attributes['is_default'],
            'formatted_address' => $addr->getFormattedAddress(),
        ], $addresses);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $formattedAddresses,
            ]);
        }
        
        return Response::view('account.addresses.index', [
            'title' => 'My Addresses',
            'addresses' => $formattedAddresses,
        ]);
    }

    /**
     * Show create address form
     */
    public function create(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        return Response::view('account.addresses.create', [
            'title' => 'Add New Address',
            'districts' => Address::getNepalDistricts(),
            'labels' => $this->addressService->getAddressLabels(),
            'types' => $this->addressService->getAddressTypes(),
        ]);
    }

    /**
     * Store new address
     */
    public function store(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $validator = new Validator($request->all(), [
            'full_name' => 'required|min:2|max:255',
            'phone' => 'required|min:10|max:20',
            'address_line_1' => 'required|min:5|max:255',
            'city' => 'required|min:2|max:100',
            'district' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/account/addresses/create');
        }
        
        $result = $this->addressService->createAddress($userId, $request->all());
        
        if ($request->expectsJson()) {
            if ($result['success']) {
                $address = $result['address'];
                return Response::json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'id' => $address->getKey(),
                        'formatted_address' => $address->getFormattedAddress(),
                    ],
                ], 201);
            }
            return Response::json($result, 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/account/addresses');
        } else {
            $session?->error($result['message']);
            $session?->flashInput($request->all());
            return Response::redirect('/account/addresses/create');
        }
    }

    /**
     * Show edit address form
     */
    public function edit(Request $request, string $id): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        $address = $this->addressService->getAddress((int) $id, $userId);
        
        if ($address === null) {
            $session = Application::getInstance()?->session();
            $session?->error('Address not found');
            return Response::redirect('/account/addresses');
        }
        
        return Response::view('account.addresses.edit', [
            'title' => 'Edit Address',
            'address' => $address->toArray(),
            'districts' => Address::getNepalDistricts(),
            'labels' => $this->addressService->getAddressLabels(),
            'types' => $this->addressService->getAddressTypes(),
        ]);
    }

    /**
     * Update address
     */
    public function update(Request $request, string $id): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $validator = new Validator($request->all(), [
            'full_name' => 'required|min:2|max:255',
            'phone' => 'required|min:10|max:20',
            'address_line_1' => 'required|min:5|max:255',
            'city' => 'required|min:2|max:100',
            'district' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/account/addresses/' . $id . '/edit');
        }
        
        $result = $this->addressService->updateAddress((int) $id, $userId, $request->all());
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/account/addresses');
        } else {
            $session?->error($result['message']);
            return Response::redirect('/account/addresses/' . $id . '/edit');
        }
    }

    /**
     * Delete address
     */
    public function delete(Request $request, string $id): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->addressService->deleteAddress((int) $id, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/addresses');
    }

    /**
     * Set default address
     */
    public function setDefault(Request $request, string $id): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->addressService->setDefaultAddress((int) $id, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/addresses');
    }
}
