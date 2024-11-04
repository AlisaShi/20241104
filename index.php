<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Delivery System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Food Delivery</h1>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-gray-300" onclick="showRestaurants()">Restaurants</a>
                <a href="#" class="hover:text-gray-300" onclick="showCart()">Cart (<span id="cartCount">0</span>)</a>
                <a href="#" class="hover:text-gray-300" onclick="showOrders()">Orders</a>
                <a href="#" class="hover:text-gray-300" onclick="showProfile()">Profile</a>
                <a href="#" class="hover:text-gray-300" id="loginButton" onclick="toggleAuth()">Login</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mx-auto p-4">
        <div id="mainContent"></div>
    </div>

    <!-- Modal for Login/Register -->
    <div id="authModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg max-w-md w-full">
            <div id="authContent"></div>
        </div>
    </div>

    <script>
        // State management
        let state = {
            user: null,
            cart: [],
            currentRestaurant: null,
            orders: []
        };

        // API handling
        const api = {
            async request(endpoint, method = 'GET', data = null) {
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                    }
                };
                if (data) {
                    options.body = JSON.stringify(data);
                }
                const response = await fetch(`/api/${endpoint}`, options);
                return response.json();
            },
            get(endpoint) {
                return this.request(endpoint);
            },
            post(endpoint, data) {
                return this.request(endpoint, 'POST', data);
            }
        };

        // Auth System
        function toggleAuth() {
            const authContent = document.getElementById('authContent');
            authContent.innerHTML = `
                <div class="mb-4">
                    <h2 class="text-2xl font-bold mb-4">Login</h2>
                    <input type="text" id="username" placeholder="Username" class="w-full p-2 mb-2 border rounded">
                    <input type="password" id="password" placeholder="Password" class="w-full p-2 mb-4 border rounded">
                    <button onclick="login()" class="w-full bg-blue-500 text-white p-2 rounded mb-2">Login</button>
                    <button onclick="showRegister()" class="w-full bg-gray-300 p-2 rounded">Register</button>
                </div>
            `;
            document.getElementById('authModal').classList.remove('hidden');
        }

        async function login() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            try {
                const response = await api.post('login', { username, password });
                if (response.success) {
                    state.user = response.user;
                    updateUI();
                    document.getElementById('authModal').classList.add('hidden');
                }
            } catch (error) {
                alert('Login failed');
            }
        }

        // Restaurant System
        async function showRestaurants() {
            const restaurants = await api.get('restaurants');
            const content = restaurants.map(restaurant => `
                <div class="border p-4 mb-4 rounded shadow-lg">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">${restaurant.name}</h2>
                        <span class="text-yellow-500">★ ${restaurant.rating}</span>
                    </div>
                    <p class="text-gray-600">${restaurant.description}</p>
                    <p class="text-sm text-gray-500 mb-2">${restaurant.address}</p>
                    <button onclick="showMenu(${restaurant.restaurant_id})" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        View Menu
                    </button>
                </div>
            `).join('');
            document.getElementById('mainContent').innerHTML = content;
        }

        // Menu System
        async function showMenu(restaurantId) {
            state.currentRestaurant = restaurantId;
            const menu = await api.get(`restaurants/${restaurantId}/menu`);
            const content = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${menu.map(item => `
                        <div class="border p-4 rounded shadow">
                            <h3 class="text-lg font-bold">${item.name}</h3>
                            <p class="text-gray-600">${item.description}</p>
                            <p class="text-lg font-bold text-green-600">$${item.price}</p>
                            <button onclick="addToCart(${item.item_id})" 
                                    class="bg-green-500 text-white px-4 py-2 rounded mt-2 hover:bg-green-600">
                                Add to Cart
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
            document.getElementById('mainContent').innerHTML = content;
        }

        // Cart System
        async function showCart() {
            const cart = await api.get('cart');
            const content = `
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">Your Cart</h2>
                    ${cart.length === 0 ? '<p>Your cart is empty</p>' : `
                        <div class="space-y-4">
                            ${cart.map(item => `
                                <div class="flex justify-between items-center border-b pb-4">
                                    <div>
                                        <h3 class="font-bold">${item.name}</h3>
                                        <p class="text-gray-600">$${item.price} x ${item.quantity}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="updateQuantity(${item.item_id}, ${item.quantity - 1})"
                                                class="bg-gray-200 px-3 py-1 rounded">-</button>
                                        <span>${item.quantity}</span>
                                        <button onclick="updateQuantity(${item.item_id}, ${item.quantity + 1})"
                                                class="bg-gray-200 px-3 py-1 rounded">+</button>
                                    </div>
                                </div>
                            `).join('')}
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-xl font-bold">Total: $${calculateTotal(cart)}</span>
                                <button onclick="checkout()" 
                                        class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                                    Checkout
                                </button>
                            </div>
                        </div>
                    `}
                </div>
            `;
            document.getElementById('mainContent').innerHTML = content;
        }

        // Order System
        async function showOrders() {
            const orders = await api.get('orders');
            const content = `
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold mb-4">Your Orders</h2>
                    ${orders.map(order => `
                        <div class="border p-4 rounded shadow">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-bold">Order #${order.order_id}</h3>
                                <span class="px-2 py-1 rounded ${getStatusClass(order.status)}">
                                    ${order.status}
                                </span>
                            </div>
                            <p class="text-gray-600">Restaurant: ${order.restaurant_name}</p>
                            <p class="text-gray-600">Total: $${order.total_amount}</p>
                            <button onclick="showOrderDetails(${order.order_id})"
                                    class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                View Details
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
            document.getElementById('mainContent').innerHTML = content;
        }

        // Review System
        async function showReviewForm(orderId) {
            const content = `
                <div class="border p-4 rounded shadow">
                    <h3 class="text-xl font-bold mb-4">Leave a Review</h3>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Rating</label>
                        <div class="flex space-x-2">
                            ${[1,2,3,4,5].map(rating => `
                                <button onclick="setRating(${rating})" 
                                        class="text-2xl" id="star${rating}">
                                    ★
                                </button>
                            `).join('')}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Comment</label>
                        <textarea id="reviewComment" 
                                  class="w-full p-2 border rounded"
                                  rows="4"></textarea>
                    </div>
                    <button onclick="submitReview(${orderId})"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Submit Review
                    </button>
                </div>
            `;
            document.getElementById('mainContent').innerHTML = content;
        }

        // Utility Functions
        function calculateTotal(cart) {
            return cart.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2);
        }

        function getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'confirmed': 'bg-blue-100 text-blue-800',
                'delivering': 'bg-purple-100 text-purple-800',
                'delivered': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        // Initialize the application
        async function init() {
            try {
                const user = await api.get('user');
                if (user) {
                    state.user = user;
                    updateUI();
                }
                showRestaurants();
            } catch (error) {
                console.error('Initialization failed:', error);
            }
        }

        init();
    </script>
</body>
</html>