<?php
namespace App\Controllers;

use App\Services\RestaurantService;
use App\Utils\Response;
use App\Utils\Validator;
use App\Utils\Session;

class RestaurantController {
    private $restaurantService;

    public function __construct() {
        $this->restaurantService = new RestaurantService();
    }

    public function index() {
        $filters = [
            'cuisine_type' => $_GET['cuisine'] ?? null,
            'search' => $_GET['search'] ?? null,
            'rating' => $_GET['rating'] ?? null,
        ];

        $restaurants = $this->restaurantService->getRestaurants($filters);
        return Response::success($restaurants);
    }

    public function show($id) {
        try {
            $restaurant = $this->restaurantService->getRestaurantDetails($id);
            return Response::success($restaurant);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 404);
        }
    }

    public function store() {
        // Check if user is admin
        if (!Session::get('user')->role === 'admin') {
            return Response::error('Unauthorized', 403);
        }

        $validator = new Validator($_POST);
        $validator
            ->required('name')
            ->required('address')
            ->required('phone')
            ->required('cuisine_type');

        if ($validator->fails()) {
            return Response::error($validator->getErrors(), 422);
        }

        try {
            $restaurant = $this->restaurantService->createRestaurant($_POST);
            return Response::success($restaurant, 'Restaurant created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}