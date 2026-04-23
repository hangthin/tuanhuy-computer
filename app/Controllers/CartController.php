<?php
// app/Controllers/CartController.php
require_once __DIR__ . '/../Models/Models.php';

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new CartModel();
    }

    private function calcSubtotal($items) {
        $total = 0;
        foreach ($items as $i) { $total += $i['unit_price'] * $i['quantity']; }
        return $total;
    }

    public function index($p = null) {
        $items      = $this->cartModel->getItems();
        $subtotal   = $this->calcSubtotal($items);
        $shipping   = $subtotal >= 500000 ? 0 : 30000;
        $appliedCoupon  = null;
        $couponDiscount = 0;
        $sc = $_SESSION['applied_coupon'] ?? null;
        if (!empty($sc['code'])) {
            $appliedCoupon  = $sc;
            $couponDiscount = (float)($sc['discount'] ?? 0);
        }
        $total      = max(0, $subtotal + $shipping - $couponDiscount);
        $categories = (new CategoryModel())->getAll();
        $pageTitle  = 'Giỏ hàng';
        include __DIR__ . '/../Views/cart/index.php';
    }

    public function update($p = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: '.APP_URL.'/cart'); exit; }
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $qty    = (int)($_POST['quantity'] ?? 1);
        if ($cartId && $qty > 0) $this->cartModel->update($cartId, $qty);
        header('Location: ' . APP_URL . '/cart'); exit;
    }

    public function remove($cartId) {
        $this->cartModel->remove((int)$cartId);
        setFlash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
        header('Location: ' . APP_URL . '/cart'); exit;
    }

    public function clear($p = null) {
        $this->cartModel->clear();
        setFlash('success', 'Đã xóa toàn bộ giỏ hàng.');
        header('Location: ' . APP_URL . '/cart'); exit;
    }
}
