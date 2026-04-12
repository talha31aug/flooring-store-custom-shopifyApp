<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Illuminate\Support\Facades\Http; // Import the Http facade
use Illuminate\Support\Facades\Validator; // Import Validator

class ShopifyController extends Controller
{



    public function customWebHook() {
        // Check if the user is authenticated
        $userName = Auth::check() ? Auth::user()->name : 'Guest';

        // Manually set the shop domain and access token using environment variables
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';

        // Validate incoming request data
        // $validator = Validator::make($request->all(), [
        //     'title' => 'required|string|max:255',
        //     'price' => 'required|numeric|min:0',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['message' => 'Validation Error: ' . $validator->errors()], 422);
        // }

        // Product ID and Variant ID to update
        $productId = 9525188952371;
        $variantId = 50416055189811;

        // Prepare the data to update the product title and price
        $data = [
            'product' => [
                'id' => $productId,
                'title' => 'Webhook Run',
                'variants' => [
                    [
                        'id' => $variantId,
                        'price' => '12',
                    ],
                ],
            ],
        ];

        // Update the product
        try {
            $updateResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->put("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json", $data);

            // Check for a successful update response
            if ($updateResponse->successful()) {
                return response()->json(['message' => 'Product updated successfully!']);
            } else {
                $errorResponse = json_decode($updateResponse->body(), true);
                return response()->json(['message' => 'Update Error: ' . $updateResponse->status() . ': ' . $errorResponse['errors']], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Update failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }


    public function customAPICreateProduct(Request $request) {
        // Check if the user is authenticated
        $userName = Auth::check() ? Auth::user()->name : 'Guest';
    
        // Manually set the shop domain and access token using environment variables
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';
    
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'img' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error: ' . $validator->errors()], 422);
        }
    
        // Prepare the data to create the new product with specific options
        $data = [
            'product' => [
                'title' => $request->title,
                'tags' => 'ps-api-product',  // Add the tag
                'status' => 'active',  // Ensure the product is published and active
                'published_scope' => 'web',  // Available only on the online store
    
                'variants' => [
                    [
                        'price' => $request->price,
                        'inventory_management' => null,  // No inventory tracking
                        'inventory_policy' => 'deny',  // Prevents selling when out of stock if you decide to track inventory later
                        'requires_shipping' => true,  // Mark as a physical product
                    ],
                ],
                'images' => [
                    [
                        'src' => 'https:' . $request->img,  // Image URL passed from the request
                    ],
                ],
            ],
        ];
    
        // Create the new product
        try {
            $createResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->post("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", $data);
    
            // Check for a successful creation response
            if ($createResponse->successful()) {
                // Get the created product and variant IDs
                $product = $createResponse->json()['product'];
                $productId = $product['id'];
                $variantId = $product['variants'][0]['id'];  // Assuming the first variant is created
    
                return response()->json([
                    'message' => 'Product created successfully!',
                    'product_id' => $productId,  // Return product ID
                    'variant_id' => $variantId,  // Return variant ID
                ]);
            } else {
                $errorResponse = json_decode($createResponse->body(), true);
                return response()->json(['message' => 'Creation Error: ' . $createResponse->status() . ': ' . $errorResponse['errors']], 400);
            }
    
        } catch (\Exception $e) {
            \Log::error('Creation failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    

    public function customAPIDeleteProduct(Request $request) {
        // Log the incoming request
        \Log::info('Webhook received', ['headers' => $request->headers->all(), 'body' => $request->all()]);
    
        $shopifySecret = env('SHOPIFY_API_SECRET');  // Ensure you set this in .env file
        // $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        // $calculatedHmac = base64_encode(hash_hmac('sha256', $request->getContent(), $shopifySecret, true));
    
        // // Verify HMAC
        // if (!hash_equals($hmacHeader, $calculatedHmac)) {
        //     \Log::warning('Webhook HMAC verification failed', [
        //         'hmacHeader' => $hmacHeader,
        //         'calculatedHmac' => $calculatedHmac
        //     ]);
        //     return response()->json(['message' => 'HMAC verification failed'], 403);  // Forbidden
        // }
    
        // \Log::info('HMAC verification passed');
    
        // Check if the user is authenticated
        $userName = Auth::check() ? Auth::user()->name : 'Guest';
        // Manually set the shop domain and access token using environment variables
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';

        // The tag to search for
        $tag = 'ps-api-product';

        try {
            // First, fetch all products with the tag
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", [
                'fields' => 'id,tags',
                'limit' => 250,  // Fetch up to 250 products in one request (max limit)
                'tag' => $tag,  // Search products with the tag
            ]);

            if ($response->successful()) {
                $products = $response->json()['products'];

                if (empty($products)) {
                    return response()->json(['message' => 'No products found with the specified tag.']);
                }

                // Loop through each product and delete it
                foreach ($products as $product) {
                    if (strpos($product['tags'], $tag) !== false) {  // Ensure the product has the specific tag
                        $productId = $product['id'];

                        // Make a DELETE request to delete the product
                        $deleteResponse = Http::withHeaders([
                            'X-Shopify-Access-Token' => $accessToken,
                        ])->delete("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json");

                        if (!$deleteResponse->successful()) {
                            return response()->json(['message' => 'Failed to delete product with ID: ' . $productId], 400);
                        }
                    }
                }

                return response()->json(['message' => 'All products with the specified tag were deleted successfully!']);
            } else {
                return response()->json(['message' => 'Failed to retrieve products.'], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to delete products', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
        
    }
    
    
    
    
    
    // Add this method to create products with session ID
    public function customAPICreateProductWithSession(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'img' => 'required|string',
            'session_id' => 'required|string|max:100', // NEW: Session ID from JS
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error: ' . $validator->errors()], 422);
        }
    
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';
    
        // Add session ID to tags
        $sessionId = $request->session_id;
        $sessionTag = 'ps-session-' . $sessionId;
    
        $data = [
            'product' => [
                'title' => $request->title,
                'tags' => 'ps-api-product, ' . $sessionTag,  // BOTH tags
                'status' => 'active',
                'published_scope' => 'web',
                'variants' => [
                    [
                        'price' => $request->price,
                        'inventory_management' => null,
                        'inventory_policy' => 'deny',
                        'requires_shipping' => true,
                    ],
                ],
                'images' => [
                    [
                        'src' => 'https:' . $request->img,
                    ],
                ],
            ],
        ];
    
        try {
            $createResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->post("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", $data);
    
            if ($createResponse->successful()) {
                $product = $createResponse->json()['product'];
                $productId = $product['id'];
                $variantId = $product['variants'][0]['id'];
    
                return response()->json([
                    'message' => 'Product created successfully!',
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'session_id' => $sessionId, // Return session ID
                ]);
            } else {
                $errorResponse = json_decode($createResponse->body(), true);
                return response()->json(['message' => 'Creation Error: ' . $createResponse->status() . ': ' . $errorResponse['errors']], 400);
            }
    
        } catch (\Exception $e) {
            \Log::error('Creation failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    
    // Add this method to delete session-specific products
    public function customAPIDeleteSessionProducts(Request $request) {
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            return response()->json(['message' => 'Session ID is required'], 422);
        }
    
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';
    
        // Search for products with this session's tag
        $sessionTag = 'ps-session-' . $sessionId;
        
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", [
                'fields' => 'id,tags',
                'limit' => 250,
                'tag' => $sessionTag, // Search by session tag
            ]);
    
            if ($response->successful()) {
                $products = $response->json()['products'];
    
                if (empty($products)) {
                    return response()->json(['message' => 'No products found for this session.']);
                }
    
                $deletedCount = 0;
                foreach ($products as $product) {
                    if (strpos($product['tags'], $sessionTag) !== false) {
                        $productId = $product['id'];
                        
                        $deleteResponse = Http::withHeaders([
                            'X-Shopify-Access-Token' => $accessToken,
                        ])->delete("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json");
    
                        if ($deleteResponse->successful()) {
                            $deletedCount++;
                        }
                    }
                }
    
                return response()->json([
                    'message' => 'Session products deleted successfully!',
                    'deleted_count' => $deletedCount,
                    'session_id' => $sessionId
                ]);
            } else {
                return response()->json(['message' => 'Failed to retrieve products.'], 400);
            }
    
        } catch (\Exception $e) {
            \Log::error('Failed to delete session products', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    


    // public function customAPIDeleteProduct(Request $request) {

    //     \Log::info('Webhook triggered before payload logging.');
    //     \Log::info('Webhook triggered', ['payload' => $request->all()]);
    //     \Log::info('Webhook triggered after payload logging.');

    //     \Log::info('Request Method: ' . $request->method());
    //     \Log::info('Request Headers: ', $request->headers->all());


    //     // Check if the user is authenticated
    //     $userName = Auth::check() ? Auth::user()->name : 'Guest';
    //     // Manually set the shop domain and access token using environment variables
    //     $shopDomain = env('SHOP_DOMAIN');
    //     $accessToken = env('SHOPIFY_ACCESS_TOKEN');
    //     $apiVersion = '2024-07';

    //     // The tag to search for
    //     $tag = 'ps-api-product';

    //     try {
    //         // First, fetch all products with the tag
    //         $response = Http::withHeaders([
    //             'X-Shopify-Access-Token' => $accessToken,
    //         ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", [
    //             'fields' => 'id,tags',
    //             'limit' => 250,  // Fetch up to 250 products in one request (max limit)
    //             'tag' => $tag,  // Search products with the tag
    //         ]);

    //         if ($response->successful()) {
    //             $products = $response->json()['products'];

    //             if (empty($products)) {
    //                 return response()->json(['message' => 'No products found with the specified tag.']);
    //             }

    //             // Loop through each product and delete it
    //             foreach ($products as $product) {
    //                 if (strpos($product['tags'], $tag) !== false) {  // Ensure the product has the specific tag
    //                     $productId = $product['id'];

    //                     // Make a DELETE request to delete the product
    //                     $deleteResponse = Http::withHeaders([
    //                         'X-Shopify-Access-Token' => $accessToken,
    //                     ])->delete("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json");

    //                     if (!$deleteResponse->successful()) {
    //                         return response()->json(['message' => 'Failed to delete product with ID: ' . $productId], 400);
    //                     }
    //                 }
    //             }

    //             return response()->json(['message' => 'All products with the specified tag were deleted successfully!']);
    //         } else {
    //             return response()->json(['message' => 'Failed to retrieve products.'], 400);
    //         }

    //     } catch (\Exception $e) {
    //         \Log::error('Failed to delete products', ['error' => $e->getMessage()]);
    //         return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
    //     }
    // }
    


    public function customAPI(Request $request) {
        // Check if the user is authenticated
        $userName = Auth::check() ? Auth::user()->name : 'Guest';

        // Manually set the shop domain and access token using environment variables
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error: ' . $validator->errors()], 422);
        }

        // Product ID and Variant ID to update
        $productId = 14859401855360;
        $variantId = 52574951702912;

        // Prepare the data to update the product title and price
        $data = [
            'product' => [
                'id' => $productId,
                'title' => $request->title,
                'variants' => [
                    [
                        'id' => $variantId,
                        'price' => $request->price,
                    ],
                ],
            ],
        ];

        // Update the product
        try {
            $updateResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->put("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json", $data);

            // Check for a successful update response
            if ($updateResponse->successful()) {
                return response()->json(['message' => 'Product updated successfully!']);
            } else {
                $errorResponse = json_decode($updateResponse->body(), true);
                return response()->json(['message' => 'Update Error: ' . $updateResponse->status() . ': ' . $errorResponse['errors']], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Update failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    
    
    
    
    // Add this method to clean up ALL session products older than a certain time
    public function cleanupOldSessionProducts()
    {
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';
        
        // Set maximum age for products (REDUCED TIME - adjust as needed)
        $maxAgeMinutes = 60;
        $currentTime = time();
        $cutoffTime = $currentTime - ($maxAgeMinutes * 60); // Convert minutes to seconds
        
        try {
            $deletedCount = 0;
            $errorCount = 0;
            $pageInfo = null;
            $hasNextPage = true;
            
            // Loop through paginated results to get ALL products
            while ($hasNextPage) {
                $params = [
                    'fields' => 'id,tags,created_at',
                    'limit' => 250,
                    'tag' => 'ps-api-product',
                ];
                
                // Add pagination if we have pageInfo
                if ($pageInfo) {
                    $params['page_info'] = $pageInfo;
                }
                
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $accessToken,
                ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", $params);
                
                if ($response->successful()) {
                    $products = $response->json()['products'];
                    
                    // Check for next page
                    $linkHeader = $response->header('Link');
                    $hasNextPage = false;
                    $pageInfo = null;
                    
                    if ($linkHeader && preg_match('/page_info=([^>]+)>; rel="next"/', $linkHeader, $matches)) {
                        $pageInfo = $matches[1];
                        $hasNextPage = true;
                    }
                    
                    if (empty($products)) {
                        \Log::info('No ps-api-products found for cleanup on this page.');
                        continue;
                    }
                    
                    foreach ($products as $product) {
                        if (strpos($product['tags'], 'ps-api-product') !== false) {
                            // Check if product was created more than $maxAgeMinutes ago
                            $createdAt = strtotime($product['created_at']);
                            
                            // If we can't determine creation time OR it's older than cutoff, delete it
                            if (!$createdAt || $createdAt < $cutoffTime) {
                                $productId = $product['id'];
                                
                                // Delete the product
                                $deleteResponse = Http::withHeaders([
                                    'X-Shopify-Access-Token' => $accessToken,
                                ])->delete("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json");
                                
                                if ($deleteResponse->successful()) {
                                    $deletedCount++;
                                    \Log::info("Deleted old product ID: {$productId} created at: " . $product['created_at']);
                                } else {
                                    $errorCount++;
                                    \Log::error("Failed to delete product ID: {$productId}", [
                                        'response' => $deleteResponse->body()
                                    ]);
                                }
                                
                                // Reduced delay for faster processing
                                usleep(100000); // 0.1 seconds instead of 0.2
                            }
                        }
                    }
                    
                    \Log::info("Processed a page of products. Current total deleted: {$deletedCount}");
                    
                } else {
                    \Log::error('Failed to retrieve products for cleanup', [
                        'response' => $response->body()
                    ]);
                    // Continue with next page instead of returning error
                }
            }
            
            \Log::info("Cleanup completed. Deleted: {$deletedCount}, Errors: {$errorCount}");
            return response()->json([
                'message' => 'Cleanup completed successfully!',
                'deleted_count' => $deletedCount,
                'error_count' => $errorCount,
                'cutoff_minutes' => $maxAgeMinutes,
                'cutoff_time' => date('Y-m-d H:i:s', $cutoffTime),
                'current_time' => date('Y-m-d H:i:s', $currentTime)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Cleanup failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    
    // ULTRA-FAST CLEANUP: Deletes ALL ps-api-products with minimal delay
    public function cleanupAllSessionProductsFast()
    {
        $shopDomain = env('SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $apiVersion = '2024-07';
        
        try {
            $deletedCount = 0;
            $errorCount = 0;
            $pageInfo = null;
            $hasNextPage = true;
            
            // Loop through paginated results
            while ($hasNextPage) {
                $params = [
                    'fields' => 'id,tags',
                    'limit' => 250,
                    'tag' => 'ps-api-product',
                ];
                
                if ($pageInfo) {
                    $params['page_info'] = $pageInfo;
                }
                
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $accessToken,
                ])->get("https://{$shopDomain}/admin/api/{$apiVersion}/products.json", $params);
                
                if ($response->successful()) {
                    $products = $response->json()['products'];
                    
                    // Check for next page
                    $linkHeader = $response->header('Link');
                    $hasNextPage = false;
                    $pageInfo = null;
                    
                    if ($linkHeader && preg_match('/page_info=([^>]+)>; rel="next"/', $linkHeader, $matches)) {
                        $pageInfo = $matches[1];
                        $hasNextPage = true;
                    }
                    
                    if (empty($products)) {
                        \Log::info('No ps-api-products found for cleanup on this page.');
                        continue;
                    }
                    
                    // Use array_chunk to process in batches for better performance
                    $productChunks = array_chunk($products, 10);
                    
                    foreach ($productChunks as $chunk) {
                        // Process each chunk in parallel if possible, or sequentially with minimal delay
                        foreach ($chunk as $product) {
                            $productId = $product['id'];
                            
                            // Delete the product
                            $deleteResponse = Http::withHeaders([
                                'X-Shopify-Access-Token' => $accessToken,
                            ])->delete("https://{$shopDomain}/admin/api/{$apiVersion}/products/{$productId}.json");
                            
                            if ($deleteResponse->successful()) {
                                $deletedCount++;
                                \Log::info("Deleted product ID: {$productId}");
                            } else {
                                $errorCount++;
                                \Log::error("Failed to delete product ID: {$productId}", [
                                    'response' => $deleteResponse->body()
                                ]);
                            }
                        }
                        
                        // Small delay between chunks
                        usleep(50000); // 0.05 seconds
                    }
                    
                    \Log::info("Processed a page. Total deleted so far: {$deletedCount}");
                    
                } else {
                    \Log::error('Failed to retrieve products for cleanup on page');
                    // Continue to next page
                }
            }
            
            \Log::info("Fast cleanup completed. Deleted: {$deletedCount}, Errors: {$errorCount}");
            return response()->json([
                'message' => 'All session products cleaned up successfully!',
                'deleted_count' => $deletedCount,
                'error_count' => $errorCount,
                'processing_time' => 'fast_mode'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Cleanup failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
    
    
    
}