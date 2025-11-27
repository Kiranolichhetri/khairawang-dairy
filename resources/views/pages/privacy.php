<?php
/**
 * Privacy Policy Page
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<!-- Hero Section -->
<section class="bg-dark-brown py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-heading font-bold text-white mb-4">
            Privacy Policy
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
                    At KHAIRAWANG DAIRY, we are committed to protecting your privacy and personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">2. Information We Collect</h2>
                <h3 class="text-xl font-semibold text-dark-brown mb-3">2.1 Personal Information</h3>
                <p class="text-gray-600 mb-4">
                    We may collect the following personal information:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li>Name and contact information (email, phone number, address)</li>
                    <li>Account credentials (username, password)</li>
                    <li>Payment information (processed securely through payment providers)</li>
                    <li>Order history and preferences</li>
                    <li>Communication records with our customer service</li>
                </ul>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">2.2 Automatically Collected Information</h3>
                <p class="text-gray-600 mb-6">
                    When you visit our website, we automatically collect certain information including your IP address, browser type, device information, pages visited, and referring website. This helps us improve our services and user experience.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">3. How We Use Your Information</h2>
                <p class="text-gray-600 mb-4">
                    We use the collected information for the following purposes:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Processing and fulfilling your orders</li>
                    <li>Communicating about your orders and account</li>
                    <li>Sending promotional offers and newsletters (with your consent)</li>
                    <li>Improving our products, services, and website</li>
                    <li>Personalizing your shopping experience</li>
                    <li>Detecting and preventing fraud</li>
                    <li>Complying with legal obligations</li>
                </ul>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">4. Cookie Policy</h2>
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.1 What Are Cookies</h3>
                <p class="text-gray-600 mb-4">
                    Cookies are small text files stored on your device when you visit our website. They help us provide a better user experience and understand how you use our site.
                </p>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.2 Types of Cookies We Use</h3>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li><strong>Essential Cookies:</strong> Required for basic website functionality</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors use our site</li>
                    <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                    <li><strong>Marketing Cookies:</strong> Used to deliver relevant advertisements</li>
                </ul>
                
                <h3 class="text-xl font-semibold text-dark-brown mb-3">4.3 Managing Cookies</h3>
                <p class="text-gray-600 mb-6">
                    You can control and manage cookies through your browser settings. Please note that disabling certain cookies may affect website functionality.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">5. Information Sharing</h2>
                <p class="text-gray-600 mb-4">
                    We do not sell your personal information. We may share your information with:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li><strong>Service Providers:</strong> Third parties who help us operate our business (delivery partners, payment processors)</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                    <li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets</li>
                </ul>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">6. Data Security</h2>
                <p class="text-gray-600 mb-6">
                    We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet is 100% secure.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">7. Your Rights</h2>
                <p class="text-gray-600 mb-4">
                    You have the following rights regarding your personal information:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate data</li>
                    <li><strong>Deletion:</strong> Request deletion of your data (subject to legal requirements)</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
                    <li><strong>Portability:</strong> Request transfer of your data</li>
                </ul>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">8. Data Retention</h2>
                <p class="text-gray-600 mb-6">
                    We retain your personal information for as long as necessary to fulfill the purposes for which it was collected, comply with legal obligations, resolve disputes, and enforce our agreements. Order records are typically retained for 7 years for tax and legal purposes.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">9. Children's Privacy</h2>
                <p class="text-gray-600 mb-6">
                    Our services are not intended for children under 16 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">10. Third-Party Links</h2>
                <p class="text-gray-600 mb-6">
                    Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to review their privacy policies before providing any personal information.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">11. Changes to This Policy</h2>
                <p class="text-gray-600 mb-6">
                    We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated revision date. We encourage you to review this policy periodically.
                </p>
                
                <h2 class="text-2xl font-heading font-bold text-dark-brown mb-4">12. Contact Us</h2>
                <p class="text-gray-600 mb-4">
                    If you have questions or concerns about this Privacy Policy or our data practices, please contact us:
                </p>
                <div class="bg-light-gray rounded-xl p-6">
                    <p class="text-gray-600 mb-2"><strong>KHAIRAWANG DAIRY - Privacy Office</strong></p>
                    <p class="text-gray-600 mb-2">Khairawang, Rupandehi, Lumbini Province, Nepal</p>
                    <p class="text-gray-600 mb-2">Email: privacy@khairawangdairy.com</p>
                    <p class="text-gray-600">Phone: +977 9812345678</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <a href="/contact" class="inline-flex items-center text-accent-orange hover:underline">
                Have questions about your privacy? Contact us
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
