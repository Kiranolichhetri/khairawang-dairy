<?php
/**
 * Terms and Conditions Page
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<!-- Hero Section -->
<section class="bg-dark-brown py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-heading font-bold text-white mb-4">
            Terms &amp; Conditions
        </h1>
        <p class="text-gray-400">
            Last updated: <?= date('F d, Y') ?>
        </p>
    </div>
</section>

<!-- Content -->
<section class="py-12 md:py-16 bg-cream">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-soft p-8 md:p-12">
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">1. Introduction</h2>
                <p class="text-gray-600 mb-6">
                    Welcome to KHAIRAWANG DAIRY. These Terms and Conditions govern your use of our website, products, and services. By accessing our website or purchasing our products, you agree to be bound by these terms.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">2. Definitions</h2>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li><strong>"Company," "we," "us," "our"</strong> refers to KHAIRAWANG DAIRY.</li>
                    <li><strong>"Customer," "you," "your"</strong> refers to any individual or entity using our services.</li>
                    <li><strong>"Products"</strong> refers to dairy products sold through our website or retail outlets.</li>
                    <li><strong>"Services"</strong> refers to delivery, subscription, and other services we provide.</li>
                </ul>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">3. Orders and Purchases</h2>
                <h3 class="text-xl font-semibold text-dark-brown mb-3">3.1 Placing Orders</h3>
                <p class="text-gray-600 mb-4">
                    By placing an order through our website, you confirm that you are at least 18 years old or have parental consent. All orders are subject to availability and acceptance by KHAIRAWANG DAIRY.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">3.2 Pricing</h3>
                <p class="text-gray-600 mb-4">
                    All prices are displayed in Nepalese Rupees (NPR) and include applicable taxes unless otherwise stated. We reserve the right to change prices at any time without prior notice.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">3.3 Payment</h3>
                <p class="text-gray-600 mb-6">
                    We accept various payment methods including cash on delivery (COD), eSewa, and other digital payment options. Payment must be completed before or at the time of delivery.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">4. Delivery</h2>
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.1 Delivery Areas</h3>
                <p class="text-gray-600 mb-4">
                    We deliver to selected areas within Nepal. Delivery availability will be confirmed during checkout. Additional charges may apply for remote locations.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.2 Delivery Times</h3>
                <p class="text-gray-600 mb-4">
                    We strive to deliver products fresh and on time. Delivery times are estimates and may vary due to weather, traffic, or other unforeseen circumstances.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.3 Receiving Delivery</h3>
                <p class="text-gray-600 mb-6">
                    Please ensure someone is available to receive the delivery at the specified address. If delivery cannot be completed, we will contact you to arrange an alternative.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">5. Returns and Refunds</h2>
                <h3 class="text-xl font-semibold text-dark-brown mb-3">5.1 Return Policy</h3>
                <p class="text-gray-600 mb-4">
                    Due to the perishable nature of dairy products, returns are only accepted for damaged or defective products. You must report any issues within 24 hours of delivery.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">5.2 Refund Process</h3>
                <p class="text-gray-600 mb-6">
                    Approved refunds will be processed within 7-10 business days. Refunds will be issued through the original payment method or as store credit at our discretion.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">6. Product Quality</h2>
                <p class="text-gray-600 mb-6">
                    We maintain strict quality control measures to ensure all products meet our high standards. Our products are fresh, natural, and free from harmful additives. Storage instructions should be followed to maintain product quality.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">7. User Responsibilities</h2>
                <p class="text-gray-600 mb-4">
                    As a customer, you agree to:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Provide accurate and complete information when placing orders</li>
                    <li>Store products according to provided guidelines</li>
                    <li>Not misuse our website or services</li>
                    <li>Not resell our products without authorization</li>
                    <li>Comply with all applicable laws and regulations</li>
                </ul>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">8. Intellectual Property</h2>
                <p class="text-gray-600 mb-6">
                    All content on our website, including logos, images, text, and designs, is the property of KHAIRAWANG DAIRY and is protected by copyright laws. Unauthorized use is strictly prohibited.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">9. Limitation of Liability</h2>
                <p class="text-gray-600 mb-6">
                    KHAIRAWANG DAIRY shall not be liable for any indirect, incidental, or consequential damages arising from the use of our products or services. Our total liability shall not exceed the amount paid for the specific product or service in question.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">10. Changes to Terms</h2>
                <p class="text-gray-600 mb-6">
                    We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to our website. Continued use of our services constitutes acceptance of modified terms.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">11. Governing Law</h2>
                <p class="text-gray-600 mb-6">
                    These Terms and Conditions are governed by and construed in accordance with the laws of Nepal. Any disputes shall be subject to the exclusive jurisdiction of the courts of Nepal.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">12. Contact Information</h2>
                <p class="text-gray-600 mb-4">
                    For questions about these Terms and Conditions, please contact us:
                </p>
                <div class="bg-light-gray rounded-xl p-6">
                    <p class="text-gray-600 mb-2"><strong>KHAIRAWANG DAIRY</strong></p>
                    <p class="text-gray-600 mb-2">Khairawang, Rupandehi, Lumbini Province, Nepal</p>
                    <p class="text-gray-600 mb-2">Email: info@khairawangdairy.com</p>
                    <p class="text-gray-600">Phone: +977 9812345678</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <a href="/contact" class="inline-flex items-center text-accent-orange hover:underline">
                Have questions? Contact us
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
