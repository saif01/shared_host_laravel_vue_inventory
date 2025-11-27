<?php

namespace App\Traits;

trait HasImageUrl
{
    /**
     * Get the image fields that should be processed
     * Override this method in your model to specify which fields are image fields
     * 
     * @return array
     */
    protected function getImageFields()
    {
        // Default: check common image field names
        $defaultFields = ['avatar', 'image', 'images', 'featured_image', 'og_image', 'thumbnail'];
        $fillable = $this->getFillable();
        
        return array_intersect($defaultFields, $fillable);
    }

    /**
     * Mutator: Normalize image path when setting (remove host)
     */
    public function setAttribute($key, $value)
    {
        // Check if this is an image field
        $imageFields = $this->getImageFields();
        
        if (in_array($key, $imageFields) && $value !== null) {
            // Handle array fields (like 'images')
            if (is_array($value)) {
                $value = array_map(function ($item) {
                    if (is_string($item)) {
                        return $this->normalizeImagePath($item);
                    }
                    // Handle array items that might have 'url' or 'path' keys
                    if (is_array($item)) {
                        $modifiedItem = $item;
                        foreach (['url', 'path', 'image'] as $pathKey) {
                            if (isset($modifiedItem[$pathKey]) && is_string($modifiedItem[$pathKey])) {
                                $modifiedItem[$pathKey] = $this->normalizeImagePath($modifiedItem[$pathKey]);
                            }
                        }
                        return $modifiedItem;
                    }
                    return $item;
                }, $value);
            } elseif (is_string($value)) {
                $value = $this->normalizeImagePath($value);
            }
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Accessor: Add host when getting image URL
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // Check if this is an image field
        $imageFields = $this->getImageFields();
        
        if (in_array($key, $imageFields) && $value !== null) {
            // Handle array fields (like 'images')
            if (is_array($value)) {
                $value = array_map(function ($item) {
                    if (is_string($item)) {
                        return $this->getImageUrl($item);
                    }
                    // Handle array items that might have 'url' or 'path' keys
                    if (is_array($item)) {
                        $modifiedItem = $item;
                        foreach (['url', 'path', 'image'] as $pathKey) {
                            if (isset($modifiedItem[$pathKey]) && is_string($modifiedItem[$pathKey])) {
                                $modifiedItem[$pathKey] = $this->getImageUrl($modifiedItem[$pathKey]);
                            }
                        }
                        return $modifiedItem;
                    }
                    return $item;
                }, $value);
            } elseif (is_string($value)) {
                $value = $this->getImageUrl($value);
            }
        }
        
        return $value;
    }

    /**
     * Get the full URL for an image path
     * If the path is already a full URL, return it as is
     * Otherwise, generate URL from relative path
     * 
     * @param string|null $path
     * @return string|null
     */
    protected function getImageUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        // If it's already a full URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // If it starts with http:// or https:// but not valid URL, try to extract path
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $parsedUrl = parse_url($path);
            if (isset($parsedUrl['path'])) {
                $path = ltrim($parsedUrl['path'], '/');
            }
        }

        // Generate URL from relative path
        return asset($path);
    }

    /**
     * Normalize a path by removing host/domain
     * 
     * @param string|null $path
     * @return string|null
     */
    protected function normalizeImagePath($path)
    {
        if (empty($path) || !is_string($path)) {
            return $path;
        }

        $path = trim($path);

        // If it's a full URL, extract just the path
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($path);
            $path = $parsedUrl['path'] ?? '';
            // Remove leading slash
            $path = ltrim($path, '/');
            return $path;
        }

        // If it starts with http:// or https:// but filter_var failed, try manual extraction
        if (preg_match('#^https?://([^/]+)(/.*)?$#', $path, $matches)) {
            $path = isset($matches[2]) ? ltrim($matches[2], '/') : '';
            return $path;
        }

        // Remove http:// or https:// if still present (malformed URLs)
        $path = preg_replace('#^https?://[^/]+/#', '', $path);
        $path = preg_replace('#^https?://[^/]+$#', '', $path);

        // Remove leading slash if present
        $path = ltrim($path, '/');

        return $path;
    }
}

