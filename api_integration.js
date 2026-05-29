/**
 * API Integration Script
 * Use this to fetch data from your PHP APIs
 * 
 * IMPORTANT: Set the API_BASE_URL to your server address
 */

// ===== CONFIGURATION =====
const API_BASE_URL = 'http://localhost/pfe'; // Change this to your server URL

// ===== PRODUCTS API =====

/**
 * Load all products from database
 */
async function loadProductsFromDB() {
  try {
    const response = await fetch(`${API_BASE_URL}/api_products.php?action=get_all`);
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Products loaded from database:', data.data);
      return data.data;
    } else {
      console.error('✗ Error loading products:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Search products from database
 * @param {string} query - Search query (product name or description)
 */
async function searchProductsFromDB(query) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_products.php?action=search&q=${encodeURIComponent(query)}`);
    const data = await response.json();
    
    if (data.success) {
      console.log(`✓ Search results for "${query}":`, data.data);
      return data.data;
    } else {
      console.error('✗ Search error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Get products by category
 * @param {string} category - Category (rare, mountain, flower, medical)
 */
async function getProductsByCategory(category) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_products.php?action=get_category&cat=${category}`);
    const data = await response.json();
    
    if (data.success) {
      console.log(`✓ Products in "${category}" category:`, data.data);
      return data.data;
    } else {
      console.error('✗ Error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Get single product by ID
 * @param {number} id - Product ID
 */
async function getProductByID(id) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_products.php?action=get_one&id=${id}`);
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Product found:', data.data);
      return data.data;
    } else {
      console.error('✗ Error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

// ===== USERS API =====

/**
 * Register new user
 * @param {object} userData - {email, password, name, phone}
 */
async function registerUser(userData) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_users.php?action=register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(userData)
    });
    
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Registration successful:', data.user);
      return data.user;
    } else {
      console.error('✗ Registration error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Login user
 * @param {object} credentials - {email, password}
 */
async function loginUser(credentials) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_users.php?action=login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(credentials)
    });
    
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Login successful:', data.user);
      // Store token in localStorage
      localStorage.setItem('authToken', data.token);
      localStorage.setItem('userId', data.user.id);
      return data.user;
    } else {
      console.error('✗ Login error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Check if email exists
 * @param {string} email - Email to check
 */
async function checkEmailExists(email) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_users.php?action=check_email&email=${encodeURIComponent(email)}`);
    const data = await response.json();
    
    if (data.success) {
      console.log(`✓ Email "${email}" exists:`, data.exists);
      return data.exists;
    } else {
      console.error('✗ Error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

// ===== ORDERS API =====

/**
 * Create new order
 * @param {object} orderData - {user_id, items, total_amount, shipping_cost, notes}
 */
async function createOrderFromDB(orderData) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_orders.php?action=create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(orderData)
    });
    
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Order created successfully:', data);
      return data;
    } else {
      console.error('✗ Error creating order:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Get user's orders
 * @param {number} userId - User ID
 */
async function getUserOrders(userId) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_orders.php?action=get_user_orders&user_id=${userId}`);
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ User orders:', data.data);
      return data.data;
    } else {
      console.error('✗ Error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

/**
 * Get order details
 * @param {number} orderId - Order ID
 */
async function getOrderDetails(orderId) {
  try {
    const response = await fetch(`${API_BASE_URL}/api_orders.php?action=get_order&order_id=${orderId}`);
    const data = await response.json();
    
    if (data.success) {
      console.log('✓ Order details:', data.data);
      return data.data;
    } else {
      console.error('✗ Error:', data.error);
    }
  } catch (error) {
    console.error('✗ Network error:', error);
  }
}

// ===== HELPER FUNCTIONS =====

/**
 * Get current user data from localStorage
 */
function getCurrentUser() {
  const userId = localStorage.getItem('userId');
  const token = localStorage.getItem('authToken');
  
  if (userId && token) {
    return {
      id: parseInt(userId),
      token: token
    };
  }
  return null;
}

/**
 * Logout current user
 */
function logoutUser() {
  localStorage.removeItem('userId');
  localStorage.removeItem('authToken');
  console.log('✓ User logged out');
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
  return getCurrentUser() !== null;
}

// ===== USAGE EXAMPLES =====

/*
// Example 1: Load all products
loadProductsFromDB();

// Example 2: Search for products
searchProductsFromDB('السدر');

// Example 3: Get products by category
getProductsByCategory('rare');

// Example 4: Register new user
registerUser({
  email: 'user@example.com',
  password: 'password123',
  name: 'أحمد محمد',
  phone: '0558812378'
});

// Example 5: Login user
loginUser({
  email: 'user@example.com',
  password: 'password123'
});

// Example 6: Create order
createOrderFromDB({
  user_id: 1,
  total_amount: 5600,
  shipping_cost: 350,
  notes: 'Fast delivery please',
  items: [
    {
      product_id: 1,
      quantity: 2,
      price: 2800
    }
  ]
});

// Example 7: Get user orders
const user = getCurrentUser();
if (user) {
  getUserOrders(user.id);
}

// Example 8: Check if logged in
if (isUserLoggedIn()) {
  console.log('✓ User is logged in');
} else {
  console.log('✗ User is not logged in');
}
*/
