// MEMBER 3 (fahim) - cart and checkout AJAX
window.addEventListener("load", loadCart);

const checkoutForm = document.getElementById("checkoutForm");
checkoutForm.addEventListener("submit", placeOrder);

function loadCart() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showCart(jsObj.items, jsObj.total);
            } else {
                document.getElementById("cartList").innerHTML = "<p class='error'>" + jsObj.message + "</p>";
            }
        }
    };

    xhr.open("POST", "../../controllers/business/cartControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list");
}

function showCart(items, total) {
    const list = document.getElementById("cartList");

    if (items.length == 0) {
        list.innerHTML = "<p class='msg'>Your cart is empty. <a href='shop.php'>Browse products</a>.</p>";
        document.getElementById("cartTotal").innerHTML = "";
        document.getElementById("checkoutBox").style.display = "none";
        return;
    }

    document.getElementById("checkoutBox").style.display = "block";

    let html = "<table><tr><th>Product</th><th>Shop</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>";

    for (let i = 0; i < items.length; i++) {
        const it = items[i];
        const subtotal = (it.price * it.quantity).toFixed(2);

        html += "<tr>";
        html += "<td>" + it.productName + "</td>";
        html += "<td>" + it.businessName + "</td>";
        html += "<td>Tk " + it.price + "</td>";
        html += "<td><input type='number' class='qty-input' id='cartQty" + it.cartId + "' value='" + it.quantity + "' min='1' onchange='updateQty(" + it.cartId + ")'></td>";
        html += "<td>Tk " + subtotal + "</td>";
        html += "<td><button class='btn-small btn-danger' onclick='removeItem(" + it.cartId + ")'>Remove</button></td>";
        html += "</tr>";
    }

    html += "</table>";
    list.innerHTML = html;
    document.getElementById("cartTotal").innerHTML = "Total: Tk " + total;
}

function updateQty(cartId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                loadCart();
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/business/cartControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=updateQty&cartId=" + encodeURIComponent(cartId) +
        "&quantity=" + encodeURIComponent(document.getElementById("cartQty" + cartId).value);

    xhr.send(data);
}

function removeItem(cartId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            loadCart();
        }
    };

    xhr.open("POST", "../../controllers/business/cartControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=remove&cartId=" + encodeURIComponent(cartId));
}

function placeOrder(e) {
    e.preventDefault();

    document.getElementById("addressErr").innerHTML = "";
    document.getElementById("orderMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("orderMsg").innerHTML = jsObj.message;
                loadCart();
            } else {
                if (jsObj.addressErr) document.getElementById("addressErr").innerHTML = jsObj.addressErr;
                if (jsObj.message) document.getElementById("orderMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/business/orderControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=place&shippingAddress=" + encodeURIComponent(document.getElementById("shippingAddress").value));
}
