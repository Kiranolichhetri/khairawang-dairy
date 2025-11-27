<?php
/**
 * Forgot Password Page
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
                Forgot your password?
            </h2>
            <p class="mt-2 text-gray-600">
                No worries! Enter your email and we'll send you a reset link.
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($view->flash('success')): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <?= $view->e($view->flash('success')) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($view->flash('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?= $view->e($view->flash('error')) ?>
            </div>
        <?php endif; ?>

        <!-- Forgot Password Form -->
        <form class="mt-8 space-y-6 bg-white rounded-2xl shadow-soft-lg p-8" action="/forgot-password" method="POST">
            <?= $view->csrf() ?>
            
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

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-accent-orange hover:bg-accent-orange-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-orange transition-all">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-white opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    Send Reset Link
                </button>
            </div>
        </form>

        <!-- Back to Login -->
        <p class="text-center text-sm text-gray-600">
            Remember your password?
            <a href="/login" class="font-medium text-accent-orange hover:text-accent-orange-dark transition-colors">
                Back to login
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
