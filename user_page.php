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
            <section class="profile-section">
                <h2>Welcome to Ice Cream Paradise, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                <p style="text-align: center; color: #a5778a; margin-bottom: 30px;">Customize your perfect ice cream treat</p>
            </section>

            <section class="ice-cream-customizer">
                <div class="customizer-card">
                    <h2>🍦 Ice Cream Customizer</h2>
                    
                    <div class="customizer-form">
                        <div class="form-group">
                            <label>Choose Flavor:</label>
                            <select id="flavor" onchange="updatePreview()">
                                <option value="vanilla">🍨 Vanilla</option>
                                <option value="chocolate">🍫 Chocolate</option>
                                <option value="strawberry">🍓 Strawberry</option>
                                <option value="mint">🌿 Mint Chocolate</option>
                                <option value="caramel">🍯 Caramel</option>
                                <option value="pistachio">💚 Pistachio</option>
                                <option value="cookie">🍪 Cookie Dough</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Size:</label>
                            <select id="size" onchange="updatePrice()">
                                <option value="small" data-price="3.99">Small (1 Scoop) - $3.99</option>
                                <option value="medium" data-price="5.99">Medium (2 Scoops) - $5.99</option>
                                <option value="large" data-price="7.99">Large (3 Scoops) - $7.99</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Add Toppings:</label>
                            <div class="toppings-list">
                                <label class="topping-checkbox">
                                    <input type="checkbox" value="sprinkles" data-price="0.50" onchange="updatePrice()"> Sprinkles (+$0.50)
                                </label>
                                <label class="topping-checkbox">
                                    <input type="checkbox" value="chocolate_chips" data-price="0.75" onchange="updatePrice()"> Chocolate Chips (+$0.75)
                                </label>
                                <label class="topping-checkbox">
                                    <input type="checkbox" value="nuts" data-price="0.75" onchange="updatePrice()"> Nuts (+$0.75)
                                </label>
                                <label class="topping-checkbox">
                                    <input type="checkbox" value="whipped_cream" data-price="1.00" onchange="updatePrice()"> Whipped Cream (+$1.00)
                                </label>
                                <label class="topping-checkbox">
                                    <input type="checkbox" value="caramel_sauce" data-price="0.50" onchange="updatePrice()"> Caramel Sauce (+$0.50)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Quantity:</label>
                            <input type="number" id="quantity" min="1" max="10" value="1" onchange="updatePrice()">
                        </div>

                        <div class="price-display">
                            <h3>Total Price: <span id="total-price">$3.99</span></h3>
                        </div>

                        <button class="btn" onclick="addToCart()" style="width: 100%; margin-top: 20px;">🛒 Add to Cart</button>
                    </div>
                </div>

                <div class="cart-section">
                    <h2>🛒 Your Cart</h2>
                    <div id="cart-items" class="cart-items">
                        <p style="text-align: center; color: #a5778a;">Your cart is empty</p>
                    </div>
                    <div class="cart-total">
                        <h3>Cart Total: <span id="cart-total">$0.00</span></h3>
                        <button class="btn" onclick="checkout()" style="width: 100%; margin-top: 15px;">💳 Checkout</button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
<script>
let cart = [];
const flavors = {
    vanilla: "🍨 Vanilla",
    chocolate: "🍫 Chocolate",
    strawberry: "🍓 Strawberry",
    mint: "🌿 Mint Chocolate",
    caramel: "🍯 Caramel",
    pistachio: "💚 Pistachio",
    cookie: "🍪 Cookie Dough"
};

function updatePreview() {
    const flavor = document.getElementById('flavor').value;
    console.log('Flavor selected:', flavor);
}

function updatePrice() {
    const sizeSelect = document.getElementById('size');
    const quantityInput = document.getElementById('quantity');
    const basePrice = parseFloat(sizeSelect.options[sizeSelect.selectedIndex].getAttribute('data-price'));
    const quantity = parseInt(quantityInput.value) || 1;
    
    let toppingPrice = 0;
    const toppings = document.querySelectorAll('.topping-checkbox input[type="checkbox"]:checked');
    toppings.forEach(topping => {
        toppingPrice += parseFloat(topping.getAttribute('data-price'));
    });
    
    const totalPrice = (basePrice + toppingPrice) * quantity;
    document.getElementById('total-price').textContent = '$' + totalPrice.toFixed(2);
}

function addToCart() {
    const flavor = document.getElementById('flavor').value;
    const size = document.getElementById('size').value;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const sizeSelect = document.getElementById('size');
    const basePrice = parseFloat(sizeSelect.options[sizeSelect.selectedIndex].getAttribute('data-price'));
    
    let toppingPrice = 0;
    let toppingsList = [];
    const toppings = document.querySelectorAll('.topping-checkbox input[type="checkbox"]:checked');
    toppings.forEach(topping => {
        toppingPrice += parseFloat(topping.getAttribute('data-price'));
        toppingsList.push(topping.value);
    });
    
    const itemPrice = basePrice + toppingPrice;
    const totalPrice = itemPrice * quantity;
    
    const cartItem = {
        id: Date.now(),
        flavor: flavors[flavor],
        size: size,
        toppings: toppingsList,
        quantity: quantity,
        pricePerItem: itemPrice,
        totalPrice: totalPrice
    };
    
    cart.push(cartItem);
    updateCartDisplay();
    
 
    document.getElementById('quantity').value = 1;
    document.querySelectorAll('.topping-checkbox input[type="checkbox"]').forEach(cb => cb.checked = false);
    updatePrice();
    

    alert('✨ Added to cart!');
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    const cartTotalSpan = document.getElementById('cart-total');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p style="text-align: center; color: #a5778a;">Your cart is empty</p>';
        cartTotalSpan.textContent = '$0.00';
        return;
    }
    
    let html = '';
    let cartTotal = 0;
    
    cart.forEach(item => {
        cartTotal += item.totalPrice;
        const toppingsText = item.toppings.length > 0 ? item.toppings.join(', ') : 'None';
        html += `
            <div class="cart-item">
                <div class="item-details">
                    <h4>${item.flavor} (${item.size})</h4>
                    <p>Toppings: ${toppingsText}</p>
                    <p>Qty: ${item.quantity} × $${item.pricePerItem.toFixed(2)} = <strong>$${item.totalPrice.toFixed(2)}</strong></p>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${item.id})">Remove</button>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    cartTotalSpan.textContent = '$' + cartTotal.toFixed(2);
}

function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + item.totalPrice, 0);
    alert('🎉 Order placed successfully! Total: $' + total.toFixed(2) + '\n\nThank you for your purchase!');
    cart = [];
    updateCartDisplay();
}

</script>

</html>
