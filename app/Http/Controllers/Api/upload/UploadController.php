<?php

namespace App\Http\Controllers\Api\upload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Support\MediaPath;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'folder' => 'nullable|string|max:255', // Optional folder name
        ]);

        try {
            $file = $request->file('image');
            $folder = $request->input('folder', 'products');
            $prefix = $request->input('prefix', ''); // Product name prefix
            
            // Get file info before moving
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            // Generate filename with prefix if provided
            $filenameBase = '';
            if (!empty($prefix)) {
                // Sanitize prefix: remove special chars, convert to lowercase, replace spaces with hyphens
                $sanitizedPrefix = Str::slug($prefix);
                $filenameBase = $sanitizedPrefix . '-';
            }
            $filename = $filenameBase . Str::random(20) . '.' . $extension;
            
            // Create folder in public directory if it doesn't exist
            $publicPath = public_path('uploads/' . $folder);
            if (!file_exists($publicPath)) {
                File::makeDirectory($publicPath, 0755, true);
                // Ensure .htaccess exists in uploads folder
                $this->ensureUploadsHtaccess();
            }
            
            // Move file to public/uploads/{folder}/
            $file->move($publicPath, $filename);
            
            // Set proper file permissions (644 = readable by web server)
            chmod($publicPath . '/' . $filename, 0644);
            
            // Get relative path and full URL
            $relativePath = MediaPath::normalize('uploads/' . $folder . '/' . $filename);
            $url = MediaPath::url($relativePath);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $relativePath,
                'filename' => $filename,
                'size' => $fileSize,
                'mime_type' => $mimeType,
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadMultipleImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'folder' => 'nullable|string|max:255',
        ]);

        try {
            $files = $request->file('images');
            $folder = $request->input('folder', 'products');
            $prefix = $request->input('prefix', ''); // Product name prefix
            $uploaded = [];

            // Create folder in public directory if it doesn't exist
            $publicPath = public_path('uploads/' . $folder);
            if (!file_exists($publicPath)) {
                File::makeDirectory($publicPath, 0755, true);
                // Ensure .htaccess exists in uploads folder
                $this->ensureUploadsHtaccess();
            }

            // Generate filename base with prefix if provided
            $filenameBase = '';
            if (!empty($prefix)) {
                // Sanitize prefix: remove special chars, convert to lowercase, replace spaces with hyphens
                $sanitizedPrefix = Str::slug($prefix);
                $filenameBase = $sanitizedPrefix . '-';
            }

            foreach ($files as $index => $file) {
                // Get file info before moving
                $extension = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
                
                // Generate filename with prefix and index for uniqueness
                $filename = $filenameBase . Str::random(20) . '-' . ($index + 1) . '.' . $extension;
                
                // Move file to public/uploads/{folder}/
                $file->move($publicPath, $filename);
                
                // Set proper file permissions (644 = readable by web server)
                chmod($publicPath . '/' . $filename, 0644);
                
                // Get relative path and full URL
                $relativePath = MediaPath::normalize('uploads/' . $folder . '/' . $filename);
                $url = MediaPath::url($relativePath);

                $uploaded[] = [
                    'url' => $url,
                    'path' => $relativePath,
                    'filename' => $filename,
                    'size' => $fileSize,
                    'mime_type' => $mimeType,
                ];
            }

            return response()->json([
                'success' => true,
                'images' => $uploaded,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'folder' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $folder = $request->input('folder', 'downloads');
            $prefix = $request->input('prefix', ''); // Product name prefix
            
            // Get file info before moving
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $originalName = $file->getClientOriginalName();
            
            // Generate filename with prefix if provided
            $filenameBase = '';
            if (!empty($prefix)) {
                // Sanitize prefix: remove special chars, convert to lowercase, replace spaces with hyphens
                $sanitizedPrefix = Str::slug($prefix);
                $filenameBase = $sanitizedPrefix . '-';
            }
            // Use original filename (sanitized) with random suffix for uniqueness
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $sanitizedName = Str::slug($nameWithoutExt);
            $filename = $filenameBase . $sanitizedName . '-' . Str::random(10) . '.' . $extension;
            
            // Create folder in public directory if it doesn't exist
            $publicPath = public_path('uploads/' . $folder);
            if (!file_exists($publicPath)) {
                File::makeDirectory($publicPath, 0755, true);
                // Ensure .htaccess exists in uploads folder
                $this->ensureUploadsHtaccess();
            }
            
            // Move file to public/uploads/{folder}/
            $file->move($publicPath, $filename);
            
            // Set proper file permissions (644 = readable by web server)
            chmod($publicPath . '/' . $filename, 0644);
            
            // Get relative path and full URL
            $relativePath = MediaPath::normalize('uploads/' . $folder . '/' . $filename);
            $url = MediaPath::url($relativePath);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $relativePath,
                'filename' => $filename,
                'original_name' => $originalName,
                'size' => $fileSize,
                'mime_type' => $mimeType,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            // Normalize to uploads/ path
            $normalizedPath = MediaPath::normalize($request->path);
            $relativePath = $normalizedPath ? ltrim($normalizedPath, '/') : '';
            if (Str::startsWith($relativePath, 'storage/uploads')) {
                $relativePath = ltrim(Str::replaceFirst('storage/', '', $relativePath), '/');
            }

            $fullPath = public_path($relativePath);
            
            if (file_exists($fullPath)) {
                unlink($fullPath);
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure .htaccess file exists in uploads folder for proper file access
     */
    private function ensureUploadsHtaccess()
    {
        $htaccessPath = public_path('uploads/.htaccess');
        
        // Only create if it doesn't exist
        if (!file_exists($htaccessPath)) {
            $htaccessContent = <<<'HTACCESS'
# Allow direct access to uploaded files
<IfModule mod_rewrite.c>
    RewriteEngine Off
</IfModule>

# Allow access to image and file types
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico|pdf|doc|docx|xls|xlsx|zip|rar|txt|mp4|mp3|avi|mov|wmv|flv|swf)$">
    <IfModule mod_authz_core.c>
        Require all granted
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Allow from all
    </IfModule>
</FilesMatch>

# Set proper MIME types for images
<IfModule mod_mime.c>
    AddType image/jpeg .jpg .jpeg
    AddType image/png .png
    AddType image/gif .gif
    AddType image/webp .webp
    AddType image/svg+xml .svg
    AddType image/x-icon .ico
</IfModule>

# Enable CORS for images (if needed)
<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico)$">
        Header set Access-Control-Allow-Origin "*"
        Header set Access-Control-Allow-Methods "GET, OPTIONS"
    </FilesMatch>
</IfModule>

# Disable directory browsing
Options -Indexes

# Prevent execution of PHP files in uploads folder (security)
<FilesMatch "\.php$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>
HTACCESS;
            
            file_put_contents($htaccessPath, $htaccessContent);
            chmod($htaccessPath, 0644);
        }
    }
}
