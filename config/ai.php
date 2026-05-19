<?php

return [
    'unpaid_chat_limit' => env('AI_UNPAID_CHAT_LIMIT', 7),
    'payment_qr_image' => env('AI_PAYMENT_QR_IMAGE', 'images/ai-payment-qr.png'),
    'payment_qr_image_alt' => env('AI_PAYMENT_QR_IMAGE_ALT', 'QR Pembayaran AI Tutor'),
    'payment_instructions' => env('AI_PAYMENT_INSTRUCTIONS', 'Scan QR di atas kemudian konfirmasi setelah melakukan pembayaran offline.'),
];
