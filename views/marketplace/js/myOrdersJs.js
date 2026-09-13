// MEMBER 3 (fahim) - customer order history AJAX
window.addEventListener("load", loadMyOrders);

function loadMyOrders() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showMyOrders(jsObj.orders);
            } else {
                document.getElementById("orderList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/business/orderControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=myOrders");
}

function showMyOrders(orders) {
    const list = document.getElementById("orderList");

    if (orders.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>You have not ordered anything yet. <a href='shop.php'>Start shopping</a>.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < orders.length; i++) {
        const o = orders[i];
        html += "<div class='card'>";
        html += "<h2>Order #" + o.orderId + "</h2>";
        html += "<p class='meta'>" + o.orderDate + " - <span class='status status-" + o.status + "'>" + o.status + "</span></p>";
        html += "<p class='msg'>Ship to: " + o.shippingAddress + "</p>";

        html += "<table><tr><th>Product</th><th>Qty</th><th>Price</th></tr>";
        for (let j = 0; j < o.items.length; j++) {
            const it = o.items[j];
            html += "<tr><td>" + it.productName + "</td><td>" + it.quantity + "</td><td>Tk " + it.price + "</td></tr>";
        }
        html += "</table>";

        html += "<h2>Total: Tk " + o.totalAmount + "</h2>";
        html += "</div>";
    }

    list.innerHTML = html;
}
