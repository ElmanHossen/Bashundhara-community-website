// MEMBER 3 (fahim) - business order management AJAX
window.addEventListener("load", loadBusinessOrders);

function loadBusinessOrders() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showOrders(jsObj.orders);
            } else {
                document.getElementById("orderList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/business/orderControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=businessOrders");
}

function showOrders(orders) {
    const list = document.getElementById("orderList");

    if (orders.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>No orders yet.</p></div>";
        return;
    }

    const statuses = ["pending", "processing", "delivered", "cancelled"];
    let html = "";

    for (let i = 0; i < orders.length; i++) {
        const o = orders[i];
        html += "<div class='card'>";
        html += "<h2>Order #" + o.orderId + "</h2>";
        html += "<p class='meta'>" + o.customerName + " (" + o.userId + ") - " + o.orderDate + "</p>";
        html += "<p class='msg'>Ship to: " + o.shippingAddress + "</p>";

        html += "<table><tr><th>Product</th><th>Qty</th><th>Price</th></tr>";
        for (let j = 0; j < o.items.length; j++) {
            const it = o.items[j];
            html += "<tr><td>" + it.productName + "</td><td>" + it.quantity + "</td><td>Tk " + it.price + "</td></tr>";
        }
        html += "</table>";

        html += "<label>Status:</label>";
        html += "<select id='status" + o.orderId + "'>";
        for (let k = 0; k < statuses.length; k++) {
            const sel = (statuses[k] == o.status) ? " selected" : "";
            html += "<option value='" + statuses[k] + "'" + sel + ">" + statuses[k] + "</option>";
        }
        html += "</select>";
        html += "<button class='btn-small' onclick='saveStatus(" + o.orderId + ")'>Update status</button>";
        html += "<span class='success' id='statusMsg" + o.orderId + "'></span>";
        html += "</div>";
    }

    list.innerHTML = html;
}

function saveStatus(orderId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("statusMsg" + orderId).innerHTML = jsObj.message;
        }
    };

    xhr.open("POST", "../../controllers/business/orderControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=updateStatus&orderId=" + encodeURIComponent(orderId) +
        "&status=" + encodeURIComponent(document.getElementById("status" + orderId).value);

    xhr.send(data);
}
