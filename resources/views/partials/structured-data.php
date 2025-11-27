<?php
/**
 * Structured Data (JSON-LD) Partial
 * 
 * @var string $type Schema type (organization, product, article, breadcrumb, local_business)
 * @var array $data Data for the schema
 */

use App\Services\SeoService;

$seoService = new SeoService();
echo $seoService->generateStructuredData($type ?? 'organization', $data ?? []);
?>
