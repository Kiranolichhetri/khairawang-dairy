<?php

declare(strict_types=1);

namespace App\Models;

use Core\Application;
use Core\MongoDB;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * MongoCart
 *
 * Lightweight cart wrapper that persists carts in MongoDB when the app uses MongoDB.
 * Methods mirror the App\Models\Cart interface used by CartService.
 */
class MongoCart
{
    private ?array $document = null;
    private string $sessionId;
    private ?int $userId;

    public function __construct(string $sessionId = '', ?int $userId = null)
    {
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->load();
    }

    private function mongo()
    {
        return Application::getInstance()->mongo();
    }

    private function load(): void
    {
        $filter = $this->userId !== null
            ? ['user_id' => $this->userId]
            : ['session_id' => $this->sessionId];

        $this->document = $this->mongo()->findOne('carts', $filter);
    }

    private function ensureDocument(): void
    {
        if ($this->document === null) {
            $now = new UTCDateTime((int) (microtime(true) * 1000));
            $doc = [
                'session_id' => $this->sessionId ?: null,
                'user_id' => $this->userId,
                'items' => [],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $insertedId = $this->mongo()->insertOne('carts', $doc);
            $this->document = $this->mongo()->findOne('carts', ['_id' => MongoDB::objectId($insertedId)]);
        }
    }

    public function getKey(): string
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        return (string) ($this->document['_id'] ?? '');
    }

    /**
     * Return raw items array (compat with SQL path)
     * Each item should have: id, product_id, variant_id, quantity
     */
    public function items(): array
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        $items = $this->document['items'] ?? [];
        $out = [];
        foreach ($items as $idx => $it) {
            $out[] = [
                'id' => (string) ($it['item_id'] ?? $idx),
                'product_id' => (string) ($it['product_id'] ?? ''),
                'variant_id' => $it['variant_id'] ?? null,
                'quantity' => (int) ($it['quantity'] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Return items with product fields attached (name_en, price, sale_price, images, stock, slug)
     * Attempts to mirror structure returned by SQL itemsWithProducts()
     */
    public function itemsWithProducts(): array
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        $items = $this->document['items'] ?? [];
        $out = [];
        foreach ($items as $it) {
            $productId = (string) ($it['product_id'] ?? '');
            $product = null;
            try {
                $productDoc = $this->mongo()->findOne('products', ['_id' => new ObjectId($productId)]);
                if ($productDoc) {
                    $product = $productDoc;
                }
            } catch (\Exception $e) {
                // invalid object id or not found -> product stays null
            }

            $name_en = $product['name_en'] ?? ($product['name'] ?? '');
            $name_ne = $product['name_ne'] ?? '';
            $slug = $product['slug'] ?? '';
            $price = isset($product['price']) ? (float) $product['price'] : 0.0;
            $sale_price = isset($product['sale_price']) ? (float) $product['sale_price'] : null;
            $images = isset($product['images']) ? $product['images'] : [];
            $stock = isset($product['stock']) ? (int) $product['stock'] : 0;

            $out[] = [
                'id' => (string) ($it['item_id'] ?? ''),
                'product_id' => $productId,
                'variant_id' => $it['variant_id'] ?? null,
                'name_en' => $name_en,
                'name_ne' => $name_ne,
                'slug' => $slug,
                'price' => $price,
                'sale_price' => $sale_price,
                'images' => is_array($images) ? json_encode($images) : ($images ?? '[]'),
                'stock' => $stock,
                'quantity' => (int) ($it['quantity'] ?? 0),
            ];
        }

        return $out;
    }

    public function addItem(string|int $productId, int $quantity = 1, string|int|null $variantId = null): bool
    {
        $this->ensureDocument();

        // Verify product & stock
        try {
            $product = $this->mongo()->findOne('products', ['_id' => new ObjectId((string)$productId)]);
        } catch (\Exception $e) {
            return false;
        }
        if ($product === null || (($product['status'] ?? '') !== 'published')) {
            return false;
        }
        $stock = (int) ($product['stock'] ?? 0);
        if ($stock < $quantity) {
            return false;
        }

        $items = $this->document['items'] ?? [];
        $found = false;
        foreach ($items as &$it) {
            if (
                (string) ($it['product_id'] ?? '') === (string) $productId
                && (($it['variant_id'] ?? null) === $variantId)
            ) {
                $it['quantity'] = min($stock, (int) ($it['quantity'] ?? 0) + $quantity);
                $found = true;
                break;
            }
        }
        unset($it);

        if (!$found) {
            $newItem = [
                'item_id' => new ObjectId(),
                'product_id' => (string) $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'added_at' => new UTCDateTime((int) (microtime(true) * 1000)),
            ];
            $items[] = $newItem;
        }

        $now = new UTCDateTime((int) (microtime(true) * 1000));
        $this->mongo()->updateOne(
            'carts',
            ['_id' => $this->document['_id']],
            ['$set' => ['items' => $items, 'updated_at' => $now]]
        );

        // reload document
        $this->load();

        return true;
    }

    public function updateItemQuantity(string|int $itemId, int $quantity): bool
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        $items = $this->document['items'] ?? [];
        $found = false;

        foreach ($items as &$it) {
            $idStr = (string) ($it['item_id'] ?? '');
            if ($idStr === (string) $itemId) {
                if ($quantity <= 0) {
                    // remove later
                    $it['__remove'] = true;
                } else {
                    // check stock for product
                    try {
                        $product = $this->mongo()->findOne('products', ['_id' => new ObjectId((string)$it['product_id'])]);
                    } catch (\Exception $e) {
                        return false;
                    }
                    $stock = (int) ($product['stock'] ?? 0);
                    $it['quantity'] = min($stock, $quantity);
                }
                $found = true;
                break;
            }
        }
        unset($it);

        if (!$found) {
            return false;
        }

        // Remove any items marked for removal
        $items = array_filter($items, fn($i) => empty($i['__remove']));

        $now = new UTCDateTime((int) (microtime(true) * 1000));
        $this->mongo()->updateOne(
            'carts',
            ['_id' => $this->document['_id']],
            ['$set' => ['items' => array_values($items), 'updated_at' => $now]]
        );

        $this->load();
        return true;
    }

    public function removeItem(string|int $itemId): bool
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        $items = $this->document['items'] ?? [];
        $new = [];
        $deleted = false;
        foreach ($items as $it) {
            $idStr = (string) ($it['item_id'] ?? '');
            if ($idStr === (string) $itemId) {
                $deleted = true;
                continue;
            }
            $new[] = $it;
        }

        $now = new UTCDateTime((int) (microtime(true) * 1000));
        $this->mongo()->updateOne(
            'carts',
            ['_id' => $this->document['_id']],
            ['$set' => ['items' => $new, 'updated_at' => $now]]
        );

        $this->load();
        return $deleted;
    }

    public function clear(): bool
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }

        $now = new UTCDateTime((int) (microtime(true) * 1000));
        $this->mongo()->updateOne(
            'carts',
            ['_id' => $this->document['_id']],
            ['$set' => ['items' => [], 'updated_at' => $now]]
        );

        $this->load();
        return true;
    }

    public function getItemCount(): int
    {
        $items = $this->document['items'] ?? [];
        $count = 0;
        foreach ($items as $it) {
            $count += (int) ($it['quantity'] ?? 0);
        }
        return $count;
    }

    private function touch(): void
    {
        if ($this->document === null) {
            $this->ensureDocument();
        }
        $now = new UTCDateTime((int) (microtime(true) * 1000));
        $this->mongo()->updateOne(
            'carts',
            ['_id' => $this->document['_id']],
            ['$set' => ['updated_at' => $now]]
        );
        $this->load();
    }
}