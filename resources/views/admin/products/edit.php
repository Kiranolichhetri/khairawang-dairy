<?php
/**
 * Admin Edit Product
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $product
 * @var array $categories
 * @var array $statuses
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="/admin/products" class="text-sm text-gray-500 hover:text-accent-orange">
            ← Back to Products
        </a>
    </div>
    
    <form action="/admin/products/<?= $view->e($product['id']) ?>" method="POST" class="space-y-6" enctype="multipart/form-data">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                    <input type="text" id="name_en" name="name_en" required 
                           value="<?= $view->e($view->old('name_en', $product['name_en'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('name_en') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('name_en')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name_en')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="name_ne" class="block text-sm font-medium text-gray-700 mb-1">Name (Nepali)</label>
                    <input type="text" id="name_ne" name="name_ne" 
                           value="<?= $view->e($view->old('name_ne', $product['name_ne'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug *</label>
                    <input type="text" id="slug" name="slug" required 
                           value="<?= $view->e($view->old('slug', $product['slug'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('slug') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('slug')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('slug')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select id="category_id" name="category_id" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->getKey() ?>" <?= ($view->old('category_id', $product['category_id'] ?? '')) == $category->getKey() ? 'selected' : '' ?>>
                                <?= $view->e($category->attributes['name_en'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <input type="text" id="short_description" name="short_description" 
                           value="<?= $view->e($view->old('short_description', $product['short_description'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div class="md:col-span-2">
                    <label for="description_en" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea id="description_en" name="description_en" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange"><?= $view->e($view->old('description_en', $product['description_en'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Pricing & Inventory</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (NPR) *</label>
                    <input type="number" id="price" name="price" required step="0.01" min="0"
                           value="<?= $view->e($view->old('price', $product['price'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('price') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price (NPR)</label>
                    <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                           value="<?= $view->e($view->old('sale_price', $product['sale_price'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" id="sku" name="sku"
                           value="<?= $view->e($view->old('sku', $product['sku'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" min="0"
                           value="<?= $view->e($view->old('stock', $product['stock'] ?? '0')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert</label>
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0"
                           value="<?= $view->e($view->old('low_stock_threshold', $product['low_stock_threshold'] ?? '10')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0"
                           value="<?= $view->e($view->old('weight', $product['weight'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
            </div>
        </div>
        
<?php
            // Prepare existing images JSON
            $existingImages = $product['images'] ?? [];
            if (is_string($existingImages)) {
                $existingImages = json_decode($existingImages, true) ?? [];
            }
            $existingImagesJson = json_encode($existingImages);
        ?>
        
        <!-- Product Images -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Product Images</h3>
            
            <div id="image-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-accent-orange transition-colors">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-500">PNG, JPG, GIF, WebP up to 5MB</p>
                <input type="file" id="image-input" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="hidden">
            </div>
            
            <div id="upload-progress" class="mt-4 hidden">
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-accent-orange h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <span id="progress-text" class="text-sm text-gray-500">0%</span>
                </div>
            </div>
            
            <div id="image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Uploaded images will appear here -->
            </div>
            
            <input type="hidden" name="images" id="images-json" value="<?= $view->e($existingImagesJson) ?>">
        </div>
        
        <!-- Status & Options -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Status & Options</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select id="status" name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="draft" <?= ($view->old('status', $product['status'] ?? '')) === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($view->old('status', $product['status'] ?? '')) === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= ($view->old('status', $product['status'] ?? '')) === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <div class="flex items-center pt-6">
                    <input type="checkbox" id="featured" name="featured" value="1"
                           <?= $view->old('featured', $product['featured'] ?? false) ? 'checked' : '' ?>
                           class="h-4 w-4 text-accent-orange focus:ring-accent-orange border-gray-300 rounded">
                    <label for="featured" class="ml-2 text-sm text-gray-700">Featured Product</label>
                </div>
            </div>
        </div>
        
        <!-- SEO -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">SEO</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                    <input type="text" id="seo_title" name="seo_title"
                           value="<?= $view->e($view->old('seo_title', $product['seo_title'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                    <textarea id="seo_description" name="seo_description" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange"><?= $view->e($view->old('seo_description', $product['seo_description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="/admin/products" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Update Product
            </button>
        </div>
    </form>
</div>

<script>
// Image Upload Functionality
(function() {
    const uploadArea = document.getElementById('image-upload-area');
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const imagesJson = document.getElementById('images-json');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    // Initialize with existing images
    let uploadedImages = [];
    try {
        uploadedImages = JSON.parse(imagesJson.value) || [];
    } catch (e) {
        uploadedImages = [];
    }
    
    // Render existing images on page load
    renderPreview();
    
    // Click to upload
    uploadArea.addEventListener('click', function() {
        imageInput.click();
    });
    
    // Drag and drop events
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('border-accent-orange', 'bg-orange-50');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-accent-orange', 'bg-orange-50');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('border-accent-orange', 'bg-orange-50');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });
    
    // File input change
    imageInput.addEventListener('change', function() {
        handleFiles(this.files);
        this.value = ''; // Reset input to allow re-selecting same files
    });
    
    function handleFiles(files) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type: ' + file.name + '. Allowed: JPEG, PNG, GIF, WebP');
                continue;
            }
            
            if (file.size > maxSize) {
                alert('File too large: ' + file.name + '. Maximum size: 5MB');
                continue;
            }
            
            uploadFile(file);
        }
    }
    
    function uploadFile(file) {
        const formData = new FormData();
        formData.append('image', file);
        
        // Get CSRF token
        const csrfInput = document.querySelector('input[name="_csrf_token"]');
        if (csrfInput) {
            formData.append('_csrf_token', csrfInput.value);
        }
        
        // Show progress
        uploadProgress.classList.remove('hidden');
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressText.textContent = percent + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            uploadProgress.classList.add('hidden');
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && response.url) {
                        addImageToPreview(response.url);
                    } else {
                        alert('Upload failed: ' + (response.message || 'Unknown error'));
                    }
                } catch (e) {
                    alert('Upload failed: Invalid response');
                }
            } else {
                alert('Upload failed: Server error');
            }
        });
        
        xhr.addEventListener('error', function() {
            uploadProgress.classList.add('hidden');
            alert('Upload failed: Network error');
        });
        
        xhr.open('POST', '/admin/products/upload-image');
        xhr.send(formData);
    }
    
    function addImageToPreview(imageUrl) {
        uploadedImages.push(imageUrl);
        updateImagesJson();
        renderPreview();
    }
    
    function removeImage(index) {
        uploadedImages.splice(index, 1);
        updateImagesJson();
        renderPreview();
    }
    
    function updateImagesJson() {
        imagesJson.value = JSON.stringify(uploadedImages);
    }
    
    function renderPreview() {
        imagePreview.innerHTML = '';
        
        uploadedImages.forEach(function(url, index) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
                <img src="${escapeHtml(url)}" alt="Product image" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button" onclick="window.KhairawangImageUpload.removeImage(${index})" 
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            imagePreview.appendChild(div);
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Expose removeImage function globally via namespace
    window.KhairawangImageUpload = window.KhairawangImageUpload || {};
    window.KhairawangImageUpload.removeImage = removeImage;
})();
</script>
<?php $view->endSection(); ?>
