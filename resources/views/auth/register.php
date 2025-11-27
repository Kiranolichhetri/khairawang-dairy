<?php
/**
 * Register Page
 * 
 * @var \Core\View $view
 * @var string $title
 */
$view->extends('auth');
?>

<?php $view->section('title'); ?>
<?= $view->e($title) ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-cream">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <a href="/" class="inline-flex items-center gap-2 text-3xl font-heading font-bold text-dark-brown">
                <span class="text-4xl">🥛</span>
                <span>KHAIRAWANG DAIRY</span>
            </a>
            <h2 class="mt-6 text-2xl font-bold text-dark-brown">
                Create your account
            </h2>
            <p class="mt-2 text-gray-600">
                Join us for fresh dairy delivered to your door
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($view->flash('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?= $view->e($view->flash('error')) ?>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form class="mt-8 space-y-6 bg-white rounded-2xl shadow-soft-lg p-8" action="/register" method="POST">
            <?= $view->csrf() ?>
            
            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-dark-brown mb-1">
                        Full Name
                    </label>
                    <input id="name" 
                           name="name" 
                           type="text" 
                           autocomplete="name" 
                           required 
                           value="<?= $view->e($view->old('name')) ?>"
                           class="appearance-none relative block w-full px-4 py-3 border <?= $view->hasError('name') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg text-dark-brown placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent transition-all"
                           placeholder="Enter your full name">
                    <?php if ($view->hasError('name')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name')) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-dark-brown mb-1">
                        Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="<?= $view->e($view->old('email')) ?>"
                           class="appearance-none relative block w-full px-4 py-3 border <?= $view->hasError('email') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg text-dark-brown placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent transition-all"
                           placeholder="you@example.com">
                    <?php if ($view->hasError('email')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('email')) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-dark-brown mb-1">
                        Phone Number
                    </label>
                    <input id="phone" 
                           name="phone" 
                           type="tel" 
                           autocomplete="tel" 
                           required 
                           value="<?= $view->e($view->old('phone')) ?>"
                           class="appearance-none relative block w-full px-4 py-3 border <?= $view->hasError('phone') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg text-dark-brown placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent transition-all"
                           placeholder="+977 98XXXXXXXX">
                    <?php if ($view->hasError('phone')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('phone')) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-dark-brown mb-1">
                        Password
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="appearance-none relative block w-full px-4 py-3 border <?= $view->hasError('password') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg text-dark-brown placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent transition-all"
                           placeholder="Minimum 8 characters">
                    <?php if ($view->hasError('password')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('password')) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-dark-brown mb-1">
                        Confirm Password
                    </label>
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="appearance-none relative block w-full px-4 py-3 border <?= $view->hasError('password_confirmation') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg text-dark-brown placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent transition-all"
                           placeholder="Confirm your password">
                    <?php if ($view->hasError('password_confirmation')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('password_confirmation')) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Terms -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" 
                           name="terms" 
                           type="checkbox" 
                           required
                           class="h-4 w-4 text-accent-orange focus:ring-accent-orange border-gray-300 rounded">
                </div>
                <div class="ml-2 text-sm">
                    <label for="terms" class="text-gray-700">
                        I agree to the 
                        <a href="/terms" class="text-accent-orange hover:text-accent-orange-dark">Terms of Service</a> 
                        and 
                        <a href="/privacy" class="text-accent-orange hover:text-accent-orange-dark">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-accent-orange hover:bg-accent-orange-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-orange transition-all">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-white opacity-70 group-hover:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                    </span>
                    Create Account
                </button>
            </div>

            <!-- Social Login -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="/auth/google" 
                       class="w-full inline-flex justify-center items-center gap-3 py-3 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Continue with Google
                    </a>
                </div>
            </div>
        </form>

        <!-- Login Link -->
        <p class="text-center text-sm text-gray-600">
            Already have an account?
            <a href="/login" class="font-medium text-accent-orange hover:text-accent-orange-dark transition-colors">
                Sign in instead
            </a>
        </p>

        <!-- Back to Home -->
        <p class="text-center">
            <a href="/" class="text-sm text-gray-500 hover:text-dark-brown transition-colors">
                ← Back to home
            </a>
        </p>
    </div>
</div>
<?php $view->endSection(); ?>
