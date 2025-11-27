<?php
/**
 * About Us Page
 * KHAIRAWANG DAIRY - Our Story
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<!-- Hero Section -->
<section class="relative bg-dark-brown py-20 md:py-28">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1594489573732-5cd4bcc68a71?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" 
             alt="Our dairy farm" 
             class="w-full h-full object-cover opacity-30">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block px-4 py-1 bg-accent-orange/20 text-accent-orange text-sm font-medium rounded-full mb-4">
            About Us
        </span>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-heading font-bold text-white mb-6">
            Our Story
        </h1>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto">
            Delivering fresh, premium dairy products from our family farm to your table since 1999.
        </p>
    </div>
</section>

<!-- Company Story -->
<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                    Our Journey
                </span>
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown mb-6">
                    From Humble Beginnings to<br>
                    <span class="text-accent-orange">Dairy Excellence</span>
                </h2>
                <p class="text-gray-600 mb-4">
                    KHAIRAWANG DAIRY was founded in 1999 by a family of passionate farmers in the beautiful region of Khairawang, Rupandehi. What started as a small family operation with just a few cows has grown into one of Nepal's most trusted dairy brands.
                </p>
                <p class="text-gray-600 mb-4">
                    Our founder, driven by a vision to provide the purest dairy products to Nepali families, established our first processing facility with a commitment to quality that remains unchanged to this day.
                </p>
                <p class="text-gray-600">
                    Over the past 25 years, we have expanded our operations while staying true to our roots. We continue to work with local farmers, support our community, and deliver products that we're proud to serve to our own families.
                </p>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1527847263472-aa5338d178b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Our dairy heritage" 
                     class="w-full rounded-2xl shadow-soft-lg">
                <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-lg hidden md:block">
                    <span class="block text-4xl font-bold text-accent-orange">1999</span>
                    <span class="text-gray-600">Year Founded</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                Our Purpose
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                Mission &amp; Vision
            </h2>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-light-gray rounded-2xl p-8">
                <div class="w-16 h-16 bg-accent-orange/10 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-heading font-bold text-dark-brown mb-4">Our Mission</h3>
                <p class="text-gray-600">
                    To provide the freshest, highest-quality dairy products to families across Nepal while supporting local farmers and sustainable agricultural practices. We are committed to maintaining the purity and nutritional value of our products through traditional methods combined with modern food safety standards.
                </p>
            </div>
            
            <div class="bg-light-gray rounded-2xl p-8">
                <div class="w-16 h-16 bg-accent-orange/10 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-heading font-bold text-dark-brown mb-4">Our Vision</h3>
                <p class="text-gray-600">
                    To be Nepal's most trusted dairy brand, known for exceptional quality, innovation, and commitment to community welfare. We envision a future where every Nepali family has access to pure, nutritious dairy products that nourish health and happiness.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                What We Stand For
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                Our Core Values
            </h2>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 text-center shadow-soft hover:shadow-soft-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Quality</h3>
                <p class="text-gray-600 text-sm">
                    Uncompromising commitment to delivering the finest dairy products with no additives or preservatives.
                </p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center shadow-soft hover:shadow-soft-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Community</h3>
                <p class="text-gray-600 text-sm">
                    Supporting local farmers and contributing to the economic growth of our community.
                </p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center shadow-soft hover:shadow-soft-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Sustainability</h3>
                <p class="text-gray-600 text-sm">
                    Environmentally responsible practices that protect our planet for future generations.
                </p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center shadow-soft hover:shadow-soft-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Integrity</h3>
                <p class="text-gray-600 text-sm">
                    Honest and transparent practices in everything we do, from sourcing to delivery.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Quality Certifications -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="order-2 md:order-1">
                <img src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Quality certification" 
                     class="w-full rounded-2xl shadow-soft-lg">
            </div>
            <div class="order-1 md:order-2">
                <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                    Quality Assurance
                </span>
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown mb-6">
                    Certified Excellence
                </h2>
                <p class="text-gray-600 mb-6">
                    Our commitment to quality is backed by rigorous certifications and continuous quality control measures. We meet and exceed national and international food safety standards.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-dark-brown">ISO 22000 Certified</h4>
                            <p class="text-gray-600 text-sm">Food safety management system certification</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-dark-brown">HACCP Compliant</h4>
                            <p class="text-gray-600 text-sm">Hazard Analysis and Critical Control Points</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-dark-brown">DFTQC Approved</h4>
                            <p class="text-gray-600 text-sm">Department of Food Technology and Quality Control</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-16 md:py-20 bg-dark-brown">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">25+</div>
                <div class="text-gray-400">Years Experience</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">50K+</div>
                <div class="text-gray-400">Happy Customers</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">100+</div>
                <div class="text-gray-400">Partner Farmers</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">20+</div>
                <div class="text-gray-400">Product Varieties</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown mb-6">
            Ready to Taste the Difference?
        </h2>
        <p class="text-gray-600 text-lg mb-8">
            Experience the pure, fresh taste of KHAIRAWANG DAIRY products. Order now and get farm-fresh dairy delivered to your doorstep.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/products" class="inline-flex items-center px-8 py-4 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Shop Now
            </a>
            <a href="/contact" class="inline-flex items-center px-8 py-4 border-2 border-dark-brown text-dark-brown font-semibold rounded-xl hover:bg-dark-brown hover:text-white transition-colors">
                Contact Us
            </a>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
