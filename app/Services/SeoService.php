<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\BlogPost;
use Core\Application;

/**
 * SEO Service
 * 
 * Handles SEO-related operations including meta tags, 
 * sitemaps, structured data, and more.
 */
class SeoService
{
    /**
     * Generate meta tags for a page
     * 
     * @param array<string, mixed> $page
     * @return array<string, string>
     */
    public function generateMeta(array $page): array
    {
        $siteName = config('app.site_name', 'KHAIRAWANG DAIRY');
        
        $title = $page['title'] ?? $siteName;
        $description = $page['description'] ?? config('seo.meta_description', 'Premium dairy products from Nepal');
        $keywords = $page['keywords'] ?? config('seo.meta_keywords', 'dairy, milk, nepal');
        $image = $page['image'] ?? config('seo.og_image', '/assets/images/og-image.jpg');
        $url = $page['url'] ?? $this->getCurrentUrl();
        $type = $page['type'] ?? 'website';
        
        return [
            'title' => $this->truncate($title, 60),
            'description' => $this->truncate($description, 160),
            'keywords' => $keywords,
            'og:title' => $this->truncate($title, 60),
            'og:description' => $this->truncate($description, 160),
            'og:image' => $this->absoluteUrl($image),
            'og:url' => $url,
            'og:type' => $type,
            'og:site_name' => $siteName,
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->truncate($title, 60),
            'twitter:description' => $this->truncate($description, 160),
            'twitter:image' => $this->absoluteUrl($image),
            'canonical' => $url,
        ];
    }

    /**
     * Generate meta tags for a product
     * 
     * @return array<string, string>
     */
    public function generateProductMeta(Product $product): array
    {
        $title = $product->attributes['seo_title'] ?? $product->getName();
        $description = $product->attributes['seo_description'] ?? $product->attributes['short_description'] ?? '';
        
        return $this->generateMeta([
            'title' => $title . ' | KHAIRAWANG DAIRY',
            'description' => $description,
            'image' => $product->getPrimaryImage(),
            'url' => $this->absoluteUrl('/products/' . $product->attributes['slug']),
            'type' => 'product',
        ]);
    }

    /**
     * Generate meta tags for a blog post
     * 
     * @return array<string, string>
     */
    public function generateBlogMeta(BlogPost $post): array
    {
        return $this->generateMeta([
            'title' => $post->getMetaTitle() . ' | KHAIRAWANG DAIRY Blog',
            'description' => $post->getMetaDescription(),
            'image' => $post->getFeaturedImageUrl(),
            'url' => $this->absoluteUrl('/blog/' . $post->attributes['slug']),
            'type' => 'article',
        ]);
    }

    /**
     * Generate XML sitemap content
     */
    public function generateSitemap(): string
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        $urls = [];
        
        // Static pages
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/products', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => '/blog', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/about', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $baseUrl . $page['loc'],
                'priority' => $page['priority'],
                'changefreq' => $page['changefreq'],
            ];
        }
        
        // Products
        $products = Product::where('status', 'published')->get();
        foreach ($products as $productData) {
            $urls[] = [
                'loc' => $baseUrl . '/products/' . $productData['slug'],
                'lastmod' => date('Y-m-d', strtotime($productData['updated_at'] ?? $productData['created_at'])),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }
        
        // Categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/categories/' . $category->attributes['slug'],
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }
        
        // Blog posts
        $posts = BlogPost::published(100);
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $post->attributes['slug'],
                'lastmod' => date('Y-m-d', strtotime($post->attributes['updated_at'] ?? $post->attributes['published_at'])),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }
        
        return $this->buildSitemapXml($urls);
    }

    /**
     * Build sitemap XML string
     * 
     * @param array<array<string, string>> $urls
     */
    protected function buildSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            
            if (!empty($url['lastmod'])) {
                $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            }
            
            if (!empty($url['changefreq'])) {
                $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            }
            
            if (!empty($url['priority'])) {
                $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            }
            
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate robots.txt content
     */
    public function generateRobotsTxt(): string
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "\n";
        $content .= "# Disallow admin and private areas\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /account/\n";
        $content .= "Disallow: /cart/\n";
        $content .= "Disallow: /checkout/\n";
        $content .= "\n";
        $content .= "# Sitemap\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";
        
        return $content;
    }

    /**
     * Generate JSON-LD structured data
     * 
     * @param array<string, mixed> $data
     */
    public function generateStructuredData(string $type, array $data): string
    {
        $structuredData = match ($type) {
            'organization' => $this->getOrganizationSchema(),
            'product' => $this->getProductSchema($data),
            'article' => $this->getArticleSchema($data),
            'breadcrumb' => $this->getBreadcrumbSchema($data),
            'local_business' => $this->getLocalBusinessSchema(),
            default => [],
        };
        
        if (empty($structuredData)) {
            return '';
        }
        
        return '<script type="application/ld+json">' . "\n" 
            . json_encode($structuredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) 
            . "\n</script>";
    }

    /**
     * Get Organization schema
     * 
     * @return array<string, mixed>
     */
    protected function getOrganizationSchema(): array
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'KHAIRAWANG DAIRY',
            'url' => $baseUrl,
            'logo' => $baseUrl . '/assets/images/logo.png',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('app.phone', '+977-9800000000'),
                'contactType' => 'customer service',
                'areaServed' => 'NP',
                'availableLanguage' => ['English', 'Nepali'],
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Kathmandu',
                'addressCountry' => 'NP',
            ],
            'sameAs' => array_filter([
                config('social.facebook'),
                config('social.instagram'),
                config('social.twitter'),
            ]),
        ];
    }

    /**
     * Get Product schema
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getProductSchema(array $data): array
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $this->absoluteUrl($data['image'] ?? ''),
            'url' => $baseUrl . '/products/' . ($data['slug'] ?? ''),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'KHAIRAWANG DAIRY',
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $data['price'] ?? 0,
                'priceCurrency' => 'NPR',
                'availability' => ($data['in_stock'] ?? true) 
                    ? 'https://schema.org/InStock' 
                    : 'https://schema.org/OutOfStock',
            ],
        ];
    }

    /**
     * Get Article schema for blog posts
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getArticleSchema(array $data): array
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $this->absoluteUrl($data['image'] ?? ''),
            'url' => $baseUrl . '/blog/' . ($data['slug'] ?? ''),
            'datePublished' => $data['published_at'] ?? '',
            'dateModified' => $data['updated_at'] ?? $data['published_at'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? 'KHAIRAWANG DAIRY',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'KHAIRAWANG DAIRY',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $baseUrl . '/assets/images/logo.png',
                ],
            ],
        ];
    }

    /**
     * Get Breadcrumb schema
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getBreadcrumbSchema(array $data): array
    {
        $items = $data['items'] ?? [];
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        $itemListElement = [];
        foreach ($items as $position => $item) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $baseUrl . $item['url'],
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];
    }

    /**
     * Get LocalBusiness schema
     * 
     * @return array<string, mixed>
     */
    protected function getLocalBusinessSchema(): array
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'KHAIRAWANG DAIRY',
            'image' => $baseUrl . '/assets/images/logo.png',
            'telephone' => config('app.phone', '+977-9800000000'),
            'email' => config('app.email', 'info@khairawangdairy.com'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('app.address', 'Kathmandu'),
                'addressLocality' => 'Kathmandu',
                'addressCountry' => 'NP',
            ],
            'priceRange' => '$$',
            'openingHours' => 'Mo-Sa 08:00-20:00',
        ];
    }

    /**
     * Get canonical URL
     */
    public function getCanonicalUrl(string $path = ''): string
    {
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate breadcrumbs array
     * 
     * @param array<array{name: string, url: string}> $items
     * @return array<array<string, string>>
     */
    public function generateBreadcrumbs(array $items): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
        ];
        
        return array_merge($breadcrumbs, $items);
    }

    /**
     * Get current URL
     */
    protected function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . '://' . $host . $uri;
    }

    /**
     * Convert relative URL to absolute
     */
    protected function absoluteUrl(string $url): string
    {
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        $baseUrl = config('app.url', 'https://khairawangdairy.com');
        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Truncate text to specified length
     */
    protected function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - 3) . '...';
    }
}
