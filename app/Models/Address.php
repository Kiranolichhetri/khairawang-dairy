<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Address Model
 * 
 * Represents a user's shipping/billing address.
 */
class Address extends Model
{
    protected static string $table = 'addresses';
    
    protected static array $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'address_type',
        'is_default',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Nepal districts list
     * @return array<string>
     */
    public static function getNepalDistricts(): array
    {
        return [
            // Province 1
            'Bhojpur', 'Dhankuta', 'Ilam', 'Jhapa', 'Khotang', 'Morang', 'Okhaldhunga',
            'Panchthar', 'Sankhuwasabha', 'Solukhumbu', 'Sunsari', 'Taplejung', 'Terhathum', 'Udayapur',
            // Madhesh Province
            'Bara', 'Dhanusha', 'Mahottari', 'Parsa', 'Rautahat', 'Saptari', 'Sarlahi', 'Siraha',
            // Bagmati Province
            'Bhaktapur', 'Chitwan', 'Dhading', 'Dolakha', 'Kathmandu', 'Kavrepalanchok',
            'Lalitpur', 'Makwanpur', 'Nuwakot', 'Ramechhap', 'Rasuwa', 'Sindhuli', 'Sindhupalchok',
            // Gandaki Province
            'Baglung', 'Gorkha', 'Kaski', 'Lamjung', 'Manang', 'Mustang', 'Myagdi',
            'Nawalparasi East', 'Parbat', 'Syangja', 'Tanahu',
            // Lumbini Province
            'Arghakhanchi', 'Banke', 'Bardiya', 'Dang', 'Gulmi', 'Kapilvastu', 'Nawalparasi West',
            'Palpa', 'Pyuthan', 'Rolpa', 'Rukum East', 'Rupandehi',
            // Karnali Province
            'Dailekh', 'Dolpa', 'Humla', 'Jajarkot', 'Jumla', 'Kalikot', 'Mugu',
            'Rukum West', 'Salyan', 'Surkhet',
            // Sudurpashchim Province
            'Achham', 'Baitadi', 'Bajhang', 'Bajura', 'Dadeldhura', 'Darchula',
            'Doti', 'Kailali', 'Kanchanpur',
        ];
    }

    /**
     * Get user's addresses
     * 
     * @return array<self>
     */
    public static function forUser(int $userId): array
    {
        $rows = static::query()
            ->where('user_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get user's default address
     */
    public static function getDefault(int $userId, string $type = 'shipping'): ?self
    {
        // Use raw query for complex OR conditions
        $data = self::db()->selectOne(
            "SELECT * FROM " . static::getTable() . " 
             WHERE user_id = ? AND is_default = 1 
             AND (address_type = ? OR address_type = 'both')",
            [$userId, $type]
        );
        
        if ($data === null) {
            return null;
        }
        
        return static::hydrate($data);
    }

    /**
     * Set this address as default (unset others)
     */
    public function setAsDefault(): bool
    {
        // First, unset all other defaults for this user
        self::db()->update(
            static::getTable(),
            ['is_default' => 0],
            ['user_id' => $this->attributes['user_id']]
        );
        
        // Set this as default
        $this->is_default = true;
        return $this->save();
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddress(): string
    {
        $parts = [
            $this->attributes['address_line_1'] ?? '',
        ];
        
        if (!empty($this->attributes['address_line_2'])) {
            $parts[] = $this->attributes['address_line_2'];
        }
        
        $parts[] = $this->attributes['city'] ?? '';
        $parts[] = $this->attributes['district'] ?? '';
        
        if (!empty($this->attributes['postal_code'])) {
            $parts[] = $this->attributes['postal_code'];
        }
        
        return implode(', ', array_filter($parts));
    }

    /**
     * Get full address for order
     * @return array<string, mixed>
     */
    public function toShippingData(): array
    {
        return [
            'name' => $this->attributes['full_name'],
            'phone' => $this->attributes['phone'],
            'address' => $this->getFormattedAddress(),
            'city' => $this->attributes['city'],
        ];
    }
}
