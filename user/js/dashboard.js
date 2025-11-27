// Init Icons
lucide.createIcons();

// --- DATA & STATE ---
let cart = [];
let availableTables = 20; // FIXED: Starts at 20
let reservedCount = 0;    // FIXED: Starts at 0
let isShopOpen = true;

// Inventory Data
const products = [
    { id: 1, name: "Sinugbang Baboy", desc: "Traditional Filipino grilled pork, marinated and cooked over charcoal.", price: 60, img: "images/inihaw-liempo-3.jpg", stock: 8 },
    { id: 2, name: "Pancit Bihon", desc: "Stir-fried rice noodles with meat, vegetables, and savory soy sauce.", price: 15, img: "images/images.jpg", stock: 12 },
    { id: 3, name: "Ngohiong (Small)", desc: "Cebu-style spring roll with seasoned meat, fried to a crispy finish.", price: 12, img: "images/AfterlightImage_2.webp", stock: 5 },
    { id: 4, name: "Lumpia", desc: "Crispy Filipino spring rolls filled with savory meat and vegetables.", price: 10, img: "images/ND-Lumpia-lhfz-mediumSquareAt3X.jpg", stock: 20 },
    { id: 5, name: "Fried Siomai", desc: "Crispy fried dumplings filled with seasoned meat and chili sauce.", price: 12, img: "images/fried-siomai-a-chinese.jpg", stock: 0 } 
];

// --- RENDER MENU ---
function renderMenu() {
    const container = document.getElementById('menu-container');
    if(!container) return;

    let html = '';

    products.forEach(product => {
        let stockBadge = '';
        let btnState = '';
        let btnText = 'Add';

        if (!isShopOpen) {
            btnState = 'disabled';
            btnText = 'Closed';
            stockBadge = `<span class="stock-count out">Shop Closed</span>`;
        } else if (product.stock === 0) {
            stockBadge = `<span class="stock-count out">Sold Out</span>`;
            btnState = 'disabled';
            btnText = 'Sold Out';
        } else if (product.stock <= 5) {
            stockBadge = `<span class="stock-count low">${product.stock} Left</span>`;
        } else {
            stockBadge = `<span class="stock-count">${product.stock} Available</span>`;
        }

        html += `
            <div class="menu-row">
                <img src="${product.img}" alt="${product.name}">
                <div class="menu-details">
                    <h3>${product.name}</h3>
                    <p>${product.desc}</p>
                    <div class="menu-meta">
                        <span class="menu-price">₱${product.price} / Order</span>
                        ${stockBadge}
                    </div>
                </div>
                <button class="menu-add-btn" ${btnState} onclick="addToCart(${product.id})">${btnText}</button>
            </div>
        `;
    });

    container.innerHTML = html;
}

// --- OPERATING HOURS CHECKER ---
function checkShopStatus() {
    const now = new Date();
    const hours = now.getHours(); 
    
    // Open 11:00 AM to 5:00 PM
    if (hours >= 11 && hours < 17) {
        isShopOpen = true;
        document.getElementById('footer-status-dot').classList.remove('closed');
        document.getElementById('footer-status-text').innerText = "Open Now";
        
        // Enable Table Btn
        const tableBtn = document.getElementById('res-btn');
        if(tableBtn && availableTables > 0) {
            tableBtn.disabled = false;
            tableBtn.innerHTML = `<i data-lucide="calendar" style="width:18px;"></i> Reserve Table`;
            lucide.createIcons();
        }
    } else {
        isShopOpen = false; 
        document.getElementById('footer-status-dot').classList.add('closed');
        document.getElementById('footer-status-text').innerText = "Closed (Opens 11AM)";

        // Disable Table Btn
        const tableBtn = document.getElementById('res-btn');
        if(tableBtn) {
            tableBtn.disabled = true;
            tableBtn.innerText = "Shop Closed";
        }
    }
    renderMenu(); 
}

// --- UPDATE TABLE UI (HELPER) ---
function updateTableUI() {
    const display = document.getElementById('table-count-display');
    const subtext = document.getElementById('table-subtext');
    if(display && subtext) {
        display.innerText = `${availableTables} Tables Available`;
        subtext.innerText = `${reservedCount} out of 20 tables reserved today`;
    }
}

// Check status
checkShopStatus();
setInterval(checkShopStatus, 60000);
updateTableUI(); // Initial render

// --- NAVIGATION ---
function switchTab(tabId) {
    document.querySelectorAll('.view').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId + '-view').classList.add('active');
    
    const buttons = document.querySelectorAll('.nav-link');
    if(tabId === 'home') buttons[0].classList.add('active');
    if(tabId === 'menu') buttons[1].classList.add('active');
    if(tabId === 'tables') buttons[2].classList.add('active');
    if(tabId === 'profile') buttons[3].classList.add('active');

    window.scrollTo(0,0);
    setTimeout(() => {
        lucide.createIcons();
        renderMenu();
        updateTableUI();
    }, 50); 
}

// --- TOAST ---
function showToast(message, type = 'info') {
    const box = document.getElementById('toast-box');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    let iconName = type === 'success' ? 'check-circle' : (type === 'error' ? 'alert-circle' : 'info');
    toast.innerHTML = `<i data-lucide="${iconName}" style="width:18px;"></i> <span>${message}</span>`;
    box.appendChild(toast);
    lucide.createIcons();
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// --- CART ---
function toggleCart() {
    document.getElementById('cart-sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('active');
}

function addToCart(id) {
    if(!isShopOpen) {
        showToast("Shop is closed.", "error");
        return;
    }
    let product = products.find(p => p.id === id);
    if (product.stock <= 0) {
        showToast("Item sold out!", "error");
        return;
    }
    product.stock--;
    renderMenu();

    let existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({ id: product.id, name: product.name, price: product.price, image: product.img, qty: 1 });
    }
    updateCartUI();
    showToast(`${product.name} added`, "success");
    const sidebar = document.getElementById('cart-sidebar');
    if(!sidebar.classList.contains('open')) toggleCart();
}

function increaseQty(id) {
    let product = products.find(p => p.id === id);
    if(product.stock > 0) {
        product.stock--;
        renderMenu();
        let item = cart.find(i => i.id === id);
        item.qty++;
        updateCartUI();
    } else {
        showToast("No more stock", "error");
    }
}

function decreaseQty(id) {
    let item = cart.find(i => i.id === id);
    let product = products.find(p => p.id === id);
    if(item) {
        item.qty--;
        product.stock++;
        renderMenu();
        if(item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        updateCartUI();
    }
}

function clearCart() {
    if(cart.length === 0) return;
    cart.forEach(item => {
        let product = products.find(p => p.id === item.id);
        if(product) product.stock += item.qty;
    });
    cart = [];
    renderMenu();
    updateCartUI();
    showToast("Tray cleared", "info");
}

function updateCartUI() {
    const container = document.getElementById('cart-items');
    const countBadge = document.getElementById('cart-count');
    const totalEl = document.getElementById('cart-total');
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    countBadge.innerText = totalQty;
    let totalPrice = 0;

    if (cart.length === 0) {
        container.innerHTML = `<div class="empty-cart"><i data-lucide="coffee" style="width:48px;height:48px;color:#ddd;"></i><p>Your tray is empty</p><button class="btn-text" style="color:#ff5b00;background:none;border:none;cursor:pointer;font-weight:600;" onclick="switchTab('menu');toggleCart()">Browse Menu</button></div>`;
        lucide.createIcons();
        totalEl.innerText = "₱0.00";
    } else {
        let html = '';
        cart.forEach((item) => {
            totalPrice += (item.price * item.qty);
            html += `<div class="cart-item"><img src="${item.image}"><div class="cart-details"><h4>${item.name}</h4><p>₱${item.price}</p></div><div class="qty-controls"><button class="qty-btn" onclick="decreaseQty(${item.id})">−</button><span>${item.qty}</span><button class="qty-btn" onclick="increaseQty(${item.id})">+</button></div></div>`;
        });
        container.innerHTML = html;
        totalEl.innerText = "₱" + totalPrice.toFixed(2);
    }
}

function processCheckout() {
    if(cart.length === 0) { showToast("Tray is empty!", "error"); return; }
    if(!isShopOpen) { showToast("Shop is closed.", "error"); return; }

    const orderId = "#CB" + Math.floor(100000000 + Math.random() * 900000000);
    document.getElementById('receipt-order-id').innerText = "Order " + orderId;
    document.getElementById('ready-mins').innerText = Math.floor(2 + Math.random() * 13);

    const receiptList = document.getElementById('receipt-items-list');
    let html = '';
    let total = 0;
    cart.forEach(item => {
        let subtotal = item.price * item.qty;
        total += subtotal;
        html += `<div class="receipt-item-row"><div><div class="r-name">${item.name}</div><div class="r-each">₱${item.price} each</div></div><div style="text-align:right;"><span class="r-qty">x${item.qty}</span><span class="r-price">₱${subtotal}</span></div></div>`;
    });
    receiptList.innerHTML = html;
    document.getElementById('receipt-total-price').innerText = "₱" + total;

    document.getElementById('cart-main-view').style.display = 'none';
    document.getElementById('receipt-view').style.display = 'block';
    showToast("Order placed!", "success");
    lucide.createIcons();
}

function closeReceipt() {
    document.getElementById('receipt-view').style.display = 'none';
    document.getElementById('cart-main-view').style.display = 'block';
    cart = [];
    updateCartUI();
    toggleCart();
    switchTab('menu');
}

// --- TABLE RESERVATION ---
function handleReservation() {
    if (!isShopOpen) {
        showToast("Shop is closed. Reservations open at 11:00 AM.", "error");
        return;
    }
    if(availableTables <= 0) {
        showToast("Sorry, all tables booked.", "error");
        return;
    }

    const time = document.getElementById('res-time').value;
    const size = document.getElementById('res-size').value;

    if(!time) { showToast("Select a time", "error"); return; }
    if(!size || size < 1) { showToast("Enter party size", "error"); return; }

    const btn = document.getElementById('res-btn');
    const originalText = btn.innerHTML;
    btn.innerText = "Booking...";
    btn.disabled = true;

    setTimeout(() => {
        availableTables--;
        reservedCount++;
        updateTableUI();
        showToast("Table reserved!", "success");
        
        if(availableTables === 0) {
            document.getElementById('table-header').classList.replace('res-header-green', 'res-header-red');
            document.getElementById('table-icon').classList.add('full');
            document.getElementById('table-count-display').classList.add('full-text');
            const badge = document.getElementById('table-badge');
            badge.innerText = "Fully Booked";
            badge.classList.add('full');
            btn.innerText = "Fully Booked";
            btn.disabled = true; 
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        document.getElementById('res-size').value = "";
    }, 1500);
}