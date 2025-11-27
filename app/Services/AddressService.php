<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use Core\Application;
use Core\Database;

/**
 * Address Service
 * 
 * Handles user address management operations.
 */
class AddressService
{
    private ?Database $db = null;

    /**
     * Get database connection
     */
    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->db = $app->db();
            }
        }
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not available');
        }
        
        return $this->db;
    }

    /**
     * Get all addresses for a user
     * 
     * @return array<Address>
     */
    public function getUserAddresses(int $userId): array
    {
        return Address::forUser($userId);
    }

    /**
     * Get address by ID
     */
    public function getAddress(int $addressId, int $userId): ?Address
    {
        $address = Address::find($addressId);
        
        // Verify ownership
        if ($address === null || $address->attributes['user_id'] !== $userId) {
            return null;
        }
        
        return $address;
    }

    /**
     * Create a new address
     * 
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, address?: Address}
     */
    public function createAddress(int $userId, array $data): array
    {
        // Validate required fields
        $required = ['full_name', 'phone', 'address_line_1', 'city', 'district'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "The {$field} field is required"];
            }
        }
        
        // Validate phone number (Nepal format)
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return ['success' => false, 'message' => 'Invalid phone number'];
        }
        
        // Validate district
        $validDistricts = Address::getNepalDistricts();
        if (!in_array($data['district'], $validDistricts)) {
            return ['success' => false, 'message' => 'Invalid district'];
        }
        
        // Check if this is the first address (make it default)
        $existingAddresses = Address::forUser($userId);
        $isDefault = empty($existingAddresses) || !empty($data['is_default']);
        
        // If setting as default, unset other defaults first
        if ($isDefault && !empty($existingAddresses)) {
            $this->db()->update('addresses', ['is_default' => 0], ['user_id' => $userId]);
        }
        
        // Create address
        $address = Address::create([
            'user_id' => $userId,
            'label' => $data['label'] ?? 'Home',
            'full_name' => $data['full_name'],
            'phone' => $phone,
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'district' => $data['district'],
            'postal_code' => $data['postal_code'] ?? null,
            'address_type' => $data['address_type'] ?? 'both',
            'is_default' => $isDefault,
        ]);
        
        return [
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $address,
        ];
    }

    /**
     * Update an address
     * 
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string}
     */
    public function updateAddress(int $addressId, int $userId, array $data): array
    {
        $address = $this->getAddress($addressId, $userId);
        
        if ($address === null) {
            return ['success' => false, 'message' => 'Address not found'];
        }
        
        // Validate phone if provided
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                return ['success' => false, 'message' => 'Invalid phone number'];
            }
            $data['phone'] = $phone;
        }
        
        // Validate district if provided
        if (!empty($data['district'])) {
            $validDistricts = Address::getNepalDistricts();
            if (!in_array($data['district'], $validDistricts)) {
                return ['success' => false, 'message' => 'Invalid district'];
            }
        }
        
        // Handle default flag
        if (!empty($data['is_default'])) {
            $this->db()->update('addresses', ['is_default' => 0], ['user_id' => $userId]);
        }
        
        // Update address
        $address->fill($data);
        $address->save();
        
        return ['success' => true, 'message' => 'Address updated successfully'];
    }

    /**
     * Delete an address
     * 
     * @return array{success: bool, message: string}
     */
    public function deleteAddress(int $addressId, int $userId): array
    {
        $address = $this->getAddress($addressId, $userId);
        
        if ($address === null) {
            return ['success' => false, 'message' => 'Address not found'];
        }
        
        $wasDefault = $address->attributes['is_default'] ?? false;
        
        $address->delete();
        
        // If deleted address was default, set another one as default
        if ($wasDefault) {
            $remainingAddresses = Address::forUser($userId);
            if (!empty($remainingAddresses)) {
                $remainingAddresses[0]->setAsDefault();
            }
        }
        
        return ['success' => true, 'message' => 'Address deleted successfully'];
    }

    /**
     * Set address as default
     * 
     * @return array{success: bool, message: string}
     */
    public function setDefaultAddress(int $addressId, int $userId): array
    {
        $address = $this->getAddress($addressId, $userId);
        
        if ($address === null) {
            return ['success' => false, 'message' => 'Address not found'];
        }
        
        $address->setAsDefault();
        
        return ['success' => true, 'message' => 'Default address updated'];
    }

    /**
     * Get default address
     */
    public function getDefaultAddress(int $userId, string $type = 'shipping'): ?Address
    {
        return Address::getDefault($userId, $type);
    }

    /**
     * Get address labels
     * 
     * @return array<string>
     */
    public function getAddressLabels(): array
    {
        return ['Home', 'Office', 'Work', 'Other'];
    }

    /**
     * Get address types
     * 
     * @return array<string, string>
     */
    public function getAddressTypes(): array
    {
        return [
            'shipping' => 'Shipping Address',
            'billing' => 'Billing Address',
            'both' => 'Both Shipping & Billing',
        ];
    }
}
