// MEMBER 3 (fahim) - product search / filter / add to cart AJAX
window.addEventListener("load", loadProducts);

function loadProducts() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            showProducts(jsObj.products);
        }
    };

    xhr.open("POST", "../../controllers/business/productControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=shop" +
        "&search=" + encodeURIComponent(document.getElementById("searchBox").value) +
        "&pCategoryId=" + encodeURIComponent(document.getElementById("categoryFilter").value);

    xhr.send(data);
}

function showProducts(products) {
    const grid = document.getElementById("productGrid");

    if (products.length == 0) {
        grid.innerHTML = "<div class='card'><p class='msg'>No products matched your search.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < products.length; i++) {
        const p = products[i];
        html += "<div class='card product'>";
        html += "<span class='tag'>" + (p.pCategoryName == null ? "Other" : p.pCategoryName) + "</span>";
        html += "<h2>" + p.productName + "</h2>";
        html += "<p class='meta'>sold by " + p.businessName + "</p>";
        html += "<p>" + (p.description == null ? "" : p.description) + "</p>";
        html += "<p class='price'>Tk " + p.price + "</p>";

        if (p.stock > 0) {
            html += "<p class='msg'>In stock: " + p.stock + "</p>";
            html += "<input type='number' id='qty" + p.productId + "' value='1' min='1' class='qty-input'>";
            html += "<button class='btn-small' onclick='addToCart(" + p.productId + ")'>Add to cart</button>";
        } else {
            html += "<p class='error'>Out of stock</p>";
        }

        html += "<span class='msg' id='cartMsg" + p.productId + "'></span>";
        html += "</div>";
    }

    grid.innerHTML = html;
}

function addToCart(productId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("cartMsg" + productId).innerHTML = jsObj.message;
        }
    };

    xhr.open("POST", "../../controllers/business/cartControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=add&productId=" + encodeURIComponent(productId) +
        "&quantity=" + encodeURIComponent(document.getElementById("qty" + productId).value);

    xhr.send(data);
}
