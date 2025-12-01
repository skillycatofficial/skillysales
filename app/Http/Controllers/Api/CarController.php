<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    /**
     * Display a listing of cars with optional filters
     */
    public function index(Request $request)
    {
        $query = Car::with(['dealer.user', 'media']);
        
        // If user is authenticated and wants their own cars
        if ($request->has('my_cars') && $request->user()) {
            $dealer = $request->user()->dealer;
            if ($dealer) {
                $query->where('dealer_id', $dealer->id);
            } else {
                // User has no dealer, return empty
                return response()->json(['data' => [], 'current_page' => 1, 'total' => 0]);
            }
        }

        // Search by brand, model, or title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Filter by brand
        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by year range
        if ($request->has('min_year')) {
            $query->where('year', '>=', $request->min_year);
        }
        if ($request->has('max_year')) {
            $query->where('year', '<=', $request->max_year);
        }

        // Filter by fuel type
        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Filter by transmission
        if ($request->has('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        // Geolocation filter (if lat/lng provided)
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radius = $request->radius ?? 50; // Default 50km radius

            $query->whereHas('dealer', function ($q) use ($lat, $lng, $radius) {
                $q->selectRaw("*, 
                    ( 6371 * acos( cos( radians(?) ) * 
                    cos( radians( latitude ) ) * 
                    cos( radians( longitude ) - radians(?) ) + 
                    sin( radians(?) ) * 
                    sin( radians( latitude ) ) ) ) AS distance",
                    [$lat, $lng, $lat]
                )
                    ->having('distance', '<', $radius);
            });
        }

        // Sort by featured first, then by created_at
        $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate(20));
    }

    /**
     * Display the specified car
     */
    public function show($id)
    {
        $car = Car::with(['dealer.user', 'media'])->findOrFail($id);
        return response()->json($car);
    }

    /**
     * Store a newly created car (Dealer only)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'brand' => 'required|string',
                'model' => 'required|string',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'price' => 'required|numeric|min:0',
                'mileage' => 'required|integer|min:0',
                'condition' => 'required|in:new,used',
                'transmission' => 'required|string',
                'fuel_type' => 'required|string',
                'color' => 'required|string',
                'vin' => 'nullable|string',
                'description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Validate images separately - handle both images[] and images[0], images[1] formats
            $images = [];
            
            // Debug: Log all files in request
            \Log::info('All files in request: ' . json_encode(array_keys($request->allFiles())));
            \Log::info('Request has file images: ' . ($request->hasFile('images') ? 'yes' : 'no'));
            \Log::info('Request has file images.0: ' . ($request->hasFile('images.0') ? 'yes' : 'no'));
            
            // Try different formats
            if ($request->hasFile('images')) {
                $images = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            } elseif ($request->hasFile('images.0')) {
                // Handle indexed array format images.0, images.1, etc.
                $index = 0;
                while ($request->hasFile("images.$index")) {
                    $images[] = $request->file("images.$index");
                    $index++;
                }
            } else {
                // Try to get all files that start with 'images'
                $allFiles = $request->allFiles();
                foreach ($allFiles as $key => $file) {
                    if (strpos($key, 'images') === 0) {
                        if (is_array($file)) {
                            $images = array_merge($images, $file);
                        } else {
                            $images[] = $file;
                        }
                    }
                }
            }
            
            \Log::info('Found ' . count($images) . ' images');
            
            if (empty($images)) {
                return response()->json([
                    'error' => 'At least one image is required',
                    'debug' => [
                        'has_file_images' => $request->hasFile('images'),
                        'has_file_images_0' => $request->hasFile('images.0'),
                        'all_file_keys' => array_keys($request->allFiles()),
                    ]
                ], 422);
            }
            
            if (count($images) > 10) {
                return response()->json(['error' => 'Maximum 10 images allowed'], 422);
            }
            
            // Validate each image
            foreach ($images as $index => $image) {
                \Log::info("Validating image $index");
                \Log::info("Image path: " . $image->getPathname());
                \Log::info("Image size: " . $image->getSize());
                \Log::info("Image mime: " . $image->getMimeType());
                \Log::info("Image valid: " . ($image->isValid() ? 'yes' : 'no'));
                
                if (!$image->isValid()) {
                    \Log::error("Image $index is invalid. Error: " . $image->getError());
                    return response()->json([
                        'error' => 'Invalid image file',
                        'details' => [
                            'index' => $index,
                            'error_code' => $image->getError(),
                            'path' => $image->getPathname(),
                            'size' => $image->getSize(),
                            'mime' => $image->getMimeType(),
                        ]
                    ], 422);
                }
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                $mimeType = $image->getMimeType();
                if (!in_array($mimeType, $allowedMimes)) {
                    \Log::error("Image $index has invalid mime type: $mimeType");
                    return response()->json([
                        'error' => 'Only JPEG and PNG images are allowed',
                        'details' => [
                            'index' => $index,
                            'mime_type' => $mimeType,
                            'allowed' => $allowedMimes,
                        ]
                    ], 422);
                }
                if ($image->getSize() > 5120000) { // 5MB
                    \Log::error("Image $index exceeds size limit: " . $image->getSize());
                    return response()->json([
                        'error' => 'Image size must be less than 5MB',
                        'details' => [
                            'index' => $index,
                            'size' => $image->getSize(),
                            'max_size' => 5120000,
                        ]
                    ], 422);
                }
            }

            $dealer = $request->user()->dealer;
            if (!$dealer) {
                return response()->json(['error' => 'User is not a dealer'], 403);
            }

            $car = $dealer->cars()->create($validated);

            // Handle image uploads
            foreach ($images as $index => $image) {
                try {
                    // Store image in public storage
                    $path = $image->store('cars', 'public');
                    // Generate full URL for the image
                    $relativeUrl = Storage::url($path);
                    $fullUrl = url($relativeUrl);

                    // Create media record
                    CarMedia::create([
                        'car_id' => $car->id,
                        'file_path' => $fullUrl,
                        'file_type' => 'image',
                        'is_primary' => $index === 0, // First image is primary
                        'sort_order' => $index,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error uploading image: ' . $e->getMessage());
                    // Continue with other images even if one fails
                }
            }

            return response()->json($car->load('media'), 201);
        } catch (\Exception $e) {
            \Log::error('Error creating car: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create car',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified car
     */
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        // Check if user owns this car
        if ($car->dealer->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'brand' => 'string',
            'model' => 'string',
            'year' => 'integer|min:1900|max:' . (date('Y') + 1),
            'price' => 'numeric|min:0',
            'mileage' => 'integer|min:0',
            'condition' => 'in:new,used',
            'transmission' => 'string',
            'fuel_type' => 'string',
            'color' => 'string',
            'vin' => 'nullable|string',
            'description' => 'nullable|string',
            'is_sold' => 'boolean',
        ]);

        $car->update($validated);
        return response()->json($car);
    }

    /**
     * Remove the specified car
     */
    public function destroy(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        // Check if user owns this car
        if ($car->dealer->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $car->delete();
        return response()->json(['message' => 'Car deleted successfully']);
    }

    /**
     * Toggle featured status of a car (Admin)
     */
    public function toggleFeatured($id)
    {
        $car = Car::with(['dealer.user', 'media'])->findOrFail($id);
        $car->is_featured = !$car->is_featured;
        $car->save();

        return response()->json($car);
    }
}
