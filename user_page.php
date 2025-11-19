<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
    <div class="user-container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <main>
            <section class="welcome-header">
                <h2>🍦 Welcome to Ice Cream Paradise!</h2>
                <p>Customize your perfect ice cream treat, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            </section>
            <section class="user-content-grid">
                <div class="customizer-panel">
                    <h3>🎨 Build Your Ice Cream</h3>
                    <div class="selection-section">
                        <h4>1. Choose Your Flavor</h4>
                        <div class="flavor-grid" id="flavor-grid">
                        </div>
                    </div>
                    <div class="selection-section">
                        <h4>2. Select Size</h4>
                        <div class="size-options">
                            <button class="size-btn" data-size="small" onclick="selectSize('small')">
                                <span class="size-icon">🍦</span>
                                <span class="size-name">Small</span>
                                <span class="size-desc">1 Scoop</span>
                            </button>
                            <button class="size-btn active" data-size="medium" onclick="selectSize('medium')">
                                <span class="size-icon">🍦🍦</span>
                                <span class="size-name">Medium</span>
                                <span class="size-desc">2 Scoops</span>
                            </button>
                            <button class="size-btn" data-size="large" onclick="selectSize('large')">
                                <span class="size-icon">🍦🍦🍦</span>
                                <span class="size-name">Large</span>
                                <span class="size-desc">3 Scoops</span>
                            </button>
                        </div>
                    </div>
                    <div class="selection-section">
                        <h4>3. Add Toppings (Optional)</h4>
                        <div class="toppings-grid" id="toppings-grid">
                        </div>
                    </div>
                    <div class="selection-section">
                        <h4>4. Quantity</h4>
                        <div class="quantity-control">
                            <button class="qty-btn" onclick="decreaseQuantity()">−</button>
                            <input type="number" id="quantity" min="1" max="10" value="1" onchange="updatePrice()" readonly>
                            <button class="qty-btn" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>
                    <div class="add-to-cart-section">
                        <div class="price-display">
                            <span class="price-label">Total Price:</span>
                            <span class="price-value" id="total-price">$3.99</span>
                        </div>
                        <button class="add-cart-btn" onclick="addToCart()">
                            <span>🛒 Add to Cart</span>
                        </button>
                    </div>
                </div>
                <div class="cart-panel">
                    <h3>🛒 Your Cart</h3>
                    <div class="cart-content">
                        <div id="cart-items" class="cart-items">
                            <div class="empty-cart">
                                <span class="empty-icon">🛒</span>
                                <p>Your cart is empty</p>
                                <small>Add some delicious ice cream!</small>
                            </div>
                        </div>
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="cart-subtotal">$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="cart-total">$0.00</span>
                            </div>
                            <button class="checkout-btn" onclick="checkout()">
                                <span>💳 Proceed to Checkout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
<script>
let cart = [];
let flavorsData = [];
let toppingsData = [];
let pricingData = {};

async function loadData() {
    try {
        const flavorsResponse = await fetch('api.php?action=get_flavors');
        flavorsData = await flavorsResponse.json();
        const toppingsResponse = await fetch('api.php?action=get_toppings');
        toppingsData = await toppingsResponse.json();
        const pricingResponse = await fetch('api.php?action=get_pricing');
        pricingData = await pricingResponse.json();

        console.log('Loaded flavors:', flavorsData);
        console.log('Loaded toppings:', toppingsData);
        console.log('Loaded pricing:', pricingData);
        populateFlavors();
        populateToppings();
        updatePrice();
    } catch (error) {
        console.error('Error loading data:', error);
        alert('Failed to load ice cream options. Please refresh the page.');
    }
}

function populateFlavors() {
    const flavorGrid = document.getElementById('flavor-grid');
    flavorGrid.innerHTML = '';

    if (!flavorsData || flavorsData.length === 0) {
        flavorGrid.innerHTML = '<p style="text-align: center; color: #a5778a;">No flavors available</p>';
        return;
    }

    flavorsData.forEach((flavor, index) => {
        const flavorCard = document.createElement('div');
        flavorCard.className = 'flavor-card' + (index === 0 ? ' active' : '');
        flavorCard.setAttribute('data-flavor-id', flavor.id);
        flavorCard.onclick = () => selectFlavor(flavor.id);

        flavorCard.innerHTML = `
            <span class="flavor-emoji">${flavor.emoji}</span>
            <span class="flavor-name">${flavor.name}</span>
            <span class="flavor-check">✓</span>
        `;

        flavorGrid.appendChild(flavorCard);
    });
    if (flavorsData.length > 0) {
        window.selectedFlavorId = flavorsData[0].id;
    }
}

function populateToppings() {
    const toppingsGrid = document.getElementById('toppings-grid');
    toppingsGrid.innerHTML = '';

    if (!toppingsData || toppingsData.length === 0) {
        toppingsGrid.innerHTML = '<p style="text-align: center; color: #a5778a;">No toppings available</p>';
        return;
    }

    toppingsData.forEach(topping => {
        const toppingCard = document.createElement('div');
        toppingCard.className = 'topping-card';
        toppingCard.setAttribute('data-topping-id', topping.id);
        toppingCard.setAttribute('data-price', topping.price);
        toppingCard.onclick = () => toggleTopping(topping.id);

        toppingCard.innerHTML = `
            <span class="topping-emoji">${topping.emoji}</span>
            <span class="topping-name">${topping.name}</span>
            <span class="topping-price">+$${parseFloat(topping.price).toFixed(2)}</span>
            <span class="topping-check">✓</span>
        `;

        toppingsGrid.appendChild(toppingCard);
    });
}

let selectedFlavorId = null;
let selectedSize = 'medium';

function selectFlavor(flavorId) {
    selectedFlavorId = flavorId;

    document.querySelectorAll('.flavor-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelector(`[data-flavor-id="${flavorId}"]`).classList.add('active');

    updatePrice();
}

function selectSize(size) {
    selectedSize = size;
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-size="${size}"]`).classList.add('active');

    updatePrice();
}

function toggleTopping(toppingId) {
    const card = document.querySelector(`[data-topping-id="${toppingId}"]`);
    card.classList.toggle('active');
    updatePrice();
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value) || 1;
    if (current < 10) {
        input.value = current + 1;
        updatePrice();
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value) || 1;
    if (current > 1) {
        input.value = current - 1;
        updatePrice();
    }
}

function updatePrice() {
    const flavorId = selectedFlavorId;
    const size = selectedSize;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;

    if (!pricingData.flavors || !flavorId || !pricingData.flavors[flavorId]) {
        document.getElementById('total-price').textContent = '$0.00';
        return;
    }

    const basePrice = pricingData.flavors[flavorId][size];

    let toppingPrice = 0;
    const activeToppings = document.querySelectorAll('.topping-card.active');
    activeToppings.forEach(topping => {
        toppingPrice += parseFloat(topping.getAttribute('data-price'));
    });

    const totalPrice = (basePrice + toppingPrice) * quantity;
    document.getElementById('total-price').textContent = '$' + totalPrice.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, loading data...');
    loadData();
});

function addToCart() {
    const flavorId = selectedFlavorId;
    const size = selectedSize;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;

    if (!flavorId) {
        alert('Please select a flavor');
        return;
    }

    if (!pricingData.flavors || !pricingData.flavors[flavorId]) {
        alert('Pricing data not loaded. Please refresh the page.');
        return;
    }

    const basePrice = pricingData.flavors[flavorId][size];
    const flavorData = flavorsData.find(f => f.id == flavorId);

    if (!flavorData) {
        alert('Flavor data not found');
        return;
    }

    const flavorName = flavorData.name;
    const flavorEmoji = flavorData.emoji;

    let toppingPrice = 0;
    let toppingsList = [];
    let toppingsNames = [];
    const activeToppings = document.querySelectorAll('.topping-card.active');
    activeToppings.forEach(topping => {
        const toppingId = topping.getAttribute('data-topping-id');
        const price = parseFloat(topping.getAttribute('data-price'));
        toppingPrice += price;
        toppingsList.push(toppingId);

        const toppingData = toppingsData.find(t => t.id == toppingId);
        if (toppingData) {
            toppingsNames.push(`${toppingData.emoji} ${toppingData.name}`);
        }
    });

    const itemPrice = basePrice + toppingPrice;
    const totalPrice = itemPrice * quantity;

    const cartItem = {
        id: Date.now(),
        flavorId: flavorId,
        flavorName: `${flavorEmoji} ${flavorName}`,
        size: size,
        toppings: toppingsList,
        toppingsNames: toppingsNames,
        quantity: quantity,
        pricePerItem: itemPrice,
        totalPrice: totalPrice
    };

    cart.push(cartItem);
    updateCartDisplay();

    document.getElementById('quantity').value = 1;
    document.querySelectorAll('.topping-card').forEach(card => card.classList.remove('active'));
    updatePrice();

    showNotification('✨ Added to cart!');
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    const cartTotalSpan = document.getElementById('cart-total');
    const cartSubtotalSpan = document.getElementById('cart-subtotal');

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="empty-cart">
                <span class="empty-icon">🛒</span>
                <p>Your cart is empty</p>
                <small>Add some delicious ice cream!</small>
            </div>
        `;
        cartTotalSpan.textContent = '$0.00';
        cartSubtotalSpan.textContent = '$0.00';
        return;
    }

    let html = '';
    let cartTotal = 0;

    cart.forEach(item => {
        cartTotal += item.totalPrice;
        let toppingsHTML = '';
        if (item.toppingsNames.length > 0) {
            toppingsHTML = item.toppingsNames.map(topping => `<div>• ${topping}</div>`).join('');
        } else {
            toppingsHTML = '<div>No toppings</div>';
        }

        html += `
            <div class="cart-item">
                <div class="cart-item-header">
                    <button class="remove-btn" onclick="removeFromCart(${item.id})" title="Remove item">×</button>
                </div>
                <div class="cart-item-body">
                    <div class="cart-item-details">
                        <div class="detail-row">
                            <span class="detail-label">Flavor:</span>
                            <span class="detail-value item-flavor">${item.flavorName}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Size:</span>
                            <span class="detail-value">${item.size.charAt(0).toUpperCase() + item.size.slice(1)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Toppings:</span>
                            <span class="detail-value toppings-list">${toppingsHTML}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Quantity:</span>
                            <span class="detail-value">${item.quantity}</span>
                        </div>
                    </div>
                    <div class="cart-item-price">
                        <span class="price-label">Total:</span>
                        <span class="price-amount">$${item.totalPrice.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
    });

    cartItemsDiv.innerHTML = html;
    cartTotalSpan.textContent = '$' + cartTotal.toFixed(2);
    cartSubtotalSpan.textContent = '$' + cartTotal.toFixed(2);
}

async function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    const total = cart.reduce((sum, item) => sum + item.totalPrice, 0);

    console.log('Checkout - Cart data:', cart);
    console.log('Checkout - Total:', total);

    try {
        const response = await fetch('checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart: cart,
                total: total
            })
        });

        console.log('Checkout - Response status:', response.status);

        const responseText = await response.text();
        console.log('Checkout - Response text:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            alert('❌ Server returned invalid response. Check console for details.');
            return;
        }

        console.log('Checkout - Parsed result:', result);

        if (result.success) {
            alert('🎉 Order placed successfully! Order #' + result.order_id + '\nTotal: $' + total.toFixed(2) + '\n\nThank you for your purchase!');
            cart = [];
            updateCartDisplay();
        } else {
            console.error('Checkout failed:', result);
            alert('❌ Order failed: ' + (result.message || 'Unknown error') + '\n\nCheck browser console for details.');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        alert('❌ Failed to place order. Error: ' + error.message + '\n\nCheck browser console for details.');
    }
}

</script>

</html>
