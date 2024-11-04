// src/Services/CartService.php
<?php
namespace App\Services;

use App\Utils\Session;

class CartService {
    private $cart;

    public function __construct() {
        $this->cart = Session::get('cart', [
            'items' => [],
            'restaurant_id' => null,
            'total' => 0
        ]);
    }

    public function addItem($menuItem, $quantity = 1) {
        // Check if adding item from different restaurant
        if ($this->cart['restaurant_id'] && $this->cart['restaurant_id'] != $menuItem->restaurant_id) {
            throw new \Exception('Cannot add items from different restaurants');
        }

        $this->cart['restaurant_id'] = $menuItem->restaurant_id;

        // Check if item already exists in cart
        $itemKey = array_search($menuItem->id, array_column($this->cart['items'], 'id'));
        
        if ($itemKey !== false) {
            $this->cart['items'][$itemKey]['quantity'] += $quantity;
        } else {
            $this->cart['items'][] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $menuItem->price,
                'quantity' => $quantity
            ];
        }

        $this->updateTotal();
        $this->saveCart();
    }

    public function removeItem($menuItemId) {
        $this->cart['items'] = array_filter($this->cart['items'], function($item) use ($menuItemId) {
            return $item['id'] !== $menuItemId;
        });

        if (empty($this->cart['items'])) {
            $this->cart['restaurant_id'] = null;
        }

        $this->updateTotal();
        $this->saveCart();
    }

    public function updateQuantity($menuItemId, $quantity) {
        foreach ($this->cart['items'] as &$item) {
            if ($item['id'] === $menuItemId) {
                $item['quantity'] = max(1, $quantity);
                break;
            }
        }

        $this->updateTotal();
        $this->saveCart();
    }

    public function clear() {
        $this->cart = [
            'items' => [],
            'restaurant_id' => null,
            'total' => 0
        ];
        $this->saveCart();
    }

    private function updateTotal() {
        $this->cart['total'] = array_reduce($this->cart['items'], function($total, $item) {
            return $total + ($item['price'] * $item['quantity']);
        }, 0);
    }

    private function saveCart() {
        Session::set('cart', $this->cart);
    }

    public function getCart() {
        return $this->cart;
    }
}
