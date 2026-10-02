<?php
function applyPromotion($productId, $originalPrice, $conn) {
    $query = " SELECT p.discount_type, p.discount_value 
        FROM promotions p
        INNER JOIN promotion_targets pt ON p.promo_id = pt.promo_id
        WHERE pt.target_type = 'product' AND pt.target_id = ? 
          AND p.active = 1 
          AND NOW() BETWEEN p.start_date AND p.end_date
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $discount = $row['discount_value'];
        if ($row['discount_type'] == 'percentage') {
            return $originalPrice * (1 - $discount / 100);
        } else {
            return max(0, $originalPrice - $discount);
        }
    }
    return $originalPrice; // Sin descuento
}
